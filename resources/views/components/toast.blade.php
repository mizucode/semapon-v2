<div
    x-data="{
        toasts: [],
        add(data) {
            let toast = Array.isArray(data) ? (data[0] || {}) : (data || {});
            if (typeof toast === 'string') {
                toast = { message: toast };
            }
            if (!toast.message && !toast.title) {
                return;
            }
            const id = Date.now() + Math.random().toString(36).substring(2, 9);
            const newToast = {
                id,
                type: toast.type || 'info', // success, error, warning, info
                title: toast.title || '',
                message: toast.message || '',
                timeout: toast.timeout || 4000
            };
            this.toasts.push(newToast);

            setTimeout(() => {
                this.remove(id);
            }, newToast.timeout);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast.window="add($event.detail)"
    x-on:notify.window="add($event.detail)"
    class="fixed top-5 right-5 z-50 flex flex-col space-y-2 max-w-sm w-full px-4 pointer-events-none"
    aria-live="polite"
>
    <template x-for="t in toasts" :key="t.id">
        <div
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-[-10px] opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full p-4 rounded-xl shadow-lg border flex items-start space-x-3 backdrop-blur-sm"
            :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-900': t.type === 'success',
                'bg-rose-50 border-rose-200 text-rose-900': t.type === 'error',
                'bg-amber-50 border-amber-200 text-amber-900': t.type === 'warning',
                'bg-blue-50 border-blue-200 text-blue-900': t.type === 'info'
            }"
        >
            <div class="shrink-0 mt-0.5">
                <template x-if="t.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </template>
                <template x-if="t.type === 'error'">
                    <svg class="w-5 h-5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </template>
                <template x-if="t.type === 'warning'">
                    <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </template>
                <template x-if="t.type === 'info'">
                    <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <p x-show="t.title" x-text="t.title" class="text-sm font-semibold mb-0.5"></p>
                <p x-text="t.message" class="text-xs leading-relaxed opacity-90"></p>
            </div>
            <button
                type="button"
                @click="remove(t.id)"
                class="shrink-0 text-slate-400 hover:text-slate-600 transition-colors"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </template>
</div>

