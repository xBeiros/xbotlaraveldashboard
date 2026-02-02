<template>
    <Head :title="`${guild.name} - ${$t('factionManagement.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ $t('factionManagement.title') }}</h1>

            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <p class="text-gray-400 mb-6">{{ $t('factionManagement.description') }}</p>

            <!-- Kanäle -->
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 mb-6">
                <h2 class="text-lg font-semibold text-white mb-4">{{ $t('factionManagement.channels.title') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $t('factionManagement.channels.hint') }}</p>
                <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('factionManagement.channels.create') }}</label>
                        <select v-model="form.channel_id_create" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                            <option value="">{{ $t('factionManagement.channels.none') }}</option>
                            <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('factionManagement.channels.warn') }}</label>
                        <select v-model="form.channel_id_warn" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                            <option value="">{{ $t('factionManagement.channels.none') }}</option>
                            <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('factionManagement.channels.dissolve') }}</label>
                        <select v-model="form.channel_id_dissolve" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                            <option value="">{{ $t('factionManagement.channels.none') }}</option>
                            <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">{{ $t('factionManagement.channels.overview') }}</label>
                        <select v-model="form.channel_id_overview" class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]">
                            <option value="">{{ $t('factionManagement.channels.none') }}</option>
                            <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Embed-Texte pro Befehl -->
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 mb-6">
                <h2 class="text-lg font-semibold text-white mb-2">{{ $t('factionManagement.embeds.title') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $t('factionManagement.embeds.placeholders') }}</p>

                <div class="space-y-6">
                    <!-- Gründung / Aufnahme: {faction_name}, {announcement_text} -->
                    <div class="p-4 bg-[#36393f] rounded-lg border border-[#202225]">
                        <h3 class="text-white font-medium mb-1">{{ $t('factionManagement.embeds.create') }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $t('factionManagement.embeds.placeholdersCreateWarnDissolve') }}</p>
                        <div class="space-y-2">
                            <input v-model="form.embed_create.title" type="text" maxlength="256" :placeholder="$t('factionManagement.embeds.titlePlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" />
                            <textarea v-model="form.embed_create.description" rows="3" :placeholder="$t('factionManagement.embeds.descriptionPlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"></textarea>
                            <PaletteColorPicker v-model="form.embed_create.color" />
                        </div>
                    </div>
                    <!-- Abmahnung: {faction_name}, {announcement_text} -->
                    <div class="p-4 bg-[#36393f] rounded-lg border border-[#202225]">
                        <h3 class="text-white font-medium mb-1">{{ $t('factionManagement.embeds.warn') }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $t('factionManagement.embeds.placeholdersCreateWarnDissolve') }}</p>
                        <div class="space-y-2">
                            <input v-model="form.embed_warn.title" type="text" maxlength="256" :placeholder="$t('factionManagement.embeds.titlePlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" />
                            <textarea v-model="form.embed_warn.description" rows="3" :placeholder="$t('factionManagement.embeds.descriptionPlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"></textarea>
                            <PaletteColorPicker v-model="form.embed_warn.color" />
                        </div>
                    </div>
                    <!-- Auflösung: {faction_name}, {announcement_text} -->
                    <div class="p-4 bg-[#36393f] rounded-lg border border-[#202225]">
                        <h3 class="text-white font-medium mb-1">{{ $t('factionManagement.embeds.dissolve') }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $t('factionManagement.embeds.placeholdersCreateWarnDissolve') }}</p>
                        <div class="space-y-2">
                            <input v-model="form.embed_dissolve.title" type="text" maxlength="256" :placeholder="$t('factionManagement.embeds.titlePlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" />
                            <textarea v-model="form.embed_dissolve.description" rows="3" :placeholder="$t('factionManagement.embeds.descriptionPlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"></textarea>
                            <PaletteColorPicker v-model="form.embed_dissolve.color" />
                        </div>
                    </div>
                    <!-- Übersicht: {faction_list} -->
                    <div class="p-4 bg-[#36393f] rounded-lg border border-[#202225]">
                        <h3 class="text-white font-medium mb-1">{{ $t('factionManagement.embeds.overview') }}</h3>
                        <p class="text-xs text-gray-500 mb-3">{{ $t('factionManagement.embeds.placeholdersOverview') }}</p>
                        <div class="space-y-2">
                            <input v-model="form.embed_overview.title" type="text" maxlength="256" :placeholder="$t('factionManagement.embeds.titlePlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]" />
                            <textarea v-model="form.embed_overview.description" rows="2" :placeholder="$t('factionManagement.embeds.overviewDescriptionPlaceholder')" class="w-full rounded bg-[#202225] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"></textarea>
                            <PaletteColorPicker v-model="form.embed_overview.color" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    @click="saveConfig"
                    :disabled="form.processing"
                    class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                >
                    {{ form.processing ? $t('common.saving') : $t('common.save') }}
                </button>
            </div>

            <!-- Aktuelle Fraktionen + Warns -->
            <div class="mt-8 bg-[#2f3136] rounded-lg border border-[#202225] p-6">
                <h2 class="text-lg font-semibold text-white mb-2">{{ $t('factionManagement.factionsList.title') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $t('factionManagement.factionsList.hint') }}</p>
                <div v-if="factions.length === 0" class="text-gray-400 py-4">{{ $t('factionManagement.factionsList.empty') }}</div>
                <ul v-else class="space-y-4">
                    <li v-for="f in factions" :key="f.id" class="text-gray-300">
                        <span class="font-medium text-white">{{ f.name }}</span>
                        <span v-if="f.warn_count > 0" class="ml-2 text-amber-400">({{ f.warn_count }} {{ $t('factionManagement.warns.active') }})</span>
                        <ul v-if="f.warns && f.warns.length" class="mt-2 ml-4 space-y-1 text-sm text-gray-400">
                            <li v-for="w in f.warns" :key="w.id">
                                {{ w.reason || '—' }} · {{ $t('factionManagement.warns.expires') }} {{ formatExpires(w.expires_at) }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import PaletteColorPicker from '@/Components/PaletteColorPicker.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    factions: Array,
    factionConfig: Object,
    addOns: Object,
});

function normalizeEmbedDescription(desc) {
    if (!desc || typeof desc !== 'string') return desc;
    return desc.replace(/\n\n/g, ' ').replace(/\\n\\n/g, ' ');
}

const form = useForm({
    channel_id_create: props.factionConfig?.channel_id_create ?? '',
    channel_id_warn: props.factionConfig?.channel_id_warn ?? '',
    channel_id_dissolve: props.factionConfig?.channel_id_dissolve ?? '',
    channel_id_overview: props.factionConfig?.channel_id_overview ?? '',
    embed_create: {
        title: props.factionConfig?.embed_create?.title ?? 'Neue Fraktion',
        description: normalizeEmbedDescription(props.factionConfig?.embed_create?.description ?? '{faction_name} {announcement_text}'),
        color: props.factionConfig?.embed_create?.color ?? '5865F2',
    },
    embed_warn: {
        title: props.factionConfig?.embed_warn?.title ?? 'Fraktion Abmahnung',
        description: normalizeEmbedDescription(props.factionConfig?.embed_warn?.description ?? '{faction_name} {announcement_text}'),
        color: props.factionConfig?.embed_warn?.color ?? 'FEE75C',
    },
    embed_dissolve: {
        title: props.factionConfig?.embed_dissolve?.title ?? 'Fraktion aufgelöst',
        description: normalizeEmbedDescription(props.factionConfig?.embed_dissolve?.description ?? '{faction_name} {announcement_text}'),
        color: props.factionConfig?.embed_dissolve?.color ?? 'ED4245',
    },
    embed_overview: {
        title: props.factionConfig?.embed_overview?.title ?? 'Aktuelle Fraktionen',
        description: props.factionConfig?.embed_overview?.description ?? '{faction_list}',
        color: props.factionConfig?.embed_overview?.color ?? '57F287',
    },
});

function saveConfig() {
    form.put(route('guild.faction-management.config.update', { guild: props.guild.id }), { preserveScroll: true });
}

function formatExpires(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch (_) {
        return iso;
    }
}
</script>
