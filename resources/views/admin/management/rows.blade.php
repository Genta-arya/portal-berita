@php($slots = ['home_top'=>'Beranda atas','list_middle'=>'Di antara berita','sidebar'=>'Sidebar','article_bottom'=>'Bawah artikel'])
<div class="data-table-scroll"><table class="data-table management-table"><thead><tr>
@if($module==='users')<th>Pengguna</th><th>Peran</th><th>Status</th><th>Bergabung</th>
@elseif($module==='categories'||$module==='tags')<th>{{ $title }}</th>@if($module==='categories')<th>Deskripsi</th><th>Urutan</th>@else<th>Alamat tag</th>@endif<th>Status</th>
@elseif($module==='pages')<th>Halaman</th><th>Alamat publik</th><th>Status</th><th>Diperbarui</th>
@elseif($module==='trending')<th>Topik story</th><th>Urutan</th><th>Status</th>
@elseif($module==='featured')<th>Artikel pilihan</th><th>Penempatan</th><th>Urutan</th>
@elseif($module==='banners')<th>Materi iklan</th><th>Penempatan</th><th>Jadwal penayangan</th><th>Status</th>
@elseif($module==='breaking')<th>Kabar penting</th><th>Jadwal tayang</th><th>Status</th>
@elseif($module==='audit')<th>Aktivitas</th><th>Oleh</th><th>Objek</th><th>Waktu</th>
@endif
@if($module!=='audit')<th class="align-right">Aksi</th>@endif</tr></thead><tbody>
@forelse($rows as $row)
<tr>
@if($module==='users')
    <td><div class="identity-cell"><span class="user-avatar">@if($row->avatar_path)<img src="/admin/users/{{ $row->id }}/avatar" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($row->name,0,1)) }}@endif</span><div><strong>{{ $row->name }}</strong><small>{{ $row->email }}</small></div></div></td><td><span class="role-badge">{{ ucfirst($row->role) }}</span></td><td>@include('admin.management.state',['active'=>$row->active])</td><td class="muted">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
@elseif(in_array($module,['categories','tags']))
    <td><div class="identity-cell"><span class="content-icon">@include('admin.icon',['name'=>$module])</span><div><strong>{{ $module==='tags' ? '#' : '' }}{{ $row->name }}</strong><small>{{ $row->slug }}</small></div></div></td>@if($module==='categories')<td class="muted description-cell">{{ \Illuminate\Support\Str::limit($row->description ?: 'Belum ada deskripsi',90) }}</td><td><span class="order-badge">{{ $row->position }}</span></td>@else<td class="muted">/tag/{{ $row->slug }}</td>@endif<td>@include('admin.management.state',['active'=>$row->active])</td>
@elseif($module==='pages')
    <td><div class="identity-cell"><span class="content-icon">@include('admin.icon',['name'=>'pages'])</span><div><strong>{{ $row->title }}</strong><small>{{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($row->body),ENT_QUOTES,'UTF-8'),85) }}</small></div></div></td><td><span class="path-label">/halaman/{{ $row->slug }}</span></td><td>@include('admin.management.state',['active'=>$row->active])</td><td class="muted nowrap">{{ \Carbon\Carbon::parse($row->updated_at)->timezone('Asia/Jakarta')->format('d M Y') }}</td>
@elseif($module==='trending')
    <td><strong class="cell-title">{{ $topicNames[$row->category_id] ?? 'Topik tidak tersedia' }}</strong><small class="cell-subtitle">Story mengambil artikel terbit terbaru dalam topik ini</small></td><td><span class="order-badge">{{ $row->position }}</span></td><td>@include('admin.management.state',['active'=>$row->active])</td>
@elseif($module==='featured')
    <td><strong class="cell-title">{{ $articleTitles[$row->article_id] ?? 'Artikel #'.$row->article_id }}</strong><small class="cell-subtitle">Artikel #{{ $row->article_id }}</small></td><td><span class="role-badge">{{ $row->location==='headline' ? 'Headline' : 'Pilihan redaksi' }}</span></td><td><span class="order-badge">{{ $row->position }}</span></td>
@elseif($module==='banners')
    <td><div class="identity-cell"><img class="table-image" src="/media/{{ $row->media_id }}/480" alt="" loading="lazy"><div><strong>{{ $row->alt }}</strong><small>{{ \Illuminate\Support\Str::limit($row->url,40) }}</small></div></div></td><td>{{ $slots[$row->slot] ?? $row->slot }}</td><td>@include('admin.management.schedule')</td><td>@include('admin.management.state',['active'=>$row->active])</td>
@elseif($module==='breaking')
    <td><strong class="cell-title">{{ $row->text }}</strong>@if($row->url)<small class="cell-subtitle">{{ \Illuminate\Support\Str::limit($row->url,60) }}</small>@endif</td><td>@include('admin.management.schedule')</td><td>@include('admin.management.state',['active'=>$row->active])</td>
@elseif($module==='audit')
    <td><strong class="cell-title">{{ ucwords(str_replace('_',' ',$row->action)) }}</strong><small class="cell-subtitle">Aktivitas #{{ $row->id }}</small></td><td>{{ $actorNames[$row->user_id] ?? 'Sistem' }}</td><td><span class="path-label">{{ $row->target_type }}{{ $row->target_id ? ' #'.$row->target_id : '' }}</span></td><td class="muted nowrap">{{ \Carbon\Carbon::parse($row->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</td>
@endif
@if($module!=='audit')<td class="align-right">@include('admin.management.actions')</td>@endif
</tr>
@empty<tr><td colspan="6"><div class="empty-state">@include('admin.icon',['name'=>$icon])<h3>{{ request()->filled('q') || request()->filled('state') ? 'Tidak ada hasil yang cocok' : 'Belum ada '.mb_strtolower($title) }}</h3><p>{{ request()->filled('q') || request()->filled('state') ? 'Ubah kata kunci atau reset filter untuk melihat data lainnya.' : 'Data yang Anda tambahkan akan ditampilkan di sini.' }}</p>@if($module!=='audit')<button type="button" class="button-secondary" data-create-row>{{ $createLabel }}</button>@endif</div></td></tr>@endforelse
</tbody></table></div>
