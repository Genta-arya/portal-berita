@extends('layouts.admin')
@php
$modules = [
    'pages'=>['Halaman','Kelola halaman informasi, profil, dan kebijakan portal.','Tambah halaman','pages'],
    'categories'=>['Topik','Susun topik agar pembaca mudah menemukan berita.','Tambah topik','categories'],
    'tags'=>['Tag','Hubungkan berita dengan kata kunci yang relevan.','Tambah tag','tags'],
    'users'=>['Pengguna','Kelola tim, peran, dan akses ruang redaksi.','Tambah pengguna','users'],
    'trending'=>['Trending Topik','Pilih topik dan urutannya untuk deretan story di beranda. Maksimal 9 topik aktif ditampilkan.','Tambah topik trending','categories'],
    'featured'=>['Headline & pilihan','Kurasi berita utama dan pilihan redaksi di beranda.','Tambah pilihan','star'],
    'breaking'=>['Breaking news','Atur kabar penting yang tampil pada ticker berita.','Tambah kabar','bolt'],
    'banners'=>['Iklan','Kelola materi iklan, penempatan, dan jadwal penayangan.','Tambah iklan','banners'],
    'settings'=>['Pengaturan','Identitas portal, kontak, dan kanal media sosial.','Simpan pengaturan','settings'],
    'audit'=>['Audit aktivitas','Telusuri perubahan dan aktivitas tim redaksi.','','audit'],
];
[$title,$description,$createLabel,$icon] = $modules[$module];
$canDelete = in_array($module,['categories','tags','pages','banners','breaking','featured','trending']);
@endphp
@section('title', $title)
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ in_array($module,['pages','categories','tags']) ? 'MANAJEMEN KONTEN' : 'OPERASIONAL REDAKSI' }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p></div>@if(!in_array($module,['settings','audit']))<button type="button" class="primary-action" data-create-row>@include('admin.icon',['name'=>'plus']) {{ $createLabel }}</button>@endif</div>
@if($module === 'settings')
    @include('admin.management.settings')
@else
    @if($module==='users')<div class="role-guide"><div><strong>Penulis</strong><span>Membuat dan mengedit draft atau revisi miliknya, lalu mengajukan review.</span></div><div><strong>Editor / Admin</strong><span>Meninjau dan menerbitkan berita. Admin juga mengelola situs serta akun Penulis dan Editor.</span></div></div>@endif
    <div class="module-summary"><span class="summary-icon">@include('admin.icon',['name'=>$icon])</span><div><strong>{{ number_format($total,0,',','.') }}</strong><span>{{ $title }}</span></div>@if($activeCount !== null)<div class="summary-divider"></div><span class="summary-count"><i class="status-dot dot-published"></i> {{ $activeCount }} aktif</span><span class="summary-count"><i class="status-dot"></i> {{ $total-$activeCount }} nonaktif</span>@else<span class="summary-description">{{ $module === 'audit' ? 'Riwayat tersimpan otomatis dari aktivitas redaksi' : 'Data terbaru dari ruang redaksi' }}</span>@endif</div>
    <section class="workspace-card management-list">
        <form class="list-toolbar" method="get"><div class="search-field"><label for="module-search" class="sr-only">Cari {{ $title }}</label><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input id="module-search" name="q" value="{{ request('q') }}" placeholder="Cari {{ mb_strtolower($title) }}..."></div>@if($activeCount !== null)<select name="state" aria-label="Filter status"><option value="">Semua status</option><option value="active" @selected(request('state')==='active')>Aktif</option><option value="inactive" @selected(request('state')==='inactive')>Nonaktif</option></select>@endif<button class="button-secondary" type="submit">Cari</button>@if(request()->filled('q')||request()->filled('state'))<a class="text-link" href="/admin/manage/{{ $module }}">Reset</a>@endif<span class="toolbar-result">{{ $rows->total() }} hasil</span></form>
        @include('admin.management.rows')
        <div class="pagination-container">{{ $rows->links('admin.pagination') }}</div>
    </section>
    @if($module !== 'audit')
    <dialog class="management-dialog" id="management-dialog" aria-labelledby="management-title" data-open-on-error="{{ $errors->any() ? 'true' : 'false' }}">
        <div class="dialog-heading"><div><span class="eyebrow">{{ $title }}</span><h2 id="management-title" data-create-title="{{ $createLabel }}">{{ old('id') ? 'Edit '.mb_strtolower($title) : $createLabel }}</h2></div><button type="button" class="icon-button" data-close-dialog aria-label="Tutup formulir">@include('admin.icon',['name'=>'close'])</button></div>
        @if($errors->any())<div class="dialog-error" role="alert">{{ $errors->first() }}</div>@endif
        <form id="management-form" method="post" enctype="multipart/form-data" action="/admin/manage/{{ $module }}" class="management-editor" data-module="{{ $module }}">@csrf<input type="hidden" name="id" value="{{ old('id') }}">
            @include('admin.management.fields')
            <div class="dialog-actions"><button type="button" class="button-secondary" data-close-dialog>Batal</button><button type="submit">Simpan {{ $module === 'pages' ? 'halaman' : 'perubahan' }} @include('admin.icon',['name'=>'check'])</button></div>
        </form>
    </dialog>
    @endif
@endif
@endsection
@push('scripts')@vite(['resources/js/manage.js'])@endpush
