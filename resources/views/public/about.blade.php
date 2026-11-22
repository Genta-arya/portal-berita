@extends('layouts.public')
@section('title', $page->title . ' — ' . ($settings['name'] ?? 'Kapuas Raya'))
@section('description', 'Mengenal Kapuas Raya: Visi, Misi, Standar Jurnalisme, dan Komitmen Kami dalam Menyajikan Berita Aktual dan Terpercaya.')

@section('content')
<div class="reader-breadcrumb">
    <a href="/">Beranda</a>
    <span aria-hidden="true">/</span>
    <span>{{ $page->title }}</span>
</div>

{!! $page->body !!}
@endsection
