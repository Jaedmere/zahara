@extends('layouts.app')

@section('title', 'Usuarios - Zahara')
@section('page_title', 'Gestión de Usuarios')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Usuarios</span>
@endsection

@section('page_actions')
    <a href="{{ route('users.create') }}" class="btn-primary px-4 py-2.5 text-sm inline-flex items-center justify-center gap-2 shadow-sm w-full md:w-auto transition-transform active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        <span>Nuevo Usuario</span>
    </a>
@endsection
@section('content')
<div x-data="searchHandler" class="flex flex-col gap-4 md:gap-6 pb-20 md:pb-0">
    <div class="sticky top-0 z-20 bg-[#F8FAFC]/95 backdrop-blur py-2 md:static md:bg-transparent md:py-0 transition-all">
        {{-- buscador, filtros, etc. --}}
    </div>

    <div class="bg-white/60 backdrop-blur-xl rounded-2xl border border-soft shadow-sm overflow-hidden relative min-h-[300px]">
        <div x-show="isLoading" class="absolute inset-0 bg-white/40 z-10 backdrop-blur-[1px]" style="display: none;"></div>
        <div id="results-container">
            @include('users.partials.table', ['users' => $users])
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchHandler', () => ({
            search: @js(request('search')),
            isLoading: false,
            controller: null,
            performSearch() {
                const self = this;
                if (this.controller) this.controller.abort();
                this.controller = new AbortController();
                this.isLoading = true;

                const params = new URLSearchParams(window.location.search);
                this.search ? params.set('search', this.search) : params.delete('search');
                params.delete('page');

                const url = `${window.location.pathname}?${params.toString()}`;

                fetch(url, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html' 
                    },
                    signal: this.controller.signal
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('results-container').innerHTML = html;
                    window.history.pushState({}, '', url);
                })
                .finally(() => { self.isLoading = false; });
            }
        }))
    })
</script>
@endsection
