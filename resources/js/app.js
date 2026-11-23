import { createIcons, icons } from 'lucide';

window.createIcons = createIcons;
window.lucideIcons = icons;

let lucideRaf = null;
const initLucide = () => {
    if (lucideRaf) cancelAnimationFrame(lucideRaf);
    lucideRaf = requestAnimationFrame(() => {
        createIcons({ icons });
    });
};

document.addEventListener('DOMContentLoaded', initLucide);
document.addEventListener('livewire:navigated', initLucide);

const setupLivewireHooks = () => {
    initLucide();
    if (window.Livewire) {
        Livewire.hook('commit', () => {
            initLucide();
        });
        Livewire.hook('morph.updated', () => {
            initLucide();
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
