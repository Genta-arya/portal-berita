<article class="reader-card reader-card--{{ $variant ?? 'tile' }} {{ !$item->hero_media_id?'reader-card--text':'' }}">
    @if($item->hero_media_id)<a class="story-image" href="{{ $item->url }}"><img src="/media/{{ $item->hero_media_id }}/480" width="480" height="300" alt="{{ $item->hero_alt }}" loading="lazy"></a>@endif
    <div class="reader-card-body">
        <div class="story-meta"><a href="/kanal/{{ $item->category?->slug }}">{{ $item->category?->name }}</a>@if($item->sponsored)<span>Konten sponsor</span>@endif</div>
        <h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3>
        @if(($variant ?? 'tile')==='feed')<p>{{ $item->summary }}</p>@endif
        <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</time>
    </div>
</article>
