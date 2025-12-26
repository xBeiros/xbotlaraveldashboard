<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    socialNotifications: Array,
});

const platforms = [
    { value: 'twitch', name: 'Twitch', icon: '🎮' },
    { value: 'tiktok', name: 'TikTok', icon: '🎵' },
    { value: 'x', name: 'X (Twitter)', icon: '🐦' },
    { value: 'bluesky', name: 'BlueSky', icon: '☁️' },
    { value: 'youtube', name: 'YouTube', icon: '📺' },
    { value: 'reddit', name: 'Reddit', icon: '🤖' },
    { value: 'instagram', name: 'Instagram', icon: '📷' },
    { value: 'rss', name: 'RSS', icon: '📡' },
    { value: 'kick', name: 'Kick', icon: '👊' },
    { value: 'podcast', name: 'Podcast', icon: '🎙️' },
];

const showAddForm = ref(false);
const editingId = ref(null);

const form = useForm({
    platform: '',
    channel_id: '',
    username: '',
    webhook_url: '',
    enabled: false,
    notify_live: true,
    custom_message: '',
});

function openAddForm(platform = null) {
    form.reset();
    form.platform = platform || '';
    showAddForm.value = true;
    editingId.value = null;
}

function editNotification(notification) {
    form.platform = notification.platform;
    form.channel_id = notification.channel_id;
    form.username = notification.username;
    form.webhook_url = notification.webhook_url || '';
    form.enabled = notification.enabled;
    form.notify_live = notification.notify_live;
    form.custom_message = notification.custom_message || '';
    editingId.value = notification.id;
    showAddForm.value = true;
}

function closeForm() {
    showAddForm.value = false;
    editingId.value = null;
    form.reset();
}

function saveNotification() {
    if (editingId.value) {
        form.put(route('guild.social.update', { guild: props.guild.id, id: editingId.value }), {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            }
        });
    } else {
        form.post(route('guild.social.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            }
        });
    }
}

function deleteNotification(id) {
    if (confirm(t('social.confirmDelete'))) {
        router.delete(route('guild.social.delete', { guild: props.guild.id, id }), {
            preserveScroll: true,
        });
    }
}

function getPlatformIcon(platform) {
    return platforms.find(p => p.value === platform)?.icon || '📱';
}

function getPlatformName(platform) {
    return platforms.find(p => p.value === platform)?.name || platform;
}
</script>

<template>
    <Head :title="`${guild.name} - ${t('social.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">{{ t('social.title') }}</h1>
                <button
                    @click="openAddForm()"
                    class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                    {{ t('social.addNotification') }}
                </button>
            </div>

            <div class="space-y-4">
                <!-- Hinzufügen/Bearbeiten Formular -->
                <div v-if="showAddForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">
                            {{ editingId ? t('social.editNotification') : t('social.newNotification') }}
                        </h2>
                        <button
                            @click="closeForm"
                            class="text-gray-400 hover:text-white"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveNotification" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('social.platform') }}
                            </label>
                            <select
                                v-model="form.platform"
                                required
                                :disabled="!!editingId"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">Bitte wählen...</option>
                                <option v-for="platform in platforms" :key="platform.value" :value="platform.value">
                                    {{ platform.icon }} {{ platform.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('social.discordChannel') }}
                            </label>
                            <select
                                v-model="form.channel_id"
                                required
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">Bitte wählen...</option>
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

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Username/Channel Name *
                            </label>
                            <input
                                type="text"
                                v-model="form.username"
                                required
                                placeholder="z.B. username123"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div v-if="form.platform === 'rss' || form.platform === 'podcast'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Webhook URL
                            </label>
                            <input
                                type="url"
                                v-model="form.webhook_url"
                                placeholder="https://..."
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Custom Nachricht (optional)
                            </label>
                            <textarea
                                v-model="form.custom_message"
                                rows="3"
                                placeholder="z.B. {user} ist jetzt live auf {platform}!"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">
                                Verfügbare Platzhalter: <code class="bg-[#202225] px-1 rounded">{user}</code>, <code class="bg-[#202225] px-1 rounded">{platform}</code>, <code class="bg-[#202225] px-1 rounded">{url}</code>
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.notify_live"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">Benachrichtigung wenn Live</label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="form.enabled"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">Aktivieren</label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                            >
                                {{ form.processing ? t('common.saving') : t('common.save') }}
                            </button>
                            <button
                                type="button"
                                @click="closeForm"
                                class="px-4 py-2 bg-[#36393f] hover:bg-[#2f3136] text-white rounded-lg transition-colors"
                            >
                                {{ t('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Benachrichtigungen Liste -->
                <div v-if="socialNotifications.length === 0 && !showAddForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-8">
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <h2 class="text-xl font-semibold text-white mb-2">Keine Benachrichtigungen konfiguriert</h2>
                        <p class="text-gray-400 mb-6">Erstelle eine neue Benachrichtigung, um Live-Updates von Social Media Plattformen zu erhalten.</p>
                        <button
                            @click="openAddForm()"
                            class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium"
                        >
                            + Neue Benachrichtigung
                        </button>
                    </div>
                </div>

                <!-- Plattform-Gruppierte Ansicht -->
                <div v-else class="space-y-4">
                    <div
                        v-for="platform in platforms"
                        :key="platform.value"
                        class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden"
                    >
                        <div class="p-4 border-b border-[#202225] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ platform.icon }}</span>
                                <h3 class="text-lg font-semibold text-white">{{ platform.name }}</h3>
                                <span class="text-sm text-gray-400">
                                    ({{ socialNotifications.filter(n => n.platform === platform.value).length }})
                                </span>
                            </div>
                            <button
                                @click="openAddForm(platform.value)"
                                class="px-3 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded text-sm font-medium transition-colors"
                            >
                                + Hinzufügen
                            </button>
                        </div>

                        <div v-if="socialNotifications.filter(n => n.platform === platform.value).length === 0" class="p-6 text-center text-gray-400">
                            Keine {{ platform.name }} Benachrichtigungen konfiguriert
                        </div>

                        <div v-else class="divide-y divide-[#202225]">
                            <div
                                v-for="notification in socialNotifications.filter(n => n.platform === platform.value)"
                                :key="notification.id"
                                class="p-4 hover:bg-[#36393f] transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-lg">{{ getPlatformIcon(notification.platform) }}</span>
                                            <div>
                                                <h4 class="text-white font-medium">{{ notification.username }}</h4>
                                                <p class="text-sm text-gray-400">
                                                    Kanal: 
                                                    <span class="text-white">
                                                        {{ channels.find(c => c.id === notification.channel_id)?.name || 'Unbekannt' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 text-xs text-gray-400">
                                            <span :class="{ 'text-green-400': notification.enabled, 'text-gray-500': !notification.enabled }">
                                                {{ notification.enabled ? '✓ Aktiv' : '✗ Inaktiv' }}
                                            </span>
                                            <span v-if="notification.notify_live" class="text-blue-400">
                                                🔴 Live-Benachrichtigungen
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                :checked="notification.enabled"
                                                @change="router.put(route('guild.social.update', { guild: guild.id, id: notification.id }), { enabled: !notification.enabled }, { preserveScroll: true })"
                                                class="sr-only peer"
                                            />
                                            <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#5865f2] shadow-inner"></div>
                                        </label>
                                        <button
                                            @click="editNotification(notification)"
                                            class="px-3 py-1.5 bg-[#36393f] hover:bg-[#2f3136] text-white rounded text-sm transition-colors"
                                        >
                                            Bearbeiten
                                        </button>
                                        <button
                                            @click="deleteNotification(notification.id)"
                                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors"
                                        >
                                            Löschen
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>

