<div class="w-full">
    
    <!-- VISTA MÓVIL (CARDS) -->
    <div class="md:hidden space-y-3 p-4">
        @forelse($roles as $role)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-slate-800 text-base">{{ $role->nombre }}</h3>
                    <span class="bg-indigo-50 text-indigo-700 text-[10px] px-2 py-1 rounded-md font-bold">
                        {{ $role->users_count }} Usuarios
                    </span>
                </div>
                
                {{-- Mostramos conteo de permisos --}}
                <div class="flex gap-2 mb-4">
                    @php $pCount = count($role->permisos_json ?? []); @endphp
                    <span class="text-xs text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                        {{ $pCount }} Permiso{{ $pCount !== 1 ? 's' : '' }} Activos
                    </span>
                </div>

                <div class="flex gap-2 border-t border-slate-50 pt-3">
                    <a href="{{ route('roles.edit', $role) }}" class="flex-1 btn-secondary py-2 text-xs justify-center">Editar</a>
                    
                    @if($role->users_count === 0)
                        <button type="button" 
                                class="p-2 rounded-lg bg-red-50 text-red-600 border border-red-100 flex items-center justify-center"
                                x-on:click="$dispatch('confirm-action', { 
                                    form: $el.nextElementSibling, 
                                    title: 'Eliminar Rol', 
                                    message: '¿Estás seguro de eliminar el rol {{ $role->nombre }}?' 
                                })">
                           <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm">No hay roles registrados.</div>
        @endforelse
    </div>

    <!-- VISTA DESKTOP -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                    <th class="px-6 py-4">Nombre del Rol</th>
                    <th class="px-6 py-4">Permisos</th>
                    <th class="px-6 py-4 text-center">Usuarios Asignados</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($roles as $role)
                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                        <td class="px-6 py-3 font-semibold text-sm text-slate-700">
                            {{ $role->nombre }}
                        </td>
                        <td class="px-6 py-3 text-xs text-slate-500">
                            {{-- Convertimos el JSON a string legible para la tabla --}}
                            @if(!empty($role->permisos_json))
                                <span class="truncate max-w-xs block" title="{{ implode(', ', $role->permisos_json) }}">
                                    {{ count($role->permisos_json) }} Módulos habilitados
                                </span>
                            @else
                                <span class="text-slate-400 italic">Ninguno</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $role->users_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $role->users_count }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <a href="{{ route('roles.edit', $role) }}" class="p-1 text-slate-400 hover:text-indigo-600" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @if($role->users_count === 0)
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" 
                                          x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Eliminar Rol', message: '¿Eliminar este rol permanentemente?' })">
                                        @csrf @method('DELETE')
                                        <button class="p-1 text-slate-400 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($roles->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
        {{ $roles->withQueryString()->links() }}
    </div>
@endif