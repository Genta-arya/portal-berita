<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Services\ContentSanitizer;
use App\Services\EditorialService;
use App\Services\MediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    private function access(Request $r, string $module): void
    {
        abort_unless(in_array($module, ['users', 'categories', 'tags', 'pages', 'settings', 'banners', 'breaking', 'featured', 'audit']), 404);
        abort_unless($r->user()->role === 'admin' || (in_array($module, ['breaking', 'featured']) && $r->user()->role === 'editor'), 403);
    }

    public function index(Request $r, string $module)
    {
        $this->access($r, $module);
        $table = match ($module) {
            'users' => 'users','categories' => 'categories','tags' => 'tags','pages' => 'pages','settings' => 'site_settings','banners' => 'ad_banners','breaking' => 'breaking_news','featured' => 'featured_placements','audit' => 'audit_logs'
        };
        $rows = DB::table($table)->orderByDesc($module === 'settings' ? 'key' : 'id')->limit(100)->get();

        return view('admin.manage', compact('module', 'rows'));
    }

    public function store(Request $r, string $module, EditorialService $service)
    {
        $this->access($r, $module);
        abort_if($module === 'audit', 403);
        $id = $r->integer('id');
        DB::transaction(function () use ($r, $module, $id, $service) {
            $table = '';
            $data = [];
            if ($module === 'users') {
                $table = 'users';
                $data = $r->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users')->ignore($id)], 'role' => 'required|in:admin,editor,penulis', 'active' => 'required|boolean', 'password' => ($id ? 'nullable' : 'required').'|string|min:12', 'bio' => 'nullable|string|max:2000']);
                // Serialise account changes by locking the complete account set in ID order.
                $users = User::orderBy('id')->lockForUpdate()->get();
                $user = $id ? $users->firstWhere('id', $id) : new User;
                abort_unless($user, 404);
                if ($user->role === 'admin' && $user->active && ($data['role'] !== 'admin' || ! $data['active'])) {
                    abort_if($users->where('role', 'admin')->where('active', true)->count() <= 1, 422, 'Admin aktif terakhir harus dipertahankan.');
                }
                $changed = $id && ($user->role !== $data['role'] || $user->active !== (bool) $data['active'] || ! empty($data['password']));
                foreach (['name', 'email', 'role', 'active', 'bio'] as $key) {
                    $user->$key = $data[$key] ?? null;
                }
                if (! empty($data['password'])) {
                    $user->password = $data['password'];
                }
                $user->slug ??= Str::slug($user->name).'-'.Str::lower(Str::random(8));
                if ($changed) {
                    $user->session_version++;
                    $user->remember_token = null;
                }
                $user->save();
                $service->audit($r->user(), 'account_update', 'user', $user->id);

                return;
            }
            if (in_array($module, ['categories', 'tags'])) {
                $table = $module;
                $data = $r->validate(['name' => 'required|string|max:255', 'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique($table)->ignore($id)], 'active' => 'required|boolean', 'description' => 'nullable|string|max:2000', 'position' => 'nullable|integer|min:0']);
                if ($module === 'tags') {
                    unset($data['description'],$data['position']);
                }
            }
            if ($module === 'pages') {
                $table = 'pages';
                $data = $r->validate(['title' => 'required|string|max:255', 'slug' => ['required', 'alpha_dash', Rule::unique('pages')->ignore($id)], 'body' => 'required|string|max:100000', 'active' => 'required|boolean']);
                $data['body'] = app(ContentSanitizer::class)->clean($data['body']);
            }
            if ($module === 'settings') {
                $data = $r->validate(['name' => 'required|string|max:100', 'description' => 'nullable|string|max:500', 'contact_email' => 'nullable|email', 'whatsapp' => 'nullable|regex:/^[0-9]{8,16}$/', 'facebook' => 'nullable|url:http,https', 'x' => 'nullable|url:http,https', 'logo_media_id' => 'nullable|exists:media,id', 'favicon_media_id' => 'nullable|exists:media,id']);
                foreach ($data as $key => $value) {
                    DB::table('site_settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now(), 'created_at' => now()]);
                }
                $service->audit($r->user(), 'settings_update', 'settings', null);

                return;
            }
            if (in_array($module, ['banners', 'breaking'])) {
                $table = $module === 'banners' ? 'ad_banners' : 'breaking_news';
                $rules = ['starts_at' => 'required|date', 'ends_at' => 'required|date|after:starts_at', 'active' => 'required|boolean', 'url' => ($module === 'banners' ? 'required' : 'nullable').'|url:http,https|max:255'];
                $rules += $module === 'banners' ? ['slot' => 'required|in:home_top,list_middle,sidebar,article_bottom', 'media_id' => 'required|exists:media,id', 'alt' => 'required|string|max:255'] : ['text' => 'required|string|max:255'];
                $data = $r->validate($rules);
                $data['starts_at'] = Carbon::parse($data['starts_at'], 'Asia/Jakarta')->utc();
                $data['ends_at'] = Carbon::parse($data['ends_at'], 'Asia/Jakarta')->utc();
                if ($module === 'banners') {
                    DB::table('ad_slots')->where('name', $data['slot'])->lockForUpdate()->first();
                    abort_unless(app(MediaService::class)->canUse($data['media_id'], $r->user()), 403);
                    abort_if($data['active'] && DB::table('ad_banners')->where('slot', $data['slot'])->where('id', '!=', $id)->where('active', true)->where('starts_at', '<', $data['ends_at'])->where('ends_at', '>', $data['starts_at'])->exists(), 422, 'Jadwal banner pada slot ini bertumpuk.');
                }
            }
            if ($module === 'featured') {
                $table = 'featured_placements';
                $data = $r->validate(['article_id' => 'required|exists:articles,id', 'location' => 'required|in:headline,pick', 'position' => 'required|integer|min:1|max:20']);
                abort_unless(Article::published()->whereKey($data['article_id'])->exists(), 422, 'Pilih artikel terbit.');
                DB::table($table)->where('location', $data['location'])->where('position', $data['position'])->delete();
            }
            $data['updated_at'] = now();
            if ($id) {
                abort_unless(DB::table($table)->where('id', $id)->exists(), 404);
                DB::table($table)->where('id', $id)->update($data);
            } else {
                $data['created_at'] = now();
                $id = DB::table($table)->insertGetId($data);
            }
            if ($module === 'pages') {
                DB::table('media_usages')->where('context_type', 'page')->where('context_id', $id)->delete();
                // Information pages use text only; remove images rather than expose draft media.
                DB::table('pages')->where('id', $id)->update(['body' => strip_tags($data['body'], '<p><h2><h3><strong><em><ul><ol><li><blockquote><a><br>')]);
            }
            $service->audit($r->user(), 'manage_'.$module, $module, $id);
        });
        Cache::forget('site:navigation');
        Cache::forget('site:settings');

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function destroy(Request $r, string $module, int $id, EditorialService $service)
    {
        $this->access($r, $module);
        abort_unless(in_array($module, ['categories', 'tags', 'pages', 'banners', 'breaking', 'featured']), 403);
        $table = match ($module) {
            'banners' => 'ad_banners','breaking' => 'breaking_news','featured' => 'featured_placements',default => $module
        };
        DB::transaction(function () use ($table, $id, $module, $service, $r) {
            DB::table($table)->where('id', $id)->lockForUpdate()->first();
            if ($module === 'categories') {
                abort_if(Article::withTrashed()->where('category_id', $id)->exists(), 422, 'Kanal masih digunakan. Nonaktifkan atau pindahkan artikel.');
            }
            if ($module === 'tags') {
                abort_if(DB::table('article_tag')->where('tag_id', $id)->exists(), 422, 'Tag masih digunakan.');
            }
            DB::table($table)->where('id',$id)->delete();
            $service->audit($r->user(),'delete_'.$module,$module,$id);
        });
        Cache::forget('site:navigation');

        return back()->with('success','Data dihapus.');
    }
}
