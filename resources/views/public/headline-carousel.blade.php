<section class="headline-carousel" data-headline-carousel role="region" aria-roledescription="carousel" aria-label="Headline {{ $popularTitle }}" tabindex="0">
    <div class="carousel-corner-logo" aria-hidden="true" style="position: absolute; top: 0; left: 0; z-index: 10; background: #ffffff; padding: 7px 15px 7px 12px; border-radius: 0 0 12px 0; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.22); display: flex; align-items: center; pointer-events: none;">
        <img src="/images/logo-kapuasraya.png" width="150" height="34" alt="KAPUAS RAYA" style="height: 23px; width: auto; max-width: 130px; object-fit: contain; display: block;">
    </div>
    <div class="carousel-slides">
        @foreach($featured->take(5) as $item)
            <article class="headline-slide" data-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ $featured->count() }}" @if(!$loop->first) hidden @endif>
                @if($item->hero_media_id)
                    <img class="headline-cover" src="/media/{{ $item->hero_media_id }}/1280" width="1280" height="800" alt="{{ $item->hero_alt }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" fetchpriority="{{ $loop->first ? 'high' : 'auto' }}">
                @endif
                <div class="carousel-story">
                    <div class="story-meta">
                        @if($item->category)<a href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>@endif
                        <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y') }}</time>
                    </div>
                    <h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3>
                </div>
            </article>
        @endforeach
    </div>
    <div class="carousel-controls" data-carousel-controls hidden>
        <div class="carousel-pagination" aria-hidden="true">@foreach($featured->take(5) as $item)<span data-indicator class="{{ $loop->first ? 'is-active' : '' }}"></span>@endforeach</div>
        <button type="button" class="carousel-accessible-pause" data-autoplay-toggle aria-pressed="false">Jeda carousel otomatis</button>
    </div>
    <p class="sr-only" data-carousel-status aria-live="polite" aria-atomic="true">Berita 1 dari {{ $featured->count() }}</p>
    <noscript><div class="carousel-fallback">@foreach($featured->skip(1) as $item)<a href="{{ $item->url }}">{{ $item->title }}</a>@endforeach</div></noscript>
</section>
