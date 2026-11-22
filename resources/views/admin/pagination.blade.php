@if ($paginator->hasPages())
<nav class="admin-pagination" aria-label="Navigasi halaman">
    <p>Menampilkan <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data</p>
    <div class="admin-pagination-pages">
        @if ($paginator->onFirstPage())<span class="pagination-step is-disabled" aria-disabled="true">&larr; <span>Sebelumnya</span></span>@else<a class="pagination-step" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr; <span>Sebelumnya</span></a>@endif
        @foreach ($elements as $element)
            @if (is_string($element))<span class="pagination-ellipsis">{{ $element }}</span>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())<span class="pagination-number is-current" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>@else<a class="pagination-number" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>@endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())<a class="pagination-step" href="{{ $paginator->nextPageUrl() }}" rel="next"><span>Berikutnya</span> &rarr;</a>@else<span class="pagination-step is-disabled" aria-disabled="true"><span>Berikutnya</span> &rarr;</span>@endif
    </div>
</nav>
@endif
