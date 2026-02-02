<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import PaletteColorPicker from '@/Components/PaletteColorPicker.vue';

const { t } = useI18n();
const showColorPicker = ref(false);

const colorValue = computed({
    get: () => props.model.embed_color || props.model.color || props.accentColor || '#5865f2',
    set: (v) => emit('update:color', v),
});

const props = defineProps({
    /** Embed-Daten: title, description, color, thumbnail, image, footer (boolean), authorName (optional) */
    model: {
        type: Object,
        default: () => ({}),
    },
    /** Platzhalter-Ersetzungen für Vorschau, z.B. { user: 'ibeiros#0', server: 'Mein Server', memberCount: '21' } */
    placeholderReplacements: {
        type: Object,
        default: () => ({}),
    },
    /** Akzentfarbe links (Embed-Rand), z.B. #5865f2 */
    accentColor: {
        type: String,
        default: '#f97316',
    },
    /** Ob "Neues Feld hinzufügen" angezeigt wird (Discord-Embed-Felder) */
    showAddField: {
        type: Boolean,
        default: true,
    },
    /** Icon-URL für Footer (z.B. Server-Icon) */
    footerIconUrl: {
        type: String,
        default: '',
    },
    /** Beschreibung als wachsender Block (für Vorschau), damit lange Texte sichtbar sind */
    descriptionAutoExpand: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'click-author',
    'click-hide',
    'click-add-field',
    'click-image',
    'click-footer',
    'click-timestamp',
    'update:color',
    'update:embed_title',
    'update:embed_description',
]);

function replacePlaceholders(text) {
    if (!text || typeof text !== 'string') return text || '';
    let out = text;
    const reps = { user: 'User', server: 'Server', memberCount: '0', ...props.placeholderReplacements };
    Object.entries(reps).forEach(([key, value]) => {
        out = out.replace(new RegExp(`\\{${key}\\}`, 'gi'), value);
    });
    return out;
}

const titleValue = computed({
    get: () => props.model.embed_title ?? props.model.title ?? '',
    set: (v) => emit('update:embed_title', v),
});
const descriptionValue = computed({
    get: () => props.model.embed_description ?? props.model.description ?? '',
    set: (v) => emit('update:embed_description', v),
});
const imageUrl = () => props.model.embed_image || props.model.image || null;
const footerIcon = computed(() => props.footerIconUrl || props.model?.footerIconUrl || props.model?.guildIconUrl || '');
/** Autor (Profilbild/Name) nur anzeigen, wenn embed_author !== false */
const showAuthor = computed(() => props.model.embed_author !== false);
/** Embed-Randfarbe (immer mit # für CSS) */
const embedBorderColor = computed(() => {
    const c = props.model.embed_color || props.model.color || props.accentColor || '#5865f2';
    return c && !String(c).startsWith('#') ? '#' + c : c;
});
/** Für Vorschau: formatierter Timestamp-Text (wenn embed_timestamp) */
const formattedTimestamp = computed(() => {
    if (!props.model.embed_timestamp) return '';
    const val = props.model.embed_timestamp_value;
    if (!val) return t('embedSender.timestampNow');
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
    } catch {
        return val;
    }
});
const showTimestampInPreview = computed(() => props.descriptionAutoExpand && props.model.embed_timestamp);
</script>

<template>
    <div
        class="embed-editor rounded-lg bg-[#2f3136] border border-[#202225] max-w-md min-h-[280px] flex flex-col relative"
        :class="descriptionAutoExpand ? 'self-start' : 'h-full flex-1'"
    >
        <!-- Embed: linke Farbfläche (klickbar → ColorPicker absolut) + Inhalt -->
        <div class="flex rounded-r-lg min-h-0 flex-1">
            <div class="relative flex-shrink-0 self-stretch w-1 min-w-[6px]">
                <button
                    type="button"
                    class="absolute inset-0 w-full rounded-l cursor-pointer hover:opacity-90 transition-opacity border-0 p-0"
                    :style="{ backgroundColor: embedBorderColor }"
                    :title="t('welcome.embedEditor.clickToChangeColor')"
                    @click.stop="showColorPicker = true"
                />
                <div class="absolute left-0 top-0 z-[100]">
                    <PaletteColorPicker
                        v-model="colorValue"
                        :open="showColorPicker"
                        @update:open="showColorPicker = $event"
                        panel-position="top-left"
                    >
                        <template #trigger>
                            <span class="sr-only">{{ t('welcome.embedEditor.clickToChangeColor') }}</span>
                        </template>
                    </PaletteColorPicker>
                </div>
            </div>
            <div
                class="flex-1 p-4 flex flex-col gap-3 min-w-0 min-h-0"
                :class="descriptionAutoExpand ? 'overflow-visible' : 'overflow-auto'"
            >
                <!-- Zeile 1: Titel direkt bearbeitbar + Auge für Profilbild (rechts) -->
                <div class="flex items-start justify-between gap-2">
                    <input
                        type="text"
                        :value="titleValue"
                        @input="emit('update:embed_title', $event.target.value)"
                        :placeholder="t('welcome.embedEditor.titleLabel')"
                        class="embed-zone flex-1 min-w-0 rounded-lg border-2 border-dashed border-gray-500 hover:border-[#5865f2] focus:border-[#5865f2] focus:outline-none px-3 py-2 text-sm font-semibold text-white bg-transparent placeholder-gray-500 transition-colors"
                    />
                    <button
                        type="button"
                        class="embed-zone flex-shrink-0 w-9 h-9 rounded-lg border-2 border-dashed border-gray-500 hover:border-[#5865f2] flex items-center justify-center text-gray-400 hover:text-white transition-colors"
                        @click="emit('click-hide')"
                        :title="showAuthor ? t('welcome.embedEditor.hideOrVisibility') : t('welcome.embedEditor.showAuthor')"
                    >
                        <svg v-if="showAuthor" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <!-- Darunter: Beschreibung (wachsender Block in Vorschau, sonst Textarea) -->
                <div
                    v-if="descriptionAutoExpand"
                    class="w-full rounded-lg border-2 border-dashed border-gray-500 px-3 py-2 text-sm text-gray-400 bg-transparent min-h-[4.5rem] whitespace-pre-wrap break-words"
                >{{ descriptionValue || ' ' }}</div>
                <textarea
                    v-else
                    :value="descriptionValue"
                    @input="emit('update:embed_description', $event.target.value)"
                    :placeholder="t('welcome.embedEditor.clickToConfigure')"
                    rows="3"
                    class="embed-zone w-full rounded-lg border-2 border-dashed border-gray-500 hover:border-[#5865f2] focus:border-[#5865f2] focus:outline-none px-3 py-2 text-sm text-gray-400 bg-transparent placeholder-gray-500 resize-none transition-colors min-h-[4.5rem]"
                />

                <!-- Neues Feld hinzufügen (optional) -->
                <button
                    v-if="showAddField"
                    type="button"
                    class="embed-zone flex items-center gap-2 text-[#5865f2] hover:text-[#7983f5] text-sm font-medium py-1 -mx-1 rounded transition-colors"
                    @click="emit('click-add-field')"
                >
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('welcome.embedEditor.addNewField') }}
                </button>

                <!-- Unten: Bild hinzufügbar (klickbar) -->
                <div class="space-y-1">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ t('welcome.embedEditor.addImage') }}</span>
                    <button
                        type="button"
                        class="embed-zone w-full rounded-lg border-2 border-dashed border-gray-500 hover:border-[#5865f2] overflow-hidden transition-colors flex items-center justify-center min-h-[120px] bg-[#36393f]/50"
                        @click="emit('click-image')"
                    >
                    <img
                        v-if="imageUrl()"
                        :src="imageUrl()"
                        alt="Embed"
                        class="max-h-32 w-full object-contain"
                        @error="(e) => e.target.style.display = 'none'"
                    />
                    <div v-else class="flex flex-col items-center justify-center py-6 text-gray-500">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-xs">{{ t('welcome.embedEditor.clickToConfigure') }}</span>
                    </div>
                </button>
                </div>

                <!-- Fußzeile (klickbar) -->
                <div class="flex items-center justify-between gap-2 pt-1 border-t border-[#202225]">
                    <button
                        type="button"
                        class="embed-zone flex items-center gap-2 rounded-lg border-2 border-dashed border-gray-500 hover:border-[#5865f2] px-2 py-1.5 text-left transition-colors min-w-0 flex-1"
                        @click="emit('click-footer')"
                    >
                        <span class="w-5 h-5 rounded-full border-2 border-dashed border-gray-500 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <img
                                v-if="footerIcon"
                                :src="footerIcon"
                                alt=""
                                class="w-full h-full object-cover"
                            />
                            <svg v-else class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        <span class="text-xs text-gray-400 truncate">{{ t('welcome.embedEditor.footer') }}</span>
                    </button>
                    <span v-if="showTimestampInPreview" class="text-xs text-gray-500 shrink-0 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ formattedTimestamp }}
                    </span>
                    <button
                        v-else
                        type="button"
                        class="embed-zone flex-shrink-0 w-8 h-8 rounded border-2 border-dashed border-gray-500 hover:border-[#5865f2] flex items-center justify-center text-gray-400 hover:text-white transition-colors"
                        @click="emit('click-timestamp')"
                        :title="t('welcome.embedEditor.timestamp')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
