<template>
    <Head :title="`${guild.name} - ${t('giveaway.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">{{ t('giveaway.title') }}</h1>
                <button
                    @click="openCreateForm"
                    class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                    {{ t('giveaway.createGiveaway') }}
                </button>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <!-- Create/Edit Form -->
            <div v-if="showForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">{{ t('giveaway.createGiveaway') }}</h2>
                    <button
                        @click="closeForm"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveGiveaway" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.title') }}
                        </label>
                        <input
                            type="text"
                            v-model="form.title"
                            required
                            :placeholder="t('giveaway.form.titlePlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.description') }}
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            :placeholder="t('giveaway.form.descriptionPlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.channel') }}
                        </label>
                        <select
                            v-model="form.channel_id"
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

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.prizeType') }}
                        </label>
                        <select
                            v-model="form.prize_type"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="code">{{ t('giveaway.prizeTypes.code') }}</option>
                            <option value="role">{{ t('giveaway.prizeTypes.role') }}</option>
                            <option value="custom">{{ t('giveaway.prizeTypes.custom') }}</option>
                        </select>
                    </div>

                    <div v-if="form.prize_type === 'code'">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.prizeCode') }}
                        </label>
                        <input
                            type="text"
                            v-model="form.prize_code"
                            required
                            :placeholder="t('giveaway.form.prizeCodePlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <div v-if="form.prize_type === 'role'">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.prizeRole') }}
                        </label>
                        <select
                            v-model="form.prize_role_id"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                @{{ role.name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="form.prize_type === 'custom'">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.prizeCustom') }}
                        </label>
                        <input
                            type="text"
                            v-model="form.prize_custom"
                            required
                            :placeholder="t('giveaway.form.prizeCustomPlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.winnerCount') }}
                        </label>
                        <input
                            type="number"
                            v-model.number="form.winner_count"
                            required
                            min="1"
                            max="100"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.duration') }}
                        </label>
                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ t('giveaway.form.weeks') }}</label>
                                <input
                                    type="number"
                                    v-model.number="form.duration_weeks"
                                    min="0"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ t('giveaway.form.days') }}</label>
                                <input
                                    type="number"
                                    v-model.number="form.duration_days"
                                    min="0"
                                    max="6"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ t('giveaway.form.hours') }}</label>
                                <input
                                    type="number"
                                    v-model.number="form.duration_hours"
                                    min="0"
                                    max="23"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">{{ t('giveaway.form.minutes') }}</label>
                                <input
                                    type="number"
                                    v-model.number="form.duration_minutes"
                                    min="0"
                                    max="59"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.winnerMessage') }}
                        </label>
                        <textarea
                            v-model="form.winner_message"
                            rows="2"
                            :placeholder="t('giveaway.form.winnerMessagePlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('giveaway.form.ticketMessage') }}
                        </label>
                        <textarea
                            v-model="form.ticket_message"
                            rows="2"
                            :placeholder="t('giveaway.form.ticketMessagePlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        ></textarea>
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

            <!-- Giveaways List -->
            <div v-if="giveaways.length === 0 && !showForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-8">
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    <h2 class="text-xl font-semibold text-white mb-2">{{ t('giveaway.noGiveaways') }}</h2>
                    <p class="text-gray-400 mb-6">{{ t('giveaway.noGiveawaysDescription') }}</p>
                    <button
                        @click="openCreateForm"
                        class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium"
                    >
                        {{ t('giveaway.createGiveaway') }}
                    </button>
                </div>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="giveaway in giveaways"
                    :key="giveaway.id"
                    class="bg-[#2f3136] rounded-lg border border-[#202225] p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ giveaway.title }}</h3>
                            <p v-if="giveaway.description" class="text-sm text-gray-400 mt-1">{{ giveaway.description }}</p>
                        </div>
                        <button
                            v-if="!giveaway.ended"
                            @click="deleteGiveaway(giveaway.id)"
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors"
                        >
                            {{ t('giveaway.list.delete') }}
                        </button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">{{ t('giveaway.list.prize') }}:</span>
                            <span class="text-white ml-2">
                                <span v-if="giveaway.prize_type === 'code'">🎁 {{ t('giveaway.prizeTypes.code') }}</span>
                                <span v-else-if="giveaway.prize_type === 'role'">🎭 {{ t('giveaway.prizeTypes.role') }}</span>
                                <span v-else>{{ giveaway.prize_custom }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">{{ t('giveaway.list.winners') }}:</span>
                            <span class="text-white ml-2">{{ giveaway.winner_count }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">{{ t('giveaway.list.participants') }}:</span>
                            <span class="text-white ml-2">{{ giveaway.participant_count }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">{{ t('giveaway.list.endsAt') }}:</span>
                            <span class="text-white ml-2">
                                <span v-if="giveaway.ended">{{ t('giveaway.list.ended') }}</span>
                                <span v-else>{{ new Date(giveaway.ends_at).toLocaleString() }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    roles: Array,
    giveaways: Array,
});

const showForm = ref(false);

const form = useForm({
    title: '',
    description: '',
    channel_id: '',
    prize_type: 'custom',
    prize_code: '',
    prize_role_id: '',
    prize_custom: '',
    winner_count: 1,
    duration_weeks: 0,
    duration_days: 0,
    duration_hours: 1,
    duration_minutes: 0,
    winner_message: '',
    ticket_message: '',
});

function openCreateForm() {
    form.reset();
    form.prize_type = 'custom';
    form.winner_count = 1;
    form.duration_weeks = 0;
    form.duration_days = 0;
    form.duration_hours = 1;
    form.duration_minutes = 0;
    showForm.value = true;
}

function closeForm() {
    showForm.value = false;
    form.reset();
}

function saveGiveaway() {
    form.post(route('guild.giveaway.store', { guild: props.guild.id }), {
        preserveScroll: true,
        onSuccess: () => {
            closeForm();
        },
    });
}

function deleteGiveaway(id) {
    if (confirm(t('giveaway.list.deleteConfirm'))) {
        router.delete(route('guild.giveaway.delete', { guild: props.guild.id, id }), {
            preserveScroll: true,
        });
    }
}
</script>

