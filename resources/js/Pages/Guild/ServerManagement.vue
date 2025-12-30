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

// Zeitzonen-Liste gruppiert nach UTC-Offset und gemeinsamen Zeitzonen
const timezones = [
    // UTC (GMT)
    { value: 'Europe/London', flag: '🇬🇧', label: 'UTC+0 (GMT/BST) - London, Dublin, Lissabon' },
    
    // UTC+1 (CET - Central European Time)
    { value: 'Europe/Berlin', flag: '🇪🇺', label: 'UTC+1 (CET/CEST) - Mitteleuropa (Deutschland, Österreich, Schweiz, Frankreich, Italien, Spanien, etc.)' },
    
    // UTC+2 (EET - Eastern European Time)
    { value: 'Europe/Athens', flag: '🇪🇺', label: 'UTC+2 (EET/EEST) - Osteuropa (Griechenland, Finnland, Rumänien, etc.)' },
    { value: 'Europe/Istanbul', flag: '🇹🇷', label: 'UTC+3 (TRT) - Türkei' },
    
    // UTC+4
    { value: 'Asia/Dubai', flag: '🇦🇪', label: 'UTC+4 (GST) - VAE, Georgien' },
    
    // UTC+5:30
    { value: 'Asia/Kolkata', flag: '🇮🇳', label: 'UTC+5:30 (IST) - Indien' },
    
    // UTC+8
    { value: 'Asia/Shanghai', flag: '🇨🇳', label: 'UTC+8 (CST) - China, Singapur, Malaysia' },
    { value: 'Asia/Hong_Kong', flag: '🇭🇰', label: 'UTC+8 (HKT) - Hong Kong' },
    { value: 'Asia/Singapore', flag: '🇸🇬', label: 'UTC+8 (SGT) - Singapur' },
    
    // UTC+9
    { value: 'Asia/Tokyo', flag: '🇯🇵', label: 'UTC+9 (JST) - Japan, Südkorea' },
    
    // UTC+10
    { value: 'Australia/Sydney', flag: '🇦🇺', label: 'UTC+10 (AEDT/AEST) - Australien (Ost), Papua-Neuguinea' },
    { value: 'Australia/Melbourne', flag: '🇦🇺', label: 'UTC+10 (AEDT/AEST) - Australien (Südost)' },
    
    // UTC+12
    { value: 'Pacific/Auckland', flag: '🇳🇿', label: 'UTC+12 (NZDT/NZST) - Neuseeland, Fidschi' },
    
    // UTC-3
    { value: 'America/Sao_Paulo', flag: '🇧🇷', label: 'UTC-3 (BRT/BRST) - Brasilien, Argentinien, Uruguay' },
    
    // UTC-5 (EST - Eastern Standard Time)
    { value: 'America/New_York', flag: '🇺🇸', label: 'UTC-5 (EST/EDT) - USA/Kanada (Ostküste)' },
    { value: 'America/Toronto', flag: '🇨🇦', label: 'UTC-5 (EST/EDT) - Kanada (Ost)' },
    
    // UTC-6 (CST - Central Standard Time)
    { value: 'America/Chicago', flag: '🇺🇸', label: 'UTC-6 (CST/CDT) - USA/Kanada (Mitte)' },
    { value: 'America/Mexico_City', flag: '🇲🇽', label: 'UTC-6 (CST/CDT) - Mexiko, Zentralamerika' },
    
    // UTC-7 (MST - Mountain Standard Time)
    { value: 'America/Denver', flag: '🇺🇸', label: 'UTC-7 (MST/MDT) - USA/Kanada (Rocky Mountains)' },
    
    // UTC-8 (PST - Pacific Standard Time)
    { value: 'America/Los_Angeles', flag: '🇺🇸', label: 'UTC-8 (PST/PDT) - USA/Kanada (Westküste)' },
    { value: 'America/Vancouver', flag: '🇨🇦', label: 'UTC-8 (PST/PDT) - Kanada (West)' },
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
