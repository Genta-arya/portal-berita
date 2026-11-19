<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MediaService
{
    public function public(int $id): bool
    {
        if (DB::table('media_usages as m')->join('articles as a', 'a.id', '=', 'm.context_id')->where('m.media_id', $id)->where('m.context_type', 'article')->where('a.status', 'published')->whereNull('a.deleted_at')->where('a.published_at', '<=', now())->exists()) {
            return true;
        }
        if (DB::table('ad_banners')->where('media_id', $id)->where('active', true)->where('starts_at', '<=', now())->where('ends_at', '>', now())->exists()) {
            return true;
        }

        return DB::table('site_settings')->whereIn('key', ['logo_media_id', 'favicon_media_id'])->where('value', (string) $id)->exists();
    }

    public function canUse(int $id, User $user): bool
    {
        $media = DB::table('media')->find($id);

        return $media && $user->active && ($media->user_id === $user->id || in_array($user->role, ['admin', 'editor']) || $this->public($id));
    }
}
