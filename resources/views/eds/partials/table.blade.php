<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            {{-- Header con fondo sutil y tipografía técnica --}}
            <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                <th class="px-6 py-4">Código</th>
                <th class="px-6 py-4">Estación</th>
                <th class="px-6 py-4">NIT</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($eds as $e)
                {{-- Fila con hover suave en tono Indigo --}}
                <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                    
                    {{-- Columna Código --}}
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-mono font-medium border border-slate-200 group-hover:border-indigo-200 group-hover:bg-white transition-colors">
                                {{ $e->codigo }}
                            </span>
                            
                            {{-- Indicador visual (Punto Rojo) si está inactiva --}}
                            @if(!$e->activo)
                                <span class="relative flex h-2.5 w-2.5" title="Estación Inactiva">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Columna Nombre --}}
                    <td class="px-6 py-3">
                        <div class="flex flex-col">
                            <span class="font-semibold text-sm {{ $e->activo ? 'text-slate-700 group-hover:text-indigo-900' : 'text-slate-400' }} transition-colors">
                                {{ $e->nombre }}
                            </span>
                            @if($e->ciudad)
                                <span class="text-[11px] text-slate-400 group-hover:text-indigo-400 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $e->ciudad }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Columna NIT --}}
                    <td class="px-6 py-3">
                        <span class="text-sm text-slate-500 font-mono">
                            {{ $e->nit ?? '—' }}
                        </span>
                    </td>

                    {{-- Columna Acciones --}}
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-x-2 group-hover:translate-x-0">
                            
                            {{-- Botón Editar --}}
                            <a href="{{ route('eds.edit', $e) }}" 
                               class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-indigo-100 hover:text-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40" 
                               title="Editar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            
                    {{-- Lógica de Botones Activo/Inactivo --}}
                    @if($e->activo)
                        {{-- FORMULARIO DE DESACTIVACIÓN --}}
                        <form action="{{ route('eds.destroy', $e) }}" method="POST" 
                            {{-- AQUÍ ESTÁ EL CAMBIO: --}}
                            @submit.prevent="$dispatch('confirm-action', { 
                                form: $el, 
                                title: '¿Desactivar Estación?', 
                                message: 'La estación {{ $e->nombre }} pasará a estado inactivo y no podrá facturar. ¿Deseas continuar?' 
                            })"
                        >
                            @csrf @method('DELETE')
                            
                            <button type="submit" 
                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500/40"
                                title="Desactivar Estación">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </button>
                        </form>
                    @else
                                {{-- Si es inactiva -> Badge visual --}}
                                <span class="inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold uppercase tracking-wide border border-red-100 select-none">
                                    Inactiva
                                </span>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                {{-- Estado Vacío --}}
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-slate-500">No se encontraron resultados</span>
                            <p class="text-xs text-slate-400 mt-1">Prueba ajustando los filtros de búsqueda o estado.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Paginación Integrada --}}
@if($eds->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
        {{ $eds->withQueryString()->links() }}
    </div>
@endif