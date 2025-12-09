@extends('layouts.app')

@section('title', 'Estado de Cuentas a Corte - Zahara')
@section('page_title', 'Estado de Cuentas por Cobrar a Corte')
@section('page_subtitle', 'Foto histórica de las cuentas por cobrar a una fecha de corte específica.')

@section('breadcrumb')
    <span>Inicio</span> /
    <span class="text-slate-900 font-medium">Reportes</span> /
    <span class="text-slate-900 font-medium">Estado de Cuentas</span>
@endsection

@section('content')
<div class="flex flex-col gap-6 w-full max-w-full pb-20">

    {{-- TOOLBAR: FILTROS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 animate-enter">
        
        <div class="flex flex-col lg:flex-row lg:items-end gap-5">
            {{-- Formulario --}}
            <div class="flex-1 w-full">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest block mb-2">
                    Filtros de Búsqueda
                </label>
                <form method="GET" action="{{ route('informes.estado_cuentas') }}"
                      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

                    {{-- Fecha --}}
                    <div>
                        <input type="date" name="fecha_corte"
                               value="{{ $fechaCorte }}"
                               class="input-pill w-full h-11 text-sm bg-slate-50 border-slate-200 focus:bg-white transition-colors">
                        <span class="text-[10px] text-slate-400 ml-1">Fecha de corte</span>
                    </div>

                    {{-- Buscador --}}
                    <div>
                        <input type="text" name="search"
                               value="{{ $search }}"
                               placeholder="Cliente o NIT..."
                               class="input-pill w-full h-11 text-sm bg-slate-50 border-slate-200 focus:bg-white transition-colors">
                    </div>

                    {{-- EDS --}}
                    <div class="relative">
                        <select name="eds_id"
                                class="input-pill w-full h-11 text-xs pl-3 pr-8 appearance-none cursor-pointer bg-slate-50 border-slate-200 focus:bg-white transition-colors">
                            <option value="">Todas las EDS</option>
                            @foreach($eds_list as $e)
                                <option value="{{ $e->id }}" {{ (string)$eds_id === (string)$e->id ? 'selected' : '' }}>
                                    {{ $e->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500 top-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary h-11 w-full flex items-center justify-center gap-2 shadow-lg shadow-indigo-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span class="hidden sm:inline">Buscar</span>
                        </button>
                        
                        <a href="{{ route('informes.estado_cuentas') }}" class="btn-secondary h-11 w-11 flex items-center justify-center flex-none" title="Limpiar filtros">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- KPIS & EXPORTAR --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-5 pt-5 border-t border-slate-100">
            
            {{-- KPI 1: TOTAL --}}
            <div class="bg-slate-900 rounded-xl p-4 text-white shadow-xl shadow-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Cartera Total</span>
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold tracking-tight">
                    {{ number_format(($totalCartera ?? 0) / 1000000000, 2, ',', '.') }} <span class="text-sm font-normal text-slate-400">B$</span>
                </div>
                <div class="text-[10px] text-slate-400 mt-1 truncate">
                    ${{ number_format($totalCartera ?? 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- KPI 2: FACTURAS --}}
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <span class="text-[10px] uppercase tracking-widest text-slate-500 block mb-2">Facturas Pendientes</span>
                <div class="text-2xl font-bold text-slate-800">
                    {{ $totalFacturas }}
                </div>
                <div class="text-[10px] text-slate-400 mt-1">
                   Promedio: ${{ number_format(($totalFacturas > 0) ? ($totalCartera / $totalFacturas) : 0, 0, ',', '.') }}
                </div>
            </div>

            {{-- KPI 3: EXPORTAR --}}
            @php
                $exportParams = [
                    'fecha_corte' => $fechaCorte,
                    'eds_id'      => $eds_id,
                    'search'      => $search,
                ];
            @endphp
            <a href="{{ route('informes.estado_cuentas_export', $exportParams) }}" 
               class="group bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex flex-col justify-center items-center cursor-pointer hover:bg-emerald-100 transition-colors">
                <div class="w-8 h-8 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-emerald-800">Descargar CSV</span>
                <span class="text-[10px] text-emerald-600 mt-0.5">Reporte a Corte</span>
            </a>
        </div>
    </div>

    {{-- VISTA MÓVIL: CARDS (Visible solo en móviles md:hidden) --}}
    <div class="grid grid-cols-1 gap-4 md:hidden animate-enter">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Listado de Cuentas</h3>
            <span class="text-[10px] bg-slate-100 px-2 py-1 rounded-full text-slate-500">{{ $items->total() }} registros</span>
        </div>

        @forelse($items as $row)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 relative overflow-hidden">
                {{-- Borde izquierdo de color según estado --}}
                <div class="absolute left-0 top-0 bottom-0 w-1 {{ $row->dias_mora > 0 ? 'bg-red-500' : 'bg-emerald-500' }}"></div>

                <div class="pl-2 flex flex-col gap-3">
                    {{-- Header Card --}}
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $row->prefijo }}-{{ $row->consecutivo }}</span>
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1">{{ $row->cliente_nombre }}</h4>
                            <p class="text-[10px] text-slate-500">{{ $row->cliente_documento }}</p>
                        </div>
                        <div class="text-right">
                             @if($row->dias_mora > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium bg-red-50 text-red-700 border border-red-100">
                                    {{ $row->dias_mora }} días mora
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    Al día
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Body Card --}}
                    <div class="grid grid-cols-2 gap-2 py-2 border-t border-b border-slate-50">
                        <div>
                            <span class="text-[10px] text-slate-400 block">EDS</span>
                            <span class="text-xs text-slate-600 font-medium truncate block">{{ $row->eds_nombre }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block">Vencimiento</span>
                            <span class="text-xs text-slate-600 font-medium block">
                                {{ \Carbon\Carbon::parse($row->fecha_vencimiento)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    {{-- Footer Card --}}
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Valor Original</span>
                            <span class="text-xs text-slate-500 decoration-slate-300">
                                ${{ number_format($row->valor_total, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block mb-0.5">Saldo Pendiente</span>
                            <span class="text-base font-bold font-mono {{ $row->dias_mora > 0 ? 'text-red-600' : 'text-slate-800' }}">
                                ${{ number_format($row->saldo_corte, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
                <p class="text-slate-400 text-sm">No se encontraron resultados.</p>
            </div>
        @endforelse

        {{-- Paginación Móvil --}}
        <div class="mt-2">
            {{ $items->onEachSide(0)->links('pagination::simple-tailwind') }}
        </div>
    </div>

    {{-- VISTA ESCRITORIO: TABLA (Visible solo en escritorio md:block) --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-200 p-4 animate-enter">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase">
                Detalle de Cuentas ({{ $items->total() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold rounded-l-lg">Cuenta</th>
                        <th class="px-3 py-3 text-left font-semibold">Cliente</th>
                        <th class="px-3 py-3 text-left font-semibold">EDS</th>
                        <th class="px-3 py-3 text-left font-semibold">Emisión</th>
                        <th class="px-3 py-3 text-left font-semibold">Vencimiento</th>
                        <th class="px-3 py-3 text-right font-semibold">Días Mora</th>
                        <th class="px-3 py-3 text-right font-semibold">Original</th>
                        <th class="px-3 py-3 text-right font-semibold rounded-r-lg">Saldo Corte</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-3 py-3 font-mono text-[11px] text-slate-500 group-hover:text-indigo-600 transition-colors">
                                {{ $row->prefijo }}-{{ $row->consecutivo }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-700">{{ $row->cliente_nombre }}</div>
                                <div class="text-[10px] text-slate-400">{{ $row->cliente_documento }}</div>
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ $row->eds_nombre }}
                            </td>
                            <td class="px-3 py-3 text-slate-500">
                                {{ $row->fecha_emision ? \Carbon\Carbon::parse($row->fecha_emision)->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-3 py-3 text-slate-500">
                                {{ $row->fecha_vencimiento ? \Carbon\Carbon::parse($row->fecha_vencimiento)->format('d/m/Y') : '' }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                @if($row->dias_mora > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-700">
                                        {{ $row->dias_mora }}
                                    </span>
                                @else
                                    <span class="text-emerald-600 font-medium">Al día</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right font-mono text-slate-400">
                                ${{ number_format($row->valor_total, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right font-mono font-bold {{ $row->dias_mora > 0 ? 'text-red-600' : 'text-slate-800' }}">
                                ${{ number_format($row->saldo_corte, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-8 h-8 text-slate-200 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-xs">No hay cuentas pendientes a esta fecha.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 border-t border-slate-50 pt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection