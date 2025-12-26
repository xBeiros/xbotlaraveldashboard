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
                            <div class="flex items-start gap-4">
                                <div v-if="avatarPreview" class="flex-shrink-0">
                                    <div class="w-32 h-32 rounded-full overflow-hidden border-2 border-[#5865f2] bg-[#1a1b1e] relative">
                                        <img 
                                            :src="avatarPreview" 
                                            alt="Avatar Preview" 
                                            class="w-full h-full object-cover"
                                            :style="avatarTransform"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2 text-center">{{ $t('serverManagement.botPersonalization.preview') }}</p>
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
                                        class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors text-sm font-medium mb-2"
                                    >
                                        {{ $t('serverManagement.botPersonalization.selectAvatar') }}
                                    </button>
                                    <button
                                        v-if="avatarPreview"
                                        type="button"
                                        @click="removeAvatar"
                                        class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium mb-2"
                                    >
                                        {{ $t('common.delete') }}
                                    </button>
                                    
                                    <!-- Avatar Editor -->
                                    <div v-if="avatarEditorOpen" class="mt-4 space-y-3">
                                        <div class="bg-[#1a1b1e] rounded-lg p-4 border border-[#202225]">
                                            <div class="relative w-full h-64 bg-[#0a0b0c] rounded-lg overflow-hidden cursor-move"
                                                 @mousedown="startAvatarDrag"
                                                 @mousemove="dragAvatar"
                                                 @mouseup="stopAvatarDrag"
                                                 @mouseleave="stopAvatarDrag"
                                                 ref="avatarEditorContainer"
                                            >
                                                <img 
                                                    :src="avatarOriginal" 
                                                    alt="Avatar Editor" 
                                                    class="absolute inset-0"
                                                    :style="avatarEditorTransform"
                                                    draggable="false"
                                                />
                                            </div>
                                            <div class="mt-3 flex items-center gap-4">
                                                <div class="flex-1">
                                                    <label class="text-xs text-gray-400 mb-1 block">{{ $t('serverManagement.botPersonalization.zoom') }}</label>
                                                    <input
                                                        type="range"
                                                        v-model="avatarZoom"
                                                        min="25"
                                                        max="300"
                                                        step="5"
                                                        class="w-full"
                                                        @input="updateAvatarTransform"
                                                    />
                                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                                        <span>25%</span>
                                                        <span>{{ avatarZoom }}%</span>
                                                        <span>300%</span>
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="resetAvatarTransform"
                                                    class="px-3 py-1.5 bg-[#36393f] hover:bg-[#40444b] text-white rounded text-xs font-medium transition-colors"
                                                >
                                                    {{ $t('serverManagement.botPersonalization.reset') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        v-if="avatarPreview && !avatarEditorOpen"
                                        type="button"
                                        @click="openAvatarEditor"
                                        class="mt-2 px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('serverManagement.botPersonalization.editAvatar') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $t('serverManagement.botPersonalization.avatarHelp') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $t('serverManagement.botPersonalization.banner') }}
                            </label>
                            <div class="flex items-start gap-4">
                                <div v-if="bannerPreview" class="flex-shrink-0">
                                    <div class="w-48 h-24 rounded-lg overflow-hidden border-2 border-[#5865f2] bg-[#1a1b1e] relative">
                                        <img 
                                            :src="bannerPreview" 
                                            alt="Banner Preview" 
                                            class="w-full h-full object-cover"
                                            :style="bannerTransform"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2 text-center">{{ $t('serverManagement.botPersonalization.preview') }}</p>
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
                                        class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors text-sm font-medium mb-2"
                                    >
                                        {{ $t('serverManagement.botPersonalization.selectBanner') }}
                                    </button>
                                    <button
                                        v-if="bannerPreview"
                                        type="button"
                                        @click="removeBanner"
                                        class="ml-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium mb-2"
                                    >
                                        {{ $t('common.delete') }}
                                    </button>
                                    
                                    <!-- Banner Editor -->
                                    <div v-if="bannerEditorOpen" class="mt-4 space-y-3">
                                        <div class="bg-[#1a1b1e] rounded-lg p-4 border border-[#202225]">
                                            <div class="relative w-full h-48 bg-[#0a0b0c] rounded-lg overflow-hidden cursor-move"
                                                 @mousedown="startBannerDrag"
                                                 @mousemove="dragBanner"
                                                 @mouseup="stopBannerDrag"
                                                 @mouseleave="stopBannerDrag"
                                                 ref="bannerEditorContainer"
                                            >
                                                <img 
                                                    :src="bannerOriginal" 
                                                    alt="Banner Editor" 
                                                    class="absolute inset-0"
                                                    :style="bannerEditorTransform"
                                                    draggable="false"
                                                />
                                            </div>
                                            <div class="mt-3 flex items-center gap-4">
                                                <div class="flex-1">
                                                    <label class="text-xs text-gray-400 mb-1 block">{{ $t('serverManagement.botPersonalization.zoom') }}</label>
                                                    <input
                                                        type="range"
                                                        v-model="bannerZoom"
                                                        min="25"
                                                        max="300"
                                                        step="5"
                                                        class="w-full"
                                                        @input="updateBannerTransform"
                                                    />
                                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                                        <span>25%</span>
                                                        <span>{{ bannerZoom }}%</span>
                                                        <span>300%</span>
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="resetBannerTransform"
                                                    class="px-3 py-1.5 bg-[#36393f] hover:bg-[#40444b] text-white rounded text-xs font-medium transition-colors"
                                                >
                                                    {{ $t('serverManagement.botPersonalization.reset') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        v-if="bannerPreview && !bannerEditorOpen"
                                        type="button"
                                        @click="openBannerEditor"
                                        class="mt-2 px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded-lg transition-colors text-sm font-medium"
                                    >
                                        {{ $t('serverManagement.botPersonalization.editBanner') }}
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
import { ref, computed } from 'vue';

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

// Avatar Editor
const avatarEditorOpen = ref(false);
const avatarOriginal = ref(null);
const avatarZoom = ref(100);
const avatarPosition = ref({ x: 0, y: 0 });
const avatarDragging = ref(false);
const avatarDragStart = ref({ x: 0, y: 0 });
const avatarEditorContainer = ref(null);

// Banner Editor
const bannerEditorOpen = ref(false);
const bannerOriginal = ref(null);
const bannerZoom = ref(100);
const bannerPosition = ref({ x: 0, y: 0 });
const bannerDragging = ref(false);
const bannerDragStart = ref({ x: 0, y: 0 });
const bannerEditorContainer = ref(null);

const avatarTransform = computed(() => {
    const scale = avatarZoom.value / 100;
    return {
        transform: `scale(${scale}) translate(${avatarPosition.value.x / scale}px, ${avatarPosition.value.y / scale}px)`,
        transformOrigin: 'center center',
    };
});

const bannerTransform = computed(() => {
    const scale = bannerZoom.value / 100;
    return {
        transform: `scale(${scale}) translate(${bannerPosition.value.x / scale}px, ${bannerPosition.value.y / scale}px)`,
        transformOrigin: 'center center',
    };
});

const avatarEditorTransform = computed(() => {
    const scale = avatarZoom.value / 100;
    return {
        transform: `scale(${scale}) translate(${avatarPosition.value.x / scale}px, ${avatarPosition.value.y / scale}px)`,
        transformOrigin: 'center center',
        width: '100%',
        height: '100%',
        objectFit: 'cover',
    };
});

const bannerEditorTransform = computed(() => {
    const scale = bannerZoom.value / 100;
    return {
        transform: `scale(${scale}) translate(${bannerPosition.value.x / scale}px, ${bannerPosition.value.y / scale}px)`,
        transformOrigin: 'center center',
        width: '100%',
        height: '100%',
        objectFit: 'cover',
    };
});

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
            avatarOriginal.value = e.target.result;
            avatarPreview.value = e.target.result;
            avatarZoom.value = 100;
            avatarPosition.value = { x: 0, y: 0 };
        };
        reader.readAsDataURL(file);
    }
}

function openAvatarEditor() {
    // Wenn kein avatarOriginal vorhanden ist, aber eine Datei im Formular, lade sie
    if (!avatarOriginal.value && personalizationForm.avatar) {
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarOriginal.value = e.target.result;
            avatarEditorOpen.value = true;
        };
        reader.readAsDataURL(personalizationForm.avatar);
    } else if (avatarOriginal.value) {
        avatarEditorOpen.value = true;
    } else if (avatarPreview.value) {
        // Falls nur eine Vorschau vorhanden ist, verwende diese
        avatarOriginal.value = avatarPreview.value;
        avatarEditorOpen.value = true;
    } else {
        // Wenn nichts vorhanden ist, öffne den File-Input
        if (avatarInput.value) {
            avatarInput.value.click();
        }
    }
}

function startAvatarDrag(event) {
    if (event.target.tagName === 'INPUT') return;
    avatarDragging.value = true;
    const rect = avatarEditorContainer.value?.getBoundingClientRect();
    if (rect) {
        avatarDragStart.value = {
            x: event.clientX - avatarPosition.value.x,
            y: event.clientY - avatarPosition.value.y,
        };
    }
}

function dragAvatar(event) {
    if (!avatarDragging.value) return;
    avatarPosition.value = {
        x: event.clientX - avatarDragStart.value.x,
        y: event.clientY - avatarDragStart.value.y,
    };
}

function stopAvatarDrag() {
    avatarDragging.value = false;
}

function updateAvatarTransform() {
    // Transform wird automatisch über computed property aktualisiert
}

function resetAvatarTransform() {
    avatarZoom.value = 100;
    avatarPosition.value = { x: 0, y: 0 };
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
            bannerOriginal.value = e.target.result;
            bannerPreview.value = e.target.result;
            bannerZoom.value = 100;
            bannerPosition.value = { x: 0, y: 0 };
        };
        reader.readAsDataURL(file);
    }
}

function openBannerEditor() {
    // Wenn kein bannerOriginal vorhanden ist, aber eine Datei im Formular, lade sie
    if (!bannerOriginal.value && personalizationForm.banner) {
        const reader = new FileReader();
        reader.onload = (e) => {
            bannerOriginal.value = e.target.result;
            bannerEditorOpen.value = true;
        };
        reader.readAsDataURL(personalizationForm.banner);
    } else if (bannerOriginal.value) {
        bannerEditorOpen.value = true;
    } else if (bannerPreview.value) {
        // Falls nur eine Vorschau vorhanden ist, verwende diese
        bannerOriginal.value = bannerPreview.value;
        bannerEditorOpen.value = true;
    }
}

function startBannerDrag(event) {
    if (event.target.tagName === 'INPUT') return;
    bannerDragging.value = true;
    const rect = bannerEditorContainer.value?.getBoundingClientRect();
    if (rect) {
        bannerDragStart.value = {
            x: event.clientX - bannerPosition.value.x,
            y: event.clientY - bannerPosition.value.y,
        };
    }
}

function dragBanner(event) {
    if (!bannerDragging.value) return;
    bannerPosition.value = {
        x: event.clientX - bannerDragStart.value.x,
        y: event.clientY - bannerDragStart.value.y,
    };
}

function stopBannerDrag() {
    bannerDragging.value = false;
}

function updateBannerTransform() {
    // Transform wird automatisch über computed property aktualisiert
}

function resetBannerTransform() {
    bannerZoom.value = 100;
    bannerPosition.value = { x: 0, y: 0 };
}

function removeAvatar() {
    personalizationForm.avatar = null;
    avatarPreview.value = null;
    avatarOriginal.value = null;
    avatarEditorOpen.value = false;
    avatarZoom.value = 100;
    avatarPosition.value = { x: 0, y: 0 };
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
}

function removeBanner() {
    personalizationForm.banner = null;
    bannerPreview.value = null;
    bannerOriginal.value = null;
    bannerEditorOpen.value = false;
    bannerZoom.value = 100;
    bannerPosition.value = { x: 0, y: 0 };
    if (bannerInput.value) {
        bannerInput.value.value = '';
    }
}

async function savePersonalization() {
    // Speichere das Original-Bild, damit wir es nach dem Speichern behalten können
    const originalAvatarData = avatarOriginal.value;
    const originalBannerData = bannerOriginal.value;
    
    // Wenn Avatar vorhanden ist, rendere das transformierte Bild (auch wenn keine Transformation)
    if (avatarOriginal.value && personalizationForm.avatar) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        await new Promise((resolve, reject) => {
            img.onload = () => {
                const targetSize = 128; // Discord Avatar Größe
                canvas.width = targetSize;
                canvas.height = targetSize;
                
                // Hole die tatsächliche Editor-Container-Größe
                const editorContainer = avatarEditorContainer.value;
                const editorSize = editorContainer ? Math.min(editorContainer.offsetWidth, editorContainer.offsetHeight) : 256;
                
                // Berechne die Skalierung basierend auf Zoom
                const zoomScale = avatarZoom.value / 100;
                
                // Berechne das Seitenverhältnis
                const imgAspect = img.width / img.height;
                
                // Berechne die Bildgröße, die im Editor angezeigt wird (object-fit: cover)
                // Im Editor wird das Bild so skaliert, dass es den Container vollständig füllt
                let displayWidth, displayHeight;
                if (imgAspect >= 1) {
                    // Bild ist quadratisch oder breiter - Höhe bestimmt die Größe
                    displayHeight = editorSize;
                    displayWidth = displayHeight * imgAspect;
                } else {
                    // Bild ist höher - Breite bestimmt die Größe
                    displayWidth = editorSize;
                    displayHeight = displayWidth / imgAspect;
                }
                
                // Skaliere mit Zoom
                const scaledDisplayWidth = displayWidth * zoomScale;
                const scaledDisplayHeight = displayHeight * zoomScale;
                
                // Berechne die tatsächliche Bildgröße im Canvas (basierend auf skaliertem Display)
                const scaledWidth = img.width * (scaledDisplayWidth / displayWidth);
                const scaledHeight = img.height * (scaledDisplayHeight / displayHeight);
                
                // Skaliere die Position vom Editor auf Canvas
                // Die Position ist relativ zum Editor-Container
                const positionScale = targetSize / editorSize;
                const offsetX = avatarPosition.value.x * positionScale;
                const offsetY = avatarPosition.value.y * positionScale;
                
                // Berechne die Position im Canvas (zentriert + Offset)
                const x = (targetSize / 2) - (scaledWidth / 2) + offsetX;
                const y = (targetSize / 2) - (scaledHeight / 2) + offsetY;
                
                // Zeichne das Bild
                ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
                
                canvas.toBlob((blob) => {
                    const file = new File([blob], 'avatar.png', { type: 'image/png' });
                    personalizationForm.avatar = file;
                    resolve();
                }, 'image/png');
            };
            img.onerror = reject;
            img.src = avatarOriginal.value;
        });
    }
    
    // Wenn Banner vorhanden ist, rendere das transformierte Bild (auch wenn keine Transformation)
    if (bannerOriginal.value && personalizationForm.banner) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        await new Promise((resolve, reject) => {
            img.onload = () => {
                const targetWidth = 960; // Discord Banner Breite
                const targetHeight = 540; // Discord Banner Höhe
                canvas.width = targetWidth;
                canvas.height = targetHeight;
                
                // Hole die tatsächliche Editor-Container-Größe
                const editorContainer = bannerEditorContainer.value;
                const editorWidth = editorContainer ? editorContainer.offsetWidth : 384;
                const editorHeight = editorContainer ? editorContainer.offsetHeight : 192;
                
                // Berechne die Skalierung basierend auf Zoom
                const zoomScale = bannerZoom.value / 100;
                
                // Berechne das Seitenverhältnis
                const imgAspect = img.width / img.height;
                const editorAspect = editorWidth / editorHeight;
                
                // Berechne die Bildgröße, die im Editor angezeigt wird (object-fit: cover)
                let displayWidth, displayHeight;
                if (imgAspect > editorAspect) {
                    // Bild ist breiter - Höhe bestimmt die Größe
                    displayHeight = editorHeight;
                    displayWidth = displayHeight * imgAspect;
                } else {
                    // Bild ist höher - Breite bestimmt die Größe
                    displayWidth = editorWidth;
                    displayHeight = displayWidth / imgAspect;
                }
                
                // Skaliere mit Zoom
                const scaledDisplayWidth = displayWidth * zoomScale;
                const scaledDisplayHeight = displayHeight * zoomScale;
                
                // Berechne die tatsächliche Bildgröße im Canvas (basierend auf skaliertem Display)
                const scaledWidth = img.width * (scaledDisplayWidth / displayWidth);
                const scaledHeight = img.height * (scaledDisplayHeight / displayHeight);
                
                // Skaliere die Position vom Editor auf Canvas
                const positionScaleX = targetWidth / editorWidth;
                const positionScaleY = targetHeight / editorHeight;
                const offsetX = bannerPosition.value.x * positionScaleX;
                const offsetY = bannerPosition.value.y * positionScaleY;
                
                // Berechne die Position im Canvas (zentriert + Offset)
                const x = (targetWidth / 2) - (scaledWidth / 2) + offsetX;
                const y = (targetHeight / 2) - (scaledHeight / 2) + offsetY;
                
                // Zeichne das Bild
                ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
                
                canvas.toBlob((blob) => {
                    const file = new File([blob], 'banner.png', { type: 'image/png' });
                    personalizationForm.banner = file;
                    resolve();
                }, 'image/png');
            };
            img.onerror = reject;
            img.src = bannerOriginal.value;
        });
    }
    
    personalizationForm.post(route('guild.bot-personalization.update', { guild: props.guild.id }), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Nach erfolgreichem Speichern: Stelle das Original-Bild wieder her, damit man weiter bearbeiten kann
            if (originalAvatarData) {
                avatarOriginal.value = originalAvatarData;
                // Aktualisiere die Vorschau mit dem transformierten Bild
                if (personalizationForm.avatar) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        avatarPreview.value = e.target.result;
                    };
                    reader.readAsDataURL(personalizationForm.avatar);
                }
            }
            if (originalBannerData) {
                bannerOriginal.value = originalBannerData;
                // Aktualisiere die Vorschau mit dem transformierten Bild
                if (personalizationForm.banner) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        bannerPreview.value = e.target.result;
                    };
                    reader.readAsDataURL(personalizationForm.banner);
                }
            }
        },
    });
}

function saveSettings() {
    form.put(route('guild.server-management.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}
</script>
