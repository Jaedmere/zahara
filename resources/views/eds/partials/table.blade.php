<div class="w-full">
    
    <!-- VISTA MÓVIL (CARDS) -->
    <div class="md:hidden space-y-3 p-4">
        @forelse($eds as $e)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-bold text-xs border border-indigo-100">
                            {{ $e->codigo }}
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm leading-tight">{{ $e->nombre }}</h3>
                            @if(!$e->activo)
                                <span class="bg-red-100 text-red-700 text-[9px] px-1.5 py-0.5 rounded font-bold uppercase">Inactiva</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 mb-4 border-t border-slate-50 pt-2">
                    <div>
                        <span class="block text-[10px] uppercase text-slate-400 mb-0.5">Ciudad</span>
                        {{ $e->ciudad ?? 'N/A' }}
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] uppercase text-slate-400 mb-0.5">NIT</span>
                        {{ $e->nit ?? 'N/A' }}
                    </div>
                    @if($e->direccion)
                        <div class="col-span-2 truncate mt-1">
                            <span class="block text-[10px] uppercase text-slate-400 mb-0.5">Dirección</span>
                            {{ $e->direccion }}
                        </div>
                    @endif
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('eds.edit', $e) }}" class="flex-1 btn-secondary py-2 text-xs justify-center">Editar</a>
                    
                    @if($e->activo)
                        <button type="button" 
                                class="p-2 rounded-lg bg-red-50 text-red-600 border border-red-100 flex items-center justify-center"
                                x-on:click="$dispatch('confirm-action', { 
                                    form: $el.nextElementSibling, 
                                    title: 'Desactivar', 
                                    message: '¿Desactivar la estación {{ $e->nombre }}?' 
                                })">
                           <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </button>
                        <form action="{{ route('eds.destroy', $e) }}" method="POST" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm">No se encontraron estaciones.</div>
        @endforelse
    </div>

    <!-- VISTA DESKTOP (TABLA TRADICIONAL) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                    <th class="px-6 py-4">Código</th>
                    <th class="px-6 py-4">Estación</th>
                    <th class="px-6 py-4">NIT</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($eds as $e)
                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-mono font-medium border border-slate-200 group-hover:border-indigo-200 group-hover:bg-white transition-colors">
                                    {{ $e->codigo }}
                                </span>
                                @if(!$e->activo)
                                    <span class="relative flex h-2.5 w-2.5" title="Inactiva">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex flex-col">
                                <span class="font-semibold text-sm {{ $e->activo ? 'text-slate-700 group-hover:text-indigo-900' : 'text-slate-400' }}">
                                    {{ $e->nombre }}
                                </span>
                                @if($e->ciudad)
                                    <span class="text-[11px] text-slate-400 group-hover:text-indigo-400 transition-colors">{{ $e->ciudad }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-500 font-mono">
                            {{ $e->nit ?? '—' }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <a href="{{ route('eds.edit', $e) }}" class="p-1 text-slate-400 hover:text-indigo-600" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @if($e->activo)
                                    <form action="{{ route('eds.destroy', $e) }}" method="POST" 
                                          x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Desactivar', message: '¿Desactivar estación?' })">
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

@if($eds->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
        {{ $eds->withQueryString()->links() }}
    </div>
@endif