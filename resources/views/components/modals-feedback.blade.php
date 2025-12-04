<div 
    x-data="modalConfirm()" 
    x-show="open" 
    x-cloak
    class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
    @modal-confirm.window="openModal($event.detail)"
>
    <div 
        class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden transform transition-all"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    >
        <div class="px-6 pt-5 pb-4">
            <div class="flex items-start gap-3">
                <div class="mt-1 flex h-9 w-9 items-center justify-center rounded-full"
                     :class="type === 'danger' ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600'">
                    <svg x-show="type === 'danger'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/>
                    </svg>
                    <svg x-show="type !== 'danger'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10.011 10.011 0 0012 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-slate-900" x-text="title"></h3>
                    <p class="mt-1 text-sm text-slate-600 whitespace-pre-line" x-text="message"></p>
                </div>
            </div>
        </div>
        <div class="px-6 py-3 bg-slate-50 flex justify-end gap-2">
            <button type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100"
                    @click="close()">
                Cancelar
            </button>
            <button type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white flex items-center gap-1"
                    :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                    @click="confirm()">
                <span>Aceptar</span>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('modalConfirm', () => ({
                open: false,
                title: '',
                message: '',
                type: 'info',

                openModal(payload) {
                    this.title = payload.title || 'Confirmar acción';
                    this.message = payload.message || '';
                    this.type = payload.type || 'info';
                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                confirm() {
                    this.open = false;
                    // Disparamos el evento global que escucha el CRM: @confirmed-action.window="executeAction()"
                    window.dispatchEvent(new CustomEvent('confirmed-action'));
                }
            }))
        })
    </script>
</div>
