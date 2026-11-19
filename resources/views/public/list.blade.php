@extends('layouts.public')
@section('title', $title.' — Portal Berita')
@section('description', $description ?? '')
@section('content')
<div class="reader-breadcrumb"><a href="/">Beranda</a><span>/</span><span>{{ $title }}</span></div>
<header class="listing-header"><span class="section-kicker">JELAJAHI BERITA</span><h1>{{ $title }}<span>.</span></h1>@if($description)<p>{{ $description }}</p>@endif</header>
@if(request()->is('terbaru'))<form action="/terbaru" class="channel-filter"><label for="filter-channel">Pilih kanal</label><select id="filter-channel" name="category_id"><option value="">Semua kanal</option>@foreach(\App\Models\Category::where('active',true)->get() as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select><button>Tampilkan</button></form>@endif
<div class="listing-grid"><section class="news-feed">@forelse($articles as $item)@include('public.card',['variant'=>'feed'])@empty<div class="reader-empty"><h2>Belum ada berita yang sesuai.</h2><p>Coba kata pencarian lain atau jelajahi berita terbaru.</p><a href="/terbaru">Lihat berita terbaru</a></div>@endforelse{{ $articles->links() }}</section><aside class="listing-aside"><section class="perspective-note"><span class="section-kicker">TEMUKAN PERSPEKTIF</span><h2>Ada banyak sisi untuk dipahami.</h2><p>Jelajahi kabar dari berbagai kanal pilihan Anda.</p><a href="/terbaru">Buka indeks berita</a></section>@include('public.banner',['slot'=>'sidebar'])</aside></div>
@endsection
