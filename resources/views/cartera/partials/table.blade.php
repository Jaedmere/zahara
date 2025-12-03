<div class="flex flex-col gap-4 w-full">
    <!-- TOTALIZADOR (mismo estilo) -->
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

    <!-- CONTENEDOR BLANCO UNIFICADO (cards + tabla) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden w-full">
        
        {{-- VISTA MÓVIL (CARDS) --}}
        <div class="md:hidden space-y-3 p-4">
            @forelse($clientes as $c)
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden active:scale-[0.98] transition-transform cursor-pointer"
                     onclick="window.dispatchEvent(new CustomEvent('open-detail-custom', { detail: { id: {{ $c->id }}, name: '{{ addslashes($c->razon_social) }}' } }))">
                    
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1 min-w-0 pr-2">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ $c->razon_social }}</h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $c->tipo_id }} {{ $c->documento }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block font-mono font-bold text-lg text-slate-800">
                                ${{ number_format($c->total_deuda, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Deuda Total</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-50 pt-3 mt-2">
                        <div class="flex gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-50 text-slate-600 text-[10px] font-medium border border-slate-100">
                                {{ $c->cuentas_activas }} Cuentas
                            </span>

                            @php $mora = $c->max_dias_mora; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-bold 
                                {{ $mora > 60 ? 'bg-red-50 text-red-600 border border-red-100' : ($mora > 30 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100') }}">
                                @if($mora > 0) {{ $mora }} días mora @else Al día @endif
                            </span>
                        </div>
                        
                        <div class="bg-indigo-50 p-1.5 rounded-lg text-indigo-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    No se encontraron clientes con deuda.
                </div>
            @endforelse
        </div>

        {{-- VISTA ESCRITORIO (TABLA) --}}
        <div class="hidden md:block overflow-x-auto w-full">
            <table class="w-full min-w-full text-left border-collapse table-fixed">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 w-5/12">Cliente</th>
                        <th class="px-6 py-4 text-center w-2/12">Cuentas</th>
                        <th class="px-6 py-4 text-center w-2/12">Mora Máx</th>
                        <th class="px-6 py-4 text-right w-3/12">Deuda Total</th>
                        <th class="px-6 py-4 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clientes as $c)
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
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-16 text-slate-400 text-sm">
                                No se encontraron clientes con deuda pendiente que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                {{ $clientes->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
