<div class="flex flex-col gap-4 w-full">
    
    {{-- TOTALIZADOR DINÁMICO --}}
    <div class="flex justify-end w-full">
        <div class="bg-indigo-600 text-white px-4 py-2 rounded-xl shadow-sm flex items-center gap-3 animate-enter transition-all duration-300">
            <div class="text-[10px] font-bold uppercase opacity-75 text-right leading-tight">Total Cartera<br>Filtrada</div>
            <div class="text-xl font-mono font-bold border-l border-indigo-400 pl-3">
                ${{ number_format($grand_total, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-full text-left border-collapse table-fixed">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-5/12">Cliente</th> {{-- Ancho fijo relativo --}}
                        <th class="px-6 py-4 text-center w-2/12">Cuentas</th>
                        <th class="px-6 py-4 text-center w-2/12">Mora Máx</th>
                        <th class="px-6 py-4 text-right w-3/12">Deuda Total</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clientes as $c)
                        {{-- Evento optimizado para abrir el panel --}}
                        <tr class="group hover:bg-slate-50 transition-colors cursor-pointer" 
                            onclick="window.dispatchEvent(new CustomEvent('open-detail-custom', { detail: { id: {{ $c->id }}, name: '{{ addslashes($c->razon_social) }}' } }))">
                            
                            <td class="px-6 py-4">
                                <div class="flex flex-col max-w-full">
                                    <div class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors truncate" title="{{ $c->razon_social }}">
                                        {{ $c->razon_social }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                        {{ $c->tipo_id }} {{ $c->documento }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center align-middle">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 min-w-[30px]">
                                    {{ $c->cuentas_activas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center align-middle">
                                @php $mora = $c->max_dias_mora; @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold whitespace-nowrap
                                    {{ $mora > 60 ? 'bg-red-100 text-red-700' : ($mora > 30 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    @if($mora > 0) {{ $mora }} días @else Al día @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <span class="block font-mono font-bold text-slate-800 text-sm tracking-tight">
                                    ${{ number_format($c->total_deuda, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <button class="p-2 rounded-full hover:bg-indigo-50 text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-16 text-slate-400 text-sm">No se encontraron clientes con deuda pendiente que coincidan con la búsqueda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Paginación --}}
    @if($clientes->hasPages())
        <div class="px-2 pt-2">
            {{ $clientes->withQueryString()->links() }}
        </div>
    @endif
</div>