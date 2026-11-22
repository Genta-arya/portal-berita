@extends('layouts.admin')
@section('title', 'Artikel')
@section('content')
@php
$statuses = ['draft'=>'Draft','in_review'=>'Menunggu review','needs_revision'=>'Perlu revisi','approved'=>'Disetujui','scheduled'=>'Terjadwal','published'=>'Terbit','withdrawn'=>'Ditarik'];
@endphp
<div class="article-page">
    <div class="article-page-heading"><div><span class="eyebrow">MEJA REDAKSI</span><h1>Artikel <span>{{ number_format($statusCounts->sum(),0,',','.') }}</span></h1><p>Kelola naskah, tinjau progres, dan terbitkan kabar terbaru.</p></div><a class="button primary-action" href="#create-article" data-open-create>@include('admin.icon',['name'=>'plus']) Buat artikel</a></div>
    <details id="create-article" class="article-create" @if($errors->has('title')) open @endif><summary>Buat draft baru</summary><form method="post" action="/admin/articles">@csrf<div><label for="new-title">Judul artikel</label><input id="new-title" name="title" placeholder="Mulai dengan judul berita Anda..." value="{{ old('title') }}" required maxlength="255"></div><button type="submit">Buat draft @include('admin.icon',['name'=>'arrow'])</button></form></details>
    <section class="article-workbench" aria-label="Daftar artikel">
        <nav class="article-status-tabs" aria-label="Filter status artikel">
            <a href="{{ url('/admin/articles').'?'.http_build_query(request()->except('status','page')) }}" @class(['is-selected'=>!request('status')]) @if(!request('status')) aria-current="page" @endif>Semua artikel <span>{{ $statusCounts->sum() }}</span></a>
            @foreach(['published','draft','in_review','scheduled'] as $status)<a href="{{ url('/admin/articles').'?'.http_build_query(array_merge(request()->except('page'),['status'=>$status])) }}" @class(['is-selected'=>request('status')===$status]) @if(request('status')===$status) aria-current="page" @endif>{{ $statuses[$status] }} <span>{{ $statusCounts[$status] ?? 0 }}</span></a>@endforeach
        </nav>
        <form class="article-toolbar" method="get" action="/admin/articles">
            <div class="article-search"><label class="sr-only" for="article-search">Cari judul artikel</label><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input id="article-search" name="q" placeholder="Cari judul artikel..." value="{{ request('q') }}"></div>
            <div><label class="sr-only" for="article-status">Status</label><select id="article-status" name="status"><option value="">Semua status</option>@foreach($statuses as $status=>$label)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $label }}</option>@endforeach</select></div>
            <div><label class="sr-only" for="article-topic">Topik</label><select id="article-topic" name="category_id"><option value="">Semua topik</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div><label class="sr-only" for="article-date">Tanggal dibuat</label><input id="article-date" type="date" name="date" value="{{ request('date') }}" aria-label="Tanggal dibuat" title="Tanggal dibuat"></div>
            <button type="submit">Terapkan</button>
            @if(request()->hasAny(['q','status','category_id','date']))<a class="article-reset" href="/admin/articles">Reset</a>@endif
        </form>
        <div class="article-results-bar"><span><strong>{{ number_format($articles->total(),0,',','.') }}</strong> artikel ditemukan @if(request('status'))<span class="results-filter">&middot; {{ $statuses[request('status')] ?? request('status') }}</span>@endif</span><span>Terbaru lebih dulu</span></div>
        <div class="article-table-scroll"><table class="article-table"><thead><tr><th>ARTIKEL</th><th>STATUS</th><th>PENULIS</th><th>DIPERBARUI</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>
            @forelse($articles as $article)<tr>
                <td><div class="article-cell"><a class="article-thumb" href="/admin/articles/{{ $article->id }}/edit" tabindex="-1" aria-hidden="true">@if($article->hero_media_id)<img src="/media/{{ $article->hero_media_id }}/480" alt="" width="72" height="56" loading="lazy">@else @include('admin.icon',['name'=>'article']) @endif</a><div class="article-cell-content"><a class="article-title-link" href="/admin/articles/{{ $article->id }}/edit">{{ $article->title }}</a><div class="article-subline"><span>{{ $article->category?->name ?? 'Tanpa topik' }}</span><span aria-hidden="true">&middot;</span><span>#{{ $article->id }}</span><span aria-hidden="true">&middot;</span><span>Versi {{ $article->version }}</span></div></div></div></td>
                <td><span class="editorial-status status-{{ $article->status }}">{{ $statuses[$article->status] ?? $article->status }}</span></td>
                <td><div class="article-author"><span>@if($article->author?->avatar_path)<img src="/admin/users/{{ $article->author_id }}/avatar" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($article->author?->name ?? 'R',0,1)) }}@endif</span><strong>{{ $article->author?->name ?? 'Redaksi' }}</strong></div></td>
                <td><span class="article-updated">{{ $article->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') }}</span><small class="article-updated-time">{{ $article->updated_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</small></td>
                <td><a class="article-edit-link" href="/admin/articles/{{ $article->id }}/edit">Edit @include('admin.icon',['name'=>'arrow'])</a></td>
            </tr>@empty<tr><td colspan="5"><div class="article-empty">@include('admin.icon',['name'=>'article'])<h2>{{ request()->hasAny(['q','status','category_id','date']) ? 'Tidak ada artikel yang cocok' : 'Mulai cerita pertama Anda' }}</h2><p>{{ request()->hasAny(['q','status','category_id','date']) ? 'Coba kata kunci lain atau reset filter pencarian.' : 'Buat draft baru untuk memulai pemberitaan.' }}</p><a href="{{ request()->hasAny(['q','status','category_id','date']) ? '/admin/articles' : '#create-article' }}" class="text-link">{{ request()->hasAny(['q','status','category_id','date']) ? 'Reset filter' : 'Buat artikel' }}</a></div></td></tr>@endforelse
        </tbody></table></div>
        <div class="article-pagination-wrap">{{ $articles->links('admin.pagination') }}</div>
    </section>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('a[href="#create-article"]').forEach(link => link.addEventListener('click', event => { event.preventDefault(); const create = document.getElementById('create-article'); create.open = true; document.getElementById('new-title').focus(); }));
</script>
@endpush
