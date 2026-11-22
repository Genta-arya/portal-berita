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
        abort_unless(in_array($module, ['users', 'categories', 'tags', 'pages', 'settings', 'banners', 'breaking', 'featured', 'trending', 'audit']), 404);
        abort_unless($r->user()->isAdministrator() || (in_array($module, ['breaking', 'featured', 'trending']) && $r->user()->role === 'editor'), 403);
    }

    public function index(Request $r, string $module)
    {
        $this->access($r, $module);
        $table = match ($module) {
            'users' => 'users','categories' => 'categories','tags' => 'tags','pages' => 'pages','settings' => 'site_settings','banners' => 'ad_banners','breaking' => 'breaking_news','featured' => 'featured_placements','trending'=>'trending_topics','audit' => 'audit_logs'
        };
        $r->validate(['q' => 'nullable|string|max:100', 'state' => 'nullable|in:active,inactive']);
        $base = DB::table($table);
        if ($module === 'users') $base->where('role', '!=', 'developer');
        $total = (clone $base)->count();
        $activeCount = in_array($module, ['users', 'categories', 'tags', 'pages', 'banners', 'breaking', 'trending'])
            ? (clone $base)->where('active', true)->count() : null;
        $query = clone $base;
        if ($r->filled('q')) {
            $columns = match ($module) {
                'users' => ['name', 'email'], 'categories', 'tags' => ['name', 'slug'],
                'pages' => ['title', 'slug'], 'breaking' => ['text'], 'banners' => ['alt', 'slot'],
                'audit' => ['action', 'target_type'], 'featured' => ['location'], 'trending' => ['category_id'], default => ['key', 'value']
            };
            $query->where(function ($query) use ($columns, $r, $module) {
                if ($module === 'trending') {
                    $query->whereIn('category_id', DB::table('categories')->where('name','like','%'.$r->string('q').'%')->select('id'));
                    return;
                }
                foreach ($columns as $column) $query->orWhere($column, 'like', '%'.$r->string('q').'%');
            });
        }
        if ($activeCount !== null && $r->filled('state')) $query->where('active', $r->state === 'active');
        if (in_array($module, ['featured','trending'])) {
            if ($module === 'featured') $query->orderBy('location');
            $query->orderBy('position')->orderBy('id');
        } else $query->orderByDesc($module === 'settings' ? 'key' : 'id');
        $rows = $query->paginate(12)->withQueryString();
        $publishedArticles = $module === 'featured' ? Article::published()->latest('published_at')->get(['id', 'title']) : collect();
        $mediaOptions = in_array($module, ['banners', 'settings']) ? DB::table('media')->latest('id')->get(['id', 'name']) : collect();
        $articleTitles = $module === 'featured' ? Article::whereIn('id', $rows->pluck('article_id'))->pluck('title', 'id') : collect();
        $actorNames = $module === 'audit' ? User::whereIn('id', $rows->pluck('user_id')->filter())->pluck('name', 'id') : collect();
        $topicOptions = $module === 'trending' ? DB::table('categories')->orderBy('name')->get(['id','name','active']) : collect();
        $topicNames = $topicOptions->pluck('name','id');
        $siteSettings = $module === 'settings' ? DB::table('site_settings')->pluck('value', 'key') : collect();

        return view('admin.manage', compact('module', 'rows', 'total', 'activeCount', 'publishedArticles', 'mediaOptions', 'articleTitles', 'actorNames', 'siteSettings', 'topicOptions', 'topicNames'));
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
                abort_if($r->input('role') === 'developer' || ($id && User::whereKey($id)->where('role','developer')->exists()), 403, 'Akun developer tidak dikelola melalui daftar pengguna.');
                $data = $r->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', Rule::unique('users')->ignore($id)], 'role' => 'required|in:developer,admin,editor,penulis', 'active' => 'required|boolean', 'password' => ($id ? 'nullable' : 'required').'|string|min:8|confirmed', 'bio' => 'nullable|string|max:2000', 'avatar'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=4000,max_height=4000','remove_avatar'=>'sometimes|boolean']);
                // Serialise account changes by locking the complete account set in ID order.
                $users = User::orderBy('id')->lockForUpdate()->get();
                $user = $id ? $users->firstWhere('id', $id) : new User;
                abort_unless($user, 404);
                $actor = $users->firstWhere('id', $r->user()->id);
                abort_unless($actor && $actor->isAdministrator(), 403);
                if (! $actor->isDeveloper()) {
                    abort_if(in_array($data['role'], ['developer','admin'], true), 403, 'Hanya developer yang dapat membuat atau menetapkan admin.');
                    abort_if($id && in_array($user->role, ['developer','admin'], true), 403, 'Akun ini hanya dapat dikelola developer.');
                }
                if ($user->role === 'developer' && $user->active && ($data['role'] !== 'developer' || ! $data['active'])) {
                    abort_if($users->where('role','developer')->where('active',true)->count() <= 1, 422, 'Developer aktif terakhir harus dipertahankan.');
                }
                if ($user->role === 'admin' && $user->active && ($data['role'] !== 'admin' || ! $data['active'])) {
                    abort_if($users->where('role', 'admin')->where('active', true)->count() <= 1, 422, 'Admin aktif terakhir harus dipertahankan.');
                }
                $changed = $id && ($user->role !== $data['role'] || $user->active !== (bool) $data['active'] || $user->email !== $data['email'] || ! empty($data['password']));
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
                app(\App\Services\AvatarService::class)->update($user,$r->file('avatar'),$r->boolean('remove_avatar'));
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
                $data['body'] = app(ContentSanitizer::class)->clean($data['body'], true);
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
            if ($module === 'trending') {
                $table = 'trending_topics';
                $data = $r->validate([
                    'category_id' => ['required','exists:categories,id',Rule::unique('trending_topics')->ignore($id)],
                    'position' => 'required|integer|min:1|max:99', 'active' => 'required|boolean',
                ]);
            }
            if ($module === 'featured') {
                $table = 'featured_placements';
                $data = $r->validate(['article_id' => 'required|exists:articles,id', 'location' => 'required|in:headline,pick', 'position' => 'required|integer|min:1|max:20']);
                abort_unless(Article::published()->whereKey($data['article_id'])->exists(), 422, 'Pilih artikel terbit.');
                // Retain the edited placement while replacing an occupied destination slot.
                DB::table($table)->where('location', $data['location'])->where('position', $data['position'])->when($id, fn ($q) => $q->where('id','!=',$id))->delete();
                if ($data['location'] === 'headline') DB::table('site_settings')->updateOrInsert(['key'=>'home_headlines_managed'],['value'=>'1','updated_at'=>now(),'created_at'=>now()]);
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
                // Page HTML is sanitized above; preserve its approved layout classes.
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
        abort_unless(in_array($module, ['categories', 'tags', 'pages', 'banners', 'breaking', 'featured', 'trending']), 403);
        $table = match ($module) {
            'banners' => 'ad_banners','breaking' => 'breaking_news','featured' => 'featured_placements','trending'=>'trending_topics',default => $module
        };
        DB::transaction(function () use ($table, $id, $module, $service, $r) {
            DB::table($table)->where('id', $id)->lockForUpdate()->first();
            if ($module === 'categories') {
                abort_if(Article::withTrashed()->where('category_id', $id)->exists(), 422, 'Topik masih digunakan. Nonaktifkan atau pindahkan artikel.');
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
