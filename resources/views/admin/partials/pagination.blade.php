@if ($paginator->hasPages())
    @php
        $startPage = max(1, $paginator->currentPage() - 1);
        $endPage = min($paginator->lastPage(), $paginator->currentPage() + 1);
    @endphp

    <div class="flex flex-col gap-3 border-t border-admin-line/10 pt-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-white/45">
            Showing {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-8 items-center border border-admin-line/10 px-3 text-xs text-white/28">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">Prev</a>
            @endif

            @if ($startPage > 1)
                <a href="{{ $paginator->url(1) }}" class="inline-flex size-8 items-center justify-center border border-[#30384a] text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">1</a>
                @if ($startPage > 2)
                    <span class="px-1 text-xs text-white/30">...</span>
                @endif
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page === $paginator->currentPage())
                    <span class="inline-flex size-8 items-center justify-center border border-[#3b4557] bg-white/[0.08] text-xs font-medium text-white">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="inline-flex size-8 items-center justify-center border border-[#30384a] text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">{{ $page }}</a>
                @endif
            @endfor

            @if ($endPage < $paginator->lastPage())
                @if ($endPage < $paginator->lastPage() - 1)
                    <span class="px-1 text-xs text-white/30">...</span>
                @endif
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="inline-flex size-8 items-center justify-center border border-[#30384a] text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">{{ $paginator->lastPage() }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-8 items-center border border-[#30384a] px-3 text-xs text-white/72 transition hover:border-[#3b4557] hover:bg-white/[0.04]">Next</a>
            @else
                <span class="inline-flex h-8 items-center border border-admin-line/10 px-3 text-xs text-white/28">Next</span>
            @endif
        </div>
    </div>
@endif
