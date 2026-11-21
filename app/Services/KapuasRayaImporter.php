<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KapuasRayaImporter
{
    private array $images = [];

    private array $mediaIds = [];

    private array $warnings = [];

    private string $root;

    private User $owner;

    public function run(string $root, User $owner): array
    {
        $this->root = $root;
        $this->owner = $owner;
        $counts = [];
        $this->images = [];
        $this->mediaIds = [];
        $this->warnings = [];
        $beforeImages = DB::table('source_imports')->where('source', 'kapuasraya.com')->where('type', 'image')->count();
        foreach ($this->read('image-index') as $image) {
            $this->images[$image['url']] = $image;
        }
        foreach (['categories' => Category::class, 'tags' => Tag::class] as $kind => $class) {
            $counts[$kind] = 0;
            foreach ($this->read($kind) as $row) {
                if ($this->mapped($kind, $row['id'])) {
                    continue;
                }
                $data = ['name' => $this->text($row['name']), 'active' => $kind === 'categories' ? ($row['count'] > 0) : true];
                if ($kind === 'categories') {
                    $data['description'] = $this->text($row['description'] ?? '');
                }
                $entity = $class::firstOrCreate(['slug' => $row['slug']], $data);
                $this->map($kind, $row['id'], $entity->id, $row);
                $counts[$kind]++;
            }
        }
        $counts['media'] = 0;
        foreach ($this->read('media') as $row) {
            $id = $this->importImage($row['source_url'], $row['alt_text'] ?? '', $this->text($row['caption']['rendered'] ?? ''));
            if ($id) {
                $this->mediaIds[$row['id']] = $id;
                $counts['media']++;
            }
        }
        $counts['articles'] = 0;
        foreach ($this->read('posts') as $row) {
            if ($this->mapped('posts', $row['id'])) {
                continue;
            }
            DB::transaction(function () use ($row, &$counts) {
                $body = $this->rewriteImages($row['content']['rendered'] ?? '');
                $hero = $this->mediaIds[$row['featured_media'] ?? 0] ?? null;
                $a = new Article(['title' => $this->text($row['title']['rendered']), 'slug' => $row['slug'] ?: 'impor-'.$row['id'], 'summary' => Str::limit($this->text($row['excerpt']['rendered'] ?? ''), 900, ''), 'body' => app(ContentSanitizer::class)->clean($body), 'category_id' => $this->mapped('categories', $row['categories'][0] ?? 0), 'hero_media_id' => $hero, 'hero_alt' => $this->text($row['title']['rendered']), 'hero_credit' => null]);
                $a->author_id = $this->owner->id;
                $a->status = 'draft';
                $a->save();
                $tags = [];
                foreach ($row['tags'] ?? [] as $id) {
                    if ($tag = $this->mapped('tags', $id)) {
                        $tags[] = $tag;
                    }
                }$a->tags()->sync($tags);
                preg_match_all('~/media/([0-9]+)/(?:480|1280)~', $a->body, $matches);
                foreach (array_unique(array_filter([$hero, ...($matches[1] ?? [])])) as $id) {
                    DB::table('media_usages')->insert(['media_id' => $id, 'context_type' => 'article', 'context_id' => $a->id, 'kind' => $id == $hero ? 'hero' : 'body']);
                }
                app(EditorialService::class)->revision($a, $this->owner, 'Impor dari '.$row['link'].'; tanggal sumber '.$row['date'].'; periksa sumber, atribusi, kredit gambar dan isi sebelum publikasi.');
                $this->map('posts', $row['id'], $a->id, ['url' => $row['link'], 'date' => $row['date'], 'date_gmt' => $row['date_gmt'], 'modified' => $row['modified'], 'author' => $row['author'], 'categories' => $row['categories'], 'tags' => $row['tags'], 'slug' => $row['slug']]);
                $counts['articles']++;
            });
        }
        $counts['pages'] = 0;
        foreach ($this->read('pages') as $row) {
            if ($this->mapped('pages', $row['id'])) {
                continue;
            }
            DB::transaction(function () use ($row, &$counts) {
                $slug = DB::table('pages')->where('slug', $row['slug'])->exists() ? $row['slug'].'-impor-'.$row['id'] : $row['slug'];
                $body = strip_tags(app(ContentSanitizer::class)->clean($row['content']['rendered']), '<p><h2><h3><strong><em><ul><ol><li><blockquote><a><br>');
                $id = DB::table('pages')->insertGetId(['title' => $this->text($row['title']['rendered']), 'slug' => $slug, 'body' => $body, 'active' => false, 'created_at' => now(), 'updated_at' => now()]);
                $this->map('pages', $row['id'], $id, ['url' => $row['link'], 'date' => $row['date']]);
                $counts['pages']++;
            });
        }
        $counts['media'] = DB::table('source_imports')->where('source', 'kapuasraya.com')->where('type', 'image')->count() - $beforeImages;

        return ['created' => $counts, 'warnings' => $this->warnings, 'status' => 'Artikel draft; halaman nonaktif; tanggal dan URL asli ada pada source_imports dan arsip.'];
    }

    private function read(string $name): array
    {
        $raw = file_get_contents($this->root.'/'.$name.'.json');
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    private function text(string $html): string
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function mapped(string $type, int|string $id): ?int
    {
        return DB::table('source_imports')->where(['source' => 'kapuasraya.com', 'type' => $type, 'source_id' => (string) $id])->value('local_id');
    }

    private function map(string $type, int|string $id, int $local, array $meta = []): void
    {
        DB::table('source_imports')->insert(['source' => 'kapuasraya.com', 'type' => $type, 'source_id' => (string) $id, 'local_id' => $local, 'metadata' => json_encode($meta), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function importImage(string $url, string $alt = '', string $caption = ''): ?int
    {
        if ($id = $this->mapped('image', $url)) {
            return $id;
        }
        if (! isset($this->images[$url])) {
            $this->warnings[] = 'Gambar tidak diarsipkan: '.$url;

            return null;
        }
        $entry = $this->images[$url];
        $path = realpath($this->root.'/'.$entry['file']);
        $base = realpath($this->root.'/images');
        if (! $path || ! $base || ! str_starts_with(strtolower($path), strtolower($base.DIRECTORY_SEPARATOR))) {
            throw new \RuntimeException('Path gambar arsip tidak valid.');
        }
        if (hash_file('sha256', $path) !== $entry['sha256']) {
            throw new \RuntimeException('Checksum gambar tidak sesuai.');
        }
        $bytes = file_get_contents($path);
        $size = @getimagesizefromstring($bytes);
        if (! $size || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp']) || strlen($bytes) > 5 * 1024 * 1024 || $size[0] > 8000 || $size[1] > 8000) {
            $this->warnings[] = 'Gambar di luar batas unggahan: '.$url;

            return null;
        }
        $img = @imagecreatefromstring($bytes);
        if (! $img) {
            $this->warnings[] = 'Gambar gagal didekode: '.$url;

            return null;
        }
        $dir = 'editorial/import-'.Str::uuid();
        $variants = [];
        foreach ([480, 1280] as $target) {
            $w = min($target, $size[0]);
            $h = (int) round($size[1] * $w / $size[0]);
            $scaled = imagecreatetruecolor($w, $h);
            imagefill($scaled, 0, 0, imagecolorallocate($scaled, 255, 255, 255));
            imagecopyresampled($scaled, $img, 0, 0, 0, 0, $w, $h, $size[0], $size[1]);
            ob_start();
            imagewebp($scaled, null, 82);
            $out = ob_get_clean();
            imagedestroy($scaled);
            $variant = $dir.'/'.$target.'.webp';
            Storage::disk('local')->put($variant, $out);
            $variants[$target] = ['path' => $variant, 'width' => $w, 'height' => $h];
        }
        Storage::disk('local')->put($dir.'/original', $bytes);
        imagedestroy($img);

        return DB::transaction(function () use ($dir, $variants, $url, $alt, $caption, $size, $bytes) {
            $id = DB::table('media')->insertGetId(['user_id' => $this->owner->id, 'name' => Str::limit(basename(parse_url($url, PHP_URL_PATH)), 240, ''), 'path' => $dir.'/original', 'variants' => json_encode($variants), 'mime' => $size['mime'], 'size' => strlen($bytes), 'width' => $size[0], 'height' => $size[1], 'alt' => Str::limit($alt, 255, ''), 'caption' => $caption, 'credit' => null, 'created_at' => now(), 'updated_at' => now()]);
            $this->map('image', $url, $id);

            return $id;
        });
    }

    private function rewriteImages(string $html): string
    {
        $doc = new \DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        foreach (iterator_to_array($doc->getElementsByTagName('img')) as $img) {
            $url = html_entity_decode($img->getAttribute('src'));
            $id = $this->importImage($url,$img->getAttribute('alt'));
            if ($id) {
                $img->setAttribute('src','/media/'.$id.'/1280');
                $img->removeAttribute('srcset');
            } else {
                $img->parentNode->removeChild($img);
            }
        }

        $wrapper = $doc->getElementsByTagName('div')->item(0);

        return $wrapper ? implode('', array_map(fn ($node) => $doc->saveHTML($node), iterator_to_array($wrapper->childNodes))) : '';
    }
}
