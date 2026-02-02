<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    addOns: { type: Object, default: () => ({}) },
    channels: Array,
    roles: Array,
    levelingConfig: Object,
    rankCardConfig: Object,
    roleRewards: Array,
});

// Hauptformular
const form = useForm({
    enabled: props.levelingConfig?.enabled ?? false,
    xp_rate: parseFloat(props.levelingConfig?.xp_rate) || 1.00,
    min_xp: props.levelingConfig?.min_xp ?? 15,
    max_xp: props.levelingConfig?.max_xp ?? 25,
    cooldown_seconds: props.levelingConfig?.cooldown_seconds ?? 60,
    level_up_channel_id: props.levelingConfig?.level_up_channel_id,
    level_up_type: props.levelingConfig?.level_up_type ?? 'current_channel',
    level_up_message: props.levelingConfig?.level_up_message ?? 'GG {player}, you just advanced to level {level}!',
    role_reward_type: props.levelingConfig?.role_reward_type ?? 'stack',
    remove_role_on_xp_loss: props.levelingConfig?.remove_role_on_xp_loss ?? false,
    excluded_roles_type: props.levelingConfig?.excluded_roles_type ?? 'allow_all_except',
    excluded_roles: props.levelingConfig?.excluded_roles ?? [],
    excluded_channels_type: props.levelingConfig?.excluded_channels_type ?? 'allow_all_except',
    excluded_channels: props.levelingConfig?.excluded_channels ?? [],
});

// Rank Card Formular
const rankCardForm = useForm({
    background_type: props.rankCardConfig?.background_type ?? 'color',
    background_color: props.rankCardConfig?.background_color ?? '#000000',
    background_image: props.rankCardConfig?.background_image,
    custom_background_url: props.rankCardConfig?.custom_background_url,
    overlay_opacity: props.rankCardConfig?.overlay_opacity ?? 0,
    text_color: props.rankCardConfig?.text_color ?? '#ffffff',
    rank_text_color: props.rankCardConfig?.rank_text_color ?? '#ffffff',
    level_text_color: props.rankCardConfig?.level_text_color ?? '#5865f2',
    xp_text_color: props.rankCardConfig?.xp_text_color ?? '#ffffff',
    progress_bar_color: props.rankCardConfig?.progress_bar_color ?? '#5865f2',
    welcome_message: props.rankCardConfig?.welcome_message ?? '',
});

// Accordion States
const levelingOpen = ref(true);
const xpRateOpen = ref(false);
const levelUpAnnouncementOpen = ref(false);
const roleRewardsOpen = ref(false);
const excludedRolesOpen = ref(false);
const excludedChannelsOpen = ref(false);
const rankCardOpen = ref(false);

// Role Reward Management
const newRoleReward = ref({ level: '', role_id: '' });
const showRoleRewardModal = ref(false);

// Excluded Roles/Channels
const excludedRolesList = ref(props.levelingConfig?.excluded_roles ?? []);
const excludedChannelsList = ref(props.levelingConfig?.excluded_channels ?? []);
const excludedRoleSelectorOpen = ref(false);
const excludedChannelSelectorOpen = ref(false);

// Rank Card Modal
const showRankCardModal = ref(false);

// Dropdown Styles
const excludedRoleDropdownStyle = ref({});
const excludedChannelDropdownStyle = ref({});

function updateExcludedRoleDropdownPosition() {
    if (!excludedRoleSelectorOpen.value) return;
    nextTick(() => {
        const button = document.querySelector('[data-excluded-role-selector] button');
        if (button) {
            const rect = button.getBoundingClientRect();
            excludedRoleDropdownStyle.value = {
                top: `${rect.bottom + 4}px`,
                left: `${rect.left}px`,
                width: `${rect.width}px`,
            };
        }
    });
}

function updateExcludedChannelDropdownPosition() {
    if (!excludedChannelSelectorOpen.value) return;
    nextTick(() => {
        const button = document.querySelector('[data-excluded-channel-selector] button');
        if (button) {
            const rect = button.getBoundingClientRect();
            excludedChannelDropdownStyle.value = {
                top: `${rect.bottom + 4}px`,
                left: `${rect.left}px`,
                width: `${rect.width}px`,
            };
        }
    });
}

watch(excludedRoleSelectorOpen, (isOpen) => {
    if (isOpen) {
        nextTick(() => updateExcludedRoleDropdownPosition());
    }
});

watch(excludedChannelSelectorOpen, (isOpen) => {
    if (isOpen) {
        nextTick(() => updateExcludedChannelDropdownPosition());
    }
});

function addExcludedRole(roleId) {
    if (!excludedRolesList.value.includes(roleId)) {
        excludedRolesList.value.push(roleId);
        form.excluded_roles = excludedRolesList.value;
    }
    excludedRoleSelectorOpen.value = false;
}

function removeExcludedRole(roleId) {
    excludedRolesList.value = excludedRolesList.value.filter(id => id !== roleId);
    form.excluded_roles = excludedRolesList.value;
}

function addExcludedChannel(channelId) {
    if (!excludedChannelsList.value.includes(channelId)) {
        excludedChannelsList.value.push(channelId);
        form.excluded_channels = excludedChannelsList.value;
    }
    excludedChannelSelectorOpen.value = false;
}

function removeExcludedChannel(channelId) {
    excludedChannelsList.value = excludedChannelsList.value.filter(id => id !== channelId);
    form.excluded_channels = excludedChannelsList.value;
}

function getRoleName(roleId) {
    const role = props.roles.find(r => r.id === roleId);
    return role ? role.name : 'Unbekannt';
}

function getChannelName(channelId) {
    const channel = props.channels.find(c => c.id === channelId);
    return channel ? channel.name : 'Unbekannt';
}

function getRoleColor(roleId) {
    const role = props.roles.find(r => r.id === roleId);
    if (!role) return '#ffffff';
    if (role.color && role.color > 0) {
        const hex = role.color.toString(16).padStart(6, '0');
        return `#${hex}`;
    }
    return '#ffffff';
}

function addRoleReward() {
    if (newRoleReward.value.level && newRoleReward.value.role_id) {
        router.post(route('guild.role-rewards.store', { guild: props.guild.id }), {
            level: parseInt(newRoleReward.value.level),
            role_id: newRoleReward.value.role_id,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                newRoleReward.value = { level: '', role_id: '' };
                showRoleRewardModal.value = false;
            },
        });
    }
}

function deleteRoleReward(id) {
    router.delete(route('guild.role-rewards.delete', { guild: props.guild.id, id }), {
        preserveScroll: true,
    });
}

function saveLeveling() {
    form.put(route('guild.leveling.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}

function saveRankCard() {
    rankCardForm.put(route('guild.rank-card.update', { guild: props.guild.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showRankCardModal.value = false;
        },
    });
}

// Click Outside Handler
function handleClickOutside(event) {
    const excludedRoleSelector = event.target.closest('[data-excluded-role-selector]');
    const excludedChannelSelector = event.target.closest('[data-excluded-channel-selector]');
    
    if (!excludedRoleSelector) {
        excludedRoleSelectorOpen.value = false;
    }
    if (!excludedChannelSelector) {
        excludedChannelSelectorOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head :title="`${guild.name} - ${t('leveling.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns ?? {}">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">{{ t('leveling.title') }}</h1>

            <form @submit.prevent="saveLeveling" class="space-y-6">
                <!-- Haupt-Accordion: Aufleveln -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] overflow-hidden">
                    <div class="flex items-center justify-between p-6">
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-white mb-1">{{ t('leveling.title') }}</h2>
                            <p class="text-sm text-gray-400">{{ t('leveling.description') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.enabled"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5865f2]"></div>
                            </label>
                            <button
                                type="button"
                                @click="levelingOpen = !levelingOpen"
                                class="text-gray-400 hover:text-white transition-colors"
                            >
                                <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': levelingOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div v-show="levelingOpen && form.enabled" class="border-t border-[#202225] p-6 space-y-4">
                        <!-- XP-Rate Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-hidden">
                            <button
                                type="button"
                                @click="xpRateOpen = !xpRateOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-semibold text-white">{{ t('leveling.xpRate.title') }}</span>
                                    <span class="text-xs">👑</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': xpRateOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="xpRateOpen" class="border-t border-[#202225] p-6 space-y-6">
                                <p class="text-sm text-gray-400">{{ t('leveling.xpRate.description') }}</p>
                                
                                <!-- XP-Rate Slider -->
                                <div class="relative">
                                    <label class="block text-sm font-medium text-gray-300 mb-3">{{ t('leveling.xpRate.multiplier') }}</label>
                                    <div class="relative">
                                        <input
                                            type="range"
                                            v-model.number="form.xp_rate"
                                            min="0.25"
                                            max="3"
                                            step="0.25"
                                            class="w-full h-2 bg-[#202225] rounded-lg appearance-none cursor-pointer slider-custom"
                                              :style="{
                                                  background: `linear-gradient(to right, #5865f2 0%, #5865f2 ${((Number(form.xp_rate || 1.00) - 0.25) / (3 - 0.25)) * 100}%, #202225 ${((Number(form.xp_rate || 1.00) - 0.25) / (3 - 0.25)) * 100}%, #202225 100%)`
                                              }"
                                        />
                                    </div>
                                    <div class="text-center mt-4">
                                        <span class="text-2xl font-bold text-white">x{{ Number(form.xp_rate || 1.00).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Min/Max XP -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.xpRate.minXp') }}</label>
                                        <input
                                            type="number"
                                            v-model.number="form.min_xp"
                                            min="1"
                                            max="100"
                                            class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.xpRate.maxXp') }}</label>
                                        <input
                                            type="number"
                                            v-model.number="form.max_xp"
                                            min="1"
                                            max="100"
                                            class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>

                                <!-- Cooldown -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.xpRate.cooldown') }}</label>
                                    <input
                                        type="number"
                                        v-model.number="form.cooldown_seconds"
                                        min="0"
                                        max="300"
                                        class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                    <p class="text-xs text-gray-400 mt-1">{{ t('leveling.xpRate.cooldownDescription') }}</p>
                                </div>

                                <!-- Level-Informationen -->
                                <div class="bg-[#1a1b1e] rounded-lg p-4 border border-[#202225]">
                                    <h4 class="text-sm font-medium text-gray-300 mb-3">{{ t('leveling.xpRate.levelInfo') }}</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between text-gray-400">
                                            <span>{{ t('leveling.xpRate.averageXp') }}:</span>
                                            <span class="text-white font-semibold">{{ Math.round((form.min_xp + form.max_xp) / 2 * Number(form.xp_rate || 1.00)) }} XP</span>
                                        </div>
                                        <div class="flex justify-between text-gray-400">
                                            <span>{{ t('leveling.xpRate.xpForLevel', { level: 5 }) }}:</span>
                                            <span class="text-white font-semibold">{{ Math.ceil(Math.pow(5 / 0.1, 2)) }} XP</span>
                                        </div>
                                        <div class="flex justify-between text-gray-400">
                                            <span>{{ t('leveling.xpRate.xpForLevel', { level: 10 }) }}:</span>
                                            <span class="text-white font-semibold">{{ Math.ceil(Math.pow(10 / 0.1, 2)) }} XP</span>
                                        </div>
                                        <div class="flex justify-between text-gray-400">
                                            <span>{{ t('leveling.xpRate.xpForLevel', { level: 20 }) }}:</span>
                                            <span class="text-white font-semibold">{{ Math.ceil(Math.pow(20 / 0.1, 2)) }} XP</span>
                                        </div>
                                        <div class="border-t border-[#202225] pt-2 mt-2">
                                            <div class="flex justify-between text-gray-400">
                                                <span>{{ t('leveling.xpRate.estimatedMessages', { level: 10 }) }}:</span>
                                                <span class="text-white font-semibold">~{{ Math.ceil(Math.pow(10 / 0.1, 2) / ((form.min_xp + form.max_xp) / 2 * Number(form.xp_rate || 1.00))) }} {{ t('leveling.xpRate.messages') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Level-Up Ankündigung Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-hidden">
                            <button
                                type="button"
                                @click="levelUpAnnouncementOpen = !levelUpAnnouncementOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <span class="text-lg font-semibold text-white">{{ t('leveling.levelUpAnnouncement.title') }}</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': levelUpAnnouncementOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="levelUpAnnouncementOpen" class="border-t border-[#202225] p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.levelUpAnnouncement.type') }}</label>
                                    <select
                                        v-model="form.level_up_type"
                                        class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    >
                                        <option value="current_channel">{{ t('leveling.levelUpAnnouncement.currentChannel') }}</option>
                                        <option value="channel">{{ t('leveling.levelUpAnnouncement.specificChannel') }}</option>
                                        <option value="dm">{{ t('leveling.levelUpAnnouncement.dm') }}</option>
                                    </select>
                                </div>
                                <div v-if="form.level_up_type === 'channel'">
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.levelUpAnnouncement.channel') }}</label>
                                    <select
                                        v-model="form.level_up_channel_id"
                                        class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    >
                                        <option value="">{{ t('common.pleaseSelect') }}</option>
                                        <option v-for="channel in channels.filter(c => c.type === 0)" :key="channel.id" :value="channel.id">
                                            # {{ channel.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.levelUpAnnouncement.message') }}</label>
                                    <textarea
                                        v-model="form.level_up_message"
                                        rows="3"
                                        maxlength="2000"
                                        class="w-full rounded bg-[#2f3136] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                    <div class="flex justify-between mt-1">
                                        <p class="text-xs text-gray-400">
                                            {{ t('leveling.levelUpAnnouncement.availablePlaceholders') }} <code class="bg-[#202225] px-1 rounded">{player}</code>, <code class="bg-[#202225] px-1 rounded">{level}</code>
                                        </p>
                                        <span class="text-xs text-gray-400">{{ form.level_up_message.length }} / 2000</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rollenbelohnungen Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-hidden">
                            <button
                                type="button"
                                @click="roleRewardsOpen = !roleRewardsOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-semibold text-white">{{ t('leveling.roleRewards.title') }}</span>
                                    <span class="text-xs">👑</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': roleRewardsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="roleRewardsOpen" class="border-t border-[#202225] p-6 space-y-4">
                                <p class="text-sm text-gray-400">{{ t('leveling.roleRewards.description') }}</p>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-3">{{ t('leveling.roleRewards.type') }}</label>
                                    <div class="space-y-3">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.role_reward_type"
                                                value="stack"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.roleRewards.stack') }}</div>
                                                <div class="text-xs text-gray-400">{{ t('leveling.roleRewards.stackDescription') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.role_reward_type"
                                                value="highest_only"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.roleRewards.replace') }}</div>
                                                <div class="text-xs text-gray-400">{{ t('leveling.roleRewards.replaceDescription') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        v-model="form.remove_role_on_xp_loss"
                                        class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                    />
                                    <div>
                                        <label class="text-sm text-gray-300">{{ t('leveling.roleRewards.removeOnXpLoss') }}</label>
                                        <p class="text-xs text-gray-400">{{ t('leveling.roleRewards.removeOnXpLossDescription') }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.roleRewards.title') }}</label>
                                    <button
                                        type="button"
                                        @click="showRoleRewardModal = true"
                                        class="flex items-center gap-2 px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ t('leveling.roleRewards.addReward') }}</span>
                                    </button>
                                    
                                    <div v-if="roleRewards.length > 0" class="mt-4 space-y-2">
                                        <div
                                            v-for="reward in roleRewards"
                                            :key="reward.id"
                                            class="flex items-center justify-between p-3 bg-[#2f3136] rounded border border-[#202225]"
                                        >
                                            <div>
                                                <span class="text-sm text-white">{{ t('leveling.roleRewards.level') }} {{ reward.level }}</span>
                                                <span class="text-sm text-gray-400 ml-2">→ @{{ getRoleName(reward.role_id) }}</span>
                                            </div>
                                            <button
                                                type="button"
                                                @click="deleteRoleReward(reward.id)"
                                                class="text-red-400 hover:text-red-300"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Von XP ausgeschlossene Rollen Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-visible">
                            <button
                                type="button"
                                @click="excludedRolesOpen = !excludedRolesOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <span class="text-lg font-semibold text-white">{{ t('leveling.excludedRoles.title') }}</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': excludedRolesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="excludedRolesOpen" class="border-t border-[#202225] p-6 space-y-4">
                                <p class="text-sm text-gray-400">{{ t('leveling.excludedRoles.description') }}</p>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-3">Optionen</label>
                                    <div class="space-y-3">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.excluded_roles_type"
                                                value="deny_all_except"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.excludedRoles.denyAllExcept') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.excluded_roles_type"
                                                value="allow_all_except"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.excludedRoles.allowAllExcept') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="relative mb-4 z-50" data-excluded-role-selector>
                                    <button
                                        type="button"
                                        @click.stop="excludedRoleSelectorOpen = !excludedRoleSelectorOpen; nextTick(() => updateExcludedRoleDropdownPosition())"
                                        class="w-full flex items-center justify-between px-3 py-2 bg-[#2f3136] border border-[#202225] rounded text-white hover:border-[#5865f2] transition-colors"
                                    >
                                        <span class="text-sm text-gray-300">{{ t('common.pleaseSelect') }}</span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': excludedRoleSelectorOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <teleport to="body">
                                        <transition name="dropdown">
                                            <div
                                                v-if="excludedRoleSelectorOpen"
                                                class="fixed z-[9999] bg-[#36393f] border border-[#202225] rounded-lg shadow-xl max-h-64 overflow-y-auto"
                                                :style="excludedRoleDropdownStyle"
                                                @click.stop
                                            >
                                                <div
                                                    v-for="role in roles.filter(r => !excludedRolesList.includes(r.id))"
                                                    :key="role.id"
                                                    @click="addExcludedRole(role.id)"
                                                    class="px-4 py-2 hover:bg-[#2f3136] cursor-pointer transition-colors"
                                                >
                                                    <span class="text-sm" :style="{ color: getRoleColor(role.id) }">@{{ role.name }}</span>
                                                </div>
                                                <div v-if="roles.filter(r => !excludedRolesList.includes(r.id)).length === 0" class="px-4 py-2 text-sm text-gray-400">
                                                    {{ t('common.noMoreItems') }}
                                                </div>
                                            </div>
                                        </transition>
                                    </teleport>
                                </div>

                                <div v-if="excludedRolesList.length > 0" class="flex flex-wrap gap-2">
                                    <div
                                        v-for="roleId in excludedRolesList"
                                        :key="roleId"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-all"
                                        :style="{ 
                                            backgroundColor: getRoleColor(roleId) + '20',
                                            color: getRoleColor(roleId),
                                            border: `1px solid ${getRoleColor(roleId)}40`
                                        }"
                                    >
                                        <span>@{{ getRoleName(roleId) }}</span>
                                        <button
                                            type="button"
                                            @click="removeExcludedRole(roleId)"
                                            class="ml-1 text-red-400 hover:text-red-300"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Von XP ausgeschlossene Kanäle Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-visible">
                            <button
                                type="button"
                                @click="excludedChannelsOpen = !excludedChannelsOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <span class="text-lg font-semibold text-white">{{ t('leveling.excludedChannels.title') }}</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': excludedChannelsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="excludedChannelsOpen" class="border-t border-[#202225] p-6 space-y-4">
                                <p class="text-sm text-gray-400">{{ t('leveling.excludedChannels.description') }}</p>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-3">Optionen</label>
                                    <div class="space-y-3">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.excluded_channels_type"
                                                value="deny_all_except"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.excludedChannels.denyAllExcept') }}</div>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input
                                                type="radio"
                                                v-model="form.excluded_channels_type"
                                                value="allow_all_except"
                                                class="mt-1"
                                            />
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ t('leveling.excludedChannels.allowAllExcept') }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="relative mb-4 z-50" data-excluded-channel-selector>
                                    <button
                                        type="button"
                                        @click.stop="excludedChannelSelectorOpen = !excludedChannelSelectorOpen; nextTick(() => updateExcludedChannelDropdownPosition())"
                                        class="w-full flex items-center justify-between px-3 py-2 bg-[#2f3136] border border-[#202225] rounded text-white hover:border-[#5865f2] transition-colors"
                                    >
                                        <span class="text-sm text-gray-300">{{ t('common.pleaseSelect') }}</span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': excludedChannelSelectorOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <teleport to="body">
                                        <transition name="dropdown">
                                            <div
                                                v-if="excludedChannelSelectorOpen"
                                                class="fixed z-[9999] bg-[#36393f] border border-[#202225] rounded-lg shadow-xl max-h-64 overflow-y-auto"
                                                :style="excludedChannelDropdownStyle"
                                                @click.stop
                                            >
                                                <div
                                                    v-for="channel in channels.filter(c => c.type === 0 && !excludedChannelsList.includes(c.id))"
                                                    :key="channel.id"
                                                    @click="addExcludedChannel(channel.id)"
                                                    class="px-4 py-2 hover:bg-[#2f3136] cursor-pointer transition-colors"
                                                >
                                                    <span class="text-sm text-gray-300"># {{ channel.name }}</span>
                                                </div>
                                                <div v-if="channels.filter(c => c.type === 0 && !excludedChannelsList.includes(c.id)).length === 0" class="px-4 py-2 text-sm text-gray-400">
                                                    {{ t('common.noMoreItems') }}
                                                </div>
                                            </div>
                                        </transition>
                                    </teleport>
                                </div>

                                <div v-if="excludedChannelsList.length > 0" class="flex flex-wrap gap-2">
                                    <div
                                        v-for="channelId in excludedChannelsList"
                                        :key="channelId"
                                        class="inline-flex items-center gap-2 bg-[#5865f2] text-white px-3 py-1.5 rounded-full text-sm transition-all hover:bg-[#4752c4]"
                                    >
                                        <span># {{ getChannelName(channelId) }}</span>
                                        <button
                                            type="button"
                                            @click="removeExcludedChannel(channelId)"
                                            class="ml-1 text-white hover:text-red-300"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Standard-Serverrangkarte Accordion -->
                        <div class="bg-[#36393f] rounded-lg border border-[#202225] overflow-hidden">
                            <button
                                type="button"
                                @click="rankCardOpen = !rankCardOpen"
                                class="w-full flex items-center justify-between p-4 text-left"
                            >
                                <span class="text-lg font-semibold text-white">{{ t('leveling.rankCard.title') }}</span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': rankCardOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div v-show="rankCardOpen" class="border-t border-[#202225] p-6 space-y-4">
                                <p class="text-sm text-gray-400">{{ t('leveling.rankCard.description') }}</p>
                                
                                <!-- Rank Card Preview -->
                                <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-full bg-[#5865f2] flex items-center justify-center text-white text-xl font-bold">
                                            B
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-white font-semibold">ibeiros</span>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-white text-sm">RANG <span class="font-bold">#44</span></span>
                                                    <span class="text-[#5865f2] text-sm">LEVEL <span class="font-bold">12</span></span>
                                                </div>
                                            </div>
                                            <div class="w-full bg-[#2f3136] rounded-full h-3 mb-1">
                                                <div class="bg-[#5865f2] h-3 rounded-full" style="width: 32%"></div>
                                            </div>
                                            <span class="text-xs text-gray-400">429 / 1337 XP</span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="showRankCardModal = true"
                                    class="w-full px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors font-medium"
                                >
                                    {{ t('leveling.rankCard.edit') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50 font-medium"
                >
                    {{ form.processing ? t('common.saving') : t('common.save') }}
                </button>
            </form>
        </div>

        <!-- Role Reward Modal -->
        <teleport to="body">
            <div
                v-if="showRoleRewardModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[10000]"
                @click.self="showRoleRewardModal = false"
            >
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 w-full max-w-md">
                    <h3 class="text-xl font-semibold text-white mb-4">{{ t('leveling.roleRewards.addReward') }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.roleRewards.level') }}</label>
                            <input
                                type="number"
                                v-model.number="newRoleReward.level"
                                min="1"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.roleRewards.role') }}</label>
                            <select
                                v-model="newRoleReward.role_id"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">{{ t('common.pleaseSelect') }}</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    @{{ role.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showRoleRewardModal = false"
                                class="px-4 py-2 bg-[#36393f] hover:bg-[#2f3136] text-white rounded transition-colors"
                            >
                                {{ t('common.cancel') }}
                            </button>
                            <button
                                type="button"
                                @click="addRoleReward"
                                class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                            >
                                {{ t('common.add') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Rank Card Modal -->
        <teleport to="body">
            <div
                v-if="showRankCardModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[10000] overflow-y-auto p-4"
                @click.self="showRankCardModal = false"
            >
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 w-full max-w-4xl my-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-white">{{ t('leveling.rankCard.edit') }}</h3>
                        <button
                            @click="showRankCardModal = false"
                            class="text-gray-400 hover:text-white"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="saveRankCard" class="space-y-6">
                        <!-- Preview -->
                        <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-[#5865f2] flex items-center justify-center text-white text-xl font-bold">
                                    B
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-semibold" :style="{ color: rankCardForm.text_color }">ibeiros</span>
                                        <div class="flex items-center gap-4">
                                            <span class="text-sm" :style="{ color: rankCardForm.rank_text_color }">RANG <span class="font-bold">#44</span></span>
                                            <span class="text-sm" :style="{ color: rankCardForm.level_text_color }">LEVEL <span class="font-bold">12</span></span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-[#2f3136] rounded-full h-3 mb-1">
                                        <div class="h-3 rounded-full" :style="{ width: '32%', backgroundColor: rankCardForm.progress_bar_color }"></div>
                                    </div>
                                    <span class="text-xs" :style="{ color: rankCardForm.xp_text_color }">429 / 1337 XP</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hintergrund Typ -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">{{ t('leveling.rankCard.backgroundType') }}</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="rankCardForm.background_type"
                                        value="color"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('leveling.rankCard.color') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="rankCardForm.background_type"
                                        value="image"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('leveling.rankCard.image') }}</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="rankCardForm.background_type"
                                        value="custom"
                                        class="peer sr-only"
                                    />
                                    <div class="rounded-lg border-2 p-4 text-center transition-all peer-checked:border-[#5865f2] peer-checked:bg-[#5865f2]/10 border-[#202225] hover:border-[#36393f]">
                                        <p class="text-sm font-medium text-gray-300 peer-checked:text-white transition-colors">{{ t('leveling.rankCard.customUrl') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Hintergrund Farbe -->
                        <div v-if="rankCardForm.background_type === 'color'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.rankCard.backgroundColor') }}</label>
                            <div class="flex gap-2">
                                <input
                                    type="color"
                                    v-model="rankCardForm.background_color"
                                    class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                />
                                <input
                                    type="text"
                                    v-model="rankCardForm.background_color"
                                    placeholder="#000000"
                                    class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                        </div>

                        <!-- Preset Hintergrund -->
                        <div v-if="rankCardForm.background_type === 'image'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.rankCard.backgroundImage') }}</label>
                            <select
                                v-model="rankCardForm.background_image"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">{{ t('leveling.rankCard.noBackground') }}</option>
                                <option value="forest">{{ t('leveling.rankCard.presetForest') }}</option>
                                <option value="doors">{{ t('leveling.rankCard.presetDoors') }}</option>
                                <option value="night">{{ t('leveling.rankCard.presetNight') }}</option>
                                <option value="storm">{{ t('leveling.rankCard.presetStorm') }}</option>
                                <option value="sunset">{{ t('leveling.rankCard.presetSunset') }}</option>
                                <option value="mountains">{{ t('leveling.rankCard.presetMountains') }}</option>
                                <option value="rain">{{ t('leveling.rankCard.presetRain') }}</option>
                                <option value="grid">{{ t('leveling.rankCard.presetGrid') }}</option>
                                <option value="wood">{{ t('leveling.rankCard.presetWood') }}</option>
                            </select>
                        </div>

                        <!-- Benutzerdefinierter Hintergrund -->
                        <div v-if="rankCardForm.background_type === 'custom'">
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.rankCard.customUrl') }}</label>
                            <input
                                type="url"
                                v-model="rankCardForm.custom_background_url"
                                placeholder="https://example.com/image.jpg"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <!-- Deckkraft der Überlagerung -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.rankCard.overlayOpacity') }}: {{ rankCardForm.overlay_opacity }}%</label>
                            <input
                                type="range"
                                v-model.number="rankCardForm.overlay_opacity"
                                min="0"
                                max="100"
                                class="w-full h-2 bg-[#202225] rounded-lg appearance-none cursor-pointer accent-[#5865f2]"
                            />
                        </div>

                        <!-- Farben -->
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-3">{{ t('leveling.rankCard.colors') }}</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">{{ t('leveling.rankCard.textColor') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="rankCardForm.text_color"
                                            class="w-12 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="rankCardForm.text_color"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">{{ t('leveling.rankCard.rankTextColor') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="rankCardForm.rank_text_color"
                                            class="w-12 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="rankCardForm.rank_text_color"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">{{ t('leveling.rankCard.levelTextColor') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="rankCardForm.level_text_color"
                                            class="w-12 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="rankCardForm.level_text_color"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">{{ t('leveling.rankCard.xpTextColor') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="rankCardForm.xp_text_color"
                                            class="w-12 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="rankCardForm.xp_text_color"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-400 mb-1">{{ t('leveling.rankCard.progressBarColor') }}</label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="rankCardForm.progress_bar_color"
                                            class="w-12 h-10 rounded border border-[#202225] cursor-pointer"
                                        />
                                        <input
                                            type="text"
                                            v-model="rankCardForm.progress_bar_color"
                                            class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-2 py-1 text-sm focus:outline-none focus:border-[#5865f2]"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Willkommensnachricht -->
                            <div class="space-y-4 mt-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">{{ t('leveling.rankCard.welcomeMessage') }}</label>
                                    <textarea
                                        v-model="rankCardForm.welcome_message"
                                        rows="4"
                                        placeholder="Willkommen in deinem Ticket, {user}! Bitte beschreibe dein Anliegen.&#10;&#10;Ein Supporter wird sich bald bei dir melden."
                                        class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    ></textarea>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ t('leveling.rankCard.availablePlaceholders') }} <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{user}</code>, 
                                        <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{level}</code>, 
                                        <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{rank}</code>, 
                                        <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{xp}</code>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 justify-end">
                            <button
                                type="button"
                                @click="showRankCardModal = false"
                                class="px-4 py-2 bg-[#36393f] hover:bg-[#2f3136] text-white rounded transition-colors"
                            >
                                {{ t('common.cancel') }}
                            </button>
                            <button
                                type="submit"
                                :disabled="rankCardForm.processing"
                                class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50"
                            >
                                {{ rankCardForm.processing ? t('common.saving') : t('common.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </teleport>
    </GuildLayout>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Custom Slider Styling */
.slider-custom {
    -webkit-appearance: none;
    appearance: none;
    height: 8px;
}

.slider-custom::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #5865f2;
    cursor: pointer;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    margin-top: -6px;
}

.slider-custom::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #5865f2;
    cursor: pointer;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider-custom::-webkit-slider-runnable-track {
    height: 8px;
    border-radius: 4px;
}

.slider-custom::-moz-range-track {
    height: 8px;
    border-radius: 4px;
    background: transparent;
}
</style>

