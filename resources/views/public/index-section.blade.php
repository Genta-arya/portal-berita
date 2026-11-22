@if(isset($archiveMonths) && $archiveMonths->isNotEmpty())
    <section class="kr-footer-archive" aria-labelledby="footer-archive-heading">
        <div class="wrap">
            <div class="kr-archive-container">
                {{-- Baris Atas: Judul & Form Pencarian Arsip Berdasarkan Tanggal --}}
                <div class="kr-archive-header">
                    <div class="kr-archive-title-area">
                        <span class="kr-archive-badge">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            DOKUMENTASI REDAKSI
                        </span>
                        <h2 id="footer-archive-heading">Arsip Berita</h2>
                        <p class="kr-archive-subtitle">Telusuri kembali rekaman peristiwa dan publikasi berita lampau berdasarkan tanggal dan bulan penerbitan.</p>
                    </div>

                    {{-- Form Pemilih Tanggal Langsung (Otomatis Fetch saat Dipilih) --}}
                    <form action="/arsip" method="get" class="kr-archive-date-form" role="search" aria-label="Cari arsip berdasarkan tanggal">
                        <label for="krArchiveDatePicker" class="kr-archive-form-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Pilih Tanggal:</span>
                        </label>
                        <div class="kr-archive-input-group">
                            <input type="date" id="krArchiveDatePicker" name="tanggal" value="{{ request('tanggal', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" onchange="if(this.value) this.form.submit();" required aria-label="Pilih tanggal arsip">
                        </div>
                    </form>
                </div>

                {{-- Baris Bawah: Kartu Akses Cepat Arsip Per Bulan --}}
                <div class="kr-archive-months-area">
                    <span class="kr-archive-months-label">ARSIP BULANAN TERSEDIA:</span>
                    <div class="kr-archive-months-grid">
                        @foreach($archiveMonths as $item)
                            @php
                                $dt = \Carbon\Carbon::createFromDate($item->year, $item->month, 1);
                                $isCurrent = request('tahun') == $item->year && request('bulan') == $item->month;
                            @endphp
                            <a href="/arsip?tahun={{ $item->year }}&bulan={{ $item->month }}" class="kr-archive-month-card {{ $isCurrent ? 'is-active' : '' }}">
                                <div class="kr-archive-month-left">
                                    <span class="kr-archive-cal-icon" aria-hidden="true">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                    </span>
                                    <div class="kr-archive-month-text">
                                        <strong>{{ $dt->locale('id')->translatedFormat('F') }}</strong>
                                        <span>{{ $item->year }}</span>
                                    </div>
                                </div>
                                <span class="kr-archive-month-badge">{{ $item->count }} berita</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
