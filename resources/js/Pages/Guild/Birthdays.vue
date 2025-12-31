<template>
    <Head :title="`${guild.name} - ${t('birthdays.title')}`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">{{ t('birthdays.title') }}</h1>
                <button
                    @click="openCreateForm"
                    class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium shadow-lg hover:shadow-xl transform hover:scale-105"
                >
                    {{ t('birthdays.addBirthday') }}
                </button>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <!-- Create/Edit Form -->
            <div v-if="showForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">{{ editingBirthday ? t('birthdays.editBirthday') : t('birthdays.addBirthday') }}</h2>
                    <button
                        @click="closeForm"
                        class="text-gray-400 hover:text-white"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="saveBirthday" class="space-y-4">
                    <!-- Member Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.member') }} *
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="memberSearch"
                                @input="filterMembers"
                                :placeholder="t('birthdays.form.searchMember')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                            <div v-if="memberSearch && filteredMembers.length > 0" class="absolute z-10 w-full mt-1 bg-[#36393f] border border-[#202225] rounded-lg max-h-60 overflow-y-auto">
                                <div
                                    v-for="member in filteredMembers"
                                    :key="member.id"
                                    @click="selectMember(member)"
                                    class="px-3 py-2 hover:bg-[#2f3136] cursor-pointer flex items-center gap-3"
                                >
                                    <img
                                        :src="member.avatar_url"
                                        :alt="member.display_name"
                                        class="w-8 h-8 rounded-full"
                                    />
                                    <div>
                                        <div class="text-white text-sm font-medium">{{ member.display_name }}</div>
                                        <div class="text-gray-400 text-xs">{{ member.username }}#{{ member.discriminator }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.user_id" class="mt-2 flex items-center gap-2">
                            <img
                                :src="selectedMember?.avatar_url"
                                :alt="selectedMember?.display_name"
                                class="w-8 h-8 rounded-full"
                            />
                            <span class="text-white text-sm">{{ selectedMember?.display_name }}</span>
                            <button
                                type="button"
                                @click="form.user_id = ''; selectedMember = null"
                                class="text-red-400 hover:text-red-300 text-sm"
                            >
                                {{ t('common.remove') }}
                            </button>
                        </div>
                    </div>

                    <!-- Birthday Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.birthday') }} *
                        </label>
                        <input
                            type="date"
                            v-model="form.birthday"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <!-- Birthday Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.birthdayRole') }}
                        </label>
                        <select
                            v-model="form.birthday_role_id"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
                            <option
                                v-for="role in roles"
                                :key="role.id"
                                :value="role.id"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">{{ t('birthdays.form.birthdayRoleDescription') }}</p>
                    </div>

                    <!-- Channel for Embed -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.channel') }}
                        </label>
                        <select
                            v-model="form.channel_id"
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        >
                            <option value="">{{ t('common.pleaseSelect') }}</option>
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

                    <!-- Enabled -->
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            v-model="form.enabled"
                            id="enabled"
                            class="w-4 h-4 rounded bg-[#36393f] border border-[#202225] text-[#5865f2] focus:ring-[#5865f2]"
                        />
                        <label for="enabled" class="text-sm font-medium text-gray-300">
                            {{ t('birthdays.form.enabled') }}
                        </label>
                    </div>

                    <!-- Send Embed -->
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            v-model="form.send_embed"
                            id="send_embed"
                            class="w-4 h-4 rounded bg-[#36393f] border border-[#202225] text-[#5865f2] focus:ring-[#5865f2]"
                        />
                        <label for="send_embed" class="text-sm font-medium text-gray-300">
                            {{ t('birthdays.form.sendEmbed') }}
                        </label>
                    </div>

                    <!-- Embed Configuration -->
                    <div v-if="form.send_embed" class="space-y-4 border-t border-[#202225] pt-4">
                        <h3 class="text-sm font-semibold text-white">{{ t('birthdays.form.embedConfig') }}</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedTitle') }}
                            </label>
                            <input
                                type="text"
                                v-model="form.embed_title"
                                :placeholder="t('birthdays.form.embedTitlePlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedDescription') }}
                            </label>
                            <textarea
                                v-model="form.embed_description"
                                rows="3"
                                :placeholder="t('birthdays.form.embedDescriptionPlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">{{ t('birthdays.form.placeholders') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedColor') }}
                            </label>
                            <input
                                type="color"
                                v-model="form.embed_color"
                                class="w-full h-10 rounded bg-[#36393f] border border-[#202225]"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                        >
                            {{ form.processing ? t('common.saving') : t('common.save') }}
                        </button>
                        <button
                            type="button"
                            @click="closeForm"
                            class="px-4 py-2 bg-[#36393f] hover:bg-[#2f3136] text-white rounded-lg transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Birthdays List -->
            <div v-if="birthdays.length === 0 && !showForm" class="bg-[#2f3136] rounded-lg border border-[#202225] p-8">
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-white mb-2">{{ t('birthdays.noBirthdays') }}</h2>
                    <p class="text-gray-400 mb-6">{{ t('birthdays.noBirthdaysDescription') }}</p>
                    <button
                        @click="openCreateForm"
                        class="px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all font-medium"
                    >
                        {{ t('birthdays.addBirthday') }}
                    </button>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="birthday in birthdays"
                    :key="birthday.id"
                    class="bg-gradient-to-br from-[#2f3136] to-[#36393f] rounded-xl border border-[#202225] p-6 hover:border-[#5865f2] transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                    <!-- Header with Avatar and Name -->
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-[#202225]">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <img
                                    :src="getMemberAvatar(birthday.user_id)"
                                    :alt="getMemberName(birthday.user_id)"
                                    class="w-14 h-14 rounded-full ring-2 ring-[#5865f2] ring-offset-2 ring-offset-[#2f3136]"
                                />
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#5865f2] rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ getMemberName(birthday.user_id) }}</h3>
                                <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatBirthday(birthday.birthday) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Birthday Info Cards -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between p-2 bg-[#202225]/50 rounded-lg">
                            <span class="text-xs text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ t('birthdays.list.role') }}
                            </span>
                            <span class="text-sm font-medium text-white">{{ getRoleName(birthdayConfig?.role_id) || t('common.notSet') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-[#202225]/50 rounded-lg">
                            <span class="text-xs text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                {{ t('birthdays.list.channel') }}
                            </span>
                            <span class="text-sm font-medium text-white">{{ getChannelName(birthdayConfig?.channel_id) || t('common.notSet') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-[#202225]/50 rounded-lg">
                            <span class="text-xs text-gray-400 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ t('birthdays.list.enabled') }}
                            </span>
                            <span :class="[
                                'text-xs font-semibold px-2 py-1 rounded',
                                birthdayConfig?.enabled ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'
                            ]">
                                {{ birthdayConfig?.enabled ? t('common.enabled') : t('common.disabled') }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 pt-4 border-t border-[#202225]">
                        <button
                            @click="editBirthday(birthday)"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ t('common.edit') }}
                        </button>
                        <button
                            @click="deleteBirthday(birthday.id)"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ t('common.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Birthday Modal -->
        <div
            v-if="showEditModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeEditModal"
        >
            <div class="bg-[#2f3136] rounded-lg border border-[#202225] w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-[#202225] sticky top-0 bg-[#2f3136] z-10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">{{ t('birthdays.editBirthday') }}</h2>
                        <button
                            @click="closeEditModal"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="saveBirthday" class="p-6 space-y-4">
                    <!-- Member Display (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.member') }}
                        </label>
                        <div class="flex items-center gap-3 p-3 bg-[#36393f] rounded-lg border border-[#202225]">
                            <img
                                :src="selectedMember?.avatar_url"
                                :alt="selectedMember?.display_name"
                                class="w-10 h-10 rounded-full"
                            />
                            <div>
                                <div class="text-white font-medium">{{ selectedMember?.display_name }}</div>
                                <div class="text-gray-400 text-sm">{{ selectedMember?.username }}#{{ selectedMember?.discriminator }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Birthday Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('birthdays.form.birthday') }} *
                        </label>
                        <input
                            type="date"
                            v-model="editForm.birthday"
                            required
                            class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                        />
                    </div>

                    <!-- Embed Configuration -->
                    <div class="space-y-4 border-t border-[#202225] pt-4">
                        <h3 class="text-sm font-semibold text-white">{{ t('birthdays.form.embedConfig') }}</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedTitle') }}
                            </label>
                            <input
                                type="text"
                                v-model="editForm.embed_title"
                                :placeholder="t('birthdays.form.embedTitlePlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedDescription') }}
                            </label>
                            <textarea
                                v-model="editForm.embed_description"
                                rows="4"
                                :placeholder="t('birthdays.form.embedDescriptionPlaceholder')"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">{{ t('birthdays.form.placeholders') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ t('birthdays.form.embedColor') }}
                            </label>
                            <input
                                type="color"
                                v-model="editForm.embed_color"
                                class="w-full h-10 rounded bg-[#36393f] border border-[#202225]"
                            />
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-[#202225]">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                        >
                            {{ editForm.processing ? t('common.saving') : t('common.save') }}
                        </button>
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="flex-1 px-4 py-2 bg-[#36393f] hover:bg-[#2f3136] text-white rounded-lg transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref, computed } from 'vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    roles: Array,
    members: Array,
    birthdays: Array,
    birthdayConfig: Object,
});

const showForm = ref(false);
const showEditModal = ref(false);
const editingBirthday = ref(false);
const memberSearch = ref('');
const filteredMembers = ref([]);
const selectedMember = ref(null);
const editingBirthdayId = ref(null);

const form = useForm({
    user_id: '',
    birthday: '',
    birthday_role_id: '',
    channel_id: '',
    enabled: true,
    send_embed: true,
    embed_title: '',
    embed_description: '',
    embed_color: '#5865f2',
});

const editForm = useForm({
    birthday: '',
    embed_title: '',
    embed_description: '',
    embed_color: '#5865f2',
});

const configForm = useForm({
    embed_title: '',
    embed_description: '',
    embed_color: '#5865f2',
});

function filterMembers() {
    if (!memberSearch.value) {
        filteredMembers.value = [];
        return;
    }
    
    const search = memberSearch.value.toLowerCase();
    filteredMembers.value = props.members.filter(member => 
        member.display_name.toLowerCase().includes(search) ||
        member.username.toLowerCase().includes(search) ||
        member.id.includes(search)
    ).slice(0, 10);
}

function selectMember(member) {
    form.user_id = member.id;
    selectedMember.value = member;
    memberSearch.value = '';
    filteredMembers.value = [];
}

function openCreateForm() {
    form.reset();
    form.enabled = true;
    form.send_embed = true;
    form.embed_color = '#5865f2';
    editingBirthday.value = false;
    selectedMember.value = null;
    memberSearch.value = '';
    showForm.value = true;
}

function editBirthday(birthday) {
    editForm.birthday = birthday.birthday;
    editForm.embed_title = props.birthdayConfig?.embed_title || '';
    editForm.embed_description = props.birthdayConfig?.embed_description || '';
    editForm.embed_color = props.birthdayConfig?.embed_color || '#5865f2';
    
    selectedMember.value = props.members.find(m => m.id === birthday.user_id) || null;
    editingBirthdayId.value = birthday.id;
    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
    editForm.reset();
    configForm.reset();
    selectedMember.value = null;
    editingBirthdayId.value = null;
}

function closeForm() {
    showForm.value = false;
    form.reset();
    selectedMember.value = null;
    memberSearch.value = '';
    editingBirthday.value = false;
}

function saveBirthday() {
    if (showEditModal.value && editingBirthdayId.value) {
        // Update birthday first
        editForm.put(route('guild.birthdays.update', { guild: props.guild.id, id: editingBirthdayId.value }), {
            preserveScroll: true,
            onSuccess: () => {
                // Then update birthday config with embed settings
                configForm.embed_title = editForm.embed_title;
                configForm.embed_description = editForm.embed_description;
                configForm.embed_color = editForm.embed_color;
                
                configForm.put(route('guild.birthday-config.update', { guild: props.guild.id }), {
                    preserveScroll: true,
                    onSuccess: () => {
                        closeEditModal();
                    },
                });
            },
        });
    } else {
        form.post(route('guild.birthdays.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeForm();
            },
        });
    }
}

function deleteBirthday(id) {
    if (confirm(t('birthdays.deleteConfirm'))) {
        router.delete(route('guild.birthdays.delete', { guild: props.guild.id, id }), {
            preserveScroll: true,
        });
    }
}

function getMemberName(userId) {
    const member = props.members.find(m => m.id === userId);
    return member ? member.display_name : `User ${userId}`;
}

function getMemberAvatar(userId) {
    const member = props.members.find(m => m.id === userId);
    return member?.avatar_url || 'https://cdn.discordapp.com/embed/avatars/0.png';
}

function getRoleName(roleId) {
    if (!roleId) return null;
    const role = props.roles.find(r => r.id === roleId);
    return role ? role.name : null;
}

function getChannelName(channelId) {
    if (!channelId) return null;
    const channel = props.channels.find(c => c.id === channelId);
    return channel ? `# ${channel.name}` : null;
}

function formatBirthday(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

