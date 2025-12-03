<div class="flex flex-col gap-4 w-full">
    <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- MOBILE CARDS -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($facturas as $f)
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute left-0 top-0 bottom-0 w-1 
                        {{ $f->estado === 'pagada' ? 'bg-emerald-500' : ($f->estado === 'anulada' ? 'bg-slate-400' : ($f->dias_vencidos > 0 ? 'bg-red-500' : 'bg-amber-400')) }}">
                    </div>

                    <div class="pl-2">
                        <div class="flex justify-between items-start mb-2">
                            <div class="overflow-hidden pr-2 flex-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate block">{{ $f->eds->nombre }}</span>
                                <h3 class="font-bold text-slate-800 text-sm truncate block">{{ $f->cliente->razon_social }}</h3>
                                
                                <div class="flex items-center gap-1 mt-1 text-slate-400">
                                    <svg class="w-3 h-3 text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-mono text-[10px]">
                                        {{ $f->corte_desde->format('d/m/Y') }} - {{ $f->corte_hasta->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 pl-2">
                                <span class="block font-mono font-bold text-indigo-600">#{{ $f->consecutivo }}</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end border-t border-slate-50 pt-3 mt-2">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase">Saldo Pendiente</span>
                                <span class="text-sm font-bold {{ $f->saldo_pendiente > 0 ? 'text-slate-800' : 'text-emerald-600' }}">
                                    ${{ number_format($f->saldo_pendiente, 2) }}
                                </span>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-[10px] text-slate-400 block uppercase">Vencimiento</span>
                                <span class="text-xs font-medium {{ $f->dias_vencidos > 0 && $f->estado !== 'pagada' ? 'text-red-600' : 'text-slate-600' }}">
                                    {{ $f->fecha_vencimiento->format('d/m/Y') }}
                                    @if($f->dias_vencidos > 0 && $f->estado !== 'pagada')
                                        <span class="block text-[9px] font-bold text-red-500">(+{{ $f->dias_vencidos }} días)</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if($f->estado !== 'pagada' && $f->estado !== 'anulada')
                            <div class="mt-4 pt-3 border-t border-slate-50 flex gap-2 justify-end">
                                <a href="{{ route('facturas.edit', $f) }}" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg> Editar
                                </a>
                                @if($f->saldo_pendiente == $f->valor_total)
                                    <form action="{{ route('facturas.destroy', $f) }}" method="POST" x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Anular Cuenta', message: '¿Anular la cuenta #{{ $f->consecutivo }}?' })">
                                        @csrf @method('DELETE')
                                        <button class="p-2 bg-red-50 text-red-600 rounded-lg text-xs font-bold flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg> Anular
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm">No se encontraron cuentas.</div>
            @endforelse
        </div>

        <!-- DESKTOP TABLE -->
        <div class="hidden md:block w-full overflow-x-auto">
            <table class="w-full min-w-[1000px] text-left border-collapse table-fixed">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                        <th class="px-6 py-4 w-2/12">Cuenta / Acciones</th>
                        <th class="px-6 py-4 w-3/12">Cliente / EDS</th>
                        <th class="px-6 py-4 w-2/12 text-center">Cronología</th>
                        <th class="px-6 py-4 w-2/12 text-right">Total</th>
                        <th class="px-6 py-4 w-2/12 text-right">Saldo</th>
                        <th class="px-6 py-4 w-1/12 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($facturas as $f)
                        <tr class="group hover:bg-slate-50 transition-colors">
                            
                            <td class="px-6 py-3 align-top">
                                <div class="flex flex-col gap-2">
                                    <span class="font-mono font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs w-fit">
                                        {{ $f->prefijo }} {{ $f->consecutivo }}
                                    </span>
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200 -translate-x-2 group-hover:translate-x-0 h-6">
                                        @if($f->estado !== 'pagada' && $f->estado !== 'anulada')
                                            <a href="{{ route('facturas.edit', $f) }}" class="text-slate-400 hover:text-indigo-600 transition-colors" title="Editar">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </a>
                                            @if($f->saldo_pendiente == $f->valor_total)
                                                <form action="{{ route('facturas.destroy', $f) }}" method="POST" x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Anular', message: '¿Anular cuenta #{{ $f->consecutivo }}?' })">
                                                    @csrf @method('DELETE')
                                                    <button class="text-slate-400 hover:text-red-600 transition-colors" title="Anular">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-3">
                                <div class="flex flex-col w-full overflow-hidden">
                                    <span class="font-semibold text-sm text-slate-700 truncate block" title="{{ $f->cliente->razon_social }}">
                                        {{ $f->cliente->razon_social }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 uppercase truncate block" title="{{ $f->eds->nombre }}">
                                        {{ $f->eds->nombre }}
                                    </span>
                                </div>
                            </td>
                            
                            <td class="px-6 py-3">
                                <div class="flex flex-col gap-1.5 items-center">
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1">
                                        <span class="uppercase text-[9px] text-slate-400">Emi:</span> {{ $f->fecha_emision->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[10px] font-medium flex items-center gap-1 {{ $f->dias_vencidos > 0 ? 'text-red-600' : 'text-slate-600' }}">
                                        <span class="uppercase text-[9px] text-slate-400">Vence:</span> {{ $f->fecha_vencimiento->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[9px] text-indigo-500 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-mono text-[10px]">
                                            {{ $f->corte_desde->format('d/m') }} - {{ $f->corte_hasta->format('d/m') }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-3 text-right text-sm text-slate-600 font-mono">
                                ${{ number_format($f->valor_total, 2) }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="font-bold font-mono text-sm {{ $f->saldo_pendiente > 0 ? 'text-slate-800' : 'text-emerald-600' }}">
                                    ${{ number_format($f->saldo_pendiente, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($f->estado === 'pagada')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase">Pagada</span>
                                @elseif($f->estado === 'anulada')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 uppercase line-through">Anulada</span>
                                @elseif($f->dias_vencidos > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase">Vencida</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 uppercase">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($facturas->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
                {{ $facturas->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
