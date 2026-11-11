import { createIcons, icons } from 'lucide';

window.createIcons = createIcons;
window.lucideIcons = icons;

const initLucide = () => {
    createIcons({ icons });
};

document.addEventListener('DOMContentLoaded', initLucide);
document.addEventListener('livewire:navigated', initLucide);

const setupLivewireHooks = () => {
    initLucide();
    if (window.Livewire) {
        Livewire.hook('morph.updated', () => {
            queueMicrotask(initLucide);
        });
        Livewire.hook('commit', () => {
            queueMicrotask(initLucide);
        });
        Livewire.hook('element.init', () => {
            queueMicrotask(initLucide);
        });
    }
};

document.addEventListener('livewire:initialized', setupLivewireHooks);
document.addEventListener('livewire:init', setupLivewireHooks);

// Expose helper to refresh icons after dynamic DOM changes
window.refreshIcons = initLucide;

// Global Currency Formatter (Rupiah with thousand separator '.')
window.formatRupiahInput = function (el) {
    if (!el) return;
    const originalLen = el.value.length;
    const originalCursor = el.selectionStart;

    const digits = el.value.replace(/\D/g, '');
    if (!digits) {
        el.value = '';
        return;
    }

    const formatted = new Intl.NumberFormat('id-ID').format(digits);
    el.value = formatted;

    if (originalCursor !== null) {
        const diff = formatted.length - originalLen;
        const newPos = Math.max(0, originalCursor + diff);
        el.setSelectionRange(newPos, newPos);
    }
};
