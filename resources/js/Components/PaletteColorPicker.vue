<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const model = defineModel({
    type: String,
    default: '',
});

const props = defineProps({
    /** Steuerung von außen: wenn gesetzt, wird open von außen gesteuert (z. B. für Klick auf Embed-Farbbalken) */
    open: {
        type: Boolean,
        default: undefined,
    },
    /** Position des Panels: 'bottom' = unter dem Trigger, 'top-left' = oben links am Trigger */
    panelPosition: {
        type: String,
        default: 'bottom',
        validator: (v) => ['bottom', 'top-left'].includes(v),
    },
});

const emit = defineEmits(['update:open']);

const open = ref(false);
const triggerRef = ref(null);
/** Offen-Zustand: von außen gesteuert oder intern */
const isOpen = computed(() => (props.open !== undefined ? props.open : open.value));
const panelRef = ref(null);
const customHex = ref('');
const satLightBoxRef = ref(null);

// HSL: H 0–360, S 0–100, L 0–100
const hsl = ref({ h: 230, s: 80, l: 60 });

function hexToHsl(hex) {
    const h = (hex || '').replace(/^#/, '').trim();
    if (!/^[0-9A-Fa-f]{6}$/.test(h)) return { h: 230, s: 80, l: 60 };
    const r = parseInt(h.slice(0, 2), 16) / 255;
    const g = parseInt(h.slice(2, 4), 16) / 255;
    const b = parseInt(h.slice(4, 6), 16) / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    let h_ = 0;
    let s = 0;
    const l = (max + min) / 2;
    if (max !== min) {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        if (max === r) h_ = ((g - b) / d + (g < b ? 6 : 0)) / 6;
        else if (max === g) h_ = ((b - r) / d + 2) / 6;
        else h_ = ((r - g) / d + 4) / 6;
    }
    return { h: Math.round(h_ * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
}

function hslToHex(h, s, l) {
    h = h / 360;
    s = s / 100;
    l = l / 100;
    let r, g, b;
    if (s === 0) {
        r = g = b = l;
    } else {
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        r = hue2rgb(p, q, h + 1 / 3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1 / 3);
    }
    const toHex = (n) => Math.round(Math.max(0, Math.min(1, n)) * 255).toString(16).padStart(2, '0');
    return (toHex(r) + toHex(g) + toHex(b)).toUpperCase();
}
function hue2rgb(p, q, t) {
    if (t < 0) t += 1;
    if (t > 1) t -= 1;
    if (t < 1 / 6) return p + (q - p) * 6 * t;
    if (t < 1 / 2) return q;
    if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
    return p;
}

const displayValue = computed(() => {
    const v = (model.value || '').replace(/^#/, '').trim().toUpperCase();
    return v || '';
});

const swatchStyle = computed(() => {
    const hex = displayValue.value;
    const color = hex && /^[0-9A-Fa-f]{6}$/.test(hex) ? `#${hex}` : '#40444b';
    return { backgroundColor: color };
});

// 2D-Picker: Indikator-Position (0–1)
const pickerX = computed(() => hsl.value.s / 100);
const pickerY = computed(() => 1 - hsl.value.l / 100);

function toggle() {
    if (props.open !== undefined) {
        emit('update:open', !props.open);
        if (!props.open) initPicker();
    } else {
        open.value = !open.value;
        if (open.value) initPicker();
    }
}
function initPicker() {
    const hex = model.value || '5865F2';
    hsl.value = hexToHsl(hex);
    customHex.value = (hex.replace(/^#/, '') || '').toUpperCase();
}

function updateFromHsl() {
    model.value = hslToHex(hsl.value.h, hsl.value.s, hsl.value.l);
    customHex.value = model.value;
}

function onSatLightPointerDown(e) {
    const box = satLightBoxRef.value;
    if (!box) return;
    const rect = box.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    hsl.value.s = Math.round(Math.max(0, Math.min(1, x)) * 100);
    hsl.value.l = Math.round((1 - Math.max(0, Math.min(1, y))) * 100);
    updateFromHsl();
    const move = (e2) => {
        const x2 = (e2.clientX - rect.left) / rect.width;
        const y2 = (e2.clientY - rect.top) / rect.height;
        hsl.value.s = Math.round(Math.max(0, Math.min(1, x2)) * 100);
        hsl.value.l = Math.round((1 - Math.max(0, Math.min(1, y2))) * 100);
        updateFromHsl();
    };
    const up = () => {
        document.removeEventListener('pointermove', move);
        document.removeEventListener('pointerup', up);
    };
    document.addEventListener('pointermove', move);
    document.addEventListener('pointerup', up);
}

function onHueInput(e) {
    hsl.value.h = Math.round(Number(e.target.value));
    updateFromHsl();
}

function applyCustom() {
    const clean = String(customHex.value).replace(/^#/, '').trim();
    if (/^[0-9A-Fa-f]{6}$/.test(clean)) {
        model.value = clean.toUpperCase();
        hsl.value = hexToHsl(clean);
        customHex.value = clean.toUpperCase();
        if (props.open !== undefined) emit('update:open', false);
        else open.value = false;
    }
}

function handleClickOutside(e) {
    if (!isOpen.value) return;
    if (panelRef.value && panelRef.value.contains(e.target)) return;
    if (triggerRef.value && triggerRef.value.contains(e.target)) return;
    if (props.open !== undefined) emit('update:open', false);
    else open.value = false;
}

/** Klick-Outside erst in nächstem Tick prüfen, damit derselbe Klick der den Picker öffnet nicht sofort schließt */
function onDocumentClick(e) {
    setTimeout(() => handleClickOutside(e), 0);
}

watch(isOpen, (val) => {
    if (val) initPicker();
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
});
onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});

</script>

<template>
    <div class="relative h-full flex flex-col min-h-0">
        <div ref="triggerRef" class="flex-1 min-h-0 flex flex-col">
            <slot name="trigger" :open="isOpen" :toggle="toggle">
                <button
                    type="button"
                    @click="toggle"
                    class="flex items-center gap-2 min-w-[8rem] rounded-lg bg-[#202225] border border-[#202225] hover:border-[#5865f2] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2] transition-colors"
                >
                    <span
                        class="w-6 h-6 rounded border border-[#4f545c] flex-shrink-0"
                        :style="swatchStyle"
                    />
                    <span class="text-sm text-left truncate">{{ displayValue || '—' }}</span>
                    <svg class="w-4 h-4 flex-shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </slot>
        </div>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="isOpen"
                ref="panelRef"
                class="absolute left-0 z-[100] rounded-xl border border-[#202225] bg-[#2f3136] shadow-xl p-3 w-64"
                :class="panelPosition === 'top-left' ? 'top-0' : 'top-full mt-1'"
            >
                <!-- 2D-Feld: Sättigung (x) × Helligkeit (y), wie im Bild -->
                <div
                    ref="satLightBoxRef"
                    class="relative w-full h-36 rounded-lg overflow-hidden cursor-crosshair border border-[#202225] select-none"
                    :style="{
                        background: `linear-gradient(to bottom, transparent 0%, black 100%), linear-gradient(to right, white 0%, hsl(${hsl.h}, 100%, 50%) 100%)`,
                    }"
                    @pointerdown="onSatLightPointerDown"
                >
                    <div
                        class="absolute w-4 h-4 rounded-full border-2 border-white shadow pointer-events-none"
                        :style="{
                            left: `calc(${pickerX * 100}% - 8px)`,
                            top: `calc(${pickerY * 100}% - 8px)`,
                        }"
                    />
                </div>

                <!-- Hue-Slider (Farbton) – Spektrum wie im Bild -->
                <div class="mt-3">
                    <p class="text-xs text-gray-400 mb-1.5 px-0.5">{{ $t('colorPicker.hue') }}</p>
                    <div
                        class="relative h-3 rounded-full overflow-hidden border border-[#202225]"
                        style="background: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00);"
                    >
                        <input
                            type="range"
                            min="0"
                            max="360"
                            :value="hsl.h"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            @input="onHueInput"
                        />
                        <div
                            class="absolute top-1/2 -translate-y-1/2 w-2.5 h-2.5 rounded-full bg-[#202225] pointer-events-none"
                            :style="{ left: `calc(${(hsl.h / 360) * 100}% - 5px)` }"
                        />
                    </div>
                </div>

                <!-- Hex-Eingabe -->
                <div class="flex gap-2 items-center border-t border-[#202225] pt-3 mt-3">
                    <input
                        v-model="customHex"
                        type="text"
                        maxlength="7"
                        :placeholder="$t('colorPicker.hexPlaceholder')"
                        class="flex-1 rounded bg-[#202225] border border-[#202225] text-white text-sm px-2.5 py-1.5 focus:outline-none focus:border-[#5865f2]"
                        @keydown.enter="applyCustom"
                    />
                    <button
                        type="button"
                        @click="applyCustom"
                        class="rounded px-2.5 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white text-sm font-medium"
                    >
                        {{ $t('colorPicker.apply') }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
