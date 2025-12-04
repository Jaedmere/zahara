{{-- resources/views/cartera_cuentas/partials/table.blade.php --}}
<div class="flex flex-col gap-4 w-full">
    <div class="flex justify-end w-full">
        <div class="bg-indigo-600 text-white px-4 py-2 rounded-xl shadow-sm flex items-center gap-3 animate-enter">
            <div class="text-[10px] font-bold uppercase opacity-75 text-right leading-tight">
                Total Cartera<br>Visible
            </div>
            <div class="text-xl font-mono font-bold border-l border-indigo-400 pl-3">
                ${{ number_format($grand_total, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden w-full">
        
        <!-- VISTA MÓVIL (CARDS) -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($items as $i)
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden active:scale-[0.98] transition-transform cursor-pointer"
                     onclick="window.dispatchEvent(new CustomEvent('open-detail-cuenta', { detail: { id: {{ $i->id }}, consecutivo: '{{ $i->consecutivo }}' } }))">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0 pr-2">
                            <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-1.5 py-0.5 rounded">
                                {{ $i->consecutivo }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-sm mt-1 truncate">
                                {{ $i->cliente->razon_social }}
                            </h3>
                            <p class="text-xs text-slate-500 font-mono uppercase">
                                {{ $i->eds->nombre }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-bold text-lg text-slate-800">
                                ${{ number_format($i->saldo_pendiente, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Saldo</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between border-t border-slate-50 pt-2 mt-2">
                         <div class="flex flex-col">
                             <span class="text-[9px] text-slate-400 uppercase">Vence</span>
                             <span class="text-xs font-medium text-slate-600">
                                {{ $i->fecha_vencimiento->format('d/m/Y') }}
                             </span>
                         </div>
                         
                         @php $dias = $i->dias_mora_calc; @endphp
                         <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold 
                            {{ $dias > 0 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                            @if($dias > 0)
                                {{ $dias }} días mora
                            @else
                                Al día
                            @endif
                         </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm">No se encontraron cuentas.</div>
            @endforelse
        </div>

        <!-- VISTA ESCRITORIO (TABLA) -->
        <div class="hidden md:block overflow-x-auto w-full">
            <table class="w-full min-w-full text-left border-collapse table-fixed">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-2/12">Cuenta</th>
                        <th class="px-6 py-4 w-3/12">Cliente / EDS</th>
                        <th class="px-6 py-4 text-center w-2/12">Vencimiento</th>
                        <th class="px-6 py-4 text-center w-1/12">Días</th>
                        <th class="px-6 py-4 text-right w-2/12">Saldo</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $i)
                        <tr class="group hover:bg-slate-50 transition-colors cursor-pointer" 
                            onclick="window.dispatchEvent(new CustomEvent('open-detail-cuenta', { detail: { id: {{ $i->id }}, consecutivo: '{{ $i->consecutivo }}' } }))">
                            
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs">
                                    {{ $i->consecutivo }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $i->prefijo }}</div>
                            </td>
                            <td class="px-6 py-4 truncate">
                                <div class="font-bold text-slate-700 text-sm truncate" title="{{ $i->cliente->razon_social }}">
                                    {{ $i->cliente->razon_social }}
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono uppercase">
                                    {{ $i->eds->nombre }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-slate-600 font-medium text-xs">
                                    {{ $i->fecha_vencimiento->format('Y-m-d') }}
                                </span>
                                <div class="text-[9px] text-slate-400 mt-0.5">
                                    Corte: {{ $i->corte_hasta->format('d/m') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php $dias = $i->dias_mora_calc; @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold 
                                    {{ $dias > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $dias > 0 ? $dias : 'OK' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="block font-mono font-bold text-slate-800 text-sm">
                                    ${{ number_format($i->saldo_pendiente, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="p-2 rounded-full hover:bg-indigo-50 text-indigo-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                                No se encontraron cuentas pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                {{ $items->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
