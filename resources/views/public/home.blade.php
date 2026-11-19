@extends('layouts.public')
@section('content')
@include('public.banner', ['slot'=>'home_top'])
@if($channels->isNotEmpty())
<div class="discovery-bar"><span class="discovery-label">JELAJAHI HARI INI</span><div>@foreach($channels->take(6) as $channel)<a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a>@endforeach</div><a class="discovery-all" href="/terbaru">Semua berita</a></div>
@endif
<div class="newsroom-heading"><div><span class="section-kicker">PILIHAN UNTUK ANDA</span><h1>Hari ini, lebih jernih<span>.</span></h1></div><p>Kabar terkini. Konteks yang berarti.</p></div>
<div class="frontpage-grid">
<div class="frontpage-main">
@if($featured->isNotEmpty())
@include('public.headline-carousel')
@else<section class="reader-empty"><span class="section-kicker">DARI RUANG REDAKSI</span><h2>Perspektif baru dimulai di sini.</h2><p>Redaksi sedang menyiapkan berita pertama.</p></section>@endif
<section class="latest-section"><div class="reader-section-title"><h2>Berita terbaru</h2><a href="/terbaru">Lihat semua</a></div><div class="news-feed">@forelse($latest as $item)@include('public.card',['variant'=>'feed'])@if($loop->iteration===3)@include('public.banner',['slot'=>'list_middle'])@endif @empty<p class="reader-empty-note">Belum ada berita terbaru lainnya.</p>@endforelse</div><a class="all-news-link" href="/terbaru">Jelajahi semua berita</a></section>
</div>
<aside class="frontpage-aside"><section class="popular-panel"><div class="reader-section-title"><h2>{{ $popularTitle }}</h2><span class="small-mark" aria-hidden="true">PB.</span></div><p class="panel-description">{{ $popularTitle==='Terpopuler'?'Paling banyak dibaca dalam tujuh hari terakhir.':'Ikuti kabar terbaru dari ruang redaksi.' }}</p><ol class="ranked-stories">@forelse($popular as $item)<li><span class="rank-number" aria-hidden="true">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><div><a class="section-kicker" href="/kanal/{{ $item->category?->slug }}">{{ $item->category?->name }}</a><h3><a href="{{ $item->url }}">{{ $item->title }}</a></h3></div></li>@empty<li>Berita akan hadir di sini.</li>@endforelse</ol></section>@include('public.banner',['slot'=>'sidebar'])<section class="perspective-note"><span class="section-kicker">CARA KAMI BERCERITA</span><h2>Bukan sekadar kabar.<br>Juga sudut pandang.</h2><p>Kenali ruang redaksi di balik berita yang Anda baca.</p><a href="/halaman/pedoman-redaksi">Pedoman redaksi</a></section></aside>
</div>
@if($picks->isNotEmpty())<section class="editors-section"><div class="reader-section-title"><div><span class="section-kicker">LAYAK ANDA BACA</span><h2>Pilihan redaksi</h2></div><span class="editorial-flourish" aria-hidden="true">Pilihan.</span></div><div class="reader-card-grid">@foreach($picks as $item)@include('public.card',['variant'=>'tile'])@endforeach</div></section>@endif
@if($channels->isNotEmpty())<section class="channel-section"><div class="reader-section-title"><h2>Beragam kabar, satu perspektif jernih.</h2></div><div class="channel-sections">@foreach($channels as $channel)<section class="channel-block"><div class="channel-title"><h3><a href="/kanal/{{ $channel->slug }}">{{ $channel->name }}</a></h3><a href="/kanal/{{ $channel->slug }}">Lihat kanal</a></div>@foreach($channel->articles->take(3) as $item)@include('public.card',['variant'=>'compact'])@endforeach</section>@endforeach</div></section>@endif
@endsection
