<template>
    <div class="relative" ref="dropdownRef">
        <button
            @click="showDropdown = !showDropdown"
            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#36393f] hover:bg-[#40444b] transition-colors text-sm text-gray-300 hover:text-white"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.196 3.196l1.4 1.4M6 18h12M18 16v2M3 5l3 3m0 0l3-3m-3 3v12" />
            </svg>
            <span>{{ currentLanguageName }}</span>
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
                    'w-full px-4 py-2 text-left text-sm transition-colors flex items-center justify-between',
                    currentLanguage === lang.code
                        ? 'bg-[#5865f2] text-white'
                        : 'text-gray-300 hover:bg-[#36393f] hover:text-white'
                ]"
            >
                <span>{{ lang.name }}</span>
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

const currentLanguageName = computed(() => {
    const lang = languages.find(l => l.code === currentLanguage.value);
    return lang ? lang.name : 'Deutsch';
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

