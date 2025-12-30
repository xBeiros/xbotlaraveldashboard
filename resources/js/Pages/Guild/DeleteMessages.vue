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
            
            <!-- Automatische Löschungen -->
            <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225] mt-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-white mb-2">{{ $t('deleteMessages.autoDelete.title') }}</h2>
                        <p class="text-sm text-gray-400">{{ $t('deleteMessages.autoDelete.description') }}</p>
                    </div>
                    <button
                        @click="openAutoDeleteModal()"
                        class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $t('deleteMessages.autoDelete.addAutoDelete') }}
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div
                        v-for="autoDelete in autoDeletes"
                        :key="autoDelete.id"
                        class="bg-[#36393f] rounded-lg p-4 border border-[#202225] hover:border-[#5865f2] transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-medium text-white">
                                        # {{ getChannelName(autoDelete.channel_id) }}
                                    </span>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-xs font-medium',
                                            autoDelete.enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'
                                        ]"
                                    >
                                        {{ autoDelete.enabled ? $t('common.active') : $t('common.inactive') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ $t('deleteMessages.autoDelete.interval') }}: 
                                    <span class="text-gray-300">{{ formatInterval(autoDelete.interval_minutes) }}</span>
                                </div>
                                <div v-if="autoDelete.last_run_at" class="text-xs text-gray-500 mt-1">
                                    {{ $t('deleteMessages.autoDelete.lastRun') }}: {{ formatDate(autoDelete.last_run_at) }}
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="toggleAutoDelete(autoDelete.id, !autoDelete.enabled)"
                                    :class="[
                                        'px-3 py-1.5 rounded text-sm font-medium transition-colors',
                                        autoDelete.enabled 
                                            ? 'bg-yellow-600 hover:bg-yellow-700 text-white' 
                                            : 'bg-green-600 hover:bg-green-700 text-white'
                                    ]"
                                >
                                    {{ autoDelete.enabled ? $t('common.disable') : $t('common.enable') }}
                                </button>
                                <button
                                    @click="openAutoDeleteModal(autoDelete)"
                                    class="px-3 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded text-sm font-medium transition-colors"
                                >
                                    {{ $t('common.edit') }}
                                </button>
                                <button
                                    @click="deleteAutoDelete(autoDelete.id)"
                                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors"
                                >
                                    {{ $t('common.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="autoDeletes.length === 0" class="text-center py-8 text-gray-400">
                        <p>{{ $t('deleteMessages.autoDelete.noAutoDeletes') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Auto-Delete Modal -->
        <div
            v-if="showAutoDeleteModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeAutoDeleteModal"
        >
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] w-full max-w-md">
                <div class="p-6 border-b border-[#202225]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">
                            {{ editingAutoDelete ? $t('deleteMessages.autoDelete.editAutoDelete') : $t('deleteMessages.autoDelete.createAutoDelete') }}
                        </h2>
                        <button
                            @click="closeAutoDeleteModal"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="saveAutoDelete" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $t('deleteMessages.channelLabel') }} *
                        </label>
                        <select
                            v-model="autoDeleteForm.channel_id"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ $t('common.pleaseSelect') }}</option>
                            <template v-for="channel in textChannels" :key="channel.id">
                                <option :value="channel.id">{{ channel.name }}</option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ $t('deleteMessages.autoDelete.interval') }} *
                        </label>
                        <select
                            v-model.number="autoDeleteForm.interval_minutes"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option :value="5">5 {{ $t('deleteMessages.timeMinutes') }}</option>
                            <option :value="10">10 {{ $t('deleteMessages.timeMinutes') }}</option>
                            <option :value="30">30 {{ $t('deleteMessages.timeMinutes') }}</option>
                            <option :value="60">1 {{ $t('deleteMessages.timeHours') }}</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-2">{{ $t('deleteMessages.autoDelete.intervalHelp') }}</p>
                    </div>

                    <div class="p-3 bg-blue-500/20 border border-blue-500 rounded-lg">
                        <p class="text-sm text-blue-400">
                            {{ $t('deleteMessages.autoDelete.deleteAllInfo') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            v-model="autoDeleteForm.enabled"
                            class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                        />
                        <label class="text-sm text-gray-300">{{ $t('common.active') }}</label>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-[#202225] gap-3">
                        <button
                            type="button"
                            @click="closeAutoDeleteModal"
                            class="px-6 py-2.5 bg-[#36393f] hover:bg-[#40444b] text-white rounded-lg transition-colors font-medium"
                        >
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="autoDeleteForm.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                        >
                            {{ autoDeleteForm.processing ? $t('common.saving') : $t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    autoDeletes: {
        type: Array,
        default: () => []
    },
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

// Auto-Delete Management
const showAutoDeleteModal = ref(false);
const editingAutoDelete = ref(null);
const autoDeleteForm = useForm({
    channel_id: '',
    interval_minutes: 5,
    enabled: true,
});

function openAutoDeleteModal(autoDelete = null) {
    editingAutoDelete.value = autoDelete;
    if (autoDelete) {
        autoDeleteForm.channel_id = autoDelete.channel_id;
        autoDeleteForm.interval_minutes = autoDelete.interval_minutes;
        autoDeleteForm.enabled = autoDelete.enabled;
    } else {
        autoDeleteForm.reset();
        autoDeleteForm.interval_minutes = 5;
        autoDeleteForm.enabled = true;
    }
    showAutoDeleteModal.value = true;
}

function closeAutoDeleteModal() {
    showAutoDeleteModal.value = false;
    editingAutoDelete.value = null;
    autoDeleteForm.reset();
}

function saveAutoDelete() {
    if (editingAutoDelete.value) {
        autoDeleteForm.put(route('guild.auto-delete-messages.update', {
            guild: props.guild.id,
            id: editingAutoDelete.value.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                closeAutoDeleteModal();
            },
        });
    } else {
        autoDeleteForm.post(route('guild.auto-delete-messages.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeAutoDeleteModal();
            },
        });
    }
}

function toggleAutoDelete(id, enabled) {
    router.put(route('guild.auto-delete-messages.toggle', {
        guild: props.guild.id,
        id: id
    }), {
        enabled: enabled
    }, {
        preserveScroll: true,
    });
}

function deleteAutoDelete(id) {
    if (!confirm(t('deleteMessages.autoDelete.confirmDelete'))) return;
    
    router.delete(route('guild.auto-delete-messages.delete', {
        guild: props.guild.id,
        id: id
    }), {
        preserveScroll: true,
    });
}

function getChannelName(channelId) {
    const channel = props.channels?.find(c => c.id === channelId);
    return channel?.name || channelId;
}

function formatInterval(minutes) {
    if (minutes < 60) {
        return `${minutes} ${t('deleteMessages.timeMinutes')}`;
    }
    const hours = Math.floor(minutes / 60);
    return `${hours} ${t('deleteMessages.timeHours')}`;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

