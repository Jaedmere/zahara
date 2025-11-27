@extends('layouts.app')

@section('title', 'Editar Cuenta - Zahara')
@section('page_title', 'Editar Cuenta')

@section('breadcrumb')
    <a href="{{ route('facturas.index') }}" class="hover:text-indigo-600 transition-colors">Cuentas</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Editar #{{ $factura->consecutivo }}</span>
@endsection

@section('content')
<div class="max-w-4xl animate-enter">
    
    {{-- ALERTA DE BLOQUEO FINANCIERO --}}
    @if($factura->saldo_pendiente < $factura->valor_total)
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700 font-bold">
                        Edición Restringida
                    </p>
                    <p class="text-xs text-amber-600 mt-1">
                        Esta cuenta ya tiene abonos registrados. Por seguridad contable, <strong>no se pueden modificar los valores monetarios, el cliente ni la EDS</strong>. Si necesita corregir valores, debe anular los abonos primero.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('facturas.update', $factura) }}" method="POST" class="space-y-6"
          x-data="{ 
              neto: {{ $factura->valor_neto }}, 
              descuento: {{ $factura->descuento }}, 
              get total() { 
                  let n = parseFloat(this.neto) || 0;
                  let d = parseFloat(this.descuento) || 0;
                  let t = n - d;
                  return t > 0 ? t.toFixed(2) : '0.00';
              } 
          }">
        @csrf
        @method('PUT')

        @php
            // Bloquear campos si ya tiene pagos
            $readonly = $factura->saldo_pendiente < $factura->valor_total ? 'disabled' : '';
            $readonlyClass = $readonly ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white';
        @endphp

        <!-- CONTEXTO (EDS / CLIENTE) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Contexto
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Estación de Servicio</label>
                    <select name="eds_id" class="input-pill {{ $readonlyClass }}" {{ $readonly }}>
                        @foreach($eds as $e)
                            <option value="{{ $e->id }}" {{ $factura->eds_id == $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cliente</label>
                    <select name="cliente_id" class="input-pill {{ $readonlyClass }}" {{ $readonly }}>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}" {{ $factura->cliente_id == $c->id ? 'selected' : '' }}>
                                {{ $c->razon_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- DETALLE FINANCIERO -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Detalle Financiero
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Prefijo</label>
                    <input type="text" name="prefijo" value="{{ old('prefijo', $factura->prefijo) }}" class="input-pill uppercase text-center font-mono {{ $readonlyClass }}" {{ $readonly }}>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">N° Cuenta</label>
                    <input type="text" name="consecutivo" value="{{ old('consecutivo', $factura->consecutivo) }}" class="input-pill font-mono font-bold tracking-wide {{ $readonlyClass }}" {{ $readonly }}>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <!-- LAS FECHAS SIEMPRE SE PUEDEN EDITAR (Para correcciones de vencimiento) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha Emisión</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', $factura->fecha_emision->format('Y-m-d')) }}" class="input-pill">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento', $factura->fecha_vencimiento->format('Y-m-d')) }}" class="input-pill">
                </div>
            </div>

            <!-- VALORES -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Valor Neto</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="valor_neto" x-model="neto" step="0.01" class="input-pill !pl-8 text-lg {{ $readonlyClass }}" {{ $readonly }}>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Descuento (-)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" name="descuento" x-model="descuento" step="0.01" class="input-pill !pl-8 text-lg text-red-600 {{ $readonlyClass }}" {{ $readonly }}>
                        </div>
                    </div>

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
                 <textarea name="notas" rows="2" class="input-pill resize-none">{{ old('notas', $factura->notas) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('facturas.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" class="btn-primary min-w-[150px]">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection