@extends('layouts.admin')
@section('title', 'Profil saya')
@section('content')
<div class="page-heading"><div><span class="eyebrow">AKUN PRIBADI</span><h1>Profil saya</h1><p>Ganti foto profil yang tampil di ruang redaksi.</p></div></div>
<section class="workspace-card profile-card"><div class="section-intro"><span class="content-icon">@include('admin.icon',['name'=>'users'])</span><div><h2>{{ $profile->name }}</h2><p>{{ $profile->email }} &middot; {{ ucfirst($profile->role) }}</p></div></div><form action="/admin/profile" method="post" enctype="multipart/form-data">@csrf @include('admin.avatar-field')<div class="dialog-actions"><button type="submit">@include('admin.icon',['name'=>'check']) Simpan foto profil</button></div></form></section>
@endsection
@push('scripts')@vite(['resources/js/avatar.js'])@endpush
