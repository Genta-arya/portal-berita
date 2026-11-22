@php
$slots = ['home_top'=>'Beranda atas','list_middle'=>'Di antara berita','sidebar'=>'Sidebar','article_bottom'=>'Bawah artikel'];
$deleteLabel = $row->title ?? $row->name ?? $row->text ?? $row->alt ?? 'pilihan berita';
@endphp
<div class="row-actions">
    @if($module === 'pages' && $row->active)<a href="/halaman/{{ $row->slug }}" target="_blank" rel="noopener" class="icon-button" title="Lihat halaman" aria-label="Lihat {{ $row->title }}">@include('admin.icon',['name'=>'external'])</a>@endif
    @if($module === 'users' && in_array($row->role,['admin','developer']) && !auth()->user()->isDeveloper())<span class="muted">Dikelola developer</span>
    @elseif($module !== 'audit')<button class="button-secondary button-small" type="button" data-edit-row="{{ json_encode(array_merge(collect((array)$row)->except(['password','remember_token','session_version','email_verified_at','avatar_path'])->all(), ['avatar_url'=>!empty($row->avatar_path) ? url('/admin/users/'.$row->id.'/avatar') : null])) }}">@include('admin.icon',['name'=>'edit']) Edit</button>@endif
    @if($canDelete)<form method="post" action="/admin/manage/{{ $module }}/{{ $row->id }}" data-confirm-delete="Hapus {{ $deleteLabel }}? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" class="icon-button delete-button" title="Hapus" aria-label="Hapus {{ $deleteLabel }}">@include('admin.icon',['name'=>'trash'])</button></form>@endif
</div>
