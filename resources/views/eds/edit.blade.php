@extends('layouts.app')

@section('title', 'Editar EDS - Zahara')
@section('page_title', 'Editar Estación')
@section('page_subtitle', 'Modificando información de: ' . $ed->nombre)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Inicio</a>
    <span class="mx-2">/</span>
    <a href="{{ route('eds.index') }}" class="hover:text-indigo-600 transition-colors">EDS</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Editar</span>
@endsection

@section('content')
    <div class="max-w-4xl animate-enter">
        
        <form action="{{ route('eds.update', $ed) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- SECCIÓN 1: IDENTIDAD -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-colors">
                
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Información General
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 relative z-10">
                    <!-- Código -->
                    <div class="md:col-span-4">
                        <label for="codigo" class="block text-sm font-medium text-slate-700 mb-1.5">Código EDS <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            </div>
                            <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $ed->codigo) }}" 
                                class="input-pill !pl-11 uppercase font-mono tracking-wide focus:border-indigo-500 @error('codigo') border-red-500 text-red-600 @enderror" 
                                required>
                        </div>
                        @error('codigo') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Nombre -->
                    <div class="md:col-span-8">
                        <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre Comercial <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $ed->nombre) }}" 
                            class="input-pill @error('nombre') border-red-500 @enderror" 
                            required>
                        @error('nombre') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- NIT -->
                    <div class="md:col-span-6">
                        <label for="nit" class="block text-sm font-medium text-slate-700 mb-1.5">NIT / Identificación</label>
                        <input type="text" name="nit" id="nit" value="{{ old('nit', $ed->nit) }}" 
                            class="input-pill">
                    </div>

                    <!-- Toggle Activo -->
                    <div class="md:col-span-6 flex items-end pb-1">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="hidden" name="activo" value="0">
                            {{-- Notar el uso de old() con el valor del modelo como fallback --}}
                            <input type="checkbox" name="activo" value="1" class="sr-only peer" {{ old('activo', $ed->activo) ? 'checked' : '' }}>
                            
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            
                            <div class="ml-3 flex flex-col select-none">
                                <span class="text-sm font-medium text-slate-900 group-hover:text-indigo-600 transition-colors">Estado Operativo</span>
                                <span class="text-xs text-slate-500">Habilitar para facturación</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: UBICACIÓN Y CONTACTO -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    Ubicación y Contacto
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Ciudad -->
                    <div>
                        <label for="ciudad" class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad</label>
                        <input type="text" name="ciudad" id="ciudad" value="{{ old('ciudad', $ed->ciudad) }}" 
                            class="input-pill">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $ed->telefono) }}" 
                            class="input-pill">
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-medium text-slate-700 mb-1.5">Dirección Física</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion', $ed->direccion) }}" 
                                class="input-pill !pl-11">
                        </div>
                    </div>

                    <!-- Email Alertas -->
                    <div class="md:col-span-2">
                        <label for="email_alertas" class="block text-sm font-medium text-slate-700 mb-1.5">Email para Notificaciones</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="email" name="email_alertas" id="email_alertas" value="{{ old('email_alertas', $ed->email_alertas) }}" 
                                class="input-pill !pl-11 @error('email_alertas') border-red-500 @enderror">
                        </div>
                        @error('email_alertas') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- ACTION BAR -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                <a href="{{ route('eds.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary min-w-[140px]">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
@endsection