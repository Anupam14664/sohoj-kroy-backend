@if ($paginator->hasPages())
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center flex-nowrap">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                $total = $paginator->lastPage();
                $current = $paginator->currentPage();
                $start = max($current - 1, 1);
                $end = min($current + 1, $total);
            @endphp

            {{-- First Page --}}
            @if($start > 1)
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                @if($start > 2)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            {{-- Middle Pages --}}
            @for($i = $start; $i <= $end; $i++)
                @if($i == $current)
                    <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                @endif
            @endfor

            {{-- Last Page --}}
            @if($end < $total)
                @if($end < $total - 1)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($total) }}">{{ $total }}</a></li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
                </li>
            @else
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Next</a>
                </li>
            @endif
        </ul>
    </nav>
@endif

<style>
/* Prevent wrapping */
.pagination {
    flex-wrap: nowrap;      /* single line */
    overflow-x: auto;       /* scroll if needed */
    -webkit-overflow-scrolling: touch; /* smooth scroll on iOS */
}

/* Page item styling */
.pagination .page-item {
    flex: 0 0 auto;         /* don't shrink */
    margin: 0 2px;
}

/* Page link styling */
.pagination .page-link {
    padding: 6px 10px;
    font-size: 14px;
    min-width: 36px;
    text-align: center;
}

/* Active page */
.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

/* Disabled page */
.pagination .page-item.disabled .page-link {
    pointer-events: none;
    opacity: 0.6;
}


</style>
