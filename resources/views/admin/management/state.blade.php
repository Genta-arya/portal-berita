@if(in_array($module ?? '',['breaking','banners']) && $active && isset($row))
    @if(\Carbon\Carbon::parse($row->starts_at)->isFuture())<span class="editorial-status status-scheduled">Terjadwal</span>
    @elseif(\Carbon\Carbon::parse($row->ends_at)->lte(now()))<span class="editorial-status">Berakhir</span>
    @else<span class="state-badge is-active"><i></i>Sedang tayang</span>@endif
@else
<span @class(['state-badge','is-active'=>(bool)$active])><i></i>{{ $active ? 'Aktif' : 'Nonaktif' }}</span>

@endif
