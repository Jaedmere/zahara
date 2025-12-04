@extends('layouts.app')

@section('title', 'Notificaciones - Zahara')
@section('page_title', 'Centro de Notificaciones')
@section('page_subtitle', 'Compromisos pendientes para hoy y vencidos.')

@section('breadcrumb')
    <span>Inicio</span> / <span class="text-slate-900 font-medium">Notificaciones</span>
@endsection

@section('content')
<div class="flex flex-col gap-6 w-full max-w-full" x-data="notificationsManager()">
    
    <!-- Tarjetas de Alertas -->
    @forelse($alertas as $alerta)
        <div class="bg-white p-5 rounded-2xl border shadow-sm transition-all hover:shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4 group border-l-4 {{ $alerta->fecha_compromiso < now()->startOfDay() ? 'border-l-red-500' : 'border-l-amber-400' }}">
            
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl {{ $alerta->fecha_compromiso < now()->startOfDay() ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="font-bold text-slate-800 text-lg">{{ $alerta->cliente->razon_social }}</h3>
                        @if($alerta->fecha_compromiso < now()->startOfDay())
                            <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">Vencido</span>
                        @else
                            <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wide">Para Hoy</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 mb-2">{{ $alerta->observacion }}</p>
                    
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Compromiso: <span class="font-bold text-slate-700">{{ $alerta->fecha_compromiso->format('d/m/Y') }}</span>
                        </span>
                        @if($alerta->monto_compromiso)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Monto: <span class="font-bold text-emerald-600">${{ number_format($alerta->monto_compromiso, 0) }}</span>
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Gestor: {{ $alerta->usuario->name }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex md:flex-col gap-2 min-w-[140px]">
                <button @click="markAsDone({{ $alerta->id }})" 
                        class="flex-1 btn-primary bg-emerald-600 hover:bg-emerald-700 text-xs justify-center shadow-none">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Cumplido
                </button>
                
                {{-- Botón para ver detalle en CRM --}}
                <a href="{{ route('seguimientos.index', ['search' => $alerta->cliente->razon_social]) }}" 
                   class="flex-1 btn-secondary text-xs justify-center">
                    Ver en CRM
                </a>
                
                <button @click="deleteItem({{ $alerta->id }})" class="p-2 text-slate-400 hover:text-red-500 transition-colors md:self-end" title="Eliminar">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-dashed border-slate-200">
            <div class="p-4 rounded-full bg-indigo-50 mb-4">
                <svg class="w-10 h-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">¡Todo al día!</h3>
            <p class="text-slate-500 text-sm mt-1">No tienes compromisos pendientes para hoy ni vencidos.</p>
        </div>
    @endforelse

    @if($alertas->hasPages())
        <div class="mt-4">
            {{ $alertas->links() }}
        </div>
    @endif

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notificationsManager', () => ({
            markAsDone(id) {
                if(!confirm('¿Marcar compromiso como cumplido?')) return;
                
                fetch("{{ route('api.seguimientos.check', ':id') }}".replace(':id', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(() => {
                    window.location.reload(); // Recargar para actualizar lista y contador
                });
            },
            deleteItem(id) {
                if(!confirm('¿Eliminar esta notificación?')) return;
                
                fetch("{{ route('seguimientos.destroy', ':id') }}".replace(':id', id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(() => {
                    window.location.reload();
                });
            }
        }))
    })
</script>
@endsection