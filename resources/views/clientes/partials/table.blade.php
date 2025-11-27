<div class="w-full">
    
    <!-- VISTA MÓVIL (CARDS) -->
    <div class="md:hidden space-y-3 p-4">
        @forelse($clientes as $c)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm active:scale-[0.98] transition-transform">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800 text-base">{{ $c->razon_social }}</h3>
                            @if($c->estado === 'bloqueado')
                                <span class="bg-red-100 text-red-700 text-[9px] px-1.5 py-0.5 rounded font-bold">BLOQ</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $c->tipo_id }} {{ $c->documento }}</p>
                    </div>
                    {{-- Badge EDS Count --}}
                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold {{ $c->eds_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $c->eds_count }} EDS
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 mb-3 border-t border-slate-50 pt-2 mt-2">
                    <div class="truncate">
                        <span class="block text-[10px] uppercase text-slate-400">Email</span>
                        {{ $c->email ?? '--' }}
                    </div>
                    <div class="truncate text-right">
                        <span class="block text-[10px] uppercase text-slate-400">Teléfono</span>
                        {{ $c->telefono ?? '--' }}
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('clientes.edit', $c) }}" class="flex-1 btn-secondary py-2 text-xs justify-center">Editar</a>
                    
                    @if($c->estado === 'activo')
                        <button type="button" 
                                class="p-2 rounded-lg bg-red-50 text-red-600 border border-red-100"
                                @click="$dispatch('confirm-action', { form: $el.nextElementSibling, title: 'Bloquear', message: '¿Bloquear a {{ $c->razon_social }}?' })">
                           <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </button>
                        <form action="{{ route('clientes.destroy', $c) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm">No se encontraron clientes.</div>
        @endforelse
    </div>

    <!-- VISTA DESKTOP (TABLA TRADICIONAL) -->
    <div class="hidden md:block overflow-x-auto">
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
                @foreach($clientes as $c)
                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-200">
                        <td class="px-6 py-3">
                            <div class="flex flex-col">
                                <span class="font-mono text-sm font-medium text-slate-700">{{ $c->documento }}</span>
                                <span class="text-[10px] text-slate-400 uppercase">{{ $c->tipo_id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <div class="font-semibold text-sm {{ $c->estado === 'activo' ? 'text-slate-700' : 'text-slate-400' }}">
                                    {{ $c->razon_social }}
                                </div>
                                @if($c->estado === 'bloqueado')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">BLOQ</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-xs text-slate-500">
                            <div>{{ $c->email ?? '-' }}</div>
                            <div>{{ $c->telefono ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $c->eds_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-50 text-slate-400' }}">
                                {{ $c->eds_count }} EDS
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('clientes.edit', $c) }}" class="p-1 text-slate-400 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></a>
                                @if($c->estado === 'activo')
                                    <form action="{{ route('clientes.destroy', $c) }}" method="POST" 
                                          @submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Bloquear', message: '¿Bloquear cliente?' })">
                                        @csrf @method('DELETE')
                                        <button class="p-1 text-slate-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></button>
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

@if($clientes->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
        {{ $clientes->withQueryString()->links() }} {{-- Laravel Tailwind Pagination es responsive por defecto --}}
    </div>
@endif