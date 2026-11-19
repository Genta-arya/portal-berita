<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('media');
        if ($r->user()->role === 'penulis') {
            $q->where(function ($q) use ($r) {
                $q->where('user_id', $r->user()->id)->orWhereIn('id', DB::table('media_usages as m')->join('articles as a', 'a.id', '=', 'm.context_id')->where('context_type', 'article')->where('a.status', 'published')->whereNull('a.deleted_at')->select('media_id'));
            });
        }
        if ($r->filled('q')) {
            $q->where('name', 'like', '%'.mb_substr($r->string('q'), 0, 100).'%');
        }
        $media = $q->latest('id')->paginate(24)->withQueryString();
        if ($r->expectsJson()) {
            return response()->json($media);
        }

        return view('admin.media', compact('media'));
    }

    public function store(Request $r)
    {
        $r->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000', 'alt' => 'required|string|max:255', 'credit' => 'required|string|max:255', 'caption' => 'nullable|string|max:1000']);
        $file = $r->file('image');
        $bytes = file_get_contents($file->getRealPath());
        $img = @imagecreatefromstring($bytes);
        abort_unless($img, 422, 'Gambar tidak dapat dibaca.');
        $dir = 'editorial/'.Str::uuid();
        $variants = [];
        foreach ([480, 1280] as $width) {
            $w = min($width, imagesx($img));
            $h = (int) round(imagesy($img) * $w / imagesx($img));
            $scaled = imagecreatetruecolor($w, $h);
            imagefill($scaled, 0, 0, imagecolorallocate($scaled, 255, 255, 255));
            imagecopyresampled($scaled, $img, 0, 0, 0, 0, $w, $h, imagesx($img), imagesy($img));
            ob_start();
            imagewebp($scaled, null, 82);
            $out = ob_get_clean();
            imagedestroy($scaled);
            $path = $dir.'/'.$width.'.webp';
            abort_unless(Storage::disk('local')->put($path, $out), 500);
            $variants[$width] = ['path' => $path, 'width' => $w, 'height' => $h];
        }
        $original = $dir.'/original';
        Storage::disk('local')->put($original, $bytes);
        $id = DB::table('media')->insertGetId(['user_id' => $r->user()->id, 'name' => $file->getClientOriginalName(), 'path' => $original, 'variants' => json_encode($variants), 'mime' => $file->getMimeType(), 'size' => $file->getSize(), 'width' => imagesx($img), 'height' => imagesy($img), 'alt' => $r->alt, 'credit' => $r->credit, 'caption' => $r->caption, 'created_at' => now(), 'updated_at' => now()]);
        imagedestroy($img);

        return $r->expectsJson() ? response()->json(['id' => $id], 201) : back()->with('success', 'Gambar diunggah. ID: '.$id);
    }

    public function show(Request $r, int $media, string $variant, MediaService $service)
    {
        abort_unless(in_array($variant, ['480', '1280', 'original']), 404);
        $row = DB::table('media')->find($media);
        abort_unless($row, 404);
        $user = $r->user();
        $internal = $user && $user->active && $r->session()->get('session_version') === $user->session_version && ($row->user_id === $user->id || in_array($user->role, ['admin', 'editor']));
        abort_unless($internal || ($variant !== 'original' && $service->public($media)), 404);
        $variants = json_decode($row->variants, true);
        $path = $variant === 'original' ? $row->path : ($variants[$variant]['path'] ?? null);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), ['Content-Type' => $variant === 'original' ? $row->mime : 'image/webp', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function update(Request $r, int $media)
    {
        $row = DB::table('media')->find($media);
        abort_unless($row, 404);
        abort_unless($row->user_id === $r->user()->id || in_array($r->user()->role, ['editor', 'admin']), 403);
        $data = $r->validate(['alt' => 'required|string|max:255', 'credit' => 'required|string|max:255', 'caption' => 'nullable|string|max:1000']);
        DB::table('media')->where('id', $media)->update([...$data, 'updated_at' => now()]);

        return back()->with('success', 'Metadata media disimpan; metadata artikel tetap mengikuti pemakaiannya.');
    }

    public function destroy(Request $r, int $media)
    {
        DB::transaction(function () use ($r, $media) {
            $row = DB::table('media')->lockForUpdate()->find($media);
            abort_unless($row, 404);
            abort_unless($row->user_id === $r->user()->id || in_array($r->user()->role, ['editor', 'admin']), 403);
            abort_if(DB::table('media_usages')->where('media_id', $media)->exists() || DB::table('ad_banners')->where('media_id', $media)->exists() || DB::table('site_settings')->whereIn('key', ['logo_media_id', 'favicon_media_id'])->where('value', (string) $media)->exists(), 422, 'Media masih digunakan.');
            DB::table('media')->where('id',$media)->delete();
            Storage::disk('local')->deleteDirectory(dirname($row->path));
        });

        return back()->with('success','Media dihapus.');
    }
}
