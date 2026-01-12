<template>
    <Head :title="`${guild.name} - ${t('teamManagement.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">{{ t('teamManagement.title') }}</h1>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <!-- 2-Spalten Grid: Links Ränge, Rechts Mitglieder -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Links: Team-Ränge -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">{{ t('teamManagement.ranks.title') }}</h2>
                        <button
                            @click="showRankModal = true"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors text-sm"
                        >
                            {{ t('teamManagement.ranks.addRank') }}
                        </button>
                    </div>

                    <div v-if="teamRanks.length === 0" class="text-center py-8 text-gray-400">
                        <p>{{ t('teamManagement.ranks.noRanks') }}</p>
                    </div>

                    <div v-else class="space-y-3 max-h-[600px] overflow-y-auto">
                        <div
                            v-for="rank in sortedRanks"
                            :key="rank.id"
                            class="flex items-center justify-between p-3 bg-[#36393f] rounded-lg border border-[#202225] hover:border-[#5865f2] transition-colors"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                    <button
                                        @click="moveRank(rank.id, 'up')"
                                        :disabled="rank.sort_order === 0"
                                        class="text-gray-400 hover:text-white disabled:opacity-30"
                                        :title="t('teamManagement.ranks.moveUp')"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="moveRank(rank.id, 'down')"
                                        :disabled="rank.sort_order === sortedRanks.length - 1"
                                        class="text-gray-400 hover:text-white disabled:opacity-30"
                                        :title="t('teamManagement.ranks.moveDown')"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="rank.role_color && rank.role_color > 0"
                                            class="w-3 h-3 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: `#${rank.role_color.toString(16).padStart(6, '0')}` }"
                                        ></span>
                                        <h3 class="text-white font-semibold truncate">{{ rank.role_name || rank.name }}</h3>
                                    </div>
                                    <p class="text-xs text-gray-400">
                                        {{ t('teamManagement.ranks.memberCount', { count: rank.member_count }) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            :checked="rank.visible"
                                            @change="toggleRankVisibility(rank.id, $event.target.checked)"
                                            class="sr-only peer"
                                        />
                                        <div class="w-9 h-5 bg-gray-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#5865f2]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#5865f2]"></div>
                                    </label>
                                    <button
                                        @click="editRank(rank)"
                                        class="text-gray-400 hover:text-white"
                                        :title="t('teamManagement.ranks.editRank')"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteRank(rank.id)"
                                        class="text-red-400 hover:text-red-300"
                                        :title="t('teamManagement.ranks.deleteRank')"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rechts: Team-Mitglieder -->
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">{{ t('teamManagement.members.title') }}</h2>
                        <button
                            @click="showMemberModal = true"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors text-sm"
                        >
                            {{ t('teamManagement.members.addMember') }}
                        </button>
                    </div>

                    <div v-if="teamMembers.length === 0" class="text-center py-8 text-gray-400">
                        <p>{{ t('teamManagement.members.noMembers') }}</p>
                    </div>

                    <div v-else class="space-y-2 max-h-[600px] overflow-y-auto">
                        <div
                            v-for="member in teamMembers"
                            :key="member.id"
                            @contextmenu.prevent="showContextMenu($event, member)"
                            class="flex items-center justify-between p-3 bg-[#36393f] rounded-lg border border-[#202225] hover:border-[#5865f2] transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <img
                                    v-if="member.avatar_url"
                                    :src="member.avatar_url"
                                    :alt="member.display_name"
                                    class="w-8 h-8 rounded-full flex-shrink-0"
                                />
                                <div class="flex-1 min-w-0">
                                    <div class="text-white font-medium truncate">{{ member.display_name || member.user_id }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ member.rank_name }}</div>
                                </div>
                            </div>
                            <button
                                @click.stop="removeMember(member.id)"
                                class="text-red-400 hover:text-red-300 flex-shrink-0"
                                :title="t('teamManagement.members.removeMember')"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Konfiguration Sektion -->
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] p-6">
                <h2 class="text-lg font-semibold text-white mb-4">{{ t('teamManagement.config.title') }}</h2>
                
                <form @submit.prevent="saveConfig" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('teamManagement.config.channel') }}
                            </label>
                            <select
                                v-model="configForm.channel_id"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">{{ t('common.notSet') }}</option>
                                <template v-for="channel in channels" :key="channel.id">
                                    <option
                                        v-if="channel.type === 4 && channel.is_category"
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
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('teamManagement.config.defaultRole') }}
                            </label>
                            <select
                                v-model="configForm.default_role_id"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">{{ t('common.notSet') }}</option>
                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.id"
                                >
                                    <span
                                        v-if="role.color && role.color > 0"
                                        class="inline-block w-3 h-3 rounded-full mr-2"
                                        :style="{ backgroundColor: `#${role.color.toString(16).padStart(6, '0')}` }"
                                    ></span>
                                    {{ role.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">{{ t('teamManagement.config.defaultRoleHelp') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="configForm.notify_join"
                                class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                            />
                            <span class="text-sm text-gray-300">{{ t('teamManagement.config.notifyJoin') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="configForm.notify_leave"
                                class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                            />
                            <span class="text-sm text-gray-300">{{ t('teamManagement.config.notifyLeave') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="configForm.notify_upgrade"
                                class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                            />
                            <span class="text-sm text-gray-300">{{ t('teamManagement.config.notifyUpgrade') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="configForm.notify_downgrade"
                                class="w-4 h-4 text-[#5865f2] bg-[#36393f] border-[#202225] rounded focus:ring-[#5865f2]"
                            />
                            <span class="text-sm text-gray-300">{{ t('teamManagement.config.notifyDowngrade') }}</span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                        >
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rank Modal -->
        <div
            v-if="showRankModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeRankModal"
        >
            <div class="bg-[#2f3136] rounded-lg p-6 w-full max-w-md border border-[#202225]">
                <h3 class="text-xl font-bold mb-4">{{ editingRank ? t('teamManagement.ranks.editRank') : t('teamManagement.ranks.addRank') }}</h3>
                
                <form @submit.prevent="saveRank" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('teamManagement.ranks.name') }} *
                        </label>
                        <input
                            type="text"
                            v-model="rankForm.name"
                            required
                            :placeholder="t('teamManagement.ranks.namePlaceholder')"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('teamManagement.ranks.role') }} *
                        </label>
                        <select
                            v-model="rankForm.role_id"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
                            <option
                                v-for="role in availableRoles"
                                :key="role.id"
                                :value="role.id"
                            >
                                <span
                                    v-if="role.color && role.color > 0"
                                    class="inline-block w-3 h-3 rounded-full mr-2"
                                    :style="{ backgroundColor: `#${role.color.toString(16).padStart(6, '0')}` }"
                                ></span>
                                {{ role.name }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">{{ t('teamManagement.ranks.roleHelp') }}</p>
                    </div>
                    
                    <div class="flex gap-3 justify-end">
                        <button
                            type="button"
                            @click="closeRankModal"
                            class="px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                        >
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Member Modal -->
        <div
            v-if="showMemberModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeMemberModal"
        >
            <div class="bg-[#2f3136] rounded-lg p-6 w-full max-w-md border border-[#202225]">
                <h3 class="text-xl font-bold mb-4">{{ t('teamManagement.members.addMember') }}</h3>
                
                <form @submit.prevent="saveMember" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('teamManagement.members.member') }} *
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="memberSearch"
                                @input="filterMembers"
                                :placeholder="t('teamManagement.members.searchMember')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>
                        <div v-if="filteredMembers.length > 0" class="mt-2 max-h-48 overflow-y-auto bg-[#36393f] rounded border border-[#202225]">
                            <div
                                v-for="member in filteredMembers"
                                :key="member.id"
                                @click="selectMember(member)"
                                class="p-2 hover:bg-[#40444b] cursor-pointer flex items-center gap-2"
                            >
                                <img
                                    :src="member.avatar_url"
                                    :alt="member.display_name"
                                    class="w-8 h-8 rounded-full"
                                />
                                <span class="text-white">{{ member.display_name }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="memberForm.user_id">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('teamManagement.members.rank') }} *
                        </label>
                        <select
                            v-model="memberForm.rank_id"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
                            <option
                                v-for="rank in teamRanks"
                                :key="rank.id"
                                :value="rank.id"
                            >
                                {{ rank.role_name || rank.name }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="flex gap-3 justify-end">
                        <button
                            type="button"
                            @click="closeMemberModal"
                            class="px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="!memberForm.user_id || !memberForm.rank_id"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50"
                        >
                            {{ t('common.add') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Context Menu für Teammitglieder -->
        <div
            v-if="contextMenu.show"
            :style="{
                position: 'fixed',
                top: contextMenu.y + 'px',
                left: contextMenu.x + 'px',
                zIndex: 1000
            }"
            class="bg-[#2f3136] border border-[#202225] rounded-lg shadow-xl min-w-[200px]"
            @click.stop
        >
            <div class="py-1">
                <button
                    @click="removeTeamRights(contextMenu.member)"
                    class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-[#36393f] hover:text-red-300 transition-colors"
                >
                    {{ t('teamManagement.members.removeRights') }}
                </button>
                <button
                    @click="removeMember(contextMenu.member.id)"
                    class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-[#36393f] hover:text-red-300 transition-colors"
                >
                    {{ t('teamManagement.members.removeMember') }}
                </button>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    roles: Array,
    members: Array,
    teamRanks: Array,
    teamMembers: Array,
    teamConfig: Object,
    addOns: Object,
});

const showRankModal = ref(false);
const showMemberModal = ref(false);
const editingRank = ref(null);
const memberSearch = ref('');
const filteredMembers = ref([]);
const contextMenu = ref({
    show: false,
    x: 0,
    y: 0,
    member: null,
});

const rankForm = useForm({
    name: '',
    role_id: '',
});

const memberForm = useForm({
    user_id: '',
    rank_id: '',
});

const configForm = useForm({
    channel_id: props.teamConfig.channel_id || '',
    default_role_id: props.teamConfig.default_role_id || '',
    notify_join: props.teamConfig.notify_join ?? true,
    notify_leave: props.teamConfig.notify_leave ?? true,
    notify_upgrade: props.teamConfig.notify_upgrade ?? true,
    notify_downgrade: props.teamConfig.notify_downgrade ?? true,
});

const sortedRanks = computed(() => {
    return [...props.teamRanks].sort((a, b) => a.sort_order - b.sort_order);
});

const availableRoles = computed(() => {
    // Zeige nur Rollen, die noch nicht als Team-Rang verwendet werden
    const usedRoleIds = props.teamRanks.map(r => r.role_id).filter(Boolean);
    return props.roles.filter(r => !usedRoleIds.includes(r.id));
});

const filterMembers = () => {
    if (!memberSearch.value) {
        filteredMembers.value = [];
        return;
    }
    const search = memberSearch.value.toLowerCase();
    filteredMembers.value = props.members.filter(m => 
        m.display_name?.toLowerCase().includes(search) ||
        m.username?.toLowerCase().includes(search)
    ).slice(0, 10);
};

const selectMember = (member) => {
    memberForm.user_id = member.id;
    memberSearch.value = member.display_name || member.username;
    filteredMembers.value = [];
};

const showContextMenu = (event, member) => {
    contextMenu.value = {
        show: true,
        x: event.clientX,
        y: event.clientY,
        member: member,
    };
};

const closeContextMenu = () => {
    contextMenu.value.show = false;
};

const closeRankModal = () => {
    showRankModal.value = false;
    editingRank.value = null;
    rankForm.reset();
};

const editRank = (rank) => {
    editingRank.value = rank;
    rankForm.name = rank.name;
    rankForm.role_id = rank.role_id;
    showRankModal.value = true;
};

const saveRank = () => {
    if (editingRank.value) {
        rankForm.put(route('guild.team-management.rank.update', { 
            guild: props.guild.id, 
            id: editingRank.value.id 
        }), {
            preserveScroll: true,
            onSuccess: () => {
                closeRankModal();
            }
        });
    } else {
        rankForm.post(route('guild.team-management.rank.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeRankModal();
            }
        });
    }
};

const deleteRank = (rankId) => {
    if (!confirm(t('teamManagement.ranks.confirmDelete'))) {
        return;
    }
    router.delete(route('guild.team-management.rank.delete', { 
        guild: props.guild.id, 
        id: rankId 
    }), {
        preserveScroll: true,
    });
};

const toggleRankVisibility = (rankId, visible) => {
    router.put(route('guild.team-management.rank.toggle', { 
        guild: props.guild.id, 
        id: rankId 
    }), {
        visible: visible
    }, {
        preserveScroll: true,
    });
};

const moveRank = (rankId, direction) => {
    router.post(route('guild.team-management.rank.move', { 
        guild: props.guild.id, 
        id: rankId 
    }), {
        direction: direction
    }, {
        preserveScroll: true,
    });
};

const closeMemberModal = () => {
    showMemberModal.value = false;
    memberForm.reset();
    memberSearch.value = '';
    filteredMembers.value = [];
};

const saveMember = () => {
    memberForm.post(route('guild.team-management.member.store', { guild: props.guild.id }), {
        preserveScroll: true,
        onSuccess: () => {
            closeMemberModal();
        }
    });
};

const removeMember = (memberId) => {
    if (!confirm(t('teamManagement.members.confirmRemove'))) {
        return;
    }
    router.delete(route('guild.team-management.member.delete', { 
        guild: props.guild.id, 
        id: memberId 
    }), {
        preserveScroll: true,
    });
    closeContextMenu();
};

const removeTeamRights = (member) => {
    if (!confirm(t('teamManagement.members.confirmRemoveRights'))) {
        return;
    }
    router.post(route('guild.team-management.member.remove-rights', { 
        guild: props.guild.id, 
        id: member.id 
    }), {
        preserveScroll: true,
    });
    closeContextMenu();
};

const saveConfig = () => {
    configForm.put(route('guild.team-management.config.update', { guild: props.guild.id }), {
        preserveScroll: true,
    });
};

onMounted(() => {
    document.addEventListener('click', closeContextMenu);
});

onUnmounted(() => {
    document.removeEventListener('click', closeContextMenu);
});
</script>
