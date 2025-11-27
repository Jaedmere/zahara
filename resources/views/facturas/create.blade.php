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
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Datos de la Cuenta
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <!-- EDS -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Estación de Servicio <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="eds_id" class="input-pill appearance-none" required>
                            <option value="" disabled selected>Seleccione EDS...</option>
                            @foreach($eds as $e)
                                <option value="{{ $e->id }}" {{ old('eds_id') == $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Cliente -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cliente <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="cliente_id" class="input-pill appearance-none" required>
                            <option value="" disabled selected>Seleccione Cliente...</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" {{ old('cliente_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->razon_social }} ({{ $c->documento }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN: DETALLE FINANCIERO -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Detalle Financiero
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Prefijo -->
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Prefijo</label>
                    <input type="text" name="prefijo" value="{{ old('prefijo', 'FE') }}" class="input-pill uppercase text-center font-mono" placeholder="FE">
                </div>
                <!-- Consecutivo -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">N° Cuenta <span class="text-red-500">*</span></label>
                    <input type="text" name="consecutivo" value="{{ old('consecutivo') }}" class="input-pill font-mono font-bold tracking-wide" placeholder="10025" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <!-- Fechas -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha Emisión <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="input-pill" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha Vencimiento <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" class="input-pill" required>
                </div>
            </div>

            <!-- VALORES (Calculadora Alpine) -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    
                    <!-- Neto -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Valor Neto</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="valor_neto" x-model="neto" step="0.01" class="input-pill !pl-8 text-lg" placeholder="0.00" required>
                        </div>
                    </div>

                    <!-- Descuento -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Descuento (-)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="descuento" x-model="descuento" step="0.01" class="input-pill !pl-8 text-lg text-red-600" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="text-right pb-1">
                        <span class="block text-xs font-bold text-slate-400 uppercase mb-1">Total a Pagar</span>
                        <div class="text-3xl font-bold text-indigo-600 font-mono tracking-tight flex items-center justify-end gap-1">
                            <span class="text-xl text-indigo-400">$</span>
                            <span x-text="total">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                 <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas / Observaciones</label>
                 <textarea name="notas" rows="2" class="input-pill resize-none" placeholder="Opcional..."></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('facturas.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary min-w-[150px]">Registrar Cuenta</button>
        </div>
    </form>
</div>
@endsection