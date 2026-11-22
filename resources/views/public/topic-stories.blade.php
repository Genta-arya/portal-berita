@php
    $storyTopics = $trendingTopics->map(fn ($channel) => [
        'id' => $channel->id,
        'name' => $channel->name,
        'articles' => $channel->articles->map(fn ($item) => [
            'title' => $item->title,
            'summary' => \Illuminate\Support\Str::limit(strip_tags($item->summary ?? ''), 280),
            'image' => $item->hero_media_id ? url('/media/'.$item->hero_media_id.'/1280') : null,
            'alt' => $item->hero_alt ?? '',
            'url' => $item->url,
            'time' => $item->published_at?->locale('id')->diffForHumans(),
        ])->values(),
    ])->values();
@endphp
<script type="application/json" id="topic-story-data">{!! json_encode($storyTopics, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<dialog class="topic-stories" aria-labelledby="story-topic-title" data-story-dialog>
    <div class="topic-stories-stage">
        <div class="topic-stories-backdrop" aria-hidden="true"></div>
        <div class="topic-stories-progress" aria-label="Progres story"></div>
        <header class="topic-stories-header">
            <span class="topic-stories-avatar" aria-hidden="true"><img data-story-avatar data-no-skeleton alt=""><span data-story-initials>KR</span></span>
            <div><strong id="story-topic-title"></strong><small data-story-time></small></div>
            <button type="button" data-story-close aria-label="Tutup story"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
        </header>
        <div class="topic-stories-body">
            <article class="topic-stories-card">
                <a class="topic-stories-visual" data-story-image-link>
                    <img data-story-image data-no-skeleton alt="">
                    <span class="topic-stories-loader" role="status" aria-label="Memuat gambar"><span class="topic-stories-spinner" aria-hidden="true"></span></span>
                    <span class="topic-stories-brand"><img src="/images/logo-kapuasraya.png" alt="KAPUAS RAYA" data-no-skeleton width="150" height="38"></span>
                    <h2 data-story-title></h2>
                </a>
                <p data-story-summary></p>
            </article>
        </div>
        <footer class="topic-stories-footer">
            <a data-story-link>Baca selengkapnya <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
        </footer>
        <button type="button" class="topic-stories-nav topic-stories-prev" data-story-prev aria-label="Story sebelumnya"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg></button>
        <button type="button" class="topic-stories-nav topic-stories-next" data-story-next aria-label="Story berikutnya"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg></button>
    </div>
</dialog>
