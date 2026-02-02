<template>
    <Head :title="`${guild.name} - Einstellungen`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns ?? {}">
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
    addOns: { type: Object, default: () => ({}) },
    guildModel: Object,
});

const form = useForm({
    language: props.guildModel?.language || 'de',
    timezone: props.guildModel?.timezone || 'Europe/Berlin',
});

// Zeitzonen-Liste gruppiert nach UTC-Offset und gemeinsamen Zeitzonen
const timezones = computed(() => [
    // UTC (GMT)
    { value: 'Europe/London', translationKey: 'UTC+0_London' },
    
    // UTC+1 (CET - Central European Time)
    { value: 'Europe/Berlin', translationKey: 'UTC+1_Berlin' },
    
    // UTC+2 (EET - Eastern European Time)
    { value: 'Europe/Athens', translationKey: 'UTC+2_Athens' },
    { value: 'Europe/Istanbul', translationKey: 'UTC+3_Istanbul' },
    
    // UTC+4
    { value: 'Asia/Dubai', translationKey: 'UTC+4_Dubai' },
    
    // UTC+5:30
    { value: 'Asia/Kolkata', translationKey: 'UTC+5:30_Kolkata' },
    
    // UTC+8
    { value: 'Asia/Shanghai', translationKey: 'UTC+8_Shanghai' },
    { value: 'Asia/Hong_Kong', translationKey: 'UTC+8_HongKong' },
    { value: 'Asia/Singapore', translationKey: 'UTC+8_Singapore' },
    
    // UTC+9
    { value: 'Asia/Tokyo', translationKey: 'UTC+9_Tokyo' },
    
    // UTC+10
    { value: 'Australia/Sydney', translationKey: 'UTC+10_Sydney' },
    { value: 'Australia/Melbourne', translationKey: 'UTC+10_Melbourne' },
    
    // UTC+12
    { value: 'Pacific/Auckland', translationKey: 'UTC+12_Auckland' },
    
    // UTC-3
    { value: 'America/Sao_Paulo', translationKey: 'UTC-3_SaoPaulo' },
    
    // UTC-5 (EST - Eastern Standard Time)
    { value: 'America/New_York', translationKey: 'UTC-5_NewYork' },
    { value: 'America/Toronto', translationKey: 'UTC-5_Toronto' },
    
    // UTC-6 (CST - Central Standard Time)
    { value: 'America/Chicago', translationKey: 'UTC-6_Chicago' },
    { value: 'America/Mexico_City', translationKey: 'UTC-6_MexicoCity' },
    
    // UTC-7 (MST - Mountain Standard Time)
    { value: 'America/Denver', translationKey: 'UTC-7_Denver' },
    
    // UTC-8 (PST - Pacific Standard Time)
    { value: 'America/Los_Angeles', translationKey: 'UTC-8_LosAngeles' },
    { value: 'America/Vancouver', translationKey: 'UTC-8_Vancouver' },
].map(tz => ({
    ...tz,
    label: t(`serverManagement.timezones.${tz.translationKey}`)
})));

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
