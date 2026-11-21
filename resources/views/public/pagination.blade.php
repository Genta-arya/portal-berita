@if ($paginator->hasPages())
    <nav class="kr-pagination-wrapper" role="navigation" aria-label="Navigasi Halaman Berita">
        <div class="kr-pagination-meta">
            <span>Menampilkan</span>
            <strong>{{ number_format($paginator->firstItem(), 0, ',', '.') }}</strong>
            <span>–</span>
            <strong>{{ number_format($paginator->lastItem(), 0, ',', '.') }}</strong>
            <span>dari</span>
            <strong class="kr-pagination-total">{{ number_format($paginator->total(), 0, ',', '.') }}</strong>
            <span>berita</span>
        </div>

        <ul class="kr-pagination-list">
            {{-- Tombol Halaman Pertama (hanya jika jauh dari awal) --}}
            @if ($paginator->currentPage() > 3)
                <li class="kr-page-item">
                    <a href="{{ $paginator->url(1) }}" class="kr-page-btn" aria-label="Halaman Pertama" title="Halaman 1">
                        &laquo;
                    </a>
                </li>
            @endif

            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <li class="kr-page-item disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <span class="kr-page-btn is-nav" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="kr-page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" class="kr-page-btn is-nav" rel="prev" aria-label="Sebelumnya" title="Sebelumnya">
                        &lsaquo;
                    </a>
                </li>
            @endif

            {{-- Element Halaman Dinamis dengan Ellipsis Windowing --}}
            @foreach ($elements as $element)
                {{-- Tanda "..." (Separator) --}}
                @if (is_string($element))
                    <li class="kr-page-item is-dots" aria-disabled="true">
                        <span class="kr-page-dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Link Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="kr-page-item active" aria-current="page">
                                <span class="kr-page-btn is-current">{{ number_format($page, 0, ',', '.') }}</span>
                            </li>
                        @else
                            <li class="kr-page-item">
                                <a href="{{ $url }}" class="kr-page-btn" title="Halaman {{ $page }}">{{ number_format($page, 0, ',', '.') }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <li class="kr-page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" class="kr-page-btn is-nav" rel="next" aria-label="Selanjutnya" title="Selanjutnya">
                        &rsaquo;
                    </a>
                </li>
            @else
                <li class="kr-page-item disabled" aria-disabled="true" aria-label="Selanjutnya">
                    <span class="kr-page-btn is-nav" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif

            {{-- Tombol Halaman Terakhir (hanya jika jauh dari akhir) --}}
            @if ($paginator->lastPage() > 4 && $paginator->currentPage() < ($paginator->lastPage() - 2))
                <li class="kr-page-item">
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="kr-page-btn" aria-label="Halaman Terakhir" title="Halaman {{ $paginator->lastPage() }}">
                        &raquo;
                    </a>
                </li>
            @endif
        </ul>
    </nav>
@endif
