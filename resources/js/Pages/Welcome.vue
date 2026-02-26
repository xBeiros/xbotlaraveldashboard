<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import AppFooter from '@/Components/AppFooter.vue';

const { t } = useI18n();

const METEOR_COUNT = 50;

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const userDropdownOpen = ref(false);

function meteorStyle(i) {
    const group = i % 3; // 0,1,2 → 3 Wellen

    const baseDelay = group * 4; // 0s, 4s, 8s
    const jitter = Math.random() * 1.5;

    return {
        // In CSS (Y-Achse nach unten) sind ~30–60° „nach rechts/unten“.
        // ~220° würde eher „nach links/oben“ fliegen und bei top < 0% unsichtbar bleiben.
        '--angle': `${30 + Math.random() * 30}deg`,
        '--cycle': `${12 + group * 2}s`,
        animationDelay: `${baseDelay + jitter}s`,
        // Startpunkt nur leicht außerhalb, damit man sie sicher sieht
        top: `${-5 - Math.random() * 8}%`,
        left: `${10 + Math.random() * 60}%`,
    };
}

// Styles einmalig erzeugen (stabil, kein Re-Random bei Re-Renders)
const meteorStyles = Array.from({ length: METEOR_COUNT }, (_, idx) => meteorStyle(idx + 1));

// Sterne für Hintergrund
const STAR_COUNT = 30;

function starStyle(i) {
    return {
        top: `${Math.random() * 100}%`,
        left: `${Math.random() * 100}%`,
        animationDelay: `${Math.random() * 4}s`,
        opacity: `${0.3 + Math.random() * 0.7}`,
    };
}

const starStyles = Array.from({ length: STAR_COUNT }, (_, idx) => starStyle(idx + 1));

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}

function logout() {
    router.post(route('logout'));
}

function inviteBot() {
    // Öffne Bot-Einladung in neuem Popup-Fenster
    let inviteUrl;
    try {
        inviteUrl = route('bot.invite');
    } catch (error) {
        console.warn('Route-Funktion nicht verfügbar, verwende direkte URL:', error);
        // Fallback: Direkte URL erstellen
        inviteUrl = '/bot/invite';
    }
    
    // Öffne die Einladungs-URL in neuem Popup-Fenster
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
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <!-- Sticky Navbar -->
        <nav class="sticky top-0 z-50 bg-[#1F2129] border-b border-[#36393f]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex items-center justify-between h-20">
                    <!-- Logo & Brand -->
                    <div class="flex items-center gap-3">
                        <div class="logo-container">
                            <img 
                                src="/images/logo.svg" 
                                alt="XBot Logo" 
                                class="w-10 h-10 logo-shine"
                            />
                        </div>
                        <span class="font-bold text-lg text-white">XBot</span>
                    </div>

                    <!-- Navigation Items (optional, kann später erweitert werden) -->
                    <div class="hidden md:flex items-center gap-6">
                        <!-- Platzhalter für zukünftige Navigation -->
                    </div>

                    <!-- Right Side: User Dropdown -->
                    <div class="flex items-center gap-4">
                        <!-- Language Selector -->
                        <LanguageSelector />

                        <!-- User Dropdown (wenn eingeloggt) -->
                        <Dropdown v-if="$page.props.auth.user" align="right" width="48" content-classes="bg-[#1F2129] border border-[#202225]">
                            <template #trigger>
                                <button class="flex items-center gap-2 hover:bg-[#40444b] px-3 py-2 rounded transition-colors">
                                    <img
                                        v-if="$page.props.auth.user.avatar"
                                        :src="$page.props.auth.user.avatar"
                                        :alt="$page.props.auth.user.name"
                                        class="w-8 h-8 rounded-full"
                                    />
                                    <div
                                        v-else
                                        class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white text-sm font-medium"
                                    >
                                        {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                                    </div>
                                    <span class="text-sm text-white font-medium">{{ $page.props.auth.user.name || $page.props.auth.user.username || 'User' }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('dashboard')" class="text-white hover:text-white hover:bg-[#36393f]">
                                    {{ t('landing.dashboard') }}
                                </DropdownLink>
                                <div class="border-t border-[#202225] my-1"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-400 hover:text-red-300 hover:bg-[#36393f]">
                                    {{ t('landing.logout') }}
                                </DropdownLink>
                            </template>
                        </Dropdown>

                        <!-- Login Button (wenn nicht eingeloggt) -->
                        <a
                            v-else-if="canLogin"
                            :href="route('discord.login')"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors font-medium"
                        >
                            {{ t('landing.loginWithDiscord') }}
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative w-full min-h-[70vh] pt-12 pb-20 md:pt-16 md:pb-32 overflow-hidden">
            <div 
                class="absolute inset-0"
                style="background: linear-gradient(rgb(19, 21, 31) 0%, rgb(29, 28, 47) 25%, rgb(33, 32, 54) 45%, rgb(40, 38, 58) 65%, rgb(35, 34, 52) 80%, rgb(29, 28, 47) 90%, rgb(19, 21, 31) 100%);"
            ></div>
            
            <!-- Sterne Hintergrund -->
            <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
                <template v-for="(style, i) in starStyles" :key="`star-${i}`">
                    <div
                        class="star"
                        :style="style"
                    />
                </template>
            </div>
            
            <!-- Meteoren-Regen Animation -->
            <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
                <template v-for="(style, i) in meteorStyles" :key="i">
                    <div
                        class="meteor"
                        :style="style"
                    />
                </template>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-6 z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center min-h-[70vh]">
                    <!-- Links: Text-Bereich -->
                    <div>
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                            {{ t('landing.heroTitle') }}
                        </h1>
                        <p class="text-lg md:text-xl text-gray-300 mb-8 leading-relaxed">
                            {{ t('landing.heroDescription') }}
                        </p>
                        <button
                            @click="inviteBot"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#5865f2] hover:bg-[#4752c4] text-white font-semibold rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027A19.5 19.5 0 0 0 .058 18.1a.082.082 0 0 0 .031.057a19.869 19.869 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.82 19.82 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.058a19.5 19.5 0 0 0-3.6-13.703a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                            {{ t('landing.addToDiscord') }}
                        </button>
                    </div>
                    
                    <!-- Rechts: Discord Mond mittig -->
                    <div class="flex items-center justify-center relative z-10">
                        <img 
                            src="/images/discord_mond.svg" 
                            alt="Discord Mond"
                            class="discord-mond w-32 h-32 md:w-48 md:h-48 lg:w-64 lg:h-64"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer: Datenschutz, Impressum, Cookies, Nutzungsbedingungen -->
        <AppFooter />
    </div>
</template>

<style scoped>
.logo-container {
    position: relative;
    display: inline-block;
}

.logo-shine {
    animation: pulse 2s ease-in-out infinite, shine 3s ease-in-out infinite;
    filter: drop-shadow(0 0 8px rgba(88, 101, 242, 0.6));
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        filter: drop-shadow(0 0 8px rgba(88, 101, 242, 0.6));
    }
    50% {
        transform: scale(1.05);
        filter: drop-shadow(0 0 12px rgba(88, 101, 242, 0.8));
    }
}

@keyframes shine {
    0% {
        filter: drop-shadow(0 0 8px rgba(88, 101, 242, 0.6));
    }
    50% {
        filter: drop-shadow(0 0 16px rgba(88, 101, 242, 0.9)) drop-shadow(0 0 24px rgba(88, 101, 242, 0.5));
    }
    100% {
        filter: drop-shadow(0 0 8px rgba(88, 101, 242, 0.6));
    }
}

.discord-mond {
    animation: float 6s ease-in-out infinite;
    filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.5)) drop-shadow(0 0 40px rgba(255, 255, 255, 0.3));
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
        filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.5)) drop-shadow(0 0 40px rgba(255, 255, 255, 0.3));
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
        filter: drop-shadow(0 0 30px rgba(255, 255, 255, 0.7)) drop-shadow(0 0 50px rgba(255, 255, 255, 0.4));
    }
}

/* Meteoren-Regen Animation */
@keyframes meteor {
    0% {
        opacity: 0;
        transform: rotate(var(--angle)) translateX(0);
    }

    10% {
        opacity: 1;
    }

    40% {
        opacity: 1;
        transform: rotate(var(--angle)) translateX(600px);
    }

    100% {
        opacity: 0;
        transform: rotate(var(--angle)) translateX(900px);
    }
}

.meteor {
    position: absolute;
    width: 2px;
    height: 2px;
    background: rgb(161 161 170); /* zinc-400 */
    border-radius: 9999px;
    box-shadow: 0 0 8px rgba(255, 255, 255, 0.6);
    animation: meteor var(--cycle) cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Sterne Hintergrund */
.star {
    position: absolute;
    width: 2px;
    height: 2px;
    background: rgb(255, 255, 255);
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(255, 255, 255, 0.8), 0 0 8px rgba(255, 255, 255, 0.4);
    animation: star-twinkle 3s ease-in-out infinite;
}

@keyframes star-twinkle {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
        box-shadow: 0 0 4px rgba(255, 255, 255, 0.8), 0 0 8px rgba(255, 255, 255, 0.4);
    }
    50% {
        opacity: 1;
        transform: scale(1.2);
        box-shadow: 0 0 8px rgba(255, 255, 255, 1), 0 0 16px rgba(255, 255, 255, 0.6), 0 0 24px rgba(255, 255, 255, 0.3);
    }
}
</style>
