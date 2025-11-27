@extends('layouts.app')

@section('title', 'Nueva Cuenta - Zahara')
@section('page_title', 'Registrar Cuenta')

@section('breadcrumb')
    <a href="{{ route('facturas.index') }}" class="hover:text-indigo-600 transition-colors">Cuentas</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Nueva</span>
@endsection

@section('content')
<div class="max-w-4xl animate-enter">
    <form action="{{ route('facturas.store') }}" method="POST" class="space-y-6"
          x-data="{ 
              neto: '', 
              descuento: '', 
              get total() { 
                  let n = parseFloat(this.neto) || 0;
                  let d = parseFloat(this.descuento) || 0;
                  let t = n - d;
                  return t > 0 ? t.toFixed(2) : '0.00';
              } 
          }">
        @csrf

        <!-- SECCIÓN: DATOS DE CONTEXTO -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Contexto
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Estación <span class="text-red-500">*</span></label>
                    <select name="eds_id" class="input-pill appearance-none" required>
                        <option value="" disabled selected>Seleccione EDS...</option>
                        @foreach($eds as $e) <option value="{{ $e->id }}" {{ old('eds_id')==$e->id?'selected':'' }}>{{ $e->nombre }}</option> @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cliente <span class="text-red-500">*</span></label>
                    <select name="cliente_id" class="input-pill appearance-none" required>
                        <option value="" disabled selected>Seleccione Cliente...</option>
                        @foreach($clientes as $c) <option value="{{ $c->id }}" {{ old('cliente_id')==$c->id?'selected':'' }}>{{ $c->razon_social }} ({{ $c->documento }})</option> @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: DETALLE FINANCIERO -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Detalle Financiero
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Prefijo</label>
                    <input type="text" name="prefijo" value="{{ old('prefijo', 'FE') }}" class="input-pill uppercase text-center font-mono">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">N° Cuenta <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutivo" value="{{ old('consecutivo') }}" class="input-pill font-mono font-bold tracking-wide" required>
                </div>
            </div>

            <!-- FECHAS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-slate-50 rounded-xl">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Emisión</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="input-pill" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="input-pill" required>
                </div>
            </div>

            <!-- PERIODO DE CORTE -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-indigo-50/50 rounded-xl border border-indigo-100">
                <div class="md:col-span-2">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wide flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Periodo Facturado (Corte)
                    </span>
                </div>
                <div>
                    <label class="block text-xs text-indigo-500 mb-1">Desde</label>
                    <input type="date" name="corte_desde" value="{{ old('corte_desde') }}" class="input-pill border-indigo-200 focus:border-indigo-500" required>
                </div>
                <div>
                    <label class="block text-xs text-indigo-500 mb-1">Hasta</label>
                    <input type="date" name="corte_hasta" value="{{ old('corte_hasta') }}" class="input-pill border-indigo-200 focus:border-indigo-500" required>
                </div>
            </div>

            <!-- VALORES -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Valor Neto</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="valor_neto" x-model="neto" step="0.01" class="input-pill !pl-8 text-lg" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Descuento</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="descuento" x-model="descuento" step="0.01" class="input-pill !pl-8 text-lg text-red-600">
                        </div>
                    </div>
                    <div class="text-right pb-1">
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Total</span>
                        <div class="text-3xl font-bold text-indigo-600 font-mono tracking-tight flex items-center justify-end gap-1">
                            <span class="text-xl text-indigo-400">$</span>
                            <span x-text="total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                 <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas</label>
                 <textarea name="notas" rows="2" class="input-pill resize-none"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('facturas.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary min-w-[150px]">Guardar</button>
        </div>
    </form>
</div>
@endsection