@extends('layouts.app')

@section('title', 'Editar Usuario - Zahara')
@section('page_title', 'Editar Usuario')
@section('page_subtitle', 'Modificando perfil de: ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Inicio</a>
    <span class="mx-2">/</span>
    <a href="{{ route('users.index') }}" class="hover:text-indigo-600 transition-colors">Usuarios</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Editar</span>
@endsection

@section('content')
    <div class="max-w-5xl animate-enter">
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- DATOS DE CUENTA -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-colors">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Datos de Cuenta
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                            class="input-pill" required>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                            class="input-pill" required>
                    </div>

                    <!-- Password (Opcional) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nueva Contraseña</label>
                        <input type="password" name="password" 
                            class="input-pill" placeholder="Dejar en blanco para no cambiar">
                        <p class="text-[10px] text-slate-400 mt-1">Mínimo 8 caracteres si desea cambiarla.</p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Rol de Usuario <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="rol_id" class="input-pill appearance-none" required>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol_id', $user->rol_id) == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="md:col-span-2 pt-2">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" class="sr-only peer" {{ old('activo', $user->activo) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-sm font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">Usuario Activo (Permitir Acceso)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ASIGNACIÓN DE EDS -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm flex flex-col h-full">
                @php
                    // IDs ya asignados
                    $assignedIds = old('eds_ids', $user->eds->pluck('id')->toArray());
                    $edsData = $eds->map(function($e) {
                        return ['id' => $e->id, 'search_text' => strtolower($e->nombre . ' ' . $e->codigo)];
                    })->values();
                @endphp

                <div x-data='{ 
                        search: "",
                        selected: {{ json_encode($assignedIds) }}, 
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
                    
                    <div class="flex flex-col gap-3 mb-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                Acceso a Estaciones (<span x-text="selected.length"></span>)
                            </h3>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="toggleAll(true)" class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors">Todas</button>
                                <button type="button" @click="toggleAll(false)" class="text-[10px] font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded transition-colors">Ninguna</button>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" x-model="search" placeholder="Filtrar estaciones..." class="w-full rounded-lg border border-slate-200 pl-9 pr-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                        </div>
                    </div>

                    @if($eds->isEmpty())
                        <div class="text-center py-8 border-2 border-dashed border-slate-100 rounded-xl text-slate-500 text-sm">No hay estaciones activas.</div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[250px] overflow-y-auto pr-1 scrollbar-hide content-start">
                            @foreach($eds as $e)
                                <label class="cursor-pointer group relative"
                                       x-show="!search || '{{ strtolower($e->nombre . ' ' . $e->codigo) }}'.includes(search.toLowerCase())"
                                       x-transition:enter="transition ease-out duration-200"
                                       x-transition:enter-start="opacity-0 scale-95"
                                       x-transition:enter-end="opacity-100 scale-100">
                                    <input type="checkbox" name="eds_ids[]" value="{{ $e->id }}" x-model="selected" class="peer sr-only">
                                    <div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50/30 hover:bg-white hover:border-indigo-300 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:ring-1 peer-checked:ring-indigo-500 transition-all flex items-center gap-3">
                                        <div class="w-4 h-4 rounded border border-slate-300 bg-white flex items-center justify-center peer-checked:bg-indigo-500 peer-checked:border-indigo-500 transition-colors flex-shrink-0">
                                            <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-700 peer-checked:text-indigo-900 truncate">{{ $e->nombre }}</p>
                                            <p class="text-[10px] text-slate-500 font-mono">{{ $e->codigo }}</p>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary min-w-[140px]">Guardar Cambios</button>
            </div>
        </form>
    </div>
@endsection