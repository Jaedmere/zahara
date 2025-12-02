<div class="flex flex-col gap-4 w-full">
    
    {{-- TOTALIZADOR --}}
    <div class="flex justify-end w-full">
        <div class="bg-indigo-600 text-white px-4 py-2 rounded-xl shadow-sm flex items-center gap-3 animate-enter">
            <div class="text-[10px] font-bold uppercase opacity-75 text-right leading-tight">Total Cartera<br>Visible</div>
            <div class="text-xl font-mono font-bold border-l border-indigo-400 pl-3">
                ${{ number_format($grand_total, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- VISTA MÓVIL (CARDS)                                            --}}
    {{-- ============================================================== --}}
    <div class="md:hidden space-y-3">
        @forelse($items as $i)
            <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden active:scale-[0.98] transition-transform cursor-pointer"
                 onclick="window.dispatchEvent(new CustomEvent('open-detail-eds', { detail: { 
                    eds_id: {{ $i->eds_id }}, 
                    cliente_id: {{ $i->cliente_id }},
                    eds_name: '{{ addslashes($i->eds_nombre) }}',
                    cliente_name: '{{ addslashes($i->cliente_nombre) }}'
                 } }))">
                
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1 min-w-0 pr-2">
                        <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-1.5 py-0.5 rounded">{{ $i->eds_nombre }}</span>
                        <h3 class="font-bold text-slate-800 text-sm mt-1 truncate">{{ $i->cliente_nombre }}</h3>
                        <p class="text-xs text-slate-500 font-mono">{{ $i->cliente_documento }}</p>
                    </div>
                    <div class="text-right">
                        <span class="block font-mono font-bold text-lg text-slate-800">${{ number_format($i->total_deuda, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Deuda Total</span>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-2">
                    <div class="flex gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 text-slate-600 text-[10px] font-medium border border-slate-100">
                            {{ $i->cuentas_activas }} Cuentas
                        </span>

                        @php $mora = $i->max_dias_mora; @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold 
                            {{ $mora > 60 ? 'bg-red-50 text-red-600 border border-red-100' : ($mora > 30 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100') }}">
                            @if($mora > 0) {{ $mora }} días mora @else Al día @endif
                        </span>
                    </div>
                    <div class="bg-indigo-50 p-1.5 rounded-lg text-indigo-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm bg-slate-50 rounded-xl border border-dashed border-slate-200">
                No se encontraron registros.
            </div>
        @endforelse
    </div>

    {{-- ============================================================== --}}
    {{-- VISTA ESCRITORIO (TABLA)                                       --}}
    {{-- ============================================================== --}}
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-full text-left border-collapse table-fixed">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-3/12">Estación (EDS)</th>
                        <th class="px-6 py-4 w-3/12">Cliente</th>
                        <th class="px-6 py-4 text-center w-1/12">Cuentas</th>
                        <th class="px-6 py-4 text-center w-2/12">Mora Máx</th>
                        <th class="px-6 py-4 text-right w-2/12">Deuda Total</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $i)
                        <tr class="group hover:bg-slate-50 transition-colors cursor-pointer" 
                            onclick="window.dispatchEvent(new CustomEvent('open-detail-eds', { 
                                detail: { 
                                    eds_id: {{ $i->eds_id }}, 
                                    cliente_id: {{ $i->cliente_id }},
                                    eds_name: '{{ addslashes($i->eds_nombre) }}',
                                    cliente_name: '{{ addslashes($i->cliente_nombre) }}'
                                } 
                            }))">
                            
                            <td class="px-6 py-4 truncate">
                                <div class="font-bold text-indigo-600 text-xs uppercase tracking-wide truncate" title="{{ $i->eds_nombre }}">{{ $i->eds_nombre }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $i->eds_codigo ?? '---' }}</div>
                            </td>
                            <td class="px-6 py-4 truncate">
                                <div class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors truncate" title="{{ $i->cliente_nombre }}">{{ $i->cliente_nombre }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $i->cliente_documento }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                    {{ $i->cuentas_activas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php $mora = $i->max_dias_mora; @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold whitespace-nowrap
                                    {{ $mora > 60 ? 'bg-red-100 text-red-700' : ($mora > 30 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    @if($mora > 0) {{ $mora }} días @else Al día @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="block font-mono font-bold text-slate-800 text-sm">${{ number_format($i->total_deuda, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="p-2 rounded-full hover:bg-indigo-50 text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-12 text-slate-400 text-sm">No se encontraron deudas pendientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($items->hasPages())
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
            {{ $items->withQueryString()->links() }}
        </div>
    @endif
</div>