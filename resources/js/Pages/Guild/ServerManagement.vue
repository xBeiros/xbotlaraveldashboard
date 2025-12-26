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
                <!-- Bot-Personalisierung -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <h2 class="text-lg font-semibold text-white mb-4">{{ $t('serverManagement.botPersonalization.title') }}</h2>
                    <p class="text-sm text-gray-400 mb-4">{{ $t('serverManagement.botPersonalization.description') }}</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.botPersonalization.botName') }}
                            </label>
                            <input
                                type="text"
                                v-model="personalizationForm.username"
                                :placeholder="$t('serverManagement.botPersonalization.botNamePlaceholder')"
                                maxlength="32"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                            <p class="text-xs text-gray-400 mt-1">{{ $t('serverManagement.botPersonalization.botNameHelp') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.botPersonalization.avatar') }}
                            </label>
                            <div class="flex items-center gap-4">
                                <div v-if="avatarPreview" class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#5865f2]">
                                    <img :src="avatarPreview" alt="Avatar Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        @change="handleAvatarChange"
                                        accept="image/png,image/jpeg,image/jpg,image/gif"
                                        class="hidden"
                                        ref="avatarInput"
                                    />
                                    <button
                                        type="button"
                                        @click="$refs.avatarInput.click()"
                                        class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('serverManagement.botPersonalization.selectAvatar') }}
                                    </button>
                                    <button
                                        v-if="avatarPreview"
                                        type="button"
                                        @click="removeAvatar"
                                        class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('common.delete') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $t('serverManagement.botPersonalization.avatarHelp') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.botPersonalization.banner') }}
                            </label>
                            <div class="flex items-center gap-4">
                                <div v-if="bannerPreview" class="w-40 h-20 rounded-lg overflow-hidden border-2 border-[#5865f2]">
                                    <img :src="bannerPreview" alt="Banner Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        @change="handleBannerChange"
                                        accept="image/png,image/jpeg,image/jpg,image/gif"
                                        class="hidden"
                                        ref="bannerInput"
                                    />
                                    <button
                                        type="button"
                                        @click="$refs.bannerInput.click()"
                                        class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('serverManagement.botPersonalization.selectBanner') }}
                                    </button>
                                    <button
                                        v-if="bannerPreview"
                                        type="button"
                                        @click="removeBanner"
                                        class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('common.delete') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $t('serverManagement.botPersonalization.bannerHelp') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            @click="savePersonalization"
                            :disabled="personalizationForm.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                        >
                            {{ personalizationForm.processing ? $t('common.saving') : $t('common.save') }}
                        </button>
                    </div>
                </div>

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
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    guildModel: Object,
    botInfo: {
        type: Object,
        default: () => ({})
    },
});

const form = useForm({
    language: props.guildModel?.language || 'de',
});

const personalizationForm = useForm({
    username: props.botInfo?.username || '',
    avatar: null,
    banner: null,
});

const avatarPreview = ref(props.botInfo?.avatar || null);
const bannerPreview = ref(props.botInfo?.banner || null);
const avatarInput = ref(null);
const bannerInput = ref(null);

function handleAvatarChange(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 8 * 1024 * 1024) {
            alert(t('serverManagement.botPersonalization.avatarSizeError'));
            return;
        }
        personalizationForm.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function handleBannerChange(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 8 * 1024 * 1024) {
            alert(t('serverManagement.botPersonalization.bannerSizeError'));
            return;
        }
        personalizationForm.banner = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeAvatar() {
    personalizationForm.avatar = null;
    avatarPreview.value = null;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
}

function removeBanner() {
    personalizationForm.banner = null;
    bannerPreview.value = null;
    if (bannerInput.value) {
        bannerInput.value.value = '';
    }
}

function savePersonalization() {
    personalizationForm.post(route('guild.bot-personalization.update', { guild: props.guild.id }), {
        preserveScroll: true,
        forceFormData: true,
    });
}

function saveSettings() {
    form.put(route('guild.server-management.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}
</script>
