@extends('layouts.app')

@section('title', 'Nuevo Usuario - Zahara')
@section('page_title', 'Registrar Usuario')
@section('page_subtitle', 'Crea una nueva cuenta de acceso al sistema.')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Inicio</a>
    <span class="mx-2">/</span>
    <a href="{{ route('users.index') }}" class="hover:text-indigo-600 transition-colors">Usuarios</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Nuevo</span>
@endsection

@section('content')
    {{-- MISMO CONTENEDOR RAÍZ QUE EL INDEX --}}
    <div class="flex flex-col gap-6 w-full animate-enter">

        <form action="{{ route('users.store') }}" method="POST" class="space-y-8">
            @csrf

            <!-- DATOS DE CUENTA -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-colors">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Datos de Cuenta
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input-pill" placeholder="Juan Pérez" required autofocus>
                        @error('name')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input-pill" placeholder="juan@empresa.com" required>
                        @error('email')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password"
                            class="input-pill" placeholder="********" required>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Rol de Usuario <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="rol_id" class="input-pill appearance-none" required>
                                <option value="" disabled selected>Seleccionar rol...</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('rol_id')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div class="md:col-span-2 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-sm font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">
                                Usuario Activo (Permitir Acceso)
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ASIGNACIÓN DE EDS -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col h-full">
                @php
                    $preSelected = old('eds_ids', []);
                    $edsData = $eds->map(function($e) {
                        return ['id' => $e->id, 'search_text' => strtolower($e->nombre . ' ' . $e->codigo)];
                    })->values();
                @endphp

                <div x-data='{
                        search: "",
                        selected: {{ json_encode($preSelected) }},
                        stations: {{ json_encode($edsData) }},
                        get filteredStations() {
                            if (!this.search) return this.stations;
                            return this.stations.filter(s => s.search_text.includes(this.search.toLowerCase()));
                        },
                        toggleAll(state) {
                            const visibleIds = this.filteredStations.map(s => s.id);
                            if (state) { this.selected = [...new Set([...this.selected, ...visibleIds])]; }
                            else { this.selected = this.selected.filter(id => !visibleIds.includes(id)); }
                        }
                    }'>
                    {{-- aquí dejas tu bloque Alpine + checkboxes tal cual --}}
                    @if($eds->isEmpty())
                        <div class="text-center py-8 border-2 border-dashed border-slate-100 rounded-xl text-slate-500 text-sm">
                            No hay estaciones activas.
                        </div>
                    @else
                        <div class="flex flex-col gap-3 mb-4">
                            {{-- … resto de tu código … --}}
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary min-w-[140px]">Crear Usuario</button>
            </div>
        </form>
    </div>
@endsection
