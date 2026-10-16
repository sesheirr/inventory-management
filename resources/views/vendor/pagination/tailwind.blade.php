@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-2 select-none">
        {{-- Mobile Compact Prev/Next --}}
        <div class="flex items-center gap-1.5 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-600 bg-slate-100 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 rounded-xl cursor-not-allowed">
                    <i class="bi bi-chevron-left text-[10px]"></i> Seb.
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-link inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xs transition-all cursor-pointer">
                    <i class="bi bi-chevron-left text-[10px]"></i> Seb.
                </a>
            @endif

            <span class="px-2.5 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 tabular-nums">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-link inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xs transition-all cursor-pointer">
                    Sel. <i class="bi bi-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-400 dark:text-slate-600 bg-slate-100 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-800 rounded-xl cursor-not-allowed">
                    Sel. <i class="bi bi-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>

        {{-- Desktop Horizontal Pill Strip --}}
        <div class="hidden sm:flex sm:items-center sm:gap-1 flex-wrap">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman Sebelumnya" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 cursor-not-allowed">
                    <i class="bi bi-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman Sebelumnya" class="pagination-link inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex items-center justify-center px-2 h-8 text-xs font-semibold text-slate-400 dark:text-slate-500">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center min-w-[2rem] px-2.5 h-8 rounded-xl text-xs font-bold text-white bg-blue-600 border border-blue-600 shadow-xs shadow-blue-600/25 tabular-nums">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="pagination-link inline-flex items-center justify-center min-w-[2rem] px-2.5 h-8 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition-all tabular-nums cursor-pointer">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman Berikutnya" class="pagination-link inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman Berikutnya" class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 cursor-not-allowed">
                    <i class="bi bi-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
