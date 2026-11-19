@extends('layouts.public')
@section('title',$title.' — Portal Berita')
@section('description',$description ?? '')
@section('content')<span class="eyebrow">Ruang berita</span><h1>{{ $title }}</h1><p>{{ $description }}</p>@if(request()->is('terbaru'))<form action="/terbaru" class="search"><label>Kanal<select name="category_id"><option value="">Semua kanal</option>@foreach(\App\Models\Category::where('active',true)->get() as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select></label><button>Filter</button></form>@endif<div class="channel-grid">@forelse($articles as $item)@include('public.card')@empty<p>Belum ada berita yang sesuai.</p>@endforelse</div>{{ $articles->links() }}@endsection
