@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-xs text-slate-500">
            @if ($paginator->firstItem())
                <span class="font-medium text-slate-400">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-medium text-slate-400">{{ $paginator->lastItem() }}</span>
                <span class="text-slate-600"> of </span>
                <span class="font-medium text-slate-400">{{ $paginator->total() }}</span>
            @endif
        </p>
        <span class="inline-flex overflow-hidden rounded-lg border border-white/10 bg-slate-950/80 shadow-sm">
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed px-3 py-2 text-xs font-medium text-slate-600">{{ __('pagination.previous') }}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">{{ __('pagination.previous') }}</a>
            @endif
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="border-l border-white/10 px-3 py-2 text-xs text-slate-500">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="border-l border-white/10 bg-violet-600/25 px-3 py-2 text-xs font-semibold text-violet-200">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="border-l border-white/10 px-3 py-2 text-xs font-medium text-slate-400 transition hover:bg-white/5 hover:text-white">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="border-l border-white/10 px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">{{ __('pagination.next') }}</a>
            @else
                <span class="cursor-not-allowed border-l border-white/10 px-3 py-2 text-xs font-medium text-slate-600">{{ __('pagination.next') }}</span>
            @endif
        </span>
    </nav>
@endif
