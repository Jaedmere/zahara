@extends('layouts.app')

@section('title', 'Editar Cliente - Zahara')
@section('page_title', 'Editar Cliente')
@section('page_subtitle', 'Modificando información de: ' . $cliente->razon_social)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Inicio</a>
    <span class="mx-2">/</span>
    <a href="{{ route('clientes.index') }}" class="hover:text-indigo-600 transition-colors">Clientes</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Editar</span>
@endsection

@section('content')
    <div class="max-w-5xl animate-enter">
        
        <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- ========================================== -->
            <!-- SECCIÓN 1: DATOS FISCALES (IDENTIDAD)      -->
            <!-- ========================================== -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-colors">
                
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Identidad Fiscal
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 relative z-10">
                    
                    <!-- Tipo ID + Documento -->
                    <div class="md:col-span-5 flex gap-3">
                        <!-- Tipo ID -->
                        <div class="w-1/3">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo</label>
                            <div class="relative">
                                <select name="tipo_id" class="input-pill appearance-none font-medium text-slate-700 focus:text-indigo-600">
                                    @foreach(['NIT', 'CC', 'CE', 'PAS'] as $tipo)
                                        <option value="{{ $tipo }}" {{ old('tipo_id', $cliente->tipo_id) == $tipo ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Documento -->
                        <div class="w-2/3">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Número <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" name="documento" value="{{ old('documento', $cliente->documento) }}" 
                                    class="input-pill font-mono tracking-wide !pl-4 focus:border-indigo-500 @error('documento') border-red-500 text-red-600 @enderror" 
                                    required>
                            </div>
                            @error('documento') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Razón Social -->
                    <div class="md:col-span-7">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón Social / Nombre <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <input type="text" name="razon_social" value="{{ old('razon_social', $cliente->razon_social) }}" 
                                class="input-pill !pl-11 @error('razon_social') border-red-500 @enderror" 
                                required>
                        </div>
                        @error('razon_social') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Estado (Radio Cards) -->
                    <div class="md:col-span-12 pt-2">
                        <span class="block text-sm font-medium text-slate-700 mb-3">Estado Operativo</span>
                        <div class="flex gap-4">
                            @php $estadoActual = old('estado', $cliente->estado); @endphp

                            <!-- Opción Activo -->
                            <label class="cursor-pointer relative">
                                <input type="radio" name="estado" value="activo" class="peer sr-only" {{ $estadoActual == 'activo' ? 'checked' : '' }}>
                                <div class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 peer-checked:bg-emerald-50 peer-checked:border-emerald-200 peer-checked:text-emerald-700 transition-all flex items-center gap-2 shadow-sm">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-sm font-medium">Activo</span>
                                </div>
                            </label>

                            <!-- Opción Bloqueado -->
                            <label class="cursor-pointer relative">
                                <input type="radio" name="estado" value="bloqueado" class="peer sr-only" {{ $estadoActual == 'bloqueado' ? 'checked' : '' }}>
                                <div class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 peer-checked:bg-red-50 peer-checked:border-red-200 peer-checked:text-red-700 transition-all flex items-center gap-2 shadow-sm">
                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    <span class="text-sm font-medium">Bloqueado</span>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- ========================================== -->
                <!-- SECCIÓN 2: CONTACTO Y NOTAS                -->
                <!-- ========================================== -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm h-full">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Contacto y Detalles
                    </h3>

                    <div class="space-y-5">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <input type="email" name="email" value="{{ old('email', $cliente->email) }}" class="input-pill !pl-11">
                            </div>
                        </div>

                        <!-- Teléfono y Dirección -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" class="input-pill">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                                <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" class="input-pill">
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Notas Internas</label>
                            <textarea name="notas" rows="3" class="input-pill resize-none">{{ old('notas', $cliente->notas) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 3: ASIGNACIÓN DE EDS (VISUAL)      -->
                <!-- ========================================== -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm h-full flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Estaciones Autorizadas
                        </h3>
                        <span class="text-[10px] text-slate-400 bg-slate-100 px-2 py-1 rounded-md">Selección Múltiple</span>
                    </div>

                    @if($eds->isEmpty())
                        <div class="flex-1 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-slate-100 rounded-xl">
                            <p class="text-sm text-slate-500">No hay estaciones disponibles.</p>
                        </div>
                    @else
                        <!-- 
                            Pre-cálculo de IDs asignados. 
                            Usamos old('eds_ids') si falló validación, o sacamos los IDs de la relación.
                        -->
                        @php 
                            $assignedIds = old('eds_ids', $cliente->eds->pluck('id')->toArray()); 
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[300px] overflow-y-auto pr-2 scrollbar-hide">
                            @foreach($eds as $e)
                                <label class="cursor-pointer group relative">
                                    <!-- Checkbox con lógica 'in_array' para marcar los que ya tiene -->
                                    <input type="checkbox" name="eds_ids[]" value="{{ $e->id }}" 
                                           class="peer sr-only" 
                                           {{ in_array($e->id, $assignedIds) ? 'checked' : '' }}>
                                    
                                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-indigo-300 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:ring-1 peer-checked:ring-indigo-500 transition-all flex items-start gap-3">
                                        <!-- Fake Checkbox -->
                                        <div class="mt-0.5 w-4 h-4 rounded border border-slate-300 bg-white flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors">
                                            <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-700 peer-checked:text-indigo-900 truncate">{{ $e->nombre }}</p>
                                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">{{ $e->codigo }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-200/60">
                <a href="{{ route('clientes.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary min-w-[140px]">Guardar Cambios</button>
            </div>
        </form>
    </div>
@endsection
```

### Detalle importante en la lógica de las EDS:

Fíjate en esta parte del código:

```php
@php 
    $assignedIds = old('eds_ids', $cliente->eds->pluck('id')->toArray()); 
@endphp