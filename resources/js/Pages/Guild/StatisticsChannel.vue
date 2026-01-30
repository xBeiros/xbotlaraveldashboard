<template>
    <Head :title="`${guild.name} - Statistik-Channel`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ $t('serverManagement.statistics.title') }}</h1>

            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                <p class="text-sm text-gray-400 mb-4">{{ $t('serverManagement.statistics.description') }}</p>
                <div class="space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.statistics_enabled" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2] focus:ring-[#5865f2]">
                        <span class="text-gray-300">{{ $t('serverManagement.statistics.enabled') }}</span>
                    </label>
                    <div v-if="form.statistics_enabled" class="space-y-4 pl-0">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('serverManagement.statistics.category') }}</label>
                            <select v-model="form.statistics_category_id" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                                <option value="">{{ $t('serverManagement.statistics.noCategory') }}</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ $t('serverManagement.statistics.statsToShow') }}</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="form.statistics_stat_members" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2]"><span class="text-gray-300">👥 {{ $t('serverManagement.statistics.members') }}</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="form.statistics_stat_joins" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2]"><span class="text-gray-300">📈 {{ $t('serverManagement.statistics.joins') }}</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="form.statistics_stat_leaves" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2]"><span class="text-gray-300">📉 {{ $t('serverManagement.statistics.leaves') }}</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="form.statistics_stat_vc" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2]"><span class="text-gray-300">🔊 {{ $t('serverManagement.statistics.vcUsers') }}</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" v-model="form.statistics_stat_boosts" class="rounded bg-[#36393f] border-[#202225] text-[#5865f2]"><span class="text-gray-300">🚀 {{ $t('serverManagement.statistics.boosts') }}</span></label>
                            </div>
                        </div>
                        <div class="border-t border-[#202225] pt-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ $t('serverManagement.statistics.channelNamesPerStat') }}</label>
                            <p class="text-xs text-gray-500 mb-3">{{ $t('serverManagement.statistics.channelNamesPerStatHint') }}</p>
                            <div class="grid gap-3 sm:grid-cols-1 lg:grid-cols-2">
                                <div><label class="block text-xs text-gray-400 mb-1">👥 {{ $t('serverManagement.statistics.members') }}</label><input v-model="form.statistics_channel_name_members" type="text" maxlength="100" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" :placeholder="$t('serverManagement.statistics.channelNameMembersPlaceholder')"></div>
                                <div><label class="block text-xs text-gray-400 mb-1">📈 {{ $t('serverManagement.statistics.joins') }}</label><input v-model="form.statistics_channel_name_joins" type="text" maxlength="100" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" :placeholder="$t('serverManagement.statistics.channelNameJoinsPlaceholder')"></div>
                                <div><label class="block text-xs text-gray-400 mb-1">📉 {{ $t('serverManagement.statistics.leaves') }}</label><input v-model="form.statistics_channel_name_leaves" type="text" maxlength="100" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" :placeholder="$t('serverManagement.statistics.channelNameLeavesPlaceholder')"></div>
                                <div><label class="block text-xs text-gray-400 mb-1">🔊 {{ $t('serverManagement.statistics.vcUsers') }}</label><input v-model="form.statistics_channel_name_vc" type="text" maxlength="100" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" :placeholder="$t('serverManagement.statistics.channelNameVcPlaceholder')"></div>
                                <div><label class="block text-xs text-gray-400 mb-1">🚀 {{ $t('serverManagement.statistics.boosts') }}</label><input v-model="form.statistics_channel_name_boosts" type="text" maxlength="100" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" :placeholder="$t('serverManagement.statistics.channelNameBoostsPlaceholder')"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('serverManagement.statistics.updateInterval') }}</label>
                            <select v-model.number="form.statistics_update_interval_minutes" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                                <option :value="5">5 {{ $t('serverManagement.statistics.minutes') }}</option>
                                <option :value="10">10 {{ $t('serverManagement.statistics.minutes') }}</option>
                                <option :value="15">15 {{ $t('serverManagement.statistics.minutes') }}</option>
                                <option :value="30">30 {{ $t('serverManagement.statistics.minutes') }}</option>
                                <option :value="60">60 {{ $t('serverManagement.statistics.minutes') }}</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">{{ $t('serverManagement.statistics.intervalHint') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        @click="saveSettings"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                    >
                        {{ form.processing ? $t('common.saving') : $t('common.save') }}
                    </button>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    guild: Object,
    guilds: Array,
    categories: Array,
    statisticsConfig: Object,
});

const form = useForm({
    statistics_enabled: props.statisticsConfig?.enabled ?? false,
    statistics_channel_name: props.statisticsConfig?.channel_name ?? '📊 statistics',
    statistics_category_id: props.statisticsConfig?.category_id ?? '',
    statistics_channel_name_members: props.statisticsConfig?.channel_name_members ?? '',
    statistics_channel_name_joins: props.statisticsConfig?.channel_name_joins ?? '',
    statistics_channel_name_leaves: props.statisticsConfig?.channel_name_leaves ?? '',
    statistics_channel_name_vc: props.statisticsConfig?.channel_name_vc ?? '',
    statistics_channel_name_boosts: props.statisticsConfig?.channel_name_boosts ?? '',
    statistics_stat_members: props.statisticsConfig?.stat_members ?? true,
    statistics_stat_joins: props.statisticsConfig?.stat_joins ?? true,
    statistics_stat_leaves: props.statisticsConfig?.stat_leaves ?? true,
    statistics_stat_vc: props.statisticsConfig?.stat_vc ?? true,
    statistics_stat_boosts: props.statisticsConfig?.stat_boosts ?? true,
    statistics_update_interval_minutes: props.statisticsConfig?.update_interval_minutes ?? 10,
});

function saveSettings() {
    form.put(route('guild.statistics-channel.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}
</script>
