@php
    $banner = \Illuminate\Support\Facades\Cache::remember('site:banner_'.$slot, 300, function () use ($slot) {
        return \Illuminate\Support\Facades\DB::table('ad_banners')
            ->where('slot', $slot)
            ->where('active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->first();
    });
@endphp
@if($banner)<aside class="ad"><span>Iklan</span><a href="{{ $banner->url }}" target="_blank" rel="sponsored noopener noreferrer"><img src="/media/{{ $banner->media_id }}/1280" alt="{{ $banner->alt }}" width="1280" height="320" loading="lazy"></a></aside>@endif
