@extends('layouts.admin')
@section('title', 'Ringkasan')
@section('content')
@php
    $statuses = ['draft'=>'Draft', 'in_review'=>'Menunggu review', 'needs_revision'=>'Perlu revisi', 'approved'=>'Disetujui', 'scheduled'=>'Terjadwal', 'published'=>'Terbit', 'withdrawn'=>'Ditarik'];
    $total = $counts->sum();
    $maxTraffic = max(1, $traffic->max('total'));
@endphp
<div class="dashboard-heading"><div><span class="eyebrow">MEJA REDAKSI</span><h1>Selamat datang, {{ auth()->user()->name }}.</h1><p>Pantau pemberitaan dan lanjutkan pekerjaan redaksi Anda.</p></div><a class="button primary-action" href="/admin/articles">@include('admin.icon',['name'=>'plus']) Buat artikel</a></div>
<div class="dashboard-stats">
    @foreach(['draft'=>['Draft','article'], 'in_review'=>['Menunggu review','audit'], 'scheduled'=>['Terjadwal','pages'], 'published'=>['Artikel terbit','external']] as $status=>[$label,$icon])
    <a class="metric-card" href="/admin/articles?status={{ $status }}"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon metric-icon-{{ $status }}">@include('admin.icon',['name'=>$icon])</span></div><strong>{{ number_format($counts[$status] ?? 0,0,',','.') }}</strong><small>Lihat artikel @include('admin.icon',['name'=>'arrow'])</small></a>
    @endforeach
</div>
<div class="dashboard-analysis">
    <section class="dashboard-card traffic-card" aria-labelledby="traffic-title">
        <div class="card-heading"><div><h2 id="traffic-title">Performa pembaca</h2><p>Tampilan artikel selama 7 hari terakhir</p></div><span class="period-badge">{{ $traffic->first()['date'] }} — {{ $traffic->last()['date'] }}</span></div>
        <div class="traffic-summary"><strong>{{ number_format($weekViews,0,',','.') }}</strong><span>tampilan artikel</span>@if($growth !== null)<span @class(['growth', 'growth-negative'=>$growth < 0])>{{ $growth > 0 ? '+' : '' }}{{ $growth }}% <small>vs 7 hari sebelumnya</small></span>@else<span class="growth-baseline">Belum ada pembanding minggu sebelumnya</span>@endif</div>
        <div class="traffic-chart" role="img" aria-label="Tampilan artikel per hari: {{ $traffic->map(fn($day) => $day['date'].': '.$day['total'])->implode(', ') }}">
            @foreach($traffic as $day)<div class="chart-column"><span class="chart-value">{{ number_format($day['total'],0,',','.') }}</span><div class="chart-track"><span class="chart-bar" style="height:{{ $day['total'] > 0 ? max(2, round($day['total'] / $maxTraffic * 100)) : 0 }}%"></span></div><span class="chart-label">{{ $day['label'] }}</span></div>@endforeach
        </div>
        <div class="traffic-footnote">@include('admin.icon',['name'=>'eye']) Total sepanjang waktu: <strong>{{ number_format($views,0,',','.') }}</strong><span>Estimasi tampilan, bukan pembaca unik.</span></div>
    </section>
    <section class="dashboard-card pipeline-card" aria-labelledby="pipeline-title">
        <div class="card-heading"><div><h2 id="pipeline-title">Alur publikasi</h2><p>{{ number_format($total,0,',','.') }} artikel di ruang redaksi</p></div></div>
        <div class="pipeline-list">@foreach($statuses as $status=>$label)<a href="/admin/articles?status={{ $status }}"><span class="status-dot dot-{{ $status }}"></span><span>{{ $label }}</span><strong>{{ number_format($counts[$status] ?? 0,0,',','.') }}</strong><div class="pipeline-meter"><i style="width:{{ $total ? ($counts[$status] ?? 0) / $total * 100 : 0 }}%"></i></div></a>@endforeach</div>
    </section>
</div>
<div class="dashboard-bottom">
    <section class="dashboard-card recent-card" aria-labelledby="recent-title"><div class="card-heading"><div><h2 id="recent-title">Aktivitas artikel terbaru</h2><p>Diurutkan berdasarkan pembaruan terakhir</p></div><a class="text-link" href="/admin/articles">Lihat semua @include('admin.icon',['name'=>'arrow'])</a></div>
        <div class="table-wrap"><table class="recent-table"><thead><tr><th>ARTIKEL</th><th>STATUS</th><th>DIPERBARUI</th><th><span class="sr-only">Aksi</span></th></tr></thead><tbody>@forelse($recent as $item)<tr><td><a class="recent-title" href="/admin/articles/{{ $item->id }}/edit">{{ $item->title }}</a><span class="recent-meta">{{ $item->category?->name ?? 'Tanpa topik' }} <span>&middot;</span> {{ $item->author?->name ?? 'Redaksi' }}</span></td><td><span class="editorial-status status-{{ $item->status }}">{{ $statuses[$item->status] ?? $item->status }}</span></td><td class="recent-date">{{ $item->updated_at->timezone('Asia/Jakarta')->locale('id')->diffForHumans() }}</td><td><a class="row-action" href="/admin/articles/{{ $item->id }}/edit" aria-label="Edit {{ $item->title }}">@include('admin.icon',['name'=>'arrow'])</a></td></tr>@empty<tr><td colspan="4"><div class="admin-empty">Belum ada artikel. Buat draft pertama untuk memulai.</div></td></tr>@endforelse</tbody></table></div>
    </section>
    <section class="dashboard-card top-card" aria-labelledby="top-title"><div class="card-heading"><div><h2 id="top-title">Paling banyak dibaca</h2><p>Artikel terbit &middot; 7 hari terakhir</p></div></div><ol class="top-article-list">@forelse($topArticles as $item)<li><span class="top-rank">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><div><a href="{{ $item->url }}" target="_blank" rel="noopener">{{ $item->title }}</a><small>{{ number_format($item->total_views,0,',','.') }} tampilan</small></div></li>@empty<li class="admin-empty">Belum ada data tampilan dalam 7 hari terakhir.</li>@endforelse</ol></section>
</div>
@endsection
