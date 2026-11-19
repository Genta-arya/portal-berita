<section class="headline-carousel" data-headline-carousel role="region" aria-roledescription="carousel" aria-label="Headline {{ $popularTitle }}" tabindex="0">
    <div class="carousel-label"><span class="section-kicker">{{ $popularTitle==='Terpopuler'?'PALING BANYAK DIBACA':'KABAR TERBARU' }}</span><span>{{ $popularTitle==='Terpopuler'?'7 hari terakhir':'Dari ruang redaksi' }}</span></div>
    <div class="carousel-slides">
    @foreach($featured->take(5) as $item)
        <article class="headline-slide" data-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ $featured->count() }}" @if(!$loop->first) hidden @endif>
            <a class="story-image" href="{{ $item->url }}">@if($item->hero_media_id)<img src="/media/{{ $item->hero_media_id }}/1280" width="1280" height="800" alt="{{ $item->hero_alt }}" loading="{{ $loop->first?'eager':'lazy' }}" fetchpriority="{{ $loop->first?'high':'auto' }}">@else<span class="image-fallback">{{ $item->category?->name ?? 'Berita' }}</span>@endif</a>
            <div class="carousel-story"><div class="story-meta"><a href="/kanal/{{ $item->category?->slug }}">{{ $item->category?->name }}</a><span>{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span></div><h2><a href="{{ $item->url }}">{{ $item->title }}</a></h2><p>{{ $item->summary }}</p></div>
        </article>
    @endforeach
    </div>
    <div class="carousel-controls" data-carousel-controls hidden>
        <div class="carousel-pagination" aria-hidden="true">@foreach($featured->take(5) as $item)<span data-indicator class="{{ $loop->first?'is-active':'' }}"></span>@endforeach</div>
        <button type="button" class="carousel-accessible-pause" data-autoplay-toggle aria-pressed="false">Jeda carousel otomatis</button>
    </div>
    <p class="sr-only" data-carousel-status aria-live="polite" aria-atomic="true">Berita 1 dari {{ $featured->count() }}</p>
    <noscript><div class="carousel-fallback">@foreach($featured->skip(1) as $item)<a href="{{ $item->url }}">{{ $item->title }}</a>@endforeach</div></noscript>
</section>
