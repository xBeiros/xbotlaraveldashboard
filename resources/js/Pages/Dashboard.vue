<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import MembersWidget from '@/Components/Widgets/MembersWidget.vue';
import TicketsWidget from '@/Components/Widgets/TicketsWidget.vue';
import GiveawaysWidget from '@/Components/Widgets/GiveawaysWidget.vue';
import LevelingWidget from '@/Components/Widgets/LevelingWidget.vue';

const { t } = useI18n();

const props = defineProps({
    guilds: Array,
    botClientId: String,
    widgets: {
        type: Array,
        default: () => [],
    },
});

const selectedGuild = ref(null);
const refreshing = ref(false);
const showAddWidgetModal = ref(false);
const selectedWidgetType = ref(null);
const selectedWidgetGuild = ref(null);

const availableWidgetTypes = computed(() => [
    { value: 'members', label: t('widgets.types.members') },
    { value: 'tickets', label: t('widgets.types.tickets') },
    { value: 'giveaways', label: t('widgets.types.giveaways') },
    { value: 'leveling', label: t('widgets.types.leveling') },
]);

const getWidgetComponent = (type) => {
    switch (type) {
        case 'members':
            return MembersWidget;
        case 'tickets':
            return TicketsWidget;
        case 'giveaways':
            return GiveawaysWidget;
        case 'leveling':
            return LevelingWidget;
        default:
            return null;
    }
};

const addWidget = async () => {
    if (!selectedWidgetType.value) {
        return;
    }
    
    try {
        await router.post(route('dashboard.widgets.store'), {
            widget_type: selectedWidgetType.value,
            guild_id: selectedWidgetGuild.value || null,
            position: props.widgets.length,
            column: 1,
            row: 1,
        }, {
            preserveState: false,
            preserveScroll: false,
        });
        
        showAddWidgetModal.value = false;
        selectedWidgetType.value = null;
        selectedWidgetGuild.value = null;
    } catch (e) {
        console.error('Error adding widget:', e);
    }
};

const removeWidget = (widgetId) => {
    // Widget wird bereits durch WidgetContainer entfernt
    router.reload({ only: ['widgets'] });
};

function inviteBot(guildId) {
    // Öffne Bot-Einladung in neuem Popup-Fenster
    if (!guildId) {
        console.error('Guild ID fehlt');
        alert(t('dashboard.error.guildIdMissing'));
        return;
    }
    
    const clientId = props.botClientId;
    if (!clientId) {
        console.error('Bot Client ID fehlt');
        alert(t('dashboard.error.botClientIdMissing'));
        return;
    }
    
    // Versuche zuerst die Route zu verwenden
    let inviteUrl;
    try {
        inviteUrl = route('bot.invite', { guild_id: guildId });
    } catch (error) {
        console.warn('Route-Funktion nicht verfügbar, verwende direkte URL:', error);
        // Fallback: Direkte URL erstellen
        const permissionsValue = '8'; // Administrator
        inviteUrl = `/bot/invite?guild_id=${guildId}`;
    }
    
    // Öffne die Einladungs-URL in neuem Popup-Fenster (ähnlich wie Discord OAuth)
    console.log('Öffne Bot-Einladung für Guild:', guildId, 'in neuem Popup-Fenster');
    const width = 600;
    const height = 700;
    const left = (window.screen.width / 2) - (width / 2);
    const top = (window.screen.height / 2) - (height / 2);
    
    window.open(
        inviteUrl,
        'DiscordBotInvite',
        `width=${width},height=${height},left=${left},top=${top},toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes`
    );
}

function selectGuild(guild) {
    // Zur Konfigurationsseite für diesen Server
    router.visit(route('guild.config', { guild: guild.id }));
}

function refreshGuilds() {
    refreshing.value = true;
    // Force Refresh: Füge ?refresh=1 Parameter hinzu für sofortige Synchronisation
    router.visit(route('dashboard', { refresh: 1 }), {
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            refreshing.value = false;
        }
    });
}
</script>

<template>
    <Head :title="t('dashboard.title')" />

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
                    {{ refreshing ? t('common.refreshing') : t('common.refresh') }}
                </button>
                <LanguageSelector />
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
                            {{ t('navigation.logout') }}
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-[120rem] mx-auto px-6 py-12">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold">{{ t('dashboard.title') }}</h1>
                <button
                    @click="showAddWidgetModal = true"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('widgets.addWidget') }}
                </button>
            </div>
            
            <!-- Widget Grid -->
            <div v-if="widgets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-12">
                <component
                    v-for="widget in widgets"
                    :key="widget.id"
                    :is="getWidgetComponent(widget.type)"
                    :widget="widget"
                    :guild-id="widget.guild_id"
                    @remove="removeWidget"
                />
            </div>
            
            <div v-else class="text-center py-12 mb-12 bg-[#2f3136] rounded-lg border border-[#202225]">
                <p class="text-gray-400 mb-4">{{ t('widgets.noWidgets') }}</p>
                <button
                    @click="showAddWidgetModal = true"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                >
                    {{ t('widgets.addFirstWidget') }}
                </button>
            </div>
            
            <!-- Server Liste -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-6">{{ t('dashboard.servers') }}</h2>
                
                <div v-if="guilds.length === 0" class="text-center py-12">
                    <p class="text-gray-400">{{ t('dashboard.noServers') }}</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                    v-for="guild in guilds"
                    :key="guild.id"
                    class="rounded-lg overflow-hidden transition-colors flex flex-col"
                    style="width: 282px;"
                >
                    <!-- Background mit verschwommenem Profilbild - nur für Icon/Name Bereich -->
                    <div class="relative overflow-hidden rounded-lg" style="height: 160px;">
                        <div
                            v-if="guild.icon_url"
                            class="absolute inset-0 bg-cover bg-center"
                            :style="{
                                backgroundImage: `url(${guild.icon_url})`,
                                filter: 'blur(20px) brightness(0.4)',
                                transform: 'scale(1.1)'
                            }"
                        ></div>
                        <div
                            v-else
                            class="absolute inset-0 bg-gradient-to-r from-[#5865f2] to-[#4752c4] opacity-60"
                        ></div>

                        <!-- Content Container innerhalb des verschwommenen Bereichs -->
                        <div class="relative z-10 flex flex-col h-full">
                            <!-- Icon Bereich oben -->
                            <div class="flex-1 flex items-center justify-center pt-6 pb-2">
                                <img
                                    v-if="guild.icon_url"
                                    :src="guild.icon_url"
                                    :alt="guild.name"
                                    class="w-16 h-16 rounded-full border-4 border-[#2f3136] shadow-lg"
                                />
                                <div
                                    v-else
                                    class="w-16 h-16 rounded-full bg-[#5865f2] flex items-center justify-center text-white text-2xl font-bold border-4 border-[#2f3136] shadow-lg"
                                >
                                    {{ guild.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>

                            <!-- Server Informationen unter Icon -->
                            <div class="text-center px-4 pb-4">
                                <h3 class="text-base font-semibold text-white truncate mb-1">
                                    {{ guild.name }}
                                </h3>
                                <p class="text-xs text-gray-400">
                                    {{ guild.owner ? t('dashboard.owner') : t('dashboard.botMaster') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Wrapper für Button-Bereich -->
                    <div>
                        <!-- Button außerhalb des verschwommenen Bereichs -->
                        <div class="py-3">
                            <button
                                v-if="!guild.bot_joined && guild.can_manage"
                                @click="inviteBot(guild.id)"
                                class="w-full bg-[#5865f2] hover:bg-[#4752c4] text-white px-1 py-2 rounded transition-colors font-medium text-sm"
                            >
                                {{ t('dashboard.botInvite') }}
                            </button>
                            <button
                                v-else-if="guild.bot_joined && guild.can_manage"
                                @click="selectGuild(guild)"
                                class="w-full bg-[#5865f2] hover:bg-[#4752c4] text-white px-1 py-2 rounded transition-colors font-medium text-sm"
                            >
                                {{ t('dashboard.continue') }}
                            </button>
                            <div
                                v-else
                                class="w-full bg-gray-600 text-gray-400 px-1 py-2 rounded text-center cursor-not-allowed font-medium text-sm"
                            >
                                {{ t('dashboard.noPermission') }}
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </main>
        
        <!-- Add Widget Modal -->
        <div
            v-if="showAddWidgetModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showAddWidgetModal = false"
        >
            <div class="bg-[#2f3136] rounded-lg p-6 w-full max-w-md border border-[#202225]">
                <h3 class="text-xl font-bold mb-4">{{ t('widgets.addWidget') }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('widgets.selectType') }}
                        </label>
                        <select
                            v-model="selectedWidgetType"
                            class="w-full bg-[#36393f] border border-[#202225] rounded px-3 py-2 text-white"
                        >
                            <option value="">{{ t('widgets.selectTypePlaceholder') }}</option>
                            <option
                                v-for="type in availableWidgetTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="selectedWidgetType && guilds.length > 0">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('widgets.selectGuild') }} ({{ t('common.optional') }})
                        </label>
                        <select
                            v-model="selectedWidgetGuild"
                            class="w-full bg-[#36393f] border border-[#202225] rounded px-3 py-2 text-white"
                        >
                            <option value="">{{ t('widgets.allServers') }}</option>
                            <option
                                v-for="guild in guilds"
                                :key="guild.id"
                                :value="guild.id"
                            >
                                {{ guild.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="flex gap-3 justify-end mt-6">
                        <button
                            @click="showAddWidgetModal = false"
                            class="px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            @click="addWidget"
                            :disabled="!selectedWidgetType"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50"
                        >
                            {{ t('common.add') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

