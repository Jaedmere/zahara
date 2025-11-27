@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between w-full">
        
        {{-- VISTA MÓVIL (Simple y Compacta) --}}
        <div class="flex justify-between flex-1 sm:hidden items-center">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-xl cursor-default leading-5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-xl leading-5 hover:text-indigo-600 hover:border-indigo-300 transition-colors active:bg-slate-50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Info de Página --}}
            <div class="text-xs text-slate-500 font-medium uppercase tracking-wide">
                Pág <span class="font-bold text-slate-800">{{ $paginator->currentPage() }}</span> / {{ $paginator->lastPage() }}
            </div>

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-xl leading-5 hover:text-indigo-600 hover:border-indigo-300 transition-colors active:bg-slate-50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 rounded-xl cursor-default leading-5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- VISTA ESCRITORIO (Completa con Números) --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-slate-500">
                    Mostrando
                    <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span>
                    a
                    <span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span>
                    de
                    <span class="font-bold text-indigo-600">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-xl gap-1">
                    {{-- Anterior Desktop --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 cursor-default rounded-l-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-l-lg leading-5 hover:text-indigo-600 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-500/50 active:bg-slate-100 transition ease-in-out duration-150" aria-label="@lang('pagination.previous')">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </a>
                    @endif

                    {{-- Elementos de Paginación --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 cursor-default leading-5 rounded-md shadow-sm">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 leading-5 hover:text-indigo-600 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-500/50 active:bg-slate-100 transition ease-in-out duration-150 rounded-md">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Siguiente Desktop --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-200 rounded-r-lg leading-5 hover:text-indigo-600 hover:bg-slate-50 focus:z-10 focus:outline-none focus:ring ring-indigo-500/50 active:bg-slate-100 transition ease-in-out duration-150" aria-label="@lang('pagination.next')">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-200 cursor-default rounded-r-lg leading-5" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif