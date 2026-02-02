<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    addOns: { type: Object, default: () => ({}) },
    channels: Array,
    roles: Array,
    reactionRoles: Array,
});

// Reaction Roles
const showReactionRoleModal = ref(false);
const editingReactionRole = ref(null);
const reactionRoleForm = useForm({
    channel_id: '',
    enabled: true,
    embed_title: '',
    embed_description: '',
    embed_color: '#5865f2',
    embed_thumbnail: '',
    embed_image: '',
    embed_banner: '',
    embed_footer: true,
    reactions: [],
});

const reactionRoleReactions = ref([]);
const draggedIndex = ref(null);
const dragOverIndex = ref(null);

function openReactionRoleModal(reactionRole = null) {
    editingReactionRole.value = reactionRole;
    if (reactionRole) {
        reactionRoleForm.channel_id = reactionRole.channel_id;
        reactionRoleForm.enabled = reactionRole.enabled;
        reactionRoleForm.embed_title = reactionRole.embed_title || '';
        reactionRoleForm.embed_description = reactionRole.embed_description || '';
        reactionRoleForm.embed_color = reactionRole.embed_color || '#5865f2';
        reactionRoleForm.embed_thumbnail = reactionRole.embed_thumbnail || '';
        reactionRoleForm.embed_image = reactionRole.embed_image || '';
        reactionRoleForm.embed_banner = reactionRole.embed_banner || '';
        reactionRoleForm.embed_footer = reactionRole.embed_footer ?? true;
        reactionRoleReactions.value = reactionRole.reactions ? reactionRole.reactions.map((r, idx) => ({
            ...r,
            id: r.id || `reaction-${Date.now()}-${idx}` // Füge ID hinzu falls nicht vorhanden
        })) : [];
    } else {
        reactionRoleForm.reset();
        reactionRoleReactions.value = [];
    }
    showReactionRoleModal.value = true;
}

function closeReactionRoleModal() {
    showReactionRoleModal.value = false;
    editingReactionRole.value = null;
    reactionRoleForm.reset();
    reactionRoleReactions.value = [];
    draggedIndex.value = null;
    dragOverIndex.value = null;
}

function addReactionRole() {
    reactionRoleReactions.value.push({
        id: `reaction-${Date.now()}-${Math.random()}`, // Eindeutige ID für besseres Tracking
        emoji: '',
        role_id: '',
        label: '',
    });
}

function removeReactionRole(index) {
    reactionRoleReactions.value.splice(index, 1);
}

// Drag & Drop Funktionen
function onDragStart(index) {
    draggedIndex.value = index;
}

function onDragEnd() {
    draggedIndex.value = null;
    dragOverIndex.value = null;
}

function onDragOver(event, index) {
    event.preventDefault();
    dragOverIndex.value = index;
}

function onDrop(event, dropIndex) {
    event.preventDefault();
    
    if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
        dragOverIndex.value = null;
        return;
    }
    
    const oldIndex = draggedIndex.value;
    
    // Erstelle eine neue Array-Kopie für bessere Reaktivität
    const newArray = [...reactionRoleReactions.value];
    
    // Entferne das Element von der alten Position
    const [draggedItem] = newArray.splice(oldIndex, 1);
    
    // Berechne die neue Position
    // Wenn wir nach rechts verschieben (oldIndex < dropIndex), muss dropIndex um 1 reduziert werden
    // weil wir das Element bereits entfernt haben und die Indizes sich verschoben haben
    let newIndex = dropIndex;
    if (oldIndex < dropIndex) {
        newIndex = dropIndex - 1;
    }
    
    // Füge das Element an der neuen Position ein
    newArray.splice(newIndex, 0, draggedItem);
    
    // Aktualisiere das Array - das löst die Reaktivität aus
    reactionRoleReactions.value = newArray;
    
    draggedIndex.value = null;
    dragOverIndex.value = null;
}

function saveReactionRole() {
    // Entferne temporäre IDs vor dem Speichern (falls vorhanden)
    const reactionsToSave = reactionRoleReactions.value.map(({ id, ...reaction }) => reaction);
    reactionRoleForm.reactions = reactionsToSave;
    
    if (editingReactionRole.value) {
        reactionRoleForm.put(route('guild.reaction-roles.update', { 
            guild: props.guild.id, 
            id: editingReactionRole.value.id 
        }), {
            preserveScroll: true,
            onSuccess: () => {
                closeReactionRoleModal();
            },
        });
    } else {
        reactionRoleForm.post(route('guild.reaction-roles.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeReactionRoleModal();
            },
        });
    }
}

function deleteReactionRole(id) {
    if (confirm(t('reactionRoles.confirmDelete'))) {
        router.delete(route('guild.reaction-roles.delete', { guild: props.guild.id, id }), {
            preserveScroll: true,
        });
    }
}

const sendingMessage = ref({});

function resendReactionRoleMessage(reactionRoleId) {
    if (confirm(t('reactionRoles.confirmResend'))) {
        sendingMessage.value[reactionRoleId] = true;
        router.post(route('guild.reaction-roles.resend', { guild: props.guild.id, id: reactionRoleId }), {}, {
            preserveScroll: true,
            onSuccess: () => {
                sendingMessage.value[reactionRoleId] = false;
            },
            onError: () => {
                sendingMessage.value[reactionRoleId] = false;
            },
        });
    }
}

// Discord Markdown Parser - supports all Discord markdown features
function parseDiscordMarkdown(text) {
    if (!text) return '';
    
    // Store code blocks temporarily
    const codeBlocks = [];
    let html = text;
    
    // 1. Extract code blocks first and replace with placeholder
    html = html.replace(/```([\s\S]*?)```/g, (match, code) => {
        const id = `__CODEBLOCK_${codeBlocks.length}__`;
        codeBlocks.push(code);
        return id;
    });
    
    // Escape HTML
    html = html
        .replace(/&/g, '&amp;')
        .replace(/\u003C/g, '&lt;')
        .replace(/\u003E/g, '&gt;');
    
    // 2. Extract inline code and replace with placeholder
    const inlineCodes = [];
    html = html.replace(/`([^`\n]+)`/g, (match, code) => {
        const id = `__INLINECODE_${inlineCodes.length}__`;
        inlineCodes.push(code);
        return id;
    });
    
    // 3. Process lists first (before other formatting)
    html = processLists(html);
    
    // 4. Process headings
    html = html.replace(/^### (.*)$/gm, '<h3 class="text-base font-semibold text-white mt-3 mb-1">$1</h3>');
    html = html.replace(/^## (.*)$/gm, '<h2 class="text-lg font-bold text-white mt-4 mb-2">$1</h2>');
    html = html.replace(/^# (.*)$/gm, '<h1 class="text-xl font-bold text-white mt-4 mb-2">$1</h1>');
    
    // 5. Process markdown formatting (order matters!)
    
    // Bold and Italic combinations (***text*** or ___text___)
    html = html.replace(/\*\*\*([^*]+)\*\*\*/g, '<strong class="font-bold italic">$1</strong>');
    html = html.replace(/___([^_]+)___/g, '<strong class="font-bold italic">$1</strong>');
    
    // Bold (**text** or __text__) - but not if already processed
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-bold">$1</strong>');
    html = html.replace(/__(?!_)([^_]+)__(?!_)/g, '<strong class="font-bold">$1</strong>');
    
    // Strikethrough (~~text~~)
    html = html.replace(/~~([^~]+)~~/g, '<span class="line-through">$1</span>');
    
    // Italic (*text* or _text_) - but not if already processed as bold
    html = html.replace(/\*([^*]+)\*/g, '<em class="italic">$1</em>');
    html = html.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
    
    // Links [text](url)
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:underline">$1</a>');
    
    // Mentions <@userid>, <#channelid>, <@&roleid>
    html = html.replace(/&lt;@(\d+)&gt;/g, '<span class="text-blue-400 bg-blue-400 bg-opacity-20 px-1.5 py-0.5 rounded">User</span>');
    html = html.replace(/&lt;#(\d+)&gt;/g, '<span class="text-blue-400 bg-blue-400 bg-opacity-20 px-1.5 py-0.5 rounded">#channel</span>');
    html = html.replace(/&lt;@&amp;(\d+)&gt;/g, '<span class="text-blue-400 bg-blue-400 bg-opacity-20 px-1.5 py-0.5 rounded">Role</span>');
    
    // Emojis :emoji_name: (keep as is, Discord will render them)
    // We don't need to change emojis, they're already in the text
    
    // Line breaks
    html = html.replace(/\n/g, '<br>');
    
    // 6. Restore inline code
    inlineCodes.forEach((code, index) => {
        const escapedCode = code
            .replace(/&/g, '&amp;')
            .replace(/\u003C/g, '&lt;')
            .replace(/\u003E/g, '&gt;');
        html = html.replace(`__INLINECODE_${index}__`, `<code class="bg-gray-900 px-1.5 py-0.5 rounded text-xs text-gray-300 font-mono">${escapedCode}</code>`);
    });
    
    // 7. Restore code blocks
    codeBlocks.forEach((code, index) => {
        const escapedCode = code
            .replace(/&/g, '&amp;')
            .replace(/\u003C/g, '&lt;')
            .replace(/\u003E/g, '&gt;');
        html = html.replace(`__CODEBLOCK_${index}__`, `<pre class="bg-gray-900 p-2 rounded text-xs text-gray-300 font-mono overflow-x-auto my-1 whitespace-pre-wrap"><code>${escapedCode}</code></pre>`);
    });
    
    return html;
}

// Helper function to process lists
function processLists(text) {
    const lines = text.split('\n');
    const processedLines = [];
    let listItems = [];
    let inList = false;
    
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        // Check if line is a list item (starts with - or * followed by space, optionally with leading whitespace)
        const listMatch = line.match(/^(\s*)([-*])\s+(.+)$/);
        
        if (listMatch) {
            if (!inList) {
                inList = true;
                listItems = [];
            }
            // Process the content for markdown (bold, italic, etc.) before storing
            let content = listMatch[3];
            listItems.push({ indent: listMatch[1].length, content: content });
        } else {
            // If we were in a list, close it
            if (inList) {
                processedLines.push(buildList(listItems));
                listItems = [];
                inList = false;
            }
            // Only add non-empty lines or lines that aren't just whitespace
            if (line.trim() || processedLines.length === 0 || processedLines[processedLines.length - 1] !== '') {
                processedLines.push(line);
            }
        }
    }
    
    // Close any remaining list
    if (inList && listItems.length > 0) {
        processedLines.push(buildList(listItems));
    }
    
    return processedLines.join('\n');
}

// Helper function to build HTML list
function buildList(items) {
    if (items.length === 0) return '';
    
    let html = '<ul class="list-disc list-inside space-y-1 my-2 ml-4">';
    items.forEach(item => {
        // Process markdown in list item content
        let content = item.content;
        // Bold
        content = content.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-bold">$1</strong>');
        content = content.replace(/__(?!_)([^_]+)__(?!_)/g, '<strong class="font-bold">$1</strong>');
        // Italic
        content = content.replace(/\*([^*]+)\*/g, '<em class="italic">$1</em>');
        content = content.replace(/_([^_]+)_/g, '<em class="italic">$1</em>');
        // Strikethrough
        content = content.replace(/~~([^~]+)~~/g, '<span class="line-through">$1</span>');
        // Links
        content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-cyan-400 hover:underline">$1</a>');
        
        html += `<li class="text-sm text-gray-300">${content}</li>`;
    });
    html += '</ul>';
    return html;
}

// Computed properties for rendered markdown
const renderedTitle = computed(() => {
    return parseDiscordMarkdown(reactionRoleForm.embed_title);
});

const renderedDescription = computed(() => {
    return parseDiscordMarkdown(reactionRoleForm.embed_description);
});
</script>

<template>
    <Head :title="`${guild.name} - ${t('reactionRoles.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns ?? {}">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ t('reactionRoles.title') }}</h1>

            <div class="space-y-4">
                <!-- Liste der Reaktionsrollen -->
                <div v-if="reactionRoles && reactionRoles.length > 0" class="space-y-3">
                    <div
                        v-for="rr in reactionRoles"
                        :key="rr.id"
                        class="bg-[#2f3136] rounded-lg p-4 border border-[#202225] hover:border-[#5865f2] transition-colors"
                    >
                          <div class="flex items-center justify-between">
                              <div class="flex items-center gap-3">
                                  <span class="text-sm font-medium text-white">{{ rr.embed_title || t('reactionRoles.unnamed') }}</span>
                                  <span
                                      :class="[
                                          'px-2 py-0.5 rounded text-xs font-medium',
                                          rr.enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'
                                      ]"
                                  >
                                      {{ rr.enabled ? t('common.active') : t('common.inactive') }}
                                  </span>
                              </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button
                                    @click="resendReactionRoleMessage(rr.id)"
                                    :disabled="sendingMessage[rr.id]"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded text-sm font-medium transition-colors flex items-center gap-1"
                                >
                                    <svg v-if="!sendingMessage[rr.id]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ sendingMessage[rr.id] ? t('reactionRoles.sending') : t('reactionRoles.resend') }}
                                </button>
                                <button
                                    @click="openReactionRoleModal(rr)"
                                    class="px-3 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded text-sm font-medium transition-colors"
                                >
                                    {{ t('common.edit') }}
                                </button>
                                <button
                                    @click="deleteReactionRole(rr.id)"
                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors"
                                >
                                    {{ t('common.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-gray-400 bg-[#2f3136] rounded-lg border border-[#202225]">
                    <p>{{ t('reactionRoles.noReactionRoles') }}</p>
                </div>

                <!-- Button zum Hinzufügen -->
                <button
                    @click="openReactionRoleModal()"
                    class="w-full py-3 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('reactionRoles.addReactionRole') }}
                </button>
            </div>
        </div>

        <!-- Reaction Role Modal -->
        <div
            v-if="showReactionRoleModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeReactionRoleModal"
        >
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#202225]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">
                            {{ editingReactionRole ? t('reactionRoles.editReactionRole') : t('reactionRoles.createReactionRole') }}
                        </h2>
                        <button
                            @click="closeReactionRoleModal"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="saveReactionRole" class="p-6 space-y-6">
                    <!-- Kanal-Auswahl -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('reactionRoles.channel') }}
                        </label>
                        <select
                            v-model="reactionRoleForm.channel_id"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
                            <template v-for="channel in channels" :key="channel.id">
                                <option
                                    v-if="channel.type === 4 && channel.is_category"
                                    disabled
                                    class="bg-[#2f3136] font-semibold"
                                >
                                    ── {{ channel.name }} ──
                                </option>
                                <option
                                    v-else-if="channel.type === 0"
                                    :value="channel.id"
                                >
                                    # {{ channel.name }}
                                </option>
                            </template>
                        </select>
                    </div>

                    <!-- Embed Vorschau -->
                    <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                        <h4 class="text-sm font-medium text-gray-300 mb-4">{{ t('welcome.welcomeSection.preview') }}</h4>
                        <div class="flex justify-center">
                            <div class="bg-[#2f3136] rounded-lg p-4" style="max-width: 520px; width: 100%;">
                                <!-- Banner -->
                                <div v-if="reactionRoleForm.embed_banner" class="mb-2 rounded-t-lg overflow-hidden">
                                    <img :src="reactionRoleForm.embed_banner" alt="Banner" class="w-full h-32 object-cover" />
                                </div>
                                <!-- Discord-ähnliche Embed-Vorschau -->
                                <div
                                    class="rounded border-l-4 p-3"
                                    :style="{ borderLeftColor: reactionRoleForm.embed_color || '#5865f2' }"
                                >
                                    <div class="space-y-2">
                                        <h5
                                            v-if="reactionRoleForm.embed_title"
                                            class="text-base font-semibold text-white"
                                            v-html="renderedTitle"
                                        ></h5>
                                        <div
                                            v-if="reactionRoleForm.embed_description"
                                            class="text-sm text-gray-300"
                                            v-html="renderedDescription"
                                        ></div>
                                        <div
                                            v-if="reactionRoleForm.embed_thumbnail"
                                            class="mt-2"
                                        >
                                            <img :src="reactionRoleForm.embed_thumbnail" alt="Thumbnail" class="max-w-full h-20 object-contain rounded" />
                                        </div>
                                        <div
                                            v-if="reactionRoleForm.embed_image"
                                            class="mt-2"
                                        >
                                            <img :src="reactionRoleForm.embed_image" alt="Image" class="max-w-full rounded" />
                                        </div>
                                        <div
                                            v-if="reactionRoleForm.embed_footer"
                                            class="flex items-center gap-2 pt-2 border-t border-[#202225]"
                                        >
                                            <img :src="guild.icon_url" alt="Server Icon" class="w-4 h-4 rounded-full" />
                                            <span class="text-xs text-gray-400">{{ guild.name }} • Heute um {{ new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Reactions Preview -->
                                <div v-if="reactionRoleReactions.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <div
                                        v-for="(reaction, idx) in reactionRoleReactions"
                                        :key="idx"
                                        class="px-3 py-1.5 bg-[#36393f] rounded-full border border-[#202225] flex items-center gap-2"
                                    >
                                        <span class="text-lg">{{ reaction.emoji }}</span>
                                        <span class="text-sm text-gray-300">{{ reaction.label || t('reactionRoles.role') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Embed Konfiguration -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedTitle') }}</label>
                            <input
                                type="text"
                                v-model="reactionRoleForm.embed_title"
                                :placeholder="t('reactionRoles.embedTitlePlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedDescription') }}</label>
                            <textarea
                                v-model="reactionRoleForm.embed_description"
                                rows="3"
                                :placeholder="t('reactionRoles.embedDescriptionPlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedColor') }}</label>
                            <div class="flex gap-2">
                                <input
                                    type="color"
                                    v-model="reactionRoleForm.embed_color"
                                    class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                />
                                <input
                                    type="text"
                                    v-model="reactionRoleForm.embed_color"
                                    placeholder="#5865f2"
                                    class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedBanner') }}</label>
                            <input
                                type="url"
                                v-model="reactionRoleForm.embed_banner"
                                placeholder="https://example.com/banner.png"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                            <p class="text-xs text-gray-400 mt-1">{{ t('reactionRoles.embedBannerDescription') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedThumbnail') }}</label>
                            <input
                                type="url"
                                v-model="reactionRoleForm.embed_thumbnail"
                                placeholder="https://example.com/thumbnail.png"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('reactionRoles.embedImage') }}</label>
                            <input
                                type="url"
                                v-model="reactionRoleForm.embed_image"
                                placeholder="https://example.com/image.png"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="reactionRoleForm.embed_footer"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">{{ t('reactionRoles.showFooter') }}</label>
                        </div>
                    </div>

                    <!-- Reaktionsrollen -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-300">{{ t('reactionRoles.reactions') }}</label>
                            <button
                                type="button"
                                @click="addReactionRole"
                                class="px-3 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded text-sm font-medium transition-colors"
                            >
                                {{ t('reactionRoles.addReaction') }}
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(reaction, index) in reactionRoleReactions"
                                :key="reaction.id || index"
                                :draggable="true"
                                @dragstart="onDragStart(index)"
                                @dragend="onDragEnd"
                                @dragover="onDragOver($event, index)"
                                @drop="onDrop($event, index)"
                                :class="[
                                    'bg-[#36393f] rounded-lg p-4 border transition-all cursor-move',
                                    draggedIndex === index ? 'border-[#5865f2] opacity-50' : 'border-[#202225]',
                                    dragOverIndex === index && draggedIndex !== index ? 'border-[#5865f2] border-2 scale-105' : ''
                                ]"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Drag Handle -->
                                    <div class="flex flex-col items-center justify-center pt-6 text-gray-500 cursor-grab active:cursor-grabbing">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1">{{ t('reactionRoles.emoji') }}</label>
                                            <input
                                                type="text"
                                                v-model="reaction.emoji"
                                                :placeholder="t('reactionRoles.emojiPlaceholder')"
                                                class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1">{{ t('reactionRoles.role') }}</label>
                                            <select
                                                v-model="reaction.role_id"
                                                required
                                                class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2]"
                                            >
                                                <option value="">{{ t('common.pleaseSelect') }}</option>
                                                <option
                                                    v-for="role in roles"
                                                    :key="role.id"
                                                    :value="role.id"
                                                >
                                                    {{ role.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-400 mb-1">{{ t('reactionRoles.label') }}</label>
                                            <div class="flex gap-2">
                                                <input
                                                    type="text"
                                                    v-model="reaction.label"
                                                    :placeholder="t('reactionRoles.labelPlaceholder')"
                                                    class="flex-1 rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 text-sm focus:outline-none focus:border-[#5865f2]"
                                                />
                                                <button
                                                    type="button"
                                                    @click="removeReactionRole(index)"
                                                    class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors"
                                                >
                                                    ×
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="reactionRoleReactions.length === 0" class="text-center py-8 text-gray-400 text-sm">
                                <p>{{ t('reactionRoles.noReactions') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Speichern -->
                    <div class="flex items-center justify-end pt-4 border-t border-[#202225] gap-3">
                        <button
                            type="button"
                            @click="closeReactionRoleModal"
                            class="px-6 py-2.5 bg-[#36393f] hover:bg-[#40444b] text-white rounded-lg transition-colors font-medium"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="reactionRoleForm.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                        >
                            {{ reactionRoleForm.processing ? t('common.saving') : t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GuildLayout>
</template>
