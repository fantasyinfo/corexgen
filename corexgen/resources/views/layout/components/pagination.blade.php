@if ($paginator->hasPages())
    <div class="card-footer">
        <ul class="list-unstyled d-flex align-items-center gap-2 mb-0 pagination-common-style">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li>
                    <span><i class="bi bi-arrow-left"></i></span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}"><i class="bi bi-arrow-left"></i></a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage(); 
                
                // Calculate start and end for main pagination range
                $start = max(1, $currentPage - 3);
                $end = min($lastPage, $currentPage + 3);
            @endphp

            {{-- First page if not in range --}}
            @if ($start > 1)
                <li>
                    <a href="{{ $paginator->url(1) }}">1</a>
                </li>
                @if ($start > 2)
                    <li>
                        <span><i class="bi bi-dot"></i></span>
                    </li>
                @endif
            @endif

            {{-- Main pagination range --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $currentPage)
                    <li>
                        <a href="javascript:void(0);" class="active">{{ $page }}</a>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last page if not in range --}}
            @if ($end < $lastPage)
                @if ($end < $lastPage - 1)
                    <li>
                        <span><i class="bi bi-dot"></i></span>
                    </li>
                @endif
                <li>
                    <a href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}"><i class="bi bi-arrow-right"></i></a>
                </li>
            @else
                <li>
                    <span><i class="bi bi-arrow-right"></i></span>
                </li>
            @endif
        </ul>
    </div>
@endif