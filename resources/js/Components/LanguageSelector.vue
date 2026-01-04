<template>
    <div class="relative" ref="dropdownRef">
        <button
            @click="showDropdown = !showDropdown"
            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#36393f] hover:bg-[#40444b] transition-colors text-sm text-gray-300 hover:text-white"
        >
            <img 
                :src="`/images/lang/${currentLanguage}.png`" 
                :alt="currentLanguageCode"
                class="w-5 h-5 rounded-sm"
            />
            <span>{{ currentLanguageCode }}</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            v-if="showDropdown"
            class="absolute top-full right-0 mt-2 w-48 bg-[#2f3136] border border-[#202225] rounded-lg shadow-lg z-50 overflow-hidden"
        >
            <button
                v-for="lang in languages"
                :key="lang.code"
                @click="changeLanguage(lang.code)"
                :class="[
                    'w-full px-4 py-2 text-left text-sm transition-colors flex items-center justify-between gap-2',
                    currentLanguage === lang.code
                        ? 'bg-[#5865f2] text-white'
                        : 'text-gray-300 hover:bg-[#36393f] hover:text-white'
                ]"
            >
                <div class="flex items-center gap-2">
                    <img 
                        :src="`/images/lang/${lang.code}.png`" 
                        :alt="lang.name"
                        class="w-5 h-5 rounded-sm"
                    />
                    <span>{{ lang.name }}</span>
                </div>
                <svg
                    v-if="currentLanguage === lang.code"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const showDropdown = ref(false);
const dropdownRef = ref(null);

const languages = [
    { code: 'de', name: 'Deutsch' },
    { code: 'en', name: 'English' },
    { code: 'tr', name: 'Türkçe' },
];

const currentLanguage = computed(() => locale.value);

const currentLanguageCode = computed(() => {
    return currentLanguage.value.toUpperCase();
});

function changeLanguage(lang) {
    locale.value = lang;
    localStorage.setItem('app_language', lang);
    showDropdown.value = false;
    
    // Reload page to apply language changes
    window.location.reload();
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
}

onMounted(() => {
    // Load saved language
    const savedLang = localStorage.getItem('app_language');
    if (savedLang && languages.find(l => l.code === savedLang)) {
        locale.value = savedLang;
    }
    
    // Add click outside listener
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

