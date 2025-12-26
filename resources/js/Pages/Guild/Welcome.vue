<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    welcomeConfig: Object,
    goodbyeConfig: Object,
});

// Accordion States
const welcomeAccordionOpen = ref(true);
const goodbyeAccordionOpen = ref(false);

// Computed für Welcome-Nachrichtentyp
const welcomeMessageType = computed({
    get() {
        if (welcomeForm.use_welcome_card) return 'card';
        if (welcomeForm.use_embed) return 'embed';
        return 'text';
    },
    set(value) {
        welcomeForm.use_welcome_card = value === 'card';
        welcomeForm.use_embed = value === 'embed';
        if (value === 'text') {
            welcomeForm.use_welcome_card = false;
            welcomeForm.use_embed = false;
        }
    }
});

// Computed für Goodbye-Nachrichtentyp
const goodbyeMessageType = computed({
    get() {
        if (goodbyeForm.use_goodbye_card) return 'card';
        if (goodbyeForm.use_embed) return 'embed';
        return 'text';
    },
    set(value) {
        goodbyeForm.use_goodbye_card = value === 'card';
        goodbyeForm.use_embed = value === 'embed';
        if (value === 'text') {
            goodbyeForm.use_goodbye_card = false;
            goodbyeForm.use_embed = false;
        }
    }
});

const welcomeForm = useForm({
    enabled: props.welcomeConfig?.enabled ?? false,
    channel_id: props.welcomeConfig?.channel_id ?? '',
    message: props.welcomeConfig?.message ?? 'Willkommen {user} auf {server}! 🎉',
    use_embed: props.welcomeConfig?.use_embed ?? false,
    embed_title: props.welcomeConfig?.embed_title ?? '{user} ist dem Server beigetreten',
    embed_description: props.welcomeConfig?.embed_description ?? 'Willkommen auf {server}! Du bist Mitglied #{memberCount}',
    embed_color: props.welcomeConfig?.embed_color ?? '#5865f2',
    embed_thumbnail: props.welcomeConfig?.embed_thumbnail ?? '',
    embed_image: props.welcomeConfig?.embed_image ?? '',
    embed_footer: props.welcomeConfig?.embed_footer ?? true,
    use_welcome_card: props.welcomeConfig?.use_welcome_card ?? false,
    card_font: props.welcomeConfig?.card_font ?? 'Arial',
    card_text_color: props.welcomeConfig?.card_text_color ?? '#ffffff',
    card_background_color: props.welcomeConfig?.card_background_color ?? '#000000',
    card_overlay_opacity: props.welcomeConfig?.card_overlay_opacity ?? 50,
    card_background_image: props.welcomeConfig?.card_background_image ?? '',
    card_title: props.welcomeConfig?.card_title ?? '{user.idname} just joined the server',
    card_avatar_position: props.welcomeConfig?.card_avatar_position ?? 'top',
});

const goodbyeForm = useForm({
    enabled: props.goodbyeConfig?.enabled ?? false,
    channel_id: props.goodbyeConfig?.channel_id ?? '',
    message: props.goodbyeConfig?.message ?? '{user} hat den Server verlassen. 👋',
    use_embed: props.goodbyeConfig?.use_embed ?? false,
    embed_title: props.goodbyeConfig?.embed_title ?? '{user} hat den Server verlassen',
    embed_description: props.goodbyeConfig?.embed_description ?? 'Wir werden dich vermissen!',
    embed_color: props.goodbyeConfig?.embed_color ?? '#ef4444',
    embed_thumbnail: props.goodbyeConfig?.embed_thumbnail ?? '',
    embed_image: props.goodbyeConfig?.embed_image ?? '',
    embed_footer: props.goodbyeConfig?.embed_footer ?? true,
    use_goodbye_card: props.goodbyeConfig?.use_goodbye_card ?? false,
    card_font: props.goodbyeConfig?.card_font ?? 'Arial',
    card_text_color: props.goodbyeConfig?.card_text_color ?? '#ffffff',
    card_background_color: props.goodbyeConfig?.card_background_color ?? '#000000',
    card_overlay_opacity: props.goodbyeConfig?.card_overlay_opacity ?? 50,
    card_background_image: props.goodbyeConfig?.card_background_image ?? '',
    card_title: props.goodbyeConfig?.card_title ?? '{user.idname} left the server',
    card_avatar_position: props.goodbyeConfig?.card_avatar_position ?? 'top',
});

const messageLength = computed(() => welcomeForm.message?.length || 0);
const goodbyeMessageLength = computed(() => goodbyeForm.message?.length || 0);

const fileInput = ref(null);
const goodbyeFileInput = ref(null);

function handleImageUpload(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert(t('welcome.welcomeSection.fileTooLarge'));
        event.target.value = '';
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        alert(t('welcome.welcomeSection.pleaseSelectImage'));
        event.target.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        welcomeForm.card_background_image = e.target.result;
    };
    reader.onerror = () => {
        alert(t('welcome.welcomeSection.errorLoadingFile'));
        event.target.value = '';
    };
    reader.readAsDataURL(file);
}

function handleGoodbyeImageUpload(event) {
    const file = event.target.files?.[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert(t('welcome.goodbyeSection.fileTooLarge'));
        event.target.value = '';
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        alert(t('welcome.goodbyeSection.pleaseSelectImage'));
        event.target.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        goodbyeForm.card_background_image = e.target.result;
    };
    reader.onerror = () => {
        alert(t('welcome.goodbyeSection.errorLoadingFile'));
        event.target.value = '';
    };
    reader.readAsDataURL(file);
}

function removeBackgroundImage() {
    welcomeForm.card_background_image = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function removeGoodbyeBackgroundImage() {
    goodbyeForm.card_background_image = '';
    if (goodbyeFileInput.value) {
        goodbyeFileInput.value.value = '';
    }
}

const colorOptions = [
    { name: 'Gradient', value: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' },
    { name: 'Weiß', value: '#ffffff' },
    { name: 'Grau', value: '#808080' },
    { name: 'Blau', value: '#3b82f6' },
    { name: 'Rot', value: '#ef4444' },
    { name: 'Orange', value: '#f97316' },
    { name: 'Gelb', value: '#eab308' },
    { name: 'Grün', value: '#22c55e' },
    { name: 'Hellblau', value: '#06b6d4' },
    { name: 'Lila', value: '#a855f7' },
];

function saveWelcomeConfig() {
    welcomeForm.put(route('guild.welcome.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}

function saveGoodbyeConfig() {
    goodbyeForm.put(route('guild.goodbye.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}

</script>

<template>
    <Head :title="`${guild.name} - ${t('welcome.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ t('welcome.basicInfo') }}</h1>

            <div class="space-y-4">
                <!-- Begrüßung Accordion -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden">
                    <!-- Accordion Header -->
                    <div class="flex items-center justify-between p-6">
                        <button
                            @click="welcomeAccordionOpen = !welcomeAccordionOpen"
                            class="flex-1 flex items-center gap-4 text-left hover:bg-[#36393f] -m-6 p-6 rounded-lg transition-colors"
                        >
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white">{{ t('welcome.welcomeSection.title') }}</h3>
                                <p class="text-sm text-gray-400">{{ t('welcome.welcomeSection.description') }}</p>
                            </div>
                            <svg
                                class="w-5 h-5 text-gray-400 transition-transform flex-shrink-0"
                                :class="{ 'rotate-180': welcomeAccordionOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <!-- Toggle oben rechts -->
                        <div class="ml-4 flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="welcomeForm.enabled"
                                    class="sr-only peer"
                                />
                                <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#5865f2] shadow-inner"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Accordion Content -->
                    <div v-show="welcomeAccordionOpen" class="border-t border-[#202225]">
                        <form @submit.prevent="saveWelcomeConfig" class="p-6 space-y-6">
                            <!-- Kanal-Auswahl -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ t('welcome.welcomeSection.channelLabel') }}
                                </label>
                                <select
                                    v-model="welcomeForm.channel_id"
                                    required
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                >
                                    <option value="">Bitte wählen...</option>
                                    <template v-for="channel in channels" :key="channel.id">
                                        <option
                                            v-if="channel.type === 4 && (channel.is_category || channel.type === 4)"
                                            disabled
                                            class="bg-[#2f3136] font-semibold"
                                        >
                                            ── {{ channel.name }} ──
                                        </option>
                                        <option
                                            v-else-if="channel.type === 0"
                                            :value="channel.id"
                                        >
                                            # {{ channel.name }}
                                        </option>
                                    </template>
                                </select>
                                <p v-if="!channels || channels.length === 0" class="text-xs text-red-400 mt-1">
                                    Keine Channels gefunden. Bitte stelle sicher, dass der Bot Zugriff auf die Channels hat.
                                </p>
                            </div>

                            <!-- Nachrichtentyp-Auswahl -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">
                                    Nachrichtentyp auswählen
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="welcomeMessageType"
                                            value="text"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.welcomeSection.textMessage') }}</p>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="welcomeMessageType"
                                            value="embed"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.welcomeSection.embedMessage') }}</p>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="welcomeMessageType"
                                            value="card"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.welcomeSection.welcomeCard') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Textnachricht -->
                            <div v-if="welcomeMessageType === 'text'" class="space-y-4">
                                <div>
                                    <textarea
                                        v-model="welcomeForm.message"
                                        rows="4"
                                        placeholder="Willkommen {user} auf {server}!"
                                        maxlength="2000"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2] resize-none"
                                    ></textarea>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-xs text-gray-400">
                                            {{ t('welcome.welcomeSection.availablePlaceholders') }} <code class="bg-[#202225] px-1 rounded">{{ t('welcome.welcomeSection.placeholderUser') }}</code>, <code class="bg-[#202225] px-1 rounded">{{ t('welcome.welcomeSection.placeholderServer') }}</code>, <code class="bg-[#202225] px-1 rounded">{{ t('welcome.welcomeSection.placeholderMemberCount') }}</code>
                                        </p>
                                        <span class="text-xs text-gray-400">{{ messageLength }} / 2000</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Embed -->
                            <div v-if="welcomeMessageType === 'embed'" class="space-y-4">
                                <!-- Embed Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div class="bg-[#2f3136] rounded-lg p-4 max-w-md">
                                        <!-- Discord-ähnliche Embed-Vorschau -->
                                        <div 
                                            class="rounded border-l-4 p-3"
                                            :style="{ borderLeftColor: welcomeForm.embed_color || '#5865f2' }"
                                        >
                                            <div class="space-y-2">
                                                <h5 
                                                    v-if="welcomeForm.embed_title"
                                                    class="text-base font-semibold text-white"
                                                >
                                                    {{ welcomeForm.embed_title.replace('{user}', 'ibeiros#0').replace('{server}', guild.name).replace('{memberCount}', '21') }}
                                                </h5>
                                                <p 
                                                    v-if="welcomeForm.embed_description"
                                                    class="text-sm text-gray-300 whitespace-pre-wrap"
                                                >
                                                    {{ welcomeForm.embed_description.replace('{user}', 'ibeiros#0').replace('{server}', guild.name).replace('{memberCount}', '21') }}
                                                </p>
                                                <div 
                                                    v-if="welcomeForm.embed_footer"
                                                    class="flex items-center gap-2 pt-2 border-t border-[#202225]"
                                                >
                                                    <img 
                                                        v-if="guild.icon_url"
                                                        :src="guild.icon_url" 
                                                        :alt="guild.name"
                                                        class="w-4 h-4 rounded-full"
                                                    />
                                                    <span class="text-xs text-gray-400">{{ guild.name }}</span>
                                                    <span class="text-xs text-gray-500">•</span>
                                                    <span class="text-xs text-gray-400">{{ new Date().toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('welcome.welcomeSection.titleLabel') }}</label>
                                    <input
                                        type="text"
                                        v-model="welcomeForm.embed_title"
                                        placeholder="{user} ist dem Server beigetreten"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('welcome.welcomeSection.descriptionLabel') }}</label>
                                    <textarea
                                        v-model="welcomeForm.embed_description"
                                        rows="3"
                                        placeholder="Willkommen auf {server}! Du bist Mitglied #{memberCount}"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('welcome.welcomeSection.colorLabel') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="welcomeForm.embed_color"
                                            class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="welcomeForm.embed_color"
                                            placeholder="#5865f2"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="welcomeForm.embed_footer"
                                        class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                    />
                                    <label class="text-sm text-gray-300">Footer anzeigen (Server-Name und Zeitstempel)</label>
                                </div>
                            </div>

                            <!-- Willkommenskarte -->
                            <div v-if="welcomeMessageType === 'card'" class="space-y-4">
                                <!-- Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div 
                                        class="relative rounded-lg overflow-hidden"
                                        :style="{
                                            backgroundImage: welcomeForm.card_background_image ? `url(${welcomeForm.card_background_image})` : 'none',
                                            backgroundSize: 'cover',
                                            backgroundPosition: 'center',
                                            backgroundColor: welcomeForm.card_background_image ? 'transparent' : (welcomeForm.card_background_color || '#000000'),
                                            minHeight: '200px',
                                        }"
                                    >
                                        <div 
                                            class="absolute inset-0"
                                            :style="{ backgroundColor: `rgba(0, 0, 0, ${welcomeForm.card_overlay_opacity / 100})` }"
                                        ></div>
                                        <div 
                                            class="relative p-6"
                                            :class="{
                                                'flex flex-col items-center justify-center text-center': welcomeForm.card_avatar_position === 'top',
                                                'flex flex-row items-center justify-center gap-4': welcomeForm.card_avatar_position === 'left',
                                                'flex flex-row-reverse items-center justify-center gap-4': welcomeForm.card_avatar_position === 'right',
                                                'flex flex-col-reverse items-center justify-center text-center': welcomeForm.card_avatar_position === 'bottom',
                                            }"
                                        >
                                            <div 
                                                class="w-20 h-20 rounded-full bg-[#5865f2] flex items-center justify-center flex-shrink-0"
                                                :class="{
                                                    'mb-4': welcomeForm.card_avatar_position === 'top',
                                                    'mt-4': welcomeForm.card_avatar_position === 'bottom',
                                                }"
                                            >
                                                <span class="text-white text-2xl font-bold">B</span>
                                            </div>
                                            <div 
                                                :class="{
                                                    'text-center': welcomeForm.card_avatar_position === 'top' || welcomeForm.card_avatar_position === 'bottom',
                                                    'text-left': welcomeForm.card_avatar_position === 'left',
                                                    'text-right': welcomeForm.card_avatar_position === 'right',
                                                }"
                                            >
                                                <p 
                                                    class="text-lg font-semibold mb-2"
                                                    :style="{ color: welcomeForm.card_text_color, fontFamily: welcomeForm.card_font }"
                                                >
                                                    {{ welcomeForm.card_title.replace('{user.idname}', 'ibeiros#0') }}
                                                </p>
                                                <p 
                                                    class="text-sm"
                                                    :style="{ color: welcomeForm.card_text_color, fontFamily: welcomeForm.card_font }"
                                                >
                                                    Member #21
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personalisierung -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Personalisiere deine Willkommenskarte</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Schriftart</label>
                                            <input
                                                type="text"
                                                v-model="welcomeForm.card_font"
                                                placeholder="Suche nach einer Schriftart..."
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                            <input
                                                type="text"
                                                v-model="welcomeForm.card_title"
                                                placeholder="{user.idname} just joined the server"
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Avatar-Position</label>
                                            <select
                                                v-model="welcomeForm.card_avatar_position"
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            >
                                                <option value="top">Über dem Text</option>
                                                <option value="left">Links vom Text</option>
                                                <option value="right">Rechts vom Text</option>
                                                <option value="bottom">Unter dem Text</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Textfarbe</label>
                                            <div class="grid grid-cols-5 gap-2 mb-2">
                                                <button
                                                    v-for="color in colorOptions"
                                                    :key="color.value"
                                                    type="button"
                                                    @click="welcomeForm.card_text_color = color.value"
                                                    :class="[
                                                        'h-8 rounded border-2 transition-all',
                                                        welcomeForm.card_text_color === color.value ? 'border-white scale-110' : 'border-[#202225] hover:border-gray-500'
                                                    ]"
                                                    :style="{ background: color.value === 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ? color.value : color.value }"
                                                    :title="color.name"
                                                ></button>
                                            </div>
                                            <input
                                                type="color"
                                                v-model="welcomeForm.card_text_color"
                                                class="w-full h-10 rounded border border-[#202225] cursor-pointer"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Hintergrundfarbe</label>
                                            <div class="grid grid-cols-5 gap-2 mb-2">
                                                <button
                                                    v-for="color in colorOptions"
                                                    :key="color.value"
                                                    type="button"
                                                    @click="welcomeForm.card_background_color = color.value"
                                                    :class="[
                                                        'h-8 rounded border-2 transition-all',
                                                        welcomeForm.card_background_color === color.value ? 'border-white scale-110' : 'border-[#202225] hover:border-gray-500'
                                                    ]"
                                                    :style="{ background: color.value === 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ? color.value : color.value }"
                                                    :title="color.name"
                                                ></button>
                                            </div>
                                            <input
                                                type="color"
                                                v-model="welcomeForm.card_background_color"
                                                class="w-full h-10 rounded border border-[#202225] cursor-pointer"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Deckkraft der Überlagerung: {{ welcomeForm.card_overlay_opacity }}%
                                            </label>
                                            <input
                                                type="range"
                                                v-model="welcomeForm.card_overlay_opacity"
                                                min="0"
                                                max="100"
                                                class="w-full"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Hintergrund</label>
                                            <div class="relative">
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleImageUpload"
                                                    class="hidden"
                                                    id="background-upload"
                                                    ref="fileInput"
                                                />
                                                <label
                                                    for="background-upload"
                                                    class="block border-2 border-dashed border-[#202225] rounded-lg p-6 text-center cursor-pointer hover:border-[#5865f2] transition-colors"
                                                >
                                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <p class="text-sm text-gray-300 font-medium mb-1">Hintergrundbild hochladen</p>
                                                    <p class="text-xs text-gray-400">Klicke hier oder ziehe ein Bild hierher</p>
                                                    <p class="text-xs text-gray-500 mt-1">Max. 5MB, JPG, PNG, GIF</p>
                                                </label>
                                                <div v-if="welcomeForm.card_background_image" class="mt-3 border-2 border-dashed border-[#202225] rounded-lg p-4">
                                                    <img 
                                                        :src="welcomeForm.card_background_image" 
                                                        alt="Vorschau" 
                                                        class="max-w-full max-h-32 mx-auto rounded object-contain"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeBackgroundImage"
                                                        class="mt-2 text-xs text-red-400 hover:text-red-300 underline"
                                                    >
                                                        Bild entfernen
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Speichern -->
                            <div class="flex items-center justify-end pt-4 border-t border-[#202225]">
                                <button
                                    type="submit"
                                    :disabled="welcomeForm.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                                >
                                    {{ welcomeForm.processing ? t('common.saving') : t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Verabschiedungsgrüße Accordion -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden">
                    <!-- Accordion Header -->
                    <div class="flex items-center justify-between p-6">
                        <button
                            @click="goodbyeAccordionOpen = !goodbyeAccordionOpen"
                            class="flex-1 flex items-center gap-4 text-left hover:bg-[#36393f] -m-6 p-6 rounded-lg transition-colors"
                        >
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white">{{ t('welcome.goodbyeSection.title') }}</h3>
                                <p class="text-sm text-gray-400">{{ t('welcome.goodbyeSection.description') }}</p>
                            </div>
                            <svg
                                class="w-5 h-5 text-gray-400 transition-transform flex-shrink-0"
                                :class="{ 'rotate-180': goodbyeAccordionOpen }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <!-- Toggle oben rechts -->
                        <div class="ml-4 flex items-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="goodbyeForm.enabled"
                                    class="sr-only peer"
                                />
                                <div class="w-14 h-7 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#5865f2] shadow-inner"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Accordion Content -->
                    <div v-show="goodbyeAccordionOpen" class="border-t border-[#202225]">
                        <form @submit.prevent="saveGoodbyeConfig" class="p-6 space-y-6">
                            <!-- Kanal-Auswahl -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    {{ t('welcome.goodbyeSection.channelLabel') }}
                                </label>
                                <select
                                    v-model="goodbyeForm.channel_id"
                                    required
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                >
                                    <option value="">Bitte wählen...</option>
                                    <template v-for="channel in channels" :key="channel.id">
                                        <option
                                            v-if="channel.type === 4 && (channel.is_category || channel.type === 4)"
                                            disabled
                                            class="bg-[#2f3136] font-semibold"
                                        >
                                            ── {{ channel.name }} ──
                                        </option>
                                        <option
                                            v-else-if="channel.type === 0"
                                            :value="channel.id"
                                        >
                                            # {{ channel.name }}
                                        </option>
                                    </template>
                                </select>
                                <p v-if="!channels || channels.length === 0" class="text-xs text-red-400 mt-1">
                                    Keine Channels gefunden. Bitte stelle sicher, dass der Bot Zugriff auf die Channels hat.
                                </p>
                            </div>

                            <!-- Nachrichtentyp-Auswahl -->
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">
                                    Nachrichtentyp auswählen
                                </label>
                                <div class="grid grid-cols-3 gap-3">
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="goodbyeMessageType"
                                            value="text"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.goodbyeSection.textMessage') }}</p>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="goodbyeMessageType"
                                            value="embed"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.goodbyeSection.embedMessage') }}</p>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="goodbyeMessageType"
                                            value="card"
                                            class="peer sr-only"
                                        />
                                        <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-gradient-to-br peer-checked:from-[#5865f2]/20 peer-checked:to-[#4752c4]/20 border-[#202225] hover:border-[#36393f] hover:bg-[#36393f]/50">
                                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400 peer-checked:text-[#5865f2] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('welcome.goodbyeSection.goodbyeCard') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Textnachricht -->
                            <div v-if="goodbyeMessageType === 'text'" class="space-y-4">
                                <div>
                                    <textarea
                                        v-model="goodbyeForm.message"
                                        rows="4"
                                        placeholder="{user} hat den Server verlassen."
                                        maxlength="2000"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2] resize-none"
                                    ></textarea>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-xs text-gray-400">
                                            Verfügbare Platzhalter: <code class="bg-[#202225] px-1 rounded">{user}</code>, <code class="bg-[#202225] px-1 rounded">{server}</code>
                                        </p>
                                        <span class="text-xs text-gray-400">{{ goodbyeMessageLength }} / 2000</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Embed -->
                            <div v-if="goodbyeMessageType === 'embed'" class="space-y-4">
                                <!-- Embed Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div class="bg-[#2f3136] rounded-lg p-4 max-w-md">
                                        <!-- Discord-ähnliche Embed-Vorschau -->
                                        <div 
                                            class="rounded border-l-4 p-3"
                                            :style="{ borderLeftColor: goodbyeForm.embed_color || '#ef4444' }"
                                        >
                                            <div class="space-y-2">
                                                <h5 
                                                    v-if="goodbyeForm.embed_title"
                                                    class="text-base font-semibold text-white"
                                                >
                                                    {{ goodbyeForm.embed_title.replace('{user}', 'ibeiros#0').replace('{server}', guild.name) }}
                                                </h5>
                                                <p 
                                                    v-if="goodbyeForm.embed_description"
                                                    class="text-sm text-gray-300 whitespace-pre-wrap"
                                                >
                                                    {{ goodbyeForm.embed_description.replace('{user}', 'ibeiros#0').replace('{server}', guild.name) }}
                                                </p>
                                                <div 
                                                    v-if="goodbyeForm.embed_footer"
                                                    class="flex items-center gap-2 pt-2 border-t border-[#202225]"
                                                >
                                                    <img 
                                                        v-if="guild.icon_url"
                                                        :src="guild.icon_url" 
                                                        :alt="guild.name"
                                                        class="w-4 h-4 rounded-full"
                                                    />
                                                    <span class="text-xs text-gray-400">{{ guild.name }}</span>
                                                    <span class="text-xs text-gray-500">•</span>
                                                    <span class="text-xs text-gray-400">{{ new Date().toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                    <input
                                        type="text"
                                        v-model="goodbyeForm.embed_title"
                                        placeholder="{user} hat den Server verlassen"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Beschreibung</label>
                                    <textarea
                                        v-model="goodbyeForm.embed_description"
                                        rows="3"
                                        placeholder="Wir werden dich vermissen!"
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Farbe</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="goodbyeForm.embed_color"
                                            class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="goodbyeForm.embed_color"
                                            placeholder="#ef4444"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="goodbyeForm.embed_footer"
                                        class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                    />
                                    <label class="text-sm text-gray-300">Footer anzeigen (Server-Name und Zeitstempel)</label>
                                </div>
                            </div>

                            <!-- Abschiedskarte -->
                            <div v-if="goodbyeMessageType === 'card'" class="space-y-4">
                                <!-- Vorschau -->
                                <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                                    <div 
                                        class="relative rounded-lg overflow-hidden"
                                        :style="{
                                            backgroundImage: goodbyeForm.card_background_image ? `url(${goodbyeForm.card_background_image})` : 'none',
                                            backgroundSize: 'cover',
                                            backgroundPosition: 'center',
                                            backgroundColor: goodbyeForm.card_background_image ? 'transparent' : (goodbyeForm.card_background_color || '#000000'),
                                            minHeight: '200px',
                                        }"
                                    >
                                        <div 
                                            class="absolute inset-0"
                                            :style="{ backgroundColor: `rgba(0, 0, 0, ${goodbyeForm.card_overlay_opacity / 100})` }"
                                        ></div>
                                        <div 
                                            class="relative p-6"
                                            :class="{
                                                'flex flex-col items-center justify-center text-center': goodbyeForm.card_avatar_position === 'top',
                                                'flex flex-row items-center justify-center gap-4': goodbyeForm.card_avatar_position === 'left',
                                                'flex flex-row-reverse items-center justify-center gap-4': goodbyeForm.card_avatar_position === 'right',
                                                'flex flex-col-reverse items-center justify-center text-center': goodbyeForm.card_avatar_position === 'bottom',
                                            }"
                                        >
                                            <div 
                                                class="w-20 h-20 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0"
                                                :class="{
                                                    'mb-4': goodbyeForm.card_avatar_position === 'top',
                                                    'mt-4': goodbyeForm.card_avatar_position === 'bottom',
                                                }"
                                            >
                                                <span class="text-white text-2xl font-bold">B</span>
                                            </div>
                                            <div 
                                                :class="{
                                                    'text-center': goodbyeForm.card_avatar_position === 'top' || goodbyeForm.card_avatar_position === 'bottom',
                                                    'text-left': goodbyeForm.card_avatar_position === 'left',
                                                    'text-right': goodbyeForm.card_avatar_position === 'right',
                                                }"
                                            >
                                                <p 
                                                    class="text-lg font-semibold mb-2"
                                                    :style="{ color: goodbyeForm.card_text_color, fontFamily: goodbyeForm.card_font }"
                                                >
                                                    {{ goodbyeForm.card_title.replace('{user.idname}', 'ibeiros#0') }}
                                                </p>
                                                <p 
                                                    class="text-sm"
                                                    :style="{ color: goodbyeForm.card_text_color, fontFamily: goodbyeForm.card_font }"
                                                >
                                                    Member #20
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personalisierung -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-300 mb-4">Personalisiere deine Abschiedskarte</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Schriftart</label>
                                            <input
                                                type="text"
                                                v-model="goodbyeForm.card_font"
                                                placeholder="Suche nach einer Schriftart..."
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                            <input
                                                type="text"
                                                v-model="goodbyeForm.card_title"
                                                placeholder="{user.idname} left the server"
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Avatar-Position</label>
                                            <select
                                                v-model="goodbyeForm.card_avatar_position"
                                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                            >
                                                <option value="top">Über dem Text</option>
                                                <option value="left">Links vom Text</option>
                                                <option value="right">Rechts vom Text</option>
                                                <option value="bottom">Unter dem Text</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Textfarbe</label>
                                            <div class="grid grid-cols-5 gap-2 mb-2">
                                                <button
                                                    v-for="color in colorOptions"
                                                    :key="color.value"
                                                    type="button"
                                                    @click="goodbyeForm.card_text_color = color.value"
                                                    :class="[
                                                        'h-8 rounded border-2 transition-all',
                                                        goodbyeForm.card_text_color === color.value ? 'border-white scale-110' : 'border-[#202225] hover:border-gray-500'
                                                    ]"
                                                    :style="{ background: color.value === 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ? color.value : color.value }"
                                                    :title="color.name"
                                                ></button>
                                            </div>
                                            <input
                                                type="color"
                                                v-model="goodbyeForm.card_text_color"
                                                class="w-full h-10 rounded border border-[#202225] cursor-pointer"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Hintergrundfarbe</label>
                                            <div class="grid grid-cols-5 gap-2 mb-2">
                                                <button
                                                    v-for="color in colorOptions"
                                                    :key="color.value"
                                                    type="button"
                                                    @click="goodbyeForm.card_background_color = color.value"
                                                    :class="[
                                                        'h-8 rounded border-2 transition-all',
                                                        goodbyeForm.card_background_color === color.value ? 'border-white scale-110' : 'border-[#202225] hover:border-gray-500'
                                                    ]"
                                                    :style="{ background: color.value === 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ? color.value : color.value }"
                                                    :title="color.name"
                                                ></button>
                                            </div>
                                            <input
                                                type="color"
                                                v-model="goodbyeForm.card_background_color"
                                                class="w-full h-10 rounded border border-[#202225] cursor-pointer"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                                Deckkraft der Überlagerung: {{ goodbyeForm.card_overlay_opacity }}%
                                            </label>
                                            <input
                                                type="range"
                                                v-model="goodbyeForm.card_overlay_opacity"
                                                min="0"
                                                max="100"
                                                class="w-full"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Hintergrund</label>
                                            <div class="relative">
                                                <input
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleGoodbyeImageUpload"
                                                    class="hidden"
                                                    id="goodbye-background-upload"
                                                    ref="goodbyeFileInput"
                                                />
                                                <label
                                                    for="goodbye-background-upload"
                                                    class="block border-2 border-dashed border-[#202225] rounded-lg p-6 text-center cursor-pointer hover:border-[#5865f2] transition-colors"
                                                >
                                                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <p class="text-sm text-gray-300 font-medium mb-1">Hintergrundbild hochladen</p>
                                                    <p class="text-xs text-gray-400">Klicke hier oder ziehe ein Bild hierher</p>
                                                    <p class="text-xs text-gray-500 mt-1">Max. 5MB, JPG, PNG, GIF</p>
                                                </label>
                                                <div v-if="goodbyeForm.card_background_image" class="mt-3 border-2 border-dashed border-[#202225] rounded-lg p-4">
                                                    <img 
                                                        :src="goodbyeForm.card_background_image" 
                                                        alt="Vorschau" 
                                                        class="max-w-full max-h-32 mx-auto rounded object-contain"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeGoodbyeBackgroundImage"
                                                        class="mt-2 text-xs text-red-400 hover:text-red-300 underline"
                                                    >
                                                        Bild entfernen
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Speichern -->
                            <div class="flex items-center justify-end pt-4 border-t border-[#202225]">
                                <button
                                    type="submit"
                                    :disabled="goodbyeForm.processing"
                                    class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                                >
                                    {{ goodbyeForm.processing ? t('common.saving') : t('common.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>



