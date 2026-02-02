<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import GuildLayout from '@/Layouts/GuildLayout.vue';
import EmbedEditor from '@/Components/EmbedEditor.vue';
import PaletteColorPicker from '@/Components/PaletteColorPicker.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    savedEmbeds: { type: Array, default: () => [] },
    addOns: { type: Object, default: () => ({}) },
});

const MAX_SAVED = 5;
const MAX_EMBEDS = 10; // Discord-Limit pro Nachricht

function createEmptyEmbed() {
    return {
        title: '',
        description: '',
        color: '#5865f2',
        image_url: '',
        thumbnail_url: '',
        footer_text: '',
        footer_icon_url: '',
        footer_timestamp: false,
        timestamp_value: '',
        fields: [],
    };
}

// Form state
const content = ref('');
const embeds = ref([createEmptyEmbed()]);

const selectedChannelId = ref('');
const saveName = ref('');
const editingId = ref(null);
const sending = ref(false);

// Collapse pro Embed (am Anfang zugeklappt)
const embedContentOpen = ref([false]);
const embedStyleOpen = ref([false]);
const embedCardOpen = ref([false]); // am Anfang alle zugeklappt

function toggleContentOpen(idx) {
    const arr = [...embedContentOpen.value];
    arr[idx] = !arr[idx];
    embedContentOpen.value = arr;
}
function toggleStyleOpen(idx) {
    const arr = [...embedStyleOpen.value];
    arr[idx] = !arr[idx];
    embedStyleOpen.value = arr;
}
function toggleCardOpen(idx) {
    const arr = [...embedCardOpen.value];
    arr[idx] = !arr[idx];
    embedCardOpen.value = arr;
}

function setTimestampUseNow(embed, useNow) {
    if (useNow) {
        embed.timestamp_value = '';
    } else {
        const d = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        embed.timestamp_value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }
}

function getPreviewModel(emb) {
    return {
        embed_title: emb.title,
        embed_description: emb.description,
        embed_color: emb.color,
        embed_author: false,
        embed_image: emb.image_url,
        embed_footer: !!emb.footer_text || emb.footer_timestamp,
        embed_timestamp: emb.footer_timestamp,
        embed_timestamp_value: emb.timestamp_value || null,
    };
}

function addEmbed() {
    if (embeds.value.length >= MAX_EMBEDS) return;
    embeds.value.push(createEmptyEmbed());
    embedContentOpen.value.push(false);
    embedStyleOpen.value.push(false);
    embedCardOpen.value.push(false);
}

function removeEmbed(idx) {
    if (embeds.value.length <= 1) return;
    embeds.value.splice(idx, 1);
    embedContentOpen.value.splice(idx, 1);
    embedStyleOpen.value.splice(idx, 1);
    embedCardOpen.value.splice(idx, 1);
}

function duplicateEmbed(idx) {
    if (embeds.value.length >= MAX_EMBEDS) return;
    const copy = JSON.parse(JSON.stringify(embeds.value[idx]));
    embeds.value.splice(idx + 1, 0, copy);
    embedContentOpen.value.splice(idx + 1, 0, false);
    embedStyleOpen.value.splice(idx + 1, 0, false);
    embedCardOpen.value.splice(idx + 1, 0, false);
}

function addField(embedIdx) {
    embeds.value[embedIdx].fields.push({ name: '', value: '', inline: false });
}
function removeField(embedIdx, fieldIdx) {
    embeds.value[embedIdx].fields.splice(fieldIdx, 1);
}

function loadSaved(saved) {
    content.value = saved.content ?? '';
    const raw = saved.embed;
    if (Array.isArray(raw?.embeds) && raw.embeds.length > 0) {
        embeds.value = raw.embeds.map((e) => ({
            title: e.title ?? '',
            description: e.description ?? '',
            color: e.color ?? '#5865f2',
            image_url: e.image_url ?? '',
            thumbnail_url: e.thumbnail_url ?? '',
            footer_text: e.footer_text ?? '',
            footer_icon_url: e.footer_icon_url ?? '',
            footer_timestamp: !!e.footer_timestamp,
            timestamp_value: e.timestamp_value ?? '',
            fields: Array.isArray(e.fields) ? e.fields.map((f) => ({ ...f })) : [],
        }));
    } else if (raw && typeof raw === 'object' && !raw.embeds) {
        embeds.value = [{
            title: raw.title ?? '',
            description: raw.description ?? '',
            color: raw.color ?? '#5865f2',
            image_url: raw.image_url ?? '',
            thumbnail_url: raw.thumbnail_url ?? '',
            footer_text: raw.footer_text ?? '',
            footer_icon_url: raw.footer_icon_url ?? '',
            footer_timestamp: !!raw.footer_timestamp,
            timestamp_value: raw.timestamp_value ?? '',
            fields: Array.isArray(raw.fields) ? raw.fields.map((f) => ({ ...f })) : [],
        }];
    } else {
        embeds.value = [createEmptyEmbed()];
    }
    embedContentOpen.value = embeds.value.map(() => false);
    embedStyleOpen.value = embeds.value.map(() => false);
    embedCardOpen.value = embeds.value.map(() => false);
    editingId.value = saved.id;
}

function clearForm() {
    content.value = '';
    embeds.value = [createEmptyEmbed()];
    embedContentOpen.value = [false];
    embedStyleOpen.value = [false];
    embedCardOpen.value = [false];
    editingId.value = null;
    saveName.value = '';
}

function saveEmbed() {
    const name = (saveName.value || t('embedSender.slot')).trim() || t('embedSender.slot');
    const payload = {
        name,
        content: content.value,
        embed: {
            embeds: embeds.value.map((e) => ({
                title: e.title,
                description: e.description,
                color: e.color,
                image_url: e.image_url || null,
                thumbnail_url: e.thumbnail_url || null,
                footer_text: e.footer_text,
                footer_icon_url: e.footer_icon_url || null,
                footer_timestamp: e.footer_timestamp,
                timestamp_value: e.timestamp_value || null,
                fields: e.fields,
            })),
        },
    };
    if (editingId.value) {
        router.put(route('guild.saved-embeds.update', { guild: props.guild.id, id: editingId.value }), payload, {
            preserveScroll: true,
            onSuccess: () => { saveName.value = name; },
        });
    } else {
        router.post(route('guild.saved-embeds.store', { guild: props.guild.id }), payload, {
            preserveScroll: true,
            onSuccess: () => { editingId.value = null; saveName.value = ''; },
        });
    }
}

function deleteSaved(id) {
    if (!confirm(t('embedSender.confirmDelete'))) return;
    router.delete(route('guild.saved-embeds.delete', { guild: props.guild.id, id }), { preserveScroll: true });
    if (editingId.value === id) clearForm();
}

function sendEmbed() {
    if (!selectedChannelId.value) {
        alert(t('embedSender.selectChannel'));
        return;
    }
    sending.value = true;
    router.post(route('guild.embed-sender.send', { guild: props.guild.id }), {
        channel_id: selectedChannelId.value,
        content: content.value,
        embed: {
            embeds: embeds.value.map((e) => ({
                title: e.title,
                description: e.description,
                color: e.color,
                image_url: e.image_url || null,
                thumbnail_url: e.thumbnail_url || null,
                footer_text: e.footer_text,
                footer_icon_url: e.footer_icon_url || null,
                footer_timestamp: e.footer_timestamp,
                timestamp_value: e.timestamp_value || null,
                fields: e.fields,
            })),
        },
    }, {
        preserveScroll: true,
        onFinish: () => { sending.value = false; },
    });
}

const textChannels = computed(() => (props.channels || []).filter(c => c.type === 0 || c.type === 5));

/** Discord-style Markdown zu HTML für die Vorschau (Message above embed). */
function markdownToHtml(text) {
    if (!text || typeof text !== 'string') return '';
    let s = String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    // Code-Blöcke (```...```) zuerst, um Innenformatierung zu schützen
    s = s.replace(/```([\s\S]*?)```/g, '<pre class="inline-block rounded bg-[#2f3136] px-1.5 py-0.5 text-sm font-mono my-1"><code>$1</code></pre>');
    // Inline-Code (`...`)
    s = s.replace(/`([^`]+)`/g, '<code class="rounded bg-[#2f3136] px-1 py-0.5 text-sm font-mono">$1</code>');
    // ***bold+italic***, **bold**, *italic*, __underline__, ~~strikethrough~~
    s = s.replace(/\*\*\*([\s\S]*?)\*\*\*/g, '<strong><em>$1</em></strong>');
    s = s.replace(/\*\*([\s\S]*?)\*\*/g, '<strong>$1</strong>');
    s = s.replace(/\*([^\*]+)\*/g, '<em>$1</em>');
    s = s.replace(/_([^_]+)_/g, '<em>$1</em>');
    s = s.replace(/__([\s\S]*?)__/g, '<u>$1</u>');
    s = s.replace(/~~([\s\S]*?)~~/g, '<s>$1</s>');
    // Links [text](url)
    s = s.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-[#00a8fc] hover:underline" target="_blank" rel="noopener">$1</a>');
    // Zeilenumbrüche
    s = s.replace(/\n/g, '<br>');
    return s;
}

const contentPreviewHtml = computed(() => markdownToHtml(content.value));
</script>

<template>
    <Head :title="`${guild?.name} - ${t('embedSender.title')}`" />
    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns ?? {}">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-2">{{ t('embedSender.title') }}</h1>
            <p class="text-gray-400 text-sm mb-6">{{ t('embedSender.description') }}</p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left: Form -->
                <div class="bg-[#1a1b1e] rounded-lg border border-[#202225] p-6 space-y-4 max-h-[calc(100vh-12rem)] overflow-y-auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ t('embedSender.content') }}</label>
                        <textarea
                            v-model="content"
                            rows="3"
                            maxlength="2000"
                            :placeholder="t('embedSender.contentPlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2] resize-none text-sm"
                        />
                        <p class="text-xs text-gray-500 mt-1">{{ content.length }} / 2000</p>
                    </div>

                    <!-- Pro Embed: einklappbare Karte -->
                    <div v-for="(emb, embedIdx) in embeds" :key="embedIdx" class="border border-[#202225] rounded-lg overflow-hidden bg-[#1e1f23]">
                        <button
                            type="button"
                            class="flex items-center justify-between w-full text-left px-4 py-3 hover:bg-[#25262b] transition-colors"
                            @click="toggleCardOpen(embedIdx)"
                        >
                            <span class="text-sm font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 transition-transform shrink-0" :class="{ 'rotate-180': embedCardOpen[embedIdx] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                                {{ t('embedSender.embedN', { n: embedIdx + 1 }) }} — {{ emb.title || t('embedSender.embedUntitled') }}
                            </span>
                            <div class="flex items-center gap-1 shrink-0" @click.stop>
                                <button type="button" @click="duplicateEmbed(embedIdx)" class="p-1.5 rounded text-gray-400 hover:text-white hover:bg-[#36393f]" :title="t('embedSender.duplicateEmbed')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                </button>
                                <button v-if="embeds.length > 1" type="button" @click="removeEmbed(embedIdx)" class="p-1.5 rounded text-red-400 hover:text-red-300 hover:bg-red-500/20" :title="t('embedSender.removeEmbed')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </button>
                        <div v-show="embedCardOpen[embedIdx]" class="px-4 pb-4 pt-0 space-y-4 border-t border-[#202225]">
                            <!-- Titel, Beschreibung & Felder -->
                            <div>
                                <button type="button" class="flex items-center justify-between w-full text-left py-2" @click="toggleContentOpen(embedIdx)">
                                    <h4 class="text-xs font-semibold text-gray-400 uppercase">{{ t('embedSender.titleDescriptionFields') }}</h4>
                                    <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="{ 'rotate-180': embedContentOpen[embedIdx] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div v-show="embedContentOpen[embedIdx]" class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">{{ t('embedSender.embedTitle') }}</label>
                                        <input v-model="emb.title" type="text" maxlength="256" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2]" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">{{ t('embedSender.embedDescription') }}</label>
                                        <textarea v-model="emb.description" rows="4" maxlength="4096" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] resize-none" />
                                        <p class="text-xs text-gray-500">{{ emb.description.length }} / 4096</p>
                                    </div>
                                    <div class="pt-2 border-t border-[#202225]/50">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-medium text-gray-400">{{ t('embedSender.fields') }}</span>
                                            <button type="button" @click="addField(embedIdx)" class="text-xs text-[#5865f2] hover:underline font-medium">{{ t('embedSender.addField') }}</button>
                                        </div>
                                        <div v-for="(f, i) in emb.fields" :key="i" class="flex gap-2 items-start mb-2 p-2 rounded bg-[#36393f]/50">
                                            <input v-model="f.name" type="text" :placeholder="t('embedSender.fieldName')" maxlength="256" class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm" />
                                            <input v-model="f.value" type="text" :placeholder="t('embedSender.fieldValue')" maxlength="1024" class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm" />
                                            <label class="flex items-center gap-1 text-xs text-gray-400 shrink-0"><input v-model="f.inline" type="checkbox" class="rounded border-gray-500 text-[#5865f2]" /> Inline</label>
                                            <button type="button" @click="removeField(embedIdx, i)" class="text-red-400 hover:text-red-300 text-sm">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Farbe, Bilder & Footer -->
                            <div>
                                <button type="button" class="flex items-center justify-between w-full text-left py-2" @click="toggleStyleOpen(embedIdx)">
                                    <h4 class="text-xs font-semibold text-gray-400 uppercase">{{ t('embedSender.colorImagesFooter') }}</h4>
                                    <svg class="w-4 h-4 text-gray-500 transition-transform shrink-0" :class="{ 'rotate-180': embedStyleOpen[embedIdx] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                                <div v-show="embedStyleOpen[embedIdx]" class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('embedSender.color') }}</label>
                                        <PaletteColorPicker v-model="emb.color" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('embedSender.imageUrl') }}</label>
                                        <input v-model="emb.image_url" type="url" placeholder="https://..." class="w-full rounded-lg bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] focus:ring-1 focus:ring-[#5865f2]/30" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-400 mb-1.5">{{ t('embedSender.thumbnailUrl') }}</label>
                                        <input v-model="emb.thumbnail_url" type="url" placeholder="https://..." class="w-full rounded-lg bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] focus:ring-1 focus:ring-[#5865f2]/30" />
                                    </div>
                                    <div class="rounded-lg bg-[#25262b]/80 border border-[#202225] p-4 space-y-3">
                                        <h5 class="text-xs font-semibold text-gray-300 uppercase tracking-wider flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ t('embedSender.footerSection') }}
                                        </h5>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">{{ t('embedSender.footerText') }}</label>
                                            <input v-model="emb.footer_text" type="text" maxlength="2048" :placeholder="t('embedSender.footerTextPlaceholder')" class="w-full rounded-lg bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] focus:ring-1 focus:ring-[#5865f2]/30 placeholder-gray-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">{{ t('embedSender.footerIconUrl') }}</label>
                                            <input v-model="emb.footer_icon_url" type="url" placeholder="https://..." class="w-full rounded-lg bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] focus:ring-1 focus:ring-[#5865f2]/30 placeholder-gray-500" />
                                        </div>
                                        <div class="pt-2 border-t border-[#202225]/60">
                                            <label class="flex items-center gap-2 cursor-pointer mb-2">
                                                <input v-model="emb.footer_timestamp" type="checkbox" class="rounded border-gray-500 text-[#5865f2] focus:ring-[#5865f2]" />
                                                <span class="text-xs font-medium text-gray-400">{{ t('embedSender.showTimestamp') }}</span>
                                            </label>
                                            <div v-if="emb.footer_timestamp" class="mt-2 space-y-2 pl-5 border-l-2 border-[#36393f]">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" :checked="!emb.timestamp_value" @change="setTimestampUseNow(emb, true)" class="text-[#5865f2] focus:ring-[#5865f2]" />
                                                    <span class="text-xs text-gray-400">{{ t('embedSender.timestampNow') }}</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" :checked="!!emb.timestamp_value" @change="setTimestampUseNow(emb, false)" class="text-[#5865f2] focus:ring-[#5865f2]" />
                                                    <span class="text-xs text-gray-400">{{ t('embedSender.timestampFixed') }}</span>
                                                </label>
                                                <input v-if="emb.timestamp_value" v-model="emb.timestamp_value" type="datetime-local" class="w-full rounded-lg bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2] [color-scheme:dark]" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <PrimaryButton v-if="embeds.length < MAX_EMBEDS" type="button" @click="addEmbed" class="w-full">
                        {{ t('embedSender.addEmbed') }}
                    </PrimaryButton>

                    <!-- Save -->
                    <div class="border-t border-[#202225] pt-4 flex flex-wrap gap-2 items-center">
                        <input v-model="saveName" type="text" :placeholder="t('embedSender.saveAsName')" maxlength="100" class="rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm w-40 focus:outline-none focus:border-[#5865f2]" />
                        <PrimaryButton @click="saveEmbed" :disabled="savedEmbeds.length >= MAX_SAVED && !editingId">
                            {{ editingId ? t('embedSender.update') : t('embedSender.save') }}
                        </PrimaryButton>
                        <SecondaryButton @click="clearForm">{{ t('embedSender.clear') }}</SecondaryButton>
                    </div>
                </div>

                <!-- Right: Preview + Saved + Send -->
                <div class="flex flex-col gap-4">
                    <div class="bg-[#1a1b1e] rounded-lg border border-[#202225] p-6 flex flex-col min-h-[320px]">
                        <h3 class="text-sm font-medium text-gray-300 mb-3">{{ t('embedSender.preview') }}</h3>
                        <div v-if="content" class="text-gray-300 text-sm mb-3 break-words [&_pre]:whitespace-pre-wrap" v-html="contentPreviewHtml"></div>
                        <div class="min-h-[200px] space-y-2">
                            <div v-for="(emb, idx) in embeds" :key="idx" class="min-h-[120px]">
                                <EmbedEditor
                                    :model="getPreviewModel(emb)"
                                    :placeholder-replacements="{}"
                                    :accent-color="emb.color"
                                    :footer-icon-url="emb.footer_icon_url || guild?.icon_url"
                                    :show-add-field="false"
                                    :description-auto-expand="true"
                                    class="pointer-events-none"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#1a1b1e] rounded-lg border border-[#202225] p-4">
                        <h3 class="text-sm font-medium text-gray-300 mb-2">{{ t('embedSender.savedSlots') }} ({{ savedEmbeds.length }} / {{ MAX_SAVED }})</h3>
                        <div class="space-y-2">
                            <div v-for="s in savedEmbeds" :key="s.id" class="flex items-center justify-between gap-2 p-2 rounded bg-[#36393f]/50">
                                <span class="text-sm text-white truncate">{{ s.name }}</span>
                                <div class="flex gap-1 shrink-0">
                                    <button type="button" @click="loadSaved(s)" class="text-xs px-2 py-1 rounded bg-[#5865f2] hover:bg-[#4752c4] text-white">
                                        {{ t('embedSender.load') }}
                                    </button>
                                    <button type="button" @click="deleteSaved(s.id)" class="text-xs px-2 py-1 rounded bg-red-600/80 hover:bg-red-600 text-white">
                                        {{ t('common.delete') }}
                                    </button>
                                </div>
                            </div>
                            <p v-if="savedEmbeds.length === 0" class="text-xs text-gray-500">{{ t('embedSender.noSaved') }}</p>
                        </div>
                    </div>

                    <div class="bg-[#1a1b1e] rounded-lg border border-[#202225] p-4 flex flex-wrap gap-3 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs text-gray-400 mb-1">{{ t('embedSender.sendToChannel') }}</label>
                            <select v-model="selectedChannelId" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2]">
                                <option value="">{{ t('embedSender.selectChannel') }}</option>
                                <option v-for="ch in textChannels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                            </select>
                        </div>
                        <PrimaryButton @click="sendEmbed" :disabled="sending || !selectedChannelId">
                            {{ sending ? t('embedSender.sending') : t('embedSender.send') }}
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>
