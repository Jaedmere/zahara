<div class="w-full">
    
    <!-- VISTA MÓVIL (CARDS) -->
    <div class="md:hidden space-y-3 p-4">
        @forelse($users as $user)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm leading-tight">{{ $user->name }}</h3>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-medium">
                                    {{ $user->role->nombre ?? 'Sin Rol' }}
                                </span>
                                @if(!$user->activo)
                                    <span class="text-[9px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-bold uppercase">Inactivo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-xs text-slate-500 mb-4 border-t border-slate-50 pt-2 space-y-1">
                    <div class="flex justify-between">
                        <span class="text-[10px] uppercase text-slate-400">Email</span>
                        <span class="truncate ml-2">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] uppercase text-slate-400">Acceso EDS</span>
                        <span class="font-medium text-slate-700">
                            {{ $user->eds_count }} estaciones
                        </span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('users.edit', $user) }}" class="flex-1 btn-secondary py-2 text-xs justify-center">Editar</a>
                    
                    @if($user->activo)
                        <button type="button" 
                                class="p-2 rounded-lg bg-red-50 text-red-600 border border-red-100 flex items-center justify-center"
                                x-on:click="$dispatch('confirm-action', { 
                                    form: $el.nextElementSibling, 
                                    title: 'Desactivar Usuario', 
                                    message: 'El usuario {{ $user->name }} perderá el acceso al sistema. ¿Continuar?' 
                                })">
                           <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </button>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm">No se encontraron usuarios.</div>
        @endforelse
    </div>

    <!-- VISTA DESKTOP -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Rol</th>
                    <th class="px-6 py-4 text-center">Acceso EDS</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold uppercase">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm text-slate-700">{{ $user->name }}</span>
                                    <span class="text-xs text-slate-400">{{ $user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $user->role->nombre ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->eds_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-50 text-slate-400' }}">
                                {{ $user->eds_count }} Asignadas
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @if($user->activo)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <a href="{{ route('users.edit', $user) }}" class="p-1 text-slate-400 hover:text-indigo-600" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @if($user->activo)
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                          x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Desactivar', message: '¿Confirmas desactivar a este usuario?' })">
                                        @csrf @method('DELETE')
                                        <button class="p-1 text-slate-400 hover:text-red-600" title="Desactivar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
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

@if($users->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
        {{ $users->withQueryString()->links() }}
    </div>
@endif