<div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="hidden md:block w-full overflow-x-auto">
        <table class="w-full min-w-full text-left border-collapse table-fixed">
            <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 w-4/12">Cliente</th>
                    <th class="px-6 py-4 w-3/12 text-right">Deuda Total</th>
                    <th class="px-6 py-4 w-4/12">Última Gestión</th>
                    <th class="px-6 py-4 w-1/12"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($clientes as $c)
                    <tr class="group hover:bg-slate-50 transition-colors cursor-pointer"
                        onclick="window.dispatchEvent(new CustomEvent('open-crm', { detail: { id: {{ $c->id }}, name: '{{ addslashes($c->razon_social) }}' } }))">
                        
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-sm truncate" title="{{ $c->razon_social }}">{{ $c->razon_social }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $c->documento }}</div>
                        </td>
                        
                        <td class="px-6 py-4 text-right font-mono text-slate-700 font-bold">
                            ${{ number_format($c->deuda_total, 0, ',', '.') }}
                        </td>

                        <td class="px-6 py-4">
                            @if($c->seguimientos->isNotEmpty())
                                @php $last = $c->seguimientos->first(); @endphp
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full {{ $last->estado === 'pendiente' ? ($last->fecha_compromiso < now() ? 'bg-red-500' : 'bg-amber-400') : 'bg-emerald-400' }}"></div>
                                    <div class="truncate max-w-[200px]">
                                        <span class="text-xs text-slate-600 block truncate">{{ $last->observacion }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $last->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-400 italic">Sin gestión registrada</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            <button class="p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-12 text-slate-400 text-sm">No hay clientes pendientes por gestionar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Mobile View (Cards) -->
    <div class="md:hidden p-4 space-y-3">
        @foreach($clientes as $c)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm active:scale-[0.98] transition-transform"
                 onclick="window.dispatchEvent(new CustomEvent('open-crm', { detail: { id: {{ $c->id }}, name: '{{ addslashes($c->razon_social) }}' } }))">
                <div class="flex justify-between items-start">
                    <h3 class="font-bold text-slate-800 text-sm truncate pr-4">{{ $c->razon_social }}</h3>
                    <span class="font-mono font-bold text-slate-700 text-sm">${{ number_format($c->deuda_total, 0) }}</span>
                </div>
                @if($c->seguimientos->isNotEmpty())
                    <div class="mt-2 pt-2 border-t border-slate-50 text-xs text-slate-500 truncate">
                        <span class="font-bold">Último:</span> {{ $c->seguimientos->first()->observacion }}
                    </div>
                @endif
                <div class="mt-2 text-center py-1.5 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-lg">
                    Gestionar
                </div>
            </div>
        @endforeach
    </div>
    
    @if($clientes->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
            {{ $clientes->withQueryString()->links() }}
        </div>
    @endif
</div>