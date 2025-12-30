<template>
    <Head :title="`${guild.name} - Einstellungen`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ $t('serverManagement.title') }}</h1>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <div class="space-y-6">
                <!-- Serversprache -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <h2 class="text-lg font-semibold text-white mb-4">{{ $t('serverManagement.serverLanguage') }}</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.selectLanguage') }}
                            </label>
                            <select
                                v-model="form.language"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="de">{{ $t('languages.german') }}</option>
                                <option value="en">{{ $t('languages.english') }}</option>
                                <option value="tr">{{ $t('languages.turkish') }}</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-2">{{ $t('serverManagement.languageDescription') }}</p>
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
                
                <!-- Zeitzone -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <h2 class="text-lg font-semibold text-white mb-4">{{ $t('serverManagement.timezone') }}</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.selectTimezone') }}
                            </label>
                            <select
                                v-model="form.timezone"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                    {{ tz.flag }} {{ tz.label }} [{{ tz.country }}]
                                </option>
                            </select>
                            <p class="text-xs text-gray-400 mt-2">{{ $t('serverManagement.timezoneDescription') }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $t('serverManagement.currentTime') }}: 
                                <span class="text-gray-400">{{ currentTime }}</span>
                            </p>
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
        </div>
    </GuildLayout>
</template>

<style scoped>
/* Verbesserte Flaggen-Emoji Unterstützung für Windows Chrome */
select {
    font-family: "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", "EmojiOne Color", "Android Emoji", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Fallback: Wenn Flaggen-Emojis nicht funktionieren, wird der Text in eckigen Klammern angezeigt */
select option {
    font-family: "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", "EmojiOne Color", "Android Emoji", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
</style>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    guildModel: Object,
});

const form = useForm({
    language: props.guildModel?.language || 'de',
    timezone: props.guildModel?.timezone || 'Europe/Berlin',
});

// Zeitzonen-Liste mit Flaggen-Emojis und Text-Fallback
const timezones = [
    { value: 'Europe/Berlin', flag: '🇩🇪', country: 'Deutschland', label: 'Europe/Berlin (CET/CEST)' },
    { value: 'Europe/London', flag: '🇬🇧', country: 'Großbritannien', label: 'Europe/London (GMT/BST)' },
    { value: 'America/New_York', flag: '🇺🇸', country: 'USA (Ostküste)', label: 'America/New_York (EST/EDT)' },
    { value: 'America/Los_Angeles', flag: '🇺🇸', country: 'USA (Westküste)', label: 'America/Los_Angeles (PST/PDT)' },
    { value: 'America/Chicago', flag: '🇺🇸', country: 'USA (Mitte)', label: 'America/Chicago (CST/CDT)' },
    { value: 'America/Denver', flag: '🇺🇸', country: 'USA (Rocky Mountains)', label: 'America/Denver (MST/MDT)' },
    { value: 'Europe/Paris', flag: '🇫🇷', country: 'Frankreich', label: 'Europe/Paris (CET/CEST)' },
    { value: 'Europe/Rome', flag: '🇮🇹', country: 'Italien', label: 'Europe/Rome (CET/CEST)' },
    { value: 'Europe/Madrid', flag: '🇪🇸', country: 'Spanien', label: 'Europe/Madrid (CET/CEST)' },
    { value: 'Europe/Amsterdam', flag: '🇳🇱', country: 'Niederlande', label: 'Europe/Amsterdam (CET/CEST)' },
    { value: 'Europe/Vienna', flag: '🇦🇹', country: 'Österreich', label: 'Europe/Vienna (CET/CEST)' },
    { value: 'Europe/Zurich', flag: '🇨🇭', country: 'Schweiz', label: 'Europe/Zurich (CET/CEST)' },
    { value: 'Europe/Stockholm', flag: '🇸🇪', country: 'Schweden', label: 'Europe/Stockholm (CET/CEST)' },
    { value: 'Europe/Oslo', flag: '🇳🇴', country: 'Norwegen', label: 'Europe/Oslo (CET/CEST)' },
    { value: 'Europe/Copenhagen', flag: '🇩🇰', country: 'Dänemark', label: 'Europe/Copenhagen (CET/CEST)' },
    { value: 'Europe/Helsinki', flag: '🇫🇮', country: 'Finnland', label: 'Europe/Helsinki (EET/EEST)' },
    { value: 'Europe/Warsaw', flag: '🇵🇱', country: 'Polen', label: 'Europe/Warsaw (CET/CEST)' },
    { value: 'Europe/Prague', flag: '🇨🇿', country: 'Tschechien', label: 'Europe/Prague (CET/CEST)' },
    { value: 'Europe/Budapest', flag: '🇭🇺', country: 'Ungarn', label: 'Europe/Budapest (CET/CEST)' },
    { value: 'Europe/Athens', flag: '🇬🇷', country: 'Griechenland', label: 'Europe/Athens (EET/EEST)' },
    { value: 'Europe/Istanbul', flag: '🇹🇷', country: 'Türkei', label: 'Europe/Istanbul (TRT)' },
    { value: 'Asia/Tokyo', flag: '🇯🇵', country: 'Japan', label: 'Asia/Tokyo (JST)' },
    { value: 'Asia/Shanghai', flag: '🇨🇳', country: 'China', label: 'Asia/Shanghai (CST)' },
    { value: 'Asia/Hong_Kong', flag: '🇭🇰', country: 'Hong Kong', label: 'Asia/Hong_Kong (HKT)' },
    { value: 'Asia/Singapore', flag: '🇸🇬', country: 'Singapur', label: 'Asia/Singapore (SGT)' },
    { value: 'Asia/Dubai', flag: '🇦🇪', country: 'VAE', label: 'Asia/Dubai (GST)' },
    { value: 'Australia/Sydney', flag: '🇦🇺', country: 'Australien (Ost)', label: 'Australia/Sydney (AEDT/AEST)' },
    { value: 'Australia/Melbourne', flag: '🇦🇺', country: 'Australien (Südost)', label: 'Australia/Melbourne (AEDT/AEST)' },
    { value: 'Pacific/Auckland', flag: '🇳🇿', country: 'Neuseeland', label: 'Pacific/Auckland (NZDT/NZST)' },
    { value: 'America/Sao_Paulo', flag: '🇧🇷', country: 'Brasilien', label: 'America/Sao_Paulo (BRT/BRST)' },
    { value: 'America/Mexico_City', flag: '🇲🇽', country: 'Mexiko', label: 'America/Mexico_City (CST/CDT)' },
    { value: 'America/Toronto', flag: '🇨🇦', country: 'Kanada (Ost)', label: 'America/Toronto (EST/EDT)' },
    { value: 'America/Vancouver', flag: '🇨🇦', country: 'Kanada (West)', label: 'America/Vancouver (PST/PDT)' },
];

// Aktuelle Zeit in ausgewählter Zeitzone
const currentTime = ref('');
let timeInterval = null;

function updateCurrentTime() {
    try {
        const now = new Date();
        const formatter = new Intl.DateTimeFormat('de-DE', {
            timeZone: form.timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
        currentTime.value = formatter.format(now);
    } catch (error) {
        currentTime.value = 'Fehler';
    }
}

onMounted(() => {
    updateCurrentTime();
    timeInterval = setInterval(updateCurrentTime, 1000);
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
});

// Aktualisiere die Zeit, wenn sich die Zeitzone ändert
watch(() => form.timezone, () => {
    updateCurrentTime();
});

function saveSettings() {
    form.put(route('guild.server-management.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}
</script>
