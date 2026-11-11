<div
    x-data="{
        open: false,
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
        confirmVariant: 'danger', // danger, primary
        callback: null,
        ask(data) {
            this.title = data.title || 'Konfirmasi';
            this.message = data.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            this.confirmText = data.confirmText || 'Ya, Lanjutkan';
            this.cancelText = data.cancelText || 'Batal';
            this.confirmVariant = data.confirmVariant || 'danger';
            this.callback = data.callback || null;
            this.open = true;
        },
        confirm() {
            if (typeof this.callback === 'function') {
                this.callback();
            } else if (typeof this.callback === 'string') {
                Livewire.dispatch(this.callback);
            }
            this.open = false;
        },
        cancel() {
            this.open = false;
        }
    }"
    x-on:confirm-dialog.window="ask($event.detail)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="cancel()"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100"
        >
            <div class="sm:flex sm:items-start space-x-4">
                <div
                    class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                    :class="confirmVariant === 'danger' ? 'bg-rose-100 text-rose-600' : 'bg-blue-100 text-blue-600'"
                >
                    <template x-if="confirmVariant === 'danger'">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </template>
                    <template x-if="confirmVariant !== 'danger'">
                        <i data-lucide="help-circle" class="w-6 h-6"></i>
                    </template>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1">
                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title" x-text="title"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-slate-500" x-text="message"></p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button
                    type="button"
                    @click="cancel()"
                    class="inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-xs ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-colors"
                    x-text="cancelText"
                ></button>
                <button
                    type="button"
                    @click="confirm()"
                    class="inline-flex w-full justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-xs sm:w-auto transition-colors"
                    :class="confirmVariant === 'danger' ? 'bg-rose-600 hover:bg-rose-500 focus-visible:outline-rose-600' : 'bg-blue-600 hover:bg-blue-500 focus-visible:outline-blue-600'"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>
</div>

