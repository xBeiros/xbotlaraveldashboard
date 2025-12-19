<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    guilds: Array,
    botClientId: String,
});

const selectedGuild = ref(null);
const refreshing = ref(false);

function inviteBot(guildId) {
    // Direkter Redirect zu Discord Bot-Einladung
    window.location.href = route('bot.invite', { guild_id: guildId });
}

function selectGuild(guild) {
    // Zur Konfigurationsseite für diesen Server
    router.visit(route('guild.config', { guild: guild.id }));
}

function refreshGuilds() {
    refreshing.value = true;
    router.reload({
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            refreshing.value = false;
        }
    });
}
</script>

<template>
    <Head title="Server auswählen" />

    <div class="min-h-screen bg-[#1a1b1e] text-white">
        <!-- Top Navigation Bar -->
        <nav class="bg-[#2f3136] border-b border-[#202225] h-14 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">XB</span>
                    </div>
                    <span class="font-bold text-lg">XBot</span>
                </Link>
            </div>
            
            <div class="flex items-center gap-4">
                <button
                    @click="refreshGuilds"
                    :disabled="refreshing"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50 text-sm"
                >
                    {{ refreshing ? 'Aktualisiere...' : 'Aktualisieren' }}
                </button>
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button class="flex items-center gap-2 hover:bg-[#36393f] px-3 py-2 rounded">
                            <img
                                v-if="$page.props.auth.user.avatar"
                                :src="$page.props.auth.user.avatar"
                                :alt="$page.props.auth.user.name"
                                class="w-6 h-6 rounded-full"
                            />
                            <div
                                v-else
                                class="w-6 h-6 rounded-full bg-gray-600 flex items-center justify-center text-white text-xs"
                            >
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-sm">{{ $page.props.auth.user.name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Abmelden
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-6 py-12">
            <h1 class="text-3xl font-bold mb-8 text-center">Server auswählen</h1>
            
            <div v-if="guilds.length === 0" class="text-center py-12">
                <p class="text-gray-400">Du hast noch keine Server. Bitte logge dich mit Discord ein, um deine Server zu sehen.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="guild in guilds"
                    :key="guild.id"
                    class="bg-[#2f3136] rounded-lg p-6 hover:bg-[#36393f] transition-colors border border-[#202225]"
                >
                    <div class="flex items-center gap-4 mb-4">
                        <img
                            v-if="guild.icon_url"
                            :src="guild.icon_url"
                            :alt="guild.name"
                            class="w-16 h-16 rounded-full"
                        />
                        <div
                            v-else
                            class="w-16 h-16 rounded-full bg-gray-600 flex items-center justify-center text-white text-2xl font-bold"
                        >
                            {{ guild.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-white truncate">
                                {{ guild.name }}
                            </h3>
                            <p class="text-sm text-gray-400">
                                {{ guild.owner ? 'Eigentümer' : 'Bot Master' }}
                            </p>
                        </div>
                    </div>

                    <button
                        v-if="!guild.bot_joined && guild.can_manage"
                        @click="inviteBot(guild.id)"
                        class="w-full bg-[#5865f2] hover:bg-[#4752c4] text-white px-4 py-2 rounded transition-colors font-medium"
                    >
                        Bot einladen
                    </button>
                    <button
                        v-else-if="guild.bot_joined && guild.can_manage"
                        @click="selectGuild(guild)"
                        class="w-full bg-[#5865f2] hover:bg-[#4752c4] text-white px-4 py-2 rounded transition-colors font-medium"
                    >
                        Einrichtung
                    </button>
                    <div
                        v-else
                        class="w-full bg-gray-600 text-gray-400 px-4 py-2 rounded text-center cursor-not-allowed font-medium"
                    >
                        Keine Berechtigung
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
