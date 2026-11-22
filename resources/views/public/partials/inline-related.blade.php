<div class="kr-inline-related" role="complementary" aria-label="Berita Terkait">
    <div class="kr-inline-related-inner">
        <div class="kr-inline-related-badge">
            <span class="kr-inline-related-tag">BACA JUGA</span>
        </div>
        <div class="kr-inline-related-content">
            @if($relItem->hero_media_id)
                <a href="{{ $relItem->url }}" class="kr-inline-related-thumb" tabindex="-1" aria-hidden="true">
                    <img src="/media/{{ $relItem->hero_media_id }}/480" width="96" height="64" alt="{{ $relItem->hero_alt }}" loading="lazy">
                </a>
            @endif
            <div class="kr-inline-related-body">
                @if($relItem->category)
                    <span class="kr-inline-related-cat">{{ $relItem->category->name }}</span>
                @endif
                <h4 class="kr-inline-related-title">
                    <a href="{{ $relItem->url }}">{{ $relItem->title }}</a>
                </h4>
            </div>
            <a href="{{ $relItem->url }}" class="kr-inline-related-arrow" aria-hidden="true" tabindex="-1">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        </div>
    </div>
</div>
