<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                <th class="px-6 py-4">Documento</th>
                <th class="px-6 py-4">Razón Social</th>
                <th class="px-6 py-4">Contacto</th>
                <th class="px-6 py-4 text-center">Asignaciones</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($clientes as $c)
                <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                    
                    {{-- Columna Documento --}}
                    <td class="px-6 py-3">
                        <div class="flex flex-col">
                            <span class="font-mono text-sm font-medium text-slate-700">{{ $c->documento }}</span>
                            <span class="text-[10px] text-slate-400 uppercase">{{ $c->tipo_id }}</span>
                        </div>
                    </td>

                    {{-- Columna Razón Social + Estado --}}
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <div class="font-semibold text-sm {{ $c->estado === 'activo' ? 'text-slate-700 group-hover:text-indigo-900' : 'text-slate-400' }} transition-colors">
                                {{ $c->razon_social }}
                            </div>
                            @if($c->estado === 'bloqueado')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700 border border-red-200">
                                    BLOQUEADO
                                </span>
                            @endif
                        </div>
                        @if($c->direccion)
                             <div class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ $c->direccion }}</div>
                        @endif
                    </td>

                    {{-- Columna Contacto --}}
                    <td class="px-6 py-3">
                        <div class="flex flex-col gap-0.5">
                            @if($c->email)
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $c->email }}
                                </div>
                            @endif
                            @if($c->telefono)
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $c->telefono }}
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Columna EDS Asignadas (Count) --}}
                    <td class="px-6 py-3 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $c->eds_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-50 text-slate-400 border border-slate-100' }}">
                            {{ $c->eds_count }} EDS
                        </span>
                    </td>

                    {{-- Acciones --}}
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-x-2 group-hover:translate-x-0">
                            
                            {{-- Editar --}}
                            <a href="{{ route('clientes.edit', $c) }}" 
                               class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 hover:text-indigo-600 transition-colors" 
                               title="Editar Cliente">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            
                            {{-- Bloquear / Desbloquear --}}
                            @if($c->estado === 'activo')
                                <form action="{{ route('clientes.destroy', $c) }}" method="POST" 
                                      @submit.prevent="$dispatch('confirm-action', { 
                                          form: $el, 
                                          title: '¿Bloquear Cliente?', 
                                          message: 'El cliente {{ $c->razon_social }} será bloqueado y no podrá operar. ¿Confirmar?' 
                                      })">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                        class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        title="Bloquear">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </button>
                                </form>
                            @else
                                {{-- Aquí podrías poner un botón para reactivar si tuvieras la ruta --}}
                                <span class="h-8 w-8 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <span class="text-sm font-medium text-slate-500">No se encontraron clientes</span>
                            <p class="text-xs text-slate-400 mt-1">Intenta con otro término de búsqueda.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($clientes->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
        {{ $clientes->withQueryString()->links() }}
    </div>
@endif