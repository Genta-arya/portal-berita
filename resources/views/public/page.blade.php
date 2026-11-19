@extends('layouts.public')
@section('title',$page->title.' — Portal Berita')
@section('content')<article class="reading"><h1>{{ $page->title }}</h1><div class="prose">{!! $page->body !!}</div></article>@endsection
