@extends('layouts.app')

@section('title', 'Editar Rol - Zahara')
@section('page_title', 'Editar Rol')
@section('breadcrumb')
    <a href="{{ route('roles.index') }}" class="hover:text-indigo-600 transition-colors">Roles</a>
    <span class="mx-2">/</span>
    <span class="font-medium text-slate-900">Editar</span>
@endsection

@section('content')
    <div class="max-w-3xl animate-enter">
        <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Rol <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre', $role->nombre) }}" class="input-pill" required>
                @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/60 shadow-sm">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Permisos de Acceso
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php 
                        $actuales = $role->permisos_json ?? []; 
                    @endphp
                    @foreach($permisos as $key => $label)
                        <label class="flex items-center p-3 border border-slate-100 rounded-xl hover:bg-slate-50 cursor-pointer transition-colors">
                            {{-- in_array verifica si la key está en el JSON guardado --}}
                            <input type="checkbox" name="permisos[]" value="{{ $key }}" 
                                   class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"
                                   {{ in_array($key, $actuales) ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-medium text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('roles.index') }}" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary">Actualizar Rol</button>
            </div>
        </form>
    </div>
@endsection