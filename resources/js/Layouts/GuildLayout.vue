<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: {
        type: Array,
        default: () => []
    }
});

const showingSidebar = ref(true);
const basicInfoOpen = ref(true);
const serverManagementOpen = ref(true);

function switchGuild(guildId) {
    router.visit(route('guild.config', { guild: guildId }));
}
</script>

<template>
    <div class="min-h-screen bg-[#1a1b1e] text-white flex">
        <!-- Left Sidebar -->
        <aside class="w-[300px] bg-[#1F2129] min-h-screen">
                <div class="p-5">
                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <Link :href="route('dashboard')" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">XB</span>
                        </div>
                        <span class="font-bold text-lg">XBot</span>
                    </Link>
                </div>
                <!-- Server Dropdown -->
                <div class="pb-4 border-b border-[#202225] mb-4">
                    <Dropdown align="left" width="64">
                        <template #trigger>
                            <button class="w-full flex items-center gap-3 p-2 hover:bg-[#36393f] rounded text-left">
                                <img
                                    v-if="guild?.icon_url"
                                    :src="guild.icon_url"
                                    :alt="guild.name"
                                    class="w-10 h-10 rounded-full"
                                />
                                <div
                                    v-else
                                    class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-bold"
                                >
                                    {{ guild?.name?.charAt(0).toUpperCase() || 'X' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ guild?.name || t('navigation.selectServer') }}</div>
                                </div>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="max-h-96 overflow-y-auto py-1 bg-[#1e1f22]">
                                <div
                                    v-for="g in guilds"
                                    :key="g.id"
                                    @click.stop="switchGuild(g.id)"
                                    :class="[
                                        'px-2 py-1 hover:bg-[#2b2d31] cursor-pointer flex items-center gap-2 rounded',
                                        g.id === guild?.id ? 'border border-[#5865f2] bg-[#2b2d31]' : ''
                                    ]"
                                >
                                    <img
                                        v-if="g.icon_url"
                                        :src="g.icon_url"
                                        :alt="g.name"
                                        class="w-8 h-8 rounded-full"
                                    />
                                    <div
                                        v-else
                                        class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white text-xs font-bold"
                                    >
                                        {{ g.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-medium text-white truncate">{{ g.name }}</div>
                                    </div>
                                    <svg
                                        v-if="g.id === guild?.id"
                                        class="w-3 h-3 text-[#5865f2]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </div>
                                
                                <!-- Trennlinie -->
                                <div class="border-t border-[#2b2d31] my-1"></div>
                                
                                <!-- Neuen Server Hinzufügen -->
                                <Link
                                    :href="route('dashboard')"
                                    class="px-2 py-1 hover:bg-[#2b2d31] cursor-pointer flex items-center gap-2 rounded"
                                >
                                    <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-medium text-white">{{ t('navigation.addNewServer') }}</div>
                                    </div>
                                </Link>
                            </div>
                        </template>
                    </Dropdown>
                </div>

                <!-- Navigation Menu -->
                <nav class="space-y-1">
                    <Link
                        :href="route('guild.config', { guild: guild?.id })"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded mb-1 transition-colors',
                            route().current('guild.config') ? 'bg-[#5865f2] text-white' : 'text-gray-300 hover:bg-[#36393f] hover:text-white'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>{{ t('navigation.dashboard') }}</span>
                    </Link>
                    <!-- Grundlegende Informationen Dropdown -->
                    <div>
                        <button
                            @click="basicInfoOpen = !basicInfoOpen"
                            class="w-full flex items-center justify-between px-3 py-2 rounded mb-1 transition-colors text-gray-300 hover:text-white"
                        >
                            <span class="text-xs font-semibold uppercase">{{ t('navigation.basicInfo') }}</span>
                            <svg
                                class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-180': basicInfoOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="basicInfoOpen" class="ml-4 space-y-1 mt-1">
                            <Link
                                :href="route('guild.welcome', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.welcome') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.welcome') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.reaction-roles', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.reaction-roles') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.reactionRoles') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.auto-moderation', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.auto-moderation') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.autoModeration') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.leveling', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.leveling') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.leveling') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.social', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.social') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.socialNotifications') }}</span>
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Server-Verwaltung Dropdown -->
                    <div>
                        <button
                            @click="serverManagementOpen = !serverManagementOpen"
                            class="w-full flex items-center justify-between px-3 py-2 rounded mb-1 transition-colors text-gray-300 hover:text-white"
                        >
                            <span class="text-xs font-semibold uppercase">{{ t('navigation.serverManagement') }}</span>
                            <svg
                                class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-180': serverManagementOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-show="serverManagementOpen" class="ml-4 space-y-1 mt-1">
                            <Link
                                :href="route('guild.server-management', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.server-management') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.settings') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.ticket-system', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.ticket-system') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.ticketSystem') }}</span>
                            </Link>
                            <Link
                                :href="route('guild.delete-messages', { guild: guild?.id })"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2 rounded transition-colors',
                                    route().current('guild.delete-messages') ? 'bg-[#36393f] text-white' : 'text-gray-400 hover:bg-[#36393f] hover:text-white'
                                ]"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="text-sm">{{ t('navigation.deleteMessages') }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>
                </div>
        </aside>

        <!-- Right Content Area with Top Nav -->
        <div class="flex-1 flex flex-col">
            <!-- Top Navigation Bar -->
            <nav class="bg-[#1F2129] h-20 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <!-- Platzhalter für zukünftige Inhalte -->
                </div>
                <div class="flex items-center gap-4">
                    <!-- Language Selector -->
                    <LanguageSelector />
                    <!-- User Dropdown -->
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
            <main class="flex-1 bg-[#36393f] overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
