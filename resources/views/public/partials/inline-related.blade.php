<aside class="kr-inline-related" role="complementary" aria-label="Berita Terkait">
    <a href="{{ $relItem->url }}" class="kr-inline-related-card">
        @if($relItem->hero_media_id)
            <div class="kr-inline-related-thumb">
                <img src="/media/{{ $relItem->hero_media_id }}/480" width="120" height="80" alt="{{ $relItem->hero_alt }}" loading="lazy">
            </div>
        @endif
        <div class="kr-inline-related-body">
            <span class="kr-inline-related-label">Baca Juga:</span>
            <h4 class="kr-inline-related-title">{{ $relItem->title }}</h4>
        </div>
        <div class="kr-inline-related-arrow" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </div>
    </a>
</aside>

