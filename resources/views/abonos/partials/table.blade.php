<div class="w-full">
    
    <!-- MOBILE CARDS -->
    <div class="md:hidden space-y-3 p-4">
        @forelse($abonos as $a)
            @php
                $detalle = $a->detalles->first();
                $factura = $detalle ? $detalle->factura : null;
                $isAnulado = $a->deleted_at !== null;
                $count = $a->detalles->count();
            @endphp

            <div class="bg-white p-4 rounded-xl border {{ $isAnulado ? 'border-red-100 bg-red-50/30' : 'border-slate-100' }} shadow-sm relative overflow-hidden">
                @if($isAnulado)
                    <div class="absolute top-0 right-0 bg-red-100 text-red-600 text-[9px] px-2 py-1 font-bold rounded-bl-lg">ANULADO</div>
                @endif

                <!-- CABECERA CARD: EDS + FECHA -->
                <div class="flex justify-between items-center mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 uppercase border border-blue-100">
                        {{ $a->eds->nombre }}
                    </span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $a->fecha->format('d/m/Y') }}</span>
                </div>

                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm {{ $isAnulado ? 'line-through opacity-50' : '' }}">{{ $a->cliente->razon_social ?? 'Cliente General' }}</h3>
                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1">
                            <span class="font-mono text-indigo-600 font-medium">
                                @if($count > 1)
                                    Pago Múltiple ({{ $count }})
                                @elseif($factura)
                                    Fac #{{ $factura->consecutivo }}
                                @else
                                    Recibo #{{ $a->id }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="block font-mono font-bold text-lg {{ $isAnulado ? 'text-slate-400 line-through' : 'text-emerald-600' }}">+${{ number_format($a->valor, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center border-t border-slate-50 pt-3 mt-2 gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 text-[10px] font-medium text-slate-500 border border-slate-100 mr-auto">
                        {{ $a->medio_pago }}
                    </span>
                    
                    {{-- BOTÓN VER DETALLE (MÓVIL) --}}
                    <button type="button"
                            class="p-2 text-slate-500 hover:text-indigo-600 transition-colors bg-white hover:bg-indigo-50 rounded-lg border border-slate-100 shadow-sm"
                            @click="$dispatch('open-abono-details', {
                                abono: { 
                                    id: {{ $a->id }}, 
                                    fecha: '{{ $a->fecha->format('d/m/Y') }}', 
                                    total: '{{ number_format($a->valor, 2) }}',
                                    cliente: '{{ addslashes($a->cliente->razon_social) }}'
                                },
                                details: {{ $a->detalles->map(fn($d) => [
                                    'id' => $d->id,
                                    'factura' => $d->factura->consecutivo ?? '---',
                                    'eds' => $d->factura->eds->nombre ?? '---',
                                    'monto' => number_format($d->valor_aplicado, 2)
                                ])->toJson() }}
                            })">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>

                    @if(!$isAnulado)
                        <form action="{{ route('abonos.destroy', $a) }}" method="POST" 
                              x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Anular Abono', message: '¿Estás seguro de anular este abono de ${{ number_format($a->valor) }}? El saldo volverá a la cuenta.' })">
                            @csrf @method('DELETE')
                            <button class="p-2 text-slate-400 hover:text-red-600 transition-colors bg-white hover:bg-red-50 rounded-lg border border-slate-100 shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400 text-sm">No se encontraron abonos registrados.</div>
        @endforelse
    </div>

    <!-- DESKTOP TABLE -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/50 text-[11px] uppercase tracking-widest text-slate-500 font-bold">
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">EDS / Detalle</th> <!-- Título actualizado -->
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4">Método Pago</th>
                    <th class="px-6 py-4 text-right">Monto</th>
                    <th class="px-6 py-4 text-center">Usuario</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($abonos as $a)
                    @php
                        $detalle = $a->detalles->first();
                        $factura = $detalle ? $detalle->factura : null;
                        $isAnulado = $a->deleted_at !== null;
                        $count = $a->detalles->count();
                    @endphp
                    <tr class="group hover:bg-slate-50 transition-colors {{ $isAnulado ? 'bg-slate-50/50' : '' }}">
                        <td class="px-6 py-3 text-sm text-slate-600">
                            {{ $a->fecha->format('d M Y') }}
                        </td>
                        
                        {{-- COLUMNA EDS + DETALLE (Rediseñada) --}}
                        <td class="px-6 py-3">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-slate-500 uppercase mb-0.5">{{ $a->eds->nombre }}</span>
                                
                                @if($count > 1)
                                    <span class="font-bold text-indigo-600 text-xs flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Múltiples ({{ $count }})
                                    </span>
                                @elseif($factura)
                                    <span class="font-mono font-bold text-slate-700 text-xs">Fac. #{{ $factura->consecutivo }}</span>
                                @else
                                    <span class="font-mono text-slate-400 text-xs">Sin detalle</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-3 font-medium text-sm {{ $isAnulado ? 'text-slate-400 line-through' : 'text-slate-700' }}">
                            {{ $a->cliente->razon_social ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                {{ $a->medio_pago }}
                            </span>
                            @if($a->referencia_bancaria)
                                <span class="block text-[10px] text-slate-400 mt-1 truncate max-w-[100px]" title="{{ $a->referencia_bancaria }}">
                                    {{ $a->referencia_bancaria }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="font-bold font-mono {{ $isAnulado ? 'text-slate-400 line-through' : 'text-emerald-600' }} text-sm">
                                ${{ number_format($a->valor, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold" title="{{ $a->user->name ?? 'Sistema' }}">
                                {{ substr($a->user->name ?? 'S', 0, 1) }}
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                {{-- BOTÓN VER DETALLE (DESKTOP) --}}
                                <button type="button"
                                    class="p-1 text-slate-400 hover:text-indigo-600 transition-colors"
                                    title="Ver Detalle"
                                    @click="$dispatch('open-abono-details', {
                                        abono: { 
                                            id: {{ $a->id }}, 
                                            fecha: '{{ $a->fecha->format('d/m/Y') }}', 
                                            total: '{{ number_format($a->valor, 2) }}',
                                            cliente: '{{ addslashes($a->cliente->razon_social) }}'
                                        },
                                        details: {{ $a->detalles->map(fn($d) => [
                                            'id' => $d->id,
                                            'factura' => $d->factura->consecutivo ?? '---',
                                            'eds' => $d->factura->eds->nombre ?? '---',
                                            'monto' => number_format($d->valor_aplicado, 2)
                                        ])->toJson() }}
                                    })">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </button>

                                @if(!$isAnulado)
                                    <form action="{{ route('abonos.destroy', $a) }}" method="POST" 
                                          x-on:submit.prevent="$dispatch('confirm-action', { form: $el, title: 'Anular Abono', message: '¿Anular este pago? El saldo volverá a la cuenta.' })"
                                          class="inline-block">
                                        @csrf @method('DELETE')
                                        <button class="p-1 text-slate-300 hover:text-red-500 transition-colors" title="Anular Pago">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

@if($abonos->hasPages())
    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50/50">
        {{ $abonos->withQueryString()->links() }}
    </div>
@endif