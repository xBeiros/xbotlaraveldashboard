<template>
    <Head :title="`${guild.name} - ${$t('deleteMessages.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ $t('deleteMessages.title') }}</h1>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                <p class="text-sm text-gray-400 mb-6">{{ $t('deleteMessages.description') }}</p>
                
                <div class="space-y-4">
                    <!-- Channel Auswahl -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $t('deleteMessages.channelLabel') }}
                        </label>
                        <select
                            v-model="deleteForm.channel_id"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ $t('common.pleaseSelect') }}</option>
                            <template v-for="channel in textChannels" :key="channel.id">
                                <option :value="channel.id">{{ channel.name }}</option>
                            </template>
                        </select>
                    </div>

                    <!-- Lösch-Modus -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $t('deleteMessages.deleteMode') }}
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="deleteForm.mode"
                                    value="count"
                                    class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] focus:ring-[#5865f2]"
                                />
                                <span class="text-gray-300">{{ $t('deleteMessages.modeCount') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="deleteForm.mode"
                                    value="time"
                                    class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] focus:ring-[#5865f2]"
                                />
                                <span class="text-gray-300">{{ $t('deleteMessages.modeTime') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Nach Anzahl -->
                    <div v-if="deleteForm.mode === 'count'">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $t('deleteMessages.countLabel') }}
                        </label>
                        <input
                            type="number"
                            v-model.number="deleteForm.count"
                            min="1"
                            max="100"
                            :placeholder="$t('deleteMessages.countPlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                        <p class="text-xs text-gray-400 mt-2">{{ $t('deleteMessages.countHelp') }}</p>
                    </div>

                    <!-- Nach Zeitraum -->
                    <div v-if="deleteForm.mode === 'time'" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('deleteMessages.timeLabel') }}
                            </label>
                            <div class="flex gap-4">
                                <div class="flex-1">
                                    <input
                                        type="number"
                                        v-model.number="deleteForm.timeHours"
                                        min="0"
                                        :placeholder="$t('deleteMessages.timeHours')"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                                <div class="flex-1">
                                    <input
                                        type="number"
                                        v-model.number="deleteForm.timeMinutes"
                                        min="0"
                                        max="59"
                                        :placeholder="$t('deleteMessages.timeMinutes')"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">{{ $t('deleteMessages.timeHelp') }}</p>
                        </div>
                    </div>

                    <!-- Benachrichtigung -->
                    <div class="space-y-4 pt-4 border-t border-[#202225]">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="deleteForm.sendNotification"
                                class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                            />
                            <span class="text-gray-300">{{ $t('deleteMessages.sendNotification') }}</span>
                        </label>
                        <p class="text-xs text-gray-400">{{ $t('deleteMessages.sendNotificationHelp') }}</p>

                        <div v-if="deleteForm.sendNotification" class="space-y-4 pl-6 border-l-2 border-[#202225]">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ $t('deleteMessages.notificationTitle') }}
                                </label>
                                <input
                                    type="text"
                                    v-model="deleteForm.notificationTitle"
                                    :placeholder="$t('deleteMessages.notificationTitlePlaceholder')"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ $t('deleteMessages.notificationDescription') }}
                                </label>
                                <textarea
                                    v-model="deleteForm.notificationDescription"
                                    :placeholder="$t('deleteMessages.notificationDescriptionPlaceholder')"
                                    rows="3"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2] resize-none"
                                ></textarea>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ $t('deleteMessages.availablePlaceholders') }}
                                    <span class="text-[#5865f2]">{{ $t('deleteMessages.placeholderCount') }}, {{ $t('deleteMessages.placeholderChannel') }}, {{ $t('deleteMessages.placeholderUser') }}</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ $t('deleteMessages.notificationColor') }}
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        v-model="deleteForm.notificationColor"
                                        class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                    />
                                    <input
                                        type="text"
                                        v-model="deleteForm.notificationColor"
                                        placeholder="#5865f2"
                                        class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="deleteForm.showFooter"
                                    class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                                />
                                <span class="text-gray-300">{{ $t('deleteMessages.showFooter') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        @click="deleteMessages"
                        :disabled="deleteForm.processing || !canDelete"
                        class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        {{ deleteForm.processing ? $t('deleteMessages.deleting') : $t('deleteMessages.deleteButton') }}
                    </button>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
});

const deleteForm = useForm({
    channel_id: null,
    mode: 'count',
    count: 10,
    timeHours: 0,
    timeMinutes: 0,
    sendNotification: false,
    notificationTitle: '',
    notificationDescription: '',
    notificationColor: '#5865f2',
    showFooter: true,
});

// Filtere nur Text-Kanäle (keine Kategorien)
const textChannels = computed(() => {
    return props.channels?.filter(channel => !channel.is_category && channel.type === 0) || [];
});

const canDelete = computed(() => {
    if (!deleteForm.channel_id) return false;
    if (deleteForm.mode === 'count') {
        return deleteForm.count > 0 && deleteForm.count <= 100;
    } else if (deleteForm.mode === 'time') {
        return (deleteForm.timeHours > 0 || deleteForm.timeMinutes > 0);
    }
    return false;
});

function deleteMessages() {
    if (!canDelete.value) return;

    let confirmMessage = '';
    if (deleteForm.mode === 'count') {
        confirmMessage = t('deleteMessages.confirmDelete', { count: deleteForm.count });
    } else {
        const timeStr = [];
        if (deleteForm.timeHours > 0) {
            timeStr.push(`${deleteForm.timeHours} ${t('deleteMessages.timeHours')}`);
        }
        if (deleteForm.timeMinutes > 0) {
            timeStr.push(`${deleteForm.timeMinutes} ${t('deleteMessages.timeMinutes')}`);
        }
        confirmMessage = t('deleteMessages.confirmDeleteTime', { time: timeStr.join(' ') });
    }

    if (!confirm(confirmMessage)) return;

    deleteForm.post(route('guild.delete-messages.post', { guild: props.guild.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset form
            deleteForm.reset();
            deleteForm.mode = 'count';
            deleteForm.count = 10;
            deleteForm.sendNotification = false;
            deleteForm.notificationColor = '#5865f2';
            deleteForm.showFooter = true;
        },
    });
}
</script>

