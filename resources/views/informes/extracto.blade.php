@extends('layouts.app')

@section('title', 'Extracto por Cliente - Zahara')
@section('page_title', 'Extracto por Cliente')
@section('page_subtitle', 'Histórico de cuentas y abonos tipo extracto bancario.')

@section('breadcrumb')
    <span class="no-print">Inicio</span> /
    <span class="text-slate-900 font-medium no-print">Reportes</span> /
    <span class="text-slate-900 font-medium no-print">Extracto por Cliente</span>
@endsection

@section('content')

{{-- 1. ESTILOS DE IMPRESIÓN (PDF vía window.print) --}}
<style>
    @media print {
        /* Ocultar todo el cuerpo de la página por defecto */
        body * {
            visibility: hidden;
        }
        
        /* Ocultar elementos específicos marcados como no-print */
        .no-print, .no-print * {
            display: none !important;
        }

        /* Mostrar solo el área imprimible */
        #printable-area, #printable-area * {
            visibility: visible;
        }

        /* Posicionar el área imprimible en la esquina superior absoluta */
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            background-color: white;
        }

        /* Forzar impresión de fondos y colores (Tailwind) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Eliminar sombras y bordes innecesarios al imprimir */
        .shadow-sm, .shadow-lg, .border {
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Bordes de tabla más definidos para papel */
        table, th, td {
            border: 1px solid #e2e8f0 !important;
            border-collapse: collapse;
        }
        
        /* Configuración de página */
        @page {
            size: auto;
            margin: 5mm;
        }
    }
</style>

{{-- 2. CONFIGURACIÓN DE DATOS DESDE PHP HACIA ALPINE --}}
<script>
    window.reportConfig = {
        cliente: @json($cliente ? ['id' => $cliente->id, 'label' => $cliente->razon_social . ' (' . $cliente->documento . ')'] : null),
        fechaDesde: @json($fechaDesde),
        fechaHasta: @json($fechaHasta),
        hasFilters: @json(request()->has('cliente_id') || request()->has('fecha_desde') || request()->has('fecha_hasta')),
    };
</script>

<div class="flex flex-col gap-6 w-full max-w-full" x-data="extractoReport()">

    {{-- TOOLBAR: CLIENTE + FECHAS + EXPORTS (OCULTO AL IMPRIMIR) --}}
    <div class="bg-white rounded-2xl shadow-sm border p-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between animate-enter no-print">
        <div class="w-full md:max-w-lg">
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                Cliente
            </label>

            <div class="mt-1 relative h-11"> 
                
                {{-- ESTADO 1: INPUT VISIBLE --}}
                <div x-show="!selectedCliente" class="relative w-full">
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchTerm"
                           @input.debounce.300ms="buscarClientes"
                           placeholder="Buscar por razón social o NIT..."
                           class="input-pill !pl-12 h-11 bg-slate-50 focus:bg-white shadow-sm w-full pr-3">

                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- ESTADO 2: CLIENTE SELECCIONADO --}}
                <template x-if="selectedCliente">
                    <div class="absolute inset-0 z-10 w-full h-11 flex items-center justify-between px-4 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-sm font-semibold truncate" x-text="selectedCliente.label"></span>
                        </div>
                        <button type="button" 
                                @click="clearCliente" 
                                class="p-1 rounded-full hover:bg-indigo-200 text-indigo-500 hover:text-indigo-800 transition-colors"
                                title="Cambiar cliente">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>

                <input type="hidden" name="cliente_id" :value="selectedCliente ? selectedCliente.id : ''" form="form-extracto">

                {{-- Resultados de búsqueda --}}
                <div x-show="showDropdown" 
                     x-transition 
                     class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-64 overflow-y-auto no-scrollbar text-sm"
                     style="display: none;">
                    
                    <template x-if="isSearching">
                        <div class="px-3 py-2 text-xs text-slate-400 flex items-center gap-2">
                            <span class="h-3 w-3 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
                            Buscando...
                        </div>
                    </template>

                    <template x-if="!isSearching && clientes.length === 0">
                        <div class="px-3 py-2 text-xs text-slate-400">No se encontraron clientes.</div>
                    </template>

                    <template x-for="c in clientes" :key="c.id">
                        <button type="button"
                                @click="selectCliente(c)"
                                class="w-full text-left px-3 py-2 hover:bg-indigo-50 flex flex-col border-b border-slate-50 last:border-0 hover:text-indigo-600">
                            <span class="text-xs font-semibold text-slate-800" x-text="c.razon_social"></span>
                            <span class="text-[11px] text-slate-500 font-mono" x-text="c.documento"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- FORM FECHAS + BOTONES --}}
        <form id="form-extracto"
              method="GET"
              action="{{ route('informes.extracto') }}"
              class="flex flex-col md:flex-row gap-3 items-end w-full md:w-auto">

            <div class="w-full md:w-auto">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Desde</label>
                <input type="date" name="fecha_desde" x-model="fechaDesde" @change="markDirty" class="input-pill mt-1 h-11 text-sm w-full md:w-44">
            </div>

            <div class="w-full md:w-auto">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Hasta</label>
                <input type="date" name="fecha_hasta" x-model="fechaHasta" @change="markDirty" class="input-pill mt-1 h-11 text-sm w-full md:w-44">
            </div>

            <div class="flex gap-2 w-full md:w-auto justify-end">
                
                {{-- BOTÓN APLICAR --}}
                <button type="submit" 
                        x-show="!hasFilters || isDirty"
                        class="btn-primary h-11 mt-4 flex items-center gap-2 w-full md:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h6a1 1 0 011 1v2h9a1 1 0 011 1v4h-2V8h-8v2a1 1 0 01-1 1H4v9H3a1 1 0 01-1-1V5a1 1 0 011-1z" />
                    </svg>
                    Aplicar
                </button>

                {{-- BOTÓN LIMPIAR --}}
                <a href="{{ route('informes.extracto') }}" 
                   x-show="hasFilters && !isDirty"
                   class="btn-secondary h-11 mt-4 flex items-center justify-center px-3 text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                   title="Restablecer filtros (Limpiar todo)">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </a>

                @if($cliente)
                    <div class="h-8 w-px bg-slate-200 mx-1 hidden md:block self-center mt-4"></div>
                    
                    {{-- BOTÓN CSV --}}
                    <a href="{{ route('informes.extracto', ['cliente_id' => $cliente->id, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'export' => 'csv']) }}"
                       class="btn-secondary h-11 mt-4 flex items-center gap-1 text-xs w-full md:w-auto justify-center" title="Descargar CSV">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        CSV
                    </a>

                    {{-- BOTÓN PDF (IMPRIMIR) --}}
                    <button type="button" 
                            onclick="window.print()"
                            class="bg-indigo-600 text-white hover:bg-indigo-700 h-11 mt-4 px-4 rounded-full flex items-center gap-2 text-xs font-medium transition-colors shadow-sm"
                            title="Generar PDF o Imprimir">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        PDF
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- REPORTE --}}
    @if($cliente)
        {{-- INICIO ID PRINTABLE AREA: TODO LO DE ADENTRO SALE EN EL PDF/IMPRESIÓN --}}
        <div id="printable-area">
            
            {{-- Encabezado Solo para Impresión (Oculto en pantalla) --}}
            <div class="hidden print:block mb-6 border-b pb-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Extracto de Cuenta</h1>
                        <p class="text-sm text-slate-500">Zahara - Sistema de Gestión</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400">Generado el: {{ now()->format('d/m/Y H:i') }}</p>
                        <p class="text-xs text-slate-400">Página 1</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 animate-enter mb-6">
                <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-sm flex flex-col justify-between print:bg-slate-900 print:text-white">
                    <div>
                        <p class="text-[11px] uppercase tracking-widest text-slate-400">Cliente</p>
                        <p class="text-sm font-semibold mt-1 truncate">{{ $cliente->razon_social }}</p>
                        <p class="text-[11px] text-slate-300 font-mono mt-1">{{ $cliente->documento }}</p>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3">
                        Periodo: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} &mdash; {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                    </p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                    <p class="text-[11px] uppercase tracking-widest text-slate-500">Saldo Inicial</p>
                    <p class="text-xl font-bold text-slate-900 mt-1">${{ number_format($saldoInicial, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-400 mt-1">Antes de {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}</p>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 print:bg-indigo-50">
                    <p class="text-[11px] uppercase tracking-widest text-indigo-600">Débitos (Cuentas)</p>
                    <p class="text-xl font-bold text-indigo-900 mt-1">${{ number_format($totalDebitos, 0, ',', '.') }}</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 print:bg-emerald-50">
                    <p class="text-[11px] uppercase tracking-widest text-emerald-600">Créditos (Abonos)</p>
                    <p class="text-xl font-bold text-emerald-900 mt-1">${{ number_format($totalCreditos, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-slate-500 mt-1">Saldo final: <span class="font-bold text-slate-900">${{ number_format($saldoFinal, 0, ',', '.') }}</span></p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-xs font-bold tracking-widest text-slate-500 uppercase">Extracto de movimientos</h3>
                    <span class="text-[11px] text-slate-400">{{ $movimientos->count() }} movimientos</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs w-full">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wide text-slate-500 print:bg-slate-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Tipo</th>
                                <th class="px-4 py-2 text-left">Documento</th>
                                <th class="px-4 py-2 text-left">EDS</th>
                                <th class="px-4 py-2 text-left">Descripción</th>
                                <th class="px-4 py-2 text-right">Débito</th>
                                <th class="px-4 py-2 text-right">Crédito</th>
                                <th class="px-4 py-2 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="bg-slate-50/60 print:bg-slate-50">
                                <td class="px-4 py-2 text-[11px] text-slate-500 italic" colspan="7">Saldo inicial</td>
                                <td class="px-4 py-2 text-right font-bold text-slate-700">${{ number_format($saldoInicial, 0, ',', '.') }}</td>
                            </tr>
                            @forelse($movimientos as $m)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $m['fecha']->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $m['tipo'] === 'Factura' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100 print:text-indigo-800' : 'bg-emerald-50 text-emerald-700 border border-emerald-100 print:text-emerald-800' }}">
                                            {{ $m['tipo'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $m['documento'] }}</td>
                                    <td class="px-4 py-2 text-xs">{{ $m['eds'] }}</td>
                                    <td class="px-4 py-2 text-xs text-slate-600">{{ $m['descripcion'] }}</td>
                                    <td class="px-4 py-2 text-right text-xs text-slate-700">
                                        @if($m['debito'] > 0)
                                            ${{ number_format($m['debito'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-xs text-slate-700">
                                        @if($m['credito'] > 0)
                                            ${{ number_format($m['credito'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-xs font-bold {{ $m['saldo'] >= 0 ? 'text-slate-900' : 'text-red-600' }}">
                                        ${{ number_format($m['saldo'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-400 text-xs">
                                        No se encontraron movimientos en el periodo seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200 text-[11px] text-slate-600 print:bg-slate-100">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right font-bold">Totales del periodo:</td>
                                <td class="px-4 py-2 text-right font-bold text-indigo-700">${{ number_format($totalDebitos, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-bold text-emerald-700">${{ number_format($totalCreditos, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right font-bold text-slate-900">${{ number_format($saldoFinal, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            {{-- Footer solo para impresión --}}
            <div class="hidden print:block mt-8 pt-4 border-t text-center text-[10px] text-slate-400">
                <p>Este documento es un extracto informativo y no reemplaza una factura oficial.</p>
            </div>
            
        </div>
        {{-- FIN ID PRINTABLE AREA --}}
    @endif
</div>

{{-- SCRIPT ALPINE OPTIMIZADO --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('extractoReport', () => ({
            // Estado inicial
            searchTerm: '',
            clientes: [],
            selectedCliente: window.reportConfig.cliente,
            fechaDesde: window.reportConfig.fechaDesde,
            fechaHasta: window.reportConfig.fechaHasta,
            hasFilters: window.reportConfig.hasFilters,

            showDropdown: false,
            isSearching: false,
            isDirty: false,

            // Optimizaciones
            _lastTerm: '',
            _cache: {},
            _abortController: null,

            markDirty() {
                this.isDirty = true;
            },

            buscarClientes() {
                const term = (this.searchTerm || '').trim();

                // No buscar con menos de 2 caracteres
                if (term.length < 2) {
                    this.clientes = [];
                    this.showDropdown = false;
                    this._lastTerm = '';
                    return;
                }

                // Si el término es igual al último, reutilizamos datos
                if (term === this._lastTerm) {
                    this.clientes = this._cache[term] || this.clientes;
                    this.showDropdown = true;
                    return;
                }

                this._lastTerm = term;

                // Cancelar petición anterior si está en curso
                if (this._abortController) {
                    this._abortController.abort();
                }
                this._abortController = new AbortController();

                // Si ya está en cache, no pegamos al servidor
                if (this._cache[term]) {
                    this.clientes = this._cache[term];
                    this.showDropdown = true;
                    return;
                }

                this.isSearching = true;
                this.showDropdown = true;

                const params = new URLSearchParams({ q: term });

                fetch('{{ route('api.clientes.buscar') }}' + '?' + params.toString(), {
                    signal: this._abortController.signal,
                })
                    .then(res => res.ok ? res.json() : [])
                    .then(data => {
                        const result = Array.isArray(data) ? data : [];
                        this._cache[term] = result;
                        this.clientes = result;
                    })
                    .catch(() => {
                        // Si es abort no pasa nada, si es error limpiamos
                        this.clientes = [];
                    })
                    .finally(() => {
                        this.isSearching = false;
                    });
            },

            selectCliente(c) {
                this.selectedCliente = {
                    id: c.id,
                    label: c.razon_social + ' (' + c.documento + ')',
                };
                this.searchTerm = '';
                this.showDropdown = false;
                this.isDirty = true;
            },

            clearCliente() {
                this.selectedCliente = null;
                this.clientes = [];
                this.isDirty = true;
                this._lastTerm = '';

                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            },
        }));
    });
</script>
@endsection
