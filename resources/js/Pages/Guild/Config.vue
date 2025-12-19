<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    guild: Object,
    guilds: Array,
});
</script>

<template>
    <Head :title="`${guild.name} - Dashboard`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">Dashboard</h1>
            
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-8">
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-white mb-2">Willkommen im Dashboard</h2>
                    <p class="text-gray-400">Hier werden später Statistiken und Informationen angezeigt.</p>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>
