<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    roles: Array,
    autoModConfig: Object,
});

const form = useForm({
    bad_words_enabled: props.autoModConfig?.bad_words_enabled ?? false,
    bad_words_list: props.autoModConfig?.bad_words_list ?? [],
    bad_words_delete_message: props.autoModConfig?.bad_words_delete_message ?? true,
    bad_words_warning_message: props.autoModConfig?.bad_words_warning_message ?? '⚠️ {user}, solche Aussagen sind auf diesem Server nicht gestattet!',
    bad_words_use_embed: props.autoModConfig?.bad_words_use_embed ?? false,
    bad_words_embed_title: props.autoModConfig?.bad_words_embed_title ?? '⚠️ Unerlaubte Sprache',
    bad_words_embed_description: props.autoModConfig?.bad_words_embed_description ?? '{user}, solche Aussagen sind auf diesem Server nicht gestattet!',
    bad_words_embed_color: props.autoModConfig?.bad_words_embed_color ?? '#ff0000',
    bad_words_embed_footer: props.autoModConfig?.bad_words_embed_footer ?? true,
    block_discord_invites: props.autoModConfig?.block_discord_invites ?? false,
    block_discord_invites_delete_message: props.autoModConfig?.block_discord_invites_delete_message ?? true,
    block_discord_invites_warning_message: props.autoModConfig?.block_discord_invites_warning_message ?? '⚠️ {user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!',
    block_discord_invites_use_embed: props.autoModConfig?.block_discord_invites_use_embed ?? false,
    block_discord_invites_embed_title: props.autoModConfig?.block_discord_invites_embed_title ?? '⚠️ Discord-Einladungslinks nicht erlaubt',
    block_discord_invites_embed_description: props.autoModConfig?.block_discord_invites_embed_description ?? '{user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!',
    block_discord_invites_embed_color: props.autoModConfig?.block_discord_invites_embed_color ?? '#ff0000',
    block_discord_invites_embed_footer: props.autoModConfig?.block_discord_invites_embed_footer ?? true,
    whitelist_channels: props.autoModConfig?.whitelist_channels ?? [],
    whitelist_roles: props.autoModConfig?.whitelist_roles ?? [],
});

const newBadWord = ref('');
const selectedChannels = ref(props.autoModConfig?.whitelist_channels ?? []);
const selectedRoles = ref(props.autoModConfig?.whitelist_roles ?? []);
const channelSelectorOpen = ref(false);
const roleSelectorOpen = ref(false);
const hoveredChannel = ref(null);
const hoveredRole = ref(null);
const channelDropdownStyle = ref({});
const roleDropdownStyle = ref({});

function addBadWord() {
    if (newBadWord.value.trim() && !form.bad_words_list.includes(newBadWord.value.trim())) {
        form.bad_words_list.push(newBadWord.value.trim());
        newBadWord.value = '';
    }
}

function removeBadWord(word) {
    form.bad_words_list = form.bad_words_list.filter(w => w !== word);
}

function addChannel(channelId) {
    if (!selectedChannels.value.includes(channelId)) {
        selectedChannels.value.push(channelId);
        form.whitelist_channels = selectedChannels.value;
    }
    channelSelectorOpen.value = false;
}

function updateChannelDropdownPosition() {
    if (!channelSelectorOpen.value) return;
    const button = document.querySelector('[data-channel-selector] button');
    if (button) {
        const rect = button.getBoundingClientRect();
        channelDropdownStyle.value = {
            top: `${rect.bottom + 4}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    }
}

function updateRoleDropdownPosition() {
    if (!roleSelectorOpen.value) return;
    const button = document.querySelector('[data-role-selector] button');
    if (button) {
        const rect = button.getBoundingClientRect();
        roleDropdownStyle.value = {
            top: `${rect.bottom + 4}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
        };
    }
}

function removeChannel(channelId) {
    selectedChannels.value = selectedChannels.value.filter(id => id !== channelId);
    form.whitelist_channels = selectedChannels.value;
}

function addRole(roleId) {
    if (!selectedRoles.value.includes(roleId)) {
        selectedRoles.value.push(roleId);
        form.whitelist_roles = selectedRoles.value;
    }
    roleSelectorOpen.value = false;
}

function removeRole(roleId) {
    selectedRoles.value = selectedRoles.value.filter(id => id !== roleId);
    form.whitelist_roles = selectedRoles.value;
}

function getChannelName(channelId) {
    const channel = props.channels.find(c => c.id === channelId);
    return channel ? channel.name : 'Unbekannt';
}

function getRoleName(roleId) {
    const role = props.roles.find(r => r.id === roleId);
    return role ? role.name : 'Unbekannt';
}

function getRoleColor(roleId) {
    const role = props.roles.find(r => r.id === roleId);
    if (!role) return '#ffffff';
    
    // Discord gibt Farben als Integer zurück (0 = keine Farbe = Standard)
    if (role.color && role.color > 0) {
        // Konvertiere Integer zu Hex (6-stellig, mit führenden Nullen)
        const hex = role.color.toString(16).padStart(6, '0');
        return `#${hex}`;
    }
    return '#ffffff';
}

// Watch für Dropdown-Positionierung
watch(channelSelectorOpen, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            updateChannelDropdownPosition();
        });
    }
});

watch(roleSelectorOpen, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            updateRoleDropdownPosition();
        });
    }
});

// Schließe Dropdowns beim Klicken außerhalb
function handleClickOutside(event) {
    const channelSelector = event.target.closest('[data-channel-selector]');
    const roleSelector = event.target.closest('[data-role-selector]');
    
    if (!channelSelector) {
        channelSelectorOpen.value = false;
    }
    if (!roleSelector) {
        roleSelectorOpen.value = false;
    }
}

// Event Listener für Click Outside
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

function saveConfig() {
    form.put(route('guild.auto-moderation.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`${guild.name} - ${t('autoModeration.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ t('autoModeration.title') }}</h1>

            <form @submit.prevent="saveConfig" class="space-y-6">
                <!-- Bad Words -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-1">{{ t('autoModeration.badWords.title') }}</h3>
                            <p class="text-sm text-gray-400">{{ t('autoModeration.badWords.description') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.bad_words_enabled"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5865f2]"></div>
                        </label>
                    </div>

                    <div v-show="form.bad_words_enabled" class="border-t border-[#202225] p-6 space-y-4">
                        <!-- Bad Words Liste -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Bad Words Liste
                            </label>
                            <div class="flex gap-2 mb-2">
                                <input
                                    type="text"
                                    v-model="newBadWord"
                                    @keyup.enter="addBadWord"
                                    placeholder="Wort hinzufügen..."
                                    class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                                <button
                                    type="button"
                                    @click="addBadWord"
                                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                                >
                                    Hinzufügen
                                </button>
                            </div>
                            <div v-if="form.bad_words_list.length > 0" class="flex flex-wrap gap-2 mt-2">
                                <span
                                    v-for="word in form.bad_words_list"
                                    :key="word"
                                    class="inline-flex items-center gap-2 bg-[#36393f] text-white px-3 py-1 rounded-full text-sm"
                                >
                                    {{ word }}
                                    <button
                                        type="button"
                                        @click="removeBadWord(word)"
                                        class="text-red-400 hover:text-red-300"
                                    >
                                        ×
                                    </button>
                                </span>
                            </div>
                            <p v-else class="text-sm text-gray-400 mt-2">Noch keine Bad Words hinzugefügt</p>
                        </div>

                        <!-- Nachricht löschen -->
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.bad_words_delete_message"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">
                                Nachricht automatisch löschen
                            </label>
                        </div>

                        <!-- Warnung Typ -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">
                                Warnnachricht Typ
                            </label>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="form.bad_words_use_embed"
                                        :value="false"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">Textnachricht</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="form.bad_words_use_embed"
                                        :value="true"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">Embed-Nachricht</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Textnachricht -->
                            <div v-if="!form.bad_words_use_embed">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Warnnachricht
                                </label>
                                <textarea
                                    v-model="form.bad_words_warning_message"
                                    rows="2"
                                    placeholder="⚠️ {user}, solche Aussagen sind auf diesem Server nicht gestattet!"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                ></textarea>
                                <p class="text-xs text-gray-400 mt-1">
                                    Verfügbare Platzhalter: <code class="bg-[#202225] px-1 rounded">{user}</code>, <code class="bg-[#202225] px-1 rounded">{username}</code>, <code class="bg-[#202225] px-1 rounded">{server}</code>
                                </p>
                            </div>

                            <!-- Embed-Nachricht -->
                            <div v-if="form.bad_words_use_embed" class="space-y-4">
                                <!-- Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-4 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div class="flex">
                                        <div
                                            class="w-1 h-auto rounded-l-md"
                                            :style="{ backgroundColor: form.bad_words_embed_color }"
                                        ></div>
                                        <div class="flex-1 bg-[#2f3136] p-3 rounded-r-md">
                                            <p class="text-sm font-semibold text-white mb-1">{{ form.bad_words_embed_title.replace('{user}', 'ibeiros#0') }}</p>
                                            <p class="text-xs text-gray-300">{{ form.bad_words_embed_description.replace('{user}', 'ibeiros#0') }}</p>
                                            <div v-if="form.bad_words_embed_footer" class="flex items-center text-xs text-gray-400 mt-2">
                                                <img :src="guild.icon_url" alt="Server Icon" class="w-4 h-4 rounded-full mr-1" />
                                                <span>{{ guild.name }} • Heute um {{ new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                    <input
                                        type="text"
                                        v-model="form.bad_words_embed_title"
                                        placeholder="⚠️ Unerlaubte Sprache"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Beschreibung</label>
                                    <textarea
                                        v-model="form.bad_words_embed_description"
                                        rows="3"
                                        placeholder="{user}, solche Aussagen sind auf diesem Server nicht gestattet!"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Farbe</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="form.bad_words_embed_color"
                                            class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="form.bad_words_embed_color"
                                            placeholder="#ff0000"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="form.bad_words_embed_footer"
                                        class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                    />
                                    <label class="text-sm text-gray-300">
                                        Footer anzeigen (Server-Name und Zeitstempel)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discord Invite Links -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white mb-1">Discord Invite Links blockieren</h3>
                            <p class="text-sm text-gray-400">Automatisch Discord-Einladungslinks löschen</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.block_discord_invites"
                                class="sr-only peer"
                            />
                            <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5865f2]"></div>
                        </label>
                    </div>

                    <div v-show="form.block_discord_invites" class="border-t border-[#202225] p-6 space-y-4">
                        <!-- Nachricht löschen -->
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.block_discord_invites_delete_message"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">
                                Nachricht automatisch löschen
                            </label>
                        </div>

                        <!-- Warnung Typ -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">
                                Warnnachricht Typ
                            </label>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="form.block_discord_invites_use_embed"
                                        :value="false"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">Textnachricht</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="form.block_discord_invites_use_embed"
                                        :value="true"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">Embed-Nachricht</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Textnachricht -->
                            <div v-if="!form.block_discord_invites_use_embed">
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Warnnachricht
                                </label>
                                <textarea
                                    v-model="form.block_discord_invites_warning_message"
                                    rows="2"
                                    placeholder="⚠️ {user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                ></textarea>
                                <p class="text-xs text-gray-400 mt-1">
                                    Verfügbare Platzhalter: <code class="bg-[#202225] px-1 rounded">{user}</code>, <code class="bg-[#202225] px-1 rounded">{username}</code>, <code class="bg-[#202225] px-1 rounded">{server}</code>
                                </p>
                            </div>

                            <!-- Embed-Nachricht -->
                            <div v-if="form.block_discord_invites_use_embed" class="space-y-4">
                                <!-- Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-4 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div class="flex">
                                        <div
                                            class="w-1 h-auto rounded-l-md"
                                            :style="{ backgroundColor: form.block_discord_invites_embed_color }"
                                        ></div>
                                        <div class="flex-1 bg-[#2f3136] p-3 rounded-r-md">
                                            <p class="text-sm font-semibold text-white mb-1">{{ form.block_discord_invites_embed_title.replace('{user}', 'ibeiros#0') }}</p>
                                            <p class="text-xs text-gray-300">{{ form.block_discord_invites_embed_description.replace('{user}', 'ibeiros#0') }}</p>
                                            <div v-if="form.block_discord_invites_embed_footer" class="flex items-center text-xs text-gray-400 mt-2">
                                                <img :src="guild.icon_url" alt="Server Icon" class="w-4 h-4 rounded-full mr-1" />
                                                <span>{{ guild.name }} • Heute um {{ new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                    <input
                                        type="text"
                                        v-model="form.block_discord_invites_embed_title"
                                        placeholder="⚠️ Discord-Einladungslinks nicht erlaubt"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Beschreibung</label>
                                    <textarea
                                        v-model="form.block_discord_invites_embed_description"
                                        rows="3"
                                        placeholder="{user}, Discord-Einladungslinks sind auf diesem Server nicht erlaubt!"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Farbe</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="form.block_discord_invites_embed_color"
                                            class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="form.block_discord_invites_embed_color"
                                            placeholder="#ff0000"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="form.block_discord_invites_embed_footer"
                                        class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                    />
                                    <label class="text-sm text-gray-300">
                                        Footer anzeigen (Server-Name und Zeitstempel)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Whitelist Channels -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-visible">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">Whitelist Channels</h3>
                        <p class="text-sm text-gray-400 mb-4">Kanäle, die von der Auto-Moderation ausgenommen werden</p>
                        
                        <!-- Selector -->
                        <div class="relative mb-4 z-50" data-channel-selector>
                            <button
                                type="button"
                                @click.stop="channelSelectorOpen = !channelSelectorOpen; nextTick(() => updateChannelDropdownPosition())"
                                class="w-full flex items-center justify-between px-3 py-2 bg-[#36393f] border border-[#202225] rounded text-white hover:border-[#5865f2] transition-colors"
                            >
                                <span class="text-sm text-gray-300">Channel auswählen...</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': channelSelectorOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown -->
                            <teleport to="body">
                                <transition name="dropdown">
                                    <div
                                        v-if="channelSelectorOpen"
                                        class="fixed z-[9999] bg-[#36393f] border border-[#202225] rounded-lg shadow-xl max-h-64 overflow-y-auto"
                                        :style="channelDropdownStyle"
                                        @click.stop
                                    >
                                    <div
                                        v-for="channel in channels.filter(c => c.type === 0 && !selectedChannels.includes(c.id))"
                                        :key="channel.id"
                                        @click="addChannel(channel.id)"
                                        class="px-4 py-2 hover:bg-[#2f3136] cursor-pointer transition-colors"
                                    >
                                        <span class="text-sm text-gray-300"># {{ channel.name }}</span>
                                    </div>
                                    <div v-if="channels.filter(c => c.type === 0 && !selectedChannels.includes(c.id)).length === 0" class="px-4 py-2 text-sm text-gray-400">
                                        Alle Channels bereits ausgewählt
                                    </div>
                                    </div>
                                </transition>
                            </teleport>
                        </div>
                        
                        <!-- Badges -->
                        <div v-if="selectedChannels.length > 0" class="flex flex-wrap gap-2">
                            <div
                                v-for="channelId in selectedChannels"
                                :key="channelId"
                                @mouseenter="hoveredChannel = channelId"
                                @mouseleave="hoveredChannel = null"
                                class="relative inline-flex items-center gap-2 bg-[#5865f2] text-white px-3 py-1.5 rounded-full text-sm transition-all hover:bg-[#4752c4]"
                            >
                                <span># {{ getChannelName(channelId) }}</span>
                                <button
                                    type="button"
                                    @click="removeChannel(channelId)"
                                    class="ml-1 text-white hover:text-red-300 transition-colors"
                                    :class="{ 'opacity-100': hoveredChannel === channelId, 'opacity-0': hoveredChannel !== channelId }"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400">Noch keine Channels ausgewählt</p>
                    </div>
                </div>

                <!-- Whitelist Rollen -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-visible">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">Whitelist Rollen</h3>
                        <p class="text-sm text-gray-400 mb-4">Rollen, die trotzdem posten können (z.B. Moderatoren)</p>
                        
                        <!-- Selector -->
                        <div class="relative mb-4 z-50" data-role-selector>
                            <button
                                type="button"
                                @click.stop="roleSelectorOpen = !roleSelectorOpen; nextTick(() => updateRoleDropdownPosition())"
                                class="w-full flex items-center justify-between px-3 py-2 bg-[#36393f] border border-[#202225] rounded text-white hover:border-[#5865f2] transition-colors"
                            >
                                <span class="text-sm text-gray-300">Rolle auswählen...</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': roleSelectorOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <!-- Dropdown -->
                            <teleport to="body">
                                <transition name="dropdown">
                                    <div
                                        v-if="roleSelectorOpen"
                                        class="fixed z-[9999] bg-[#36393f] border border-[#202225] rounded-lg shadow-xl max-h-64 overflow-y-auto"
                                        :style="roleDropdownStyle"
                                        @click.stop
                                    >
                                    <div
                                        v-for="role in roles.filter(r => !selectedRoles.includes(r.id))"
                                        :key="role.id"
                                        @click="addRole(role.id)"
                                        class="px-4 py-2 hover:bg-[#2f3136] cursor-pointer transition-colors"
                                    >
                                        <span class="text-sm" :style="{ color: getRoleColor(role.id) }">@{{ role.name }}</span>
                                    </div>
                                    <div v-if="roles.filter(r => !selectedRoles.includes(r.id)).length === 0" class="px-4 py-2 text-sm text-gray-400">
                                        Alle Rollen bereits ausgewählt
                                    </div>
                                    </div>
                                </transition>
                            </teleport>
                        </div>
                        
                        <!-- Badges -->
                        <div v-if="selectedRoles.length > 0" class="flex flex-wrap gap-2">
                            <div
                                v-for="roleId in selectedRoles"
                                :key="roleId"
                                @mouseenter="hoveredRole = roleId"
                                @mouseleave="hoveredRole = null"
                                class="relative inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-all hover:opacity-80"
                                :style="{ 
                                    backgroundColor: getRoleColor(roleId) + '20',
                                    color: getRoleColor(roleId),
                                    border: `1px solid ${getRoleColor(roleId)}40`
                                }"
                            >
                                <span>@{{ getRoleName(roleId) }}</span>
                                <button
                                    type="button"
                                    @click="removeRole(roleId)"
                                    class="ml-1 transition-colors"
                                    :class="{ 'opacity-100': hoveredRole === roleId, 'opacity-0': hoveredRole !== roleId }"
                                    :style="{ color: getRoleColor(roleId) }"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400">Noch keine Rollen ausgewählt</p>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50 font-medium"
                >
                    {{ form.processing ? t('common.saving') : t('common.save') }}
                </button>
            </form>
        </div>
    </GuildLayout>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
