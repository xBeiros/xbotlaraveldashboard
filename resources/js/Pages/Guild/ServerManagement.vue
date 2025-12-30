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
                                    {{ tz.label }}
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

// Zeitzonen-Liste (ohne Flaggen-Emojis für bessere Kompatibilität)
const timezones = [
    { value: 'Europe/Berlin', label: 'Europe/Berlin (CET/CEST) - Deutschland' },
    { value: 'Europe/London', label: 'Europe/London (GMT/BST) - Großbritannien' },
    { value: 'America/New_York', label: 'America/New_York (EST/EDT) - USA (Ostküste)' },
    { value: 'America/Los_Angeles', label: 'America/Los_Angeles (PST/PDT) - USA (Westküste)' },
    { value: 'America/Chicago', label: 'America/Chicago (CST/CDT) - USA (Mitte)' },
    { value: 'America/Denver', label: 'America/Denver (MST/MDT) - USA (Rocky Mountains)' },
    { value: 'Europe/Paris', label: 'Europe/Paris (CET/CEST) - Frankreich' },
    { value: 'Europe/Rome', label: 'Europe/Rome (CET/CEST) - Italien' },
    { value: 'Europe/Madrid', label: 'Europe/Madrid (CET/CEST) - Spanien' },
    { value: 'Europe/Amsterdam', label: 'Europe/Amsterdam (CET/CEST) - Niederlande' },
    { value: 'Europe/Vienna', label: 'Europe/Vienna (CET/CEST) - Österreich' },
    { value: 'Europe/Zurich', label: 'Europe/Zurich (CET/CEST) - Schweiz' },
    { value: 'Europe/Stockholm', label: 'Europe/Stockholm (CET/CEST) - Schweden' },
    { value: 'Europe/Oslo', label: 'Europe/Oslo (CET/CEST) - Norwegen' },
    { value: 'Europe/Copenhagen', label: 'Europe/Copenhagen (CET/CEST) - Dänemark' },
    { value: 'Europe/Helsinki', label: 'Europe/Helsinki (EET/EEST) - Finnland' },
    { value: 'Europe/Warsaw', label: 'Europe/Warsaw (CET/CEST) - Polen' },
    { value: 'Europe/Prague', label: 'Europe/Prague (CET/CEST) - Tschechien' },
    { value: 'Europe/Budapest', label: 'Europe/Budapest (CET/CEST) - Ungarn' },
    { value: 'Europe/Athens', label: 'Europe/Athens (EET/EEST) - Griechenland' },
    { value: 'Europe/Istanbul', label: 'Europe/Istanbul (TRT) - Türkei' },
    { value: 'Asia/Tokyo', label: 'Asia/Tokyo (JST) - Japan' },
    { value: 'Asia/Shanghai', label: 'Asia/Shanghai (CST) - China' },
    { value: 'Asia/Hong_Kong', label: 'Asia/Hong_Kong (HKT) - Hong Kong' },
    { value: 'Asia/Singapore', label: 'Asia/Singapore (SGT) - Singapur' },
    { value: 'Asia/Dubai', label: 'Asia/Dubai (GST) - Vereinigte Arabische Emirate' },
    { value: 'Australia/Sydney', label: 'Australia/Sydney (AEDT/AEST) - Australien (Ost)' },
    { value: 'Australia/Melbourne', label: 'Australia/Melbourne (AEDT/AEST) - Australien (Südost)' },
    { value: 'Pacific/Auckland', label: 'Pacific/Auckland (NZDT/NZST) - Neuseeland' },
    { value: 'America/Sao_Paulo', label: 'America/Sao_Paulo (BRT/BRST) - Brasilien' },
    { value: 'America/Mexico_City', label: 'America/Mexico_City (CST/CDT) - Mexiko' },
    { value: 'America/Toronto', label: 'America/Toronto (EST/EDT) - Kanada (Ost)' },
    { value: 'America/Vancouver', label: 'America/Vancouver (PST/PDT) - Kanada (West)' },
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
