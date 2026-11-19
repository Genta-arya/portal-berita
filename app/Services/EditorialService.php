<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use App\Policies\ArticlePolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EditorialService
{
    public function save(Article $article, User $actor, array $data, bool $autosave = false): Article
    {
        return DB::transaction(function () use ($article, $actor, $data, $autosave) {
            $a = Article::lockForUpdate()->findOrFail($article->id);
            abort_unless((new ArticlePolicy)->update($actor, $a), 403);
            abort_if($a->version !== (int) $data['version'], 409, 'Versi telah berubah. Salin perubahan Anda lalu muat ulang.');
            abort_if($autosave && ! in_array($a->status, ['draft', 'needs_revision']), 403);
            $a->fill(collect($data)->except(['version', 'tags', 'note'])->all());
            $a->body = app(ContentSanitizer::class)->clean($data['body'] ?? '');
            if (in_array($a->status, ['approved', 'scheduled'])) {
                $a->status = 'draft';
                $a->approved_version = null;
                $a->scheduled_at = null;
            }
            $a->version++;
            if ($a->status === 'published') {
                $this->validatePublication($a);
            }
            $a->save();
            $tags = [];
            foreach (array_filter(array_map('trim', explode(',', $data['tags'] ?? ''))) as $name) {
                $slug = Str::slug($name);
                if ($slug !== '') {
                    $tags[] = Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
                }
            }
            $a->tags()->sync($tags);
            $ids = [];
            preg_match_all('~/media/([0-9]+)/(?:480|1280)~', $a->body, $matches);
            $ids = array_unique(array_filter([$a->hero_media_id, ...($matches[1] ?? [])]));
            foreach ($ids as $id) {
                abort_unless(app(MediaService::class)->canUse((int) $id, $actor), 403);
            }
            DB::table('media_usages')->where('context_type', 'article')->where('context_id', $a->id)->delete();
            foreach ($ids as $id) {
                DB::table('media_usages')->insert(['media_id' => $id, 'context_type' => 'article', 'context_id' => $a->id, 'kind' => $id == $a->hero_media_id ? 'hero' : 'body', 'alt' => $a->hero_alt, 'credit' => $a->hero_credit, 'caption' => $a->hero_caption]);
            }
            $this->revision($a, $actor, $data['note'] ?? 'Simpan artikel');

            return $a;
        });
    }

    public function transition(Article $article, ?User $actor, string $action, int $version, ?string $note = null, ?string $schedule = null): Article
    {
        return DB::transaction(function () use ($article, $actor, $action, $version, $note, $schedule) {
            $a = Article::lockForUpdate()->findOrFail($article->id);
            abort_if($a->version !== $version, 409, 'Versi berubah. Muat ulang.');
            if ($actor) {
                abort_unless((new ArticlePolicy)->view($actor, $a), 403);
            }
            abort_unless($actor || $action === 'due', 403);
            abort_if($actor && $action === 'due', 403, 'Publikasi otomatis hanya melalui scheduler.');
            if (! in_array($action, ['submit', 'due'])) {
                abort_unless($actor && (new ArticlePolicy)->manage($actor), 403);
            }
            $allowed = ['submit' => ['draft', 'needs_revision'], 'revise' => ['in_review'], 'approve' => ['in_review'], 'publish' => ['approved'], 'schedule' => ['approved'], 'cancel' => ['scheduled'], 'due' => ['scheduled'], 'withdraw' => ['published'], 'reopen' => ['withdrawn']];
            abort_unless(isset($allowed[$action]) && in_array($a->status, $allowed[$action]), 422, 'Transisi tidak diizinkan.');
            if (in_array($action, ['revise', 'withdraw']) && ! trim($note ?? '')) {
                throw ValidationException::withMessages(['note' => 'Catatan wajib diisi.']);
            }
            if (in_array($action, ['publish', 'schedule', 'due'])) {
                $this->validatePublication($a);
                abort_unless($a->approved_version === $a->version, 422, 'Versi belum disetujui.');
            }
            if ($action === 'due') {
                abort_unless($a->scheduled_at && $a->scheduled_at->lte(now()), 422);
            }
            if ($action === 'schedule') {
                $at = Carbon::parse($schedule, 'Asia/Jakarta')->utc();
                if ($at->lte(now())) {
                    throw ValidationException::withMessages(['scheduled_at' => 'Jadwal harus di masa depan.']);
                }
                $a->scheduled_at = $at;
            }
            $a->status = match ($action) {
                'submit' => 'in_review','revise' => 'needs_revision','approve','cancel' => 'approved','schedule' => 'scheduled','publish','due' => 'published','withdraw' => 'withdrawn','reopen' => 'draft'
            };
            if ($action === 'approve') {
                $a->editor_id = $actor->id;
                $a->approved_version = $a->version;
            }
            if (in_array($action, ['publish', 'due'])) {
                $a->published_at ??= now();
                $a->withdrawn_at = null;
            }
            if ($action !== 'schedule') {
                $a->scheduled_at = null;
            }
            if ($action === 'withdraw') {
                $a->withdrawn_at = now();
                $a->withdrawal_reason = $note;
            }
            if ($action === 'reopen') {
                $a->approved_version = null;
            }
            // Workflow changes invalidate stale forms while keeping approval tied to the new version.
            $a->version++;
            if (in_array($action, ['approve', 'schedule', 'cancel'])) {
                $a->approved_version = $a->version;
            }
            $a->save();
            if ($note && $actor) {
                DB::table('editorial_notes')->insert(['article_id' => $a->id, 'user_id' => $actor->id, 'body' => $note, 'created_at' => now(), 'updated_at' => now()]);
            }
            if ($actor) {
                $this->revision($a, $actor, $note ?? $action);
            }
            $this->audit($actor, $action, 'article', $a->id);

            return $a;
        });
    }

    public function validatePublication(Article $a): void
    {
        $valid = trim($a->title) && trim($a->slug) && trim($a->summary ?? '') && mb_strlen(trim(strip_tags($a->body ?? ''))) >= 20
            && $a->category?->active && $a->author && $a->hero_media_id && trim($a->hero_alt ?? '') && trim($a->hero_credit ?? '');
        if (! $valid) {
            throw ValidationException::withMessages(['article' => 'Lengkapi judul, ringkasan, isi (20 karakter), kanal aktif, gambar utama, alt dan kredit.']);
        }
    }

    public function revision(Article $a, User $actor, string $note): void
    {
        $snapshot = $a->getAttributes();
        $snapshot['tags'] = $a->tags()->pluck('tags.id')->all();
        $r = $a->revisions()->create(['user_id' => $actor->id, 'version' => $a->version, 'snapshot' => $snapshot, 'note' => $note]);
        foreach (DB::table('media_usages')->where('context_type', 'article')->where('context_id', $a->id)->get() as $usage) {
            DB::table('media_usages')->insert(['media_id' => $usage->media_id, 'context_type' => 'revision', 'context_id' => $r->id, 'kind' => $usage->kind, 'alt' => $usage->alt, 'caption' => $usage->caption, 'credit' => $usage->credit]);
        }
        $this->audit($actor, 'revision', 'article', $a->id);
    }

    public function audit(?User $actor, string $action, string $type, ?int $id): void
    {
        DB::table('audit_logs')->insert(['user_id' => $actor?->id, 'action' => $action, 'target_type' => $type, 'target_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
    }
}
