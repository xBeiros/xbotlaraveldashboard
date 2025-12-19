<template>
    <Head :title="`${guild.name} - Ticket-System`" />

    <GuildLayout :guild="guild" :guilds="guilds">
        <div class="p-8">
            <h1 class="text-2xl font-bold mb-6 text-white">Ticket-System</h1>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>

            <div class="space-y-6">
                <!-- Ticket-Post Konfiguration -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <h2 class="text-lg font-semibold text-white mb-4">Ticket-Post</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Kanal für Ticket-Post
                            </label>
                            <select
                                v-model="ticketPostForm.channel_id"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">Bitte wählen...</option>
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

                        <!-- Embed Vorschau -->
                        <div class="bg-[#1a1b1e] rounded-lg p-6 border border-[#202225]">
                            <h4 class="text-sm font-medium text-gray-300 mb-4">Vorschau</h4>
                            <div class="flex justify-center">
                                <div class="bg-[#2f3136] rounded-lg p-4" style="max-width: 520px; width: 100%;">
                                    <!-- Banner -->
                                    <div v-if="ticketPostForm.embed_banner" class="mb-2 rounded-t-lg overflow-hidden">
                                        <img :src="ticketPostForm.embed_banner" alt="Banner" class="w-full h-32 object-cover" />
                                    </div>
                                    <!-- Discord-ähnliche Embed-Vorschau -->
                                    <div
                                        class="rounded border-l-4 p-3"
                                        :style="{ borderLeftColor: ticketPostForm.embed_color || '#ff0000' }"
                                    >
                                        <div class="space-y-2">
                                            <h5
                                                v-if="ticketPostForm.embed_title"
                                                class="text-base font-semibold text-white"
                                                v-html="renderedTitle"
                                            ></h5>
                                            <p
                                                v-if="ticketPostForm.embed_description"
                                                class="text-sm text-gray-300 whitespace-pre-wrap"
                                                v-html="renderedDescription"
                                            ></p>
                                            <div
                                                v-if="ticketPostForm.embed_image"
                                                class="mt-2"
                                            >
                                                <img :src="ticketPostForm.embed_image" alt="Image" class="max-w-full rounded" />
                                            </div>
                                            <div
                                                v-if="ticketPostForm.embed_footer"
                                                class="flex items-center gap-2 pt-2 border-t border-[#202225]"
                                            >
                                                <img :src="guild.icon_url" alt="Server Icon" class="w-4 h-4 rounded-full" />
                                                <span class="text-xs text-gray-400">{{ guild.name }} • Heute um {{ new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Dropdown Vorschau -->
                                    <div class="mt-3 bg-[#2f3136] rounded border border-[#202225] p-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-400">Wähle eine Kategorie...</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Embed Konfiguration -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Titel</label>
                                <input
                                    type="text"
                                    v-model="ticketPostForm.embed_title"
                                    placeholder="Ticket System"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Beschreibung</label>
                                <textarea
                                    v-model="ticketPostForm.embed_description"
                                    rows="3"
                                    placeholder="Wähle eine der Optionen aus, um ein Ticket zu erstellen!"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                ></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Farbe</label>
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        v-model="ticketPostForm.embed_color"
                                        class="w-16 h-10 rounded border border-[#202225] cursor-pointer"
                                    />
                                    <input
                                        type="text"
                                        v-model="ticketPostForm.embed_color"
                                        placeholder="#ff0000"
                                        class="flex-1 rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Banner URL</label>
                                <input
                                    type="url"
                                    v-model="ticketPostForm.embed_banner"
                                    placeholder="https://example.com/banner.png"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Bild URL</label>
                                <input
                                    type="url"
                                    v-model="ticketPostForm.embed_image"
                                    placeholder="https://example.com/image.png"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="ticketPostForm.embed_footer"
                                    class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                                />
                                <label class="text-sm text-gray-300">Footer anzeigen</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            v-if="ticketPost"
                            @click="resendTicketPost"
                            :disabled="resendingPost"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ resendingPost ? 'Sende...' : 'Post erneut senden' }}
                        </button>
                        <button
                            @click="saveTicketPost"
                            :disabled="ticketPostForm.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                        >
                            {{ ticketPostForm.processing ? 'Speichere...' : 'Post speichern' }}
                        </button>
                    </div>
                </div>

                <!-- Ticket-Kategorien -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">Ticket-Kategorien</h2>
                        <button
                            @click="openCategoryModal()"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Kategorie hinzufügen
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="category in ticketCategories"
                            :key="category.id"
                            class="bg-[#36393f] rounded-lg p-4 border border-[#202225] hover:border-[#5865f2] transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ category.emoji || '🎫' }}</span>
                                    <div>
                                        <div class="text-sm font-medium text-white">{{ category.name }}</div>
                                        <div class="text-xs text-gray-400">
                                            {{ categories.find(c => c.id === category.category_id)?.name || 'Keine Kategorie' }} • 
                                            Format: {{ category.channel_name_format }}
                                        </div>
                                    </div>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-xs font-medium ml-2',
                                            category.enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'
                                        ]"
                                    >
                                        {{ category.enabled ? 'Aktiv' : 'Inaktiv' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openCategoryModal(category)"
                                        class="px-3 py-1.5 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded text-sm font-medium transition-colors"
                                    >
                                        Bearbeiten
                                    </button>
                                    <button
                                        @click="deleteCategory(category.id)"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors"
                                    >
                                        Löschen
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="ticketCategories.length === 0" class="text-center py-8 text-gray-400">
                            <p>Noch keine Ticket-Kategorien erstellt.</p>
                        </div>
                    </div>
                </div>

                <!-- Ticket-Transcripts -->
                <div class="bg-[#2f3136] rounded-lg p-6 border border-[#202225]">
                    <h2 class="text-lg font-semibold text-white mb-4">Ticket-Transcripts</h2>
                    
                    <div class="space-y-3">
                        <div
                            v-for="transcript in ticketTranscripts"
                            :key="transcript.id"
                            class="bg-[#36393f] rounded-lg p-4 border border-[#202225] hover:border-[#5865f2] transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-white">
                                        Ticket #{{ transcript.id }} - {{ transcript.category_name }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Geschlossen am: {{ transcript.closed_at || 'Unbekannt' }}
                                    </div>
                                </div>
                                <a
                                    :href="route('guild.ticket.transcript', { guild: guild.id, ticketId: transcript.id })"
                                    target="_blank"
                                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded-lg transition-colors text-sm font-medium"
                                >
                                    Transcript anzeigen
                                </a>
                            </div>
                        </div>
                        <div v-if="ticketTranscripts.length === 0" class="text-center py-8 text-gray-400">
                            <p>Noch keine Transcripts verfügbar.</p>
                            <p class="text-xs mt-2">Transcripts werden automatisch nach 7 Tagen gelöscht.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategorie Modal -->
            <div
                v-if="showCategoryModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="closeCategoryModal"
            >
                <div class="bg-[#2f3136] rounded-lg border border-[#202225] w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-[#202225]">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-white">
                                {{ editingCategory ? 'Kategorie bearbeiten' : 'Neue Kategorie erstellen' }}
                            </h2>
                            <button
                                @click="closeCategoryModal"
                                class="text-gray-400 hover:text-white transition-colors"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="saveCategory" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                            <input
                                type="text"
                                v-model="categoryForm.name"
                                required
                                placeholder="Support"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Emoji</label>
                            <input
                                type="text"
                                v-model="categoryForm.emoji"
                                placeholder="🎫 oder :ticket:"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Beschreibung</label>
                            <textarea
                                v-model="categoryForm.description"
                                rows="2"
                                placeholder="Beschreibung der Kategorie"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Willkommensnachricht</label>
                            <textarea
                                v-model="categoryForm.welcome_message"
                                rows="4"
                                placeholder="Willkommen in deinem Ticket, {user}! Bitte beschreibe dein Anliegen.&#10;&#10;Ein Supporter wird sich bald bei dir melden."
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">
                                Verfügbare Platzhalter: <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{user}</code> (wird durch den Benutzernamen ersetzt)
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Discord-Kategorie *</label>
                            <select
                                v-model="categoryForm.category_id"
                                required
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            >
                                <option value="">Bitte wählen...</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    📁 {{ cat.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Die Kategorie, in der Ticket-Channels erstellt werden</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Channel-Namen-Format *</label>
                            <input
                                type="text"
                                v-model="categoryForm.channel_name_format"
                                required
                                placeholder="ticket-{user}"
                                class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                            />
                            <p class="text-xs text-gray-400 mt-1">
                                Verfügbare Platzhalter: <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{user}</code>, 
                                <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{date}</code>, 
                                <code class="bg-[#1a1b1e] px-1 py-0.5 rounded">{id}</code>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Supporter-Rollen</label>
                            <div class="space-y-2">
                                <div
                                    v-for="role in selectedSupporterRoles"
                                    :key="role.id"
                                    class="flex items-center justify-between bg-[#36393f] rounded p-2"
                                >
                                    <span class="text-sm text-white">{{ role.name }}</span>
                                    <button
                                        type="button"
                                        @click="removeSupporterRole(role.id)"
                                        class="text-red-400 hover:text-red-300"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <select
                                    @change="addSupporterRole($event.target.value)"
                                    class="w-full rounded bg-[#36393f] border border-[#202225] text-white px-3 py-2 focus:outline-none focus:border-[#5865f2]"
                                >
                                    <option value="">Rolle hinzufügen...</option>
                                    <option
                                        v-for="role in availableRoles"
                                        :key="role.id"
                                        :value="role.id"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                v-model="categoryForm.enabled"
                                class="rounded border-gray-500 bg-[#36393f] text-[#5865f2] focus:ring-[#5865f2]"
                            />
                            <label class="text-sm text-gray-300">Kategorie aktiv</label>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-[#202225] gap-3">
                            <button
                                type="button"
                                @click="closeCategoryModal"
                                class="px-6 py-2.5 bg-[#36393f] hover:bg-[#40444b] text-white rounded-lg transition-colors font-medium"
                            >
                                Abbrechen
                            </button>
                            <button
                                type="submit"
                                :disabled="categoryForm.processing"
                                class="px-6 py-2.5 bg-gradient-to-r from-[#5865f2] to-[#4752c4] hover:from-[#4752c4] hover:to-[#3c45a5] text-white rounded-lg transition-all disabled:opacity-50 font-medium"
                            >
                                {{ categoryForm.processing ? 'Speichere...' : 'Speichern' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>

<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    guild: Object,
    guilds: Array,
    channels: Array,
    categories: Array,
    roles: Array,
    ticketCategories: Array,
    ticketPost: Object,
    ticketTranscripts: {
        type: Array,
        default: () => []
    },
});

// Ticket Post
const ticketPostForm = useForm({
    channel_id: props.ticketPost?.channel_id || '',
    embed_title: props.ticketPost?.embed_title || 'Ticket System',
    embed_description: props.ticketPost?.embed_description || '',
    embed_color: props.ticketPost?.embed_color || '#ff0000',
    embed_image: props.ticketPost?.embed_image || '',
    embed_banner: props.ticketPost?.embed_banner || '',
    embed_footer: props.ticketPost?.embed_footer ?? true,
    enabled: props.ticketPost?.enabled ?? true,
});

const resendingPost = ref(false);

// Ticket Categories
const showCategoryModal = ref(false);
const editingCategory = ref(null);
const categoryForm = useForm({
    name: '',
    emoji: '',
    description: '',
    welcome_message: '',
    category_id: '',
    channel_name_format: 'ticket-{user}',
    supporter_roles: [],
    enabled: true,
    order: 0,
});

const selectedSupporterRoles = ref([]);

// Discord Markdown Parser (vereinfacht)
function parseDiscordMarkdown(text) {
    if (!text) return '';
    return text
        .replace(/\*\*([^*]+)\*\*/g, '<strong class="font-bold">$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em class="italic">$1</em>')
        .replace(/`([^`]+)`/g, '<code class="bg-[#1a1b1e] px-1 py-0.5 rounded text-xs">$1</code>')
        .replace(/\n/g, '<br>');
}

const renderedTitle = computed(() => parseDiscordMarkdown(ticketPostForm.embed_title));
const renderedDescription = computed(() => parseDiscordMarkdown(ticketPostForm.embed_description));

const availableRoles = computed(() => {
    const selectedIds = selectedSupporterRoles.value.map(r => r.id);
    return props.roles.filter(r => !selectedIds.includes(r.id));
});

function openCategoryModal(category = null) {
    editingCategory.value = category;
    if (category) {
        categoryForm.name = category.name;
        categoryForm.emoji = category.emoji || '';
        categoryForm.description = category.description || '';
        categoryForm.welcome_message = category.welcome_message || '';
        categoryForm.category_id = category.category_id;
        categoryForm.channel_name_format = category.channel_name_format;
        categoryForm.enabled = category.enabled;
        categoryForm.order = category.order;
        selectedSupporterRoles.value = (category.supporter_roles || []).map(roleId => {
            return props.roles.find(r => r.id === roleId) || { id: roleId, name: 'Unbekannte Rolle' };
        }).filter(r => r);
    } else {
        categoryForm.reset();
        selectedSupporterRoles.value = [];
    }
    showCategoryModal.value = true;
}

function closeCategoryModal() {
    showCategoryModal.value = false;
    editingCategory.value = null;
    categoryForm.reset();
    selectedSupporterRoles.value = [];
}

function addSupporterRole(roleId) {
    if (!roleId) return;
    const role = props.roles.find(r => r.id === roleId);
    if (role && !selectedSupporterRoles.value.find(r => r.id === roleId)) {
        selectedSupporterRoles.value.push(role);
    }
    // Reset select
    const select = event?.target;
    if (select) select.value = '';
}

function removeSupporterRole(roleId) {
    selectedSupporterRoles.value = selectedSupporterRoles.value.filter(r => r.id !== roleId);
}

function saveCategory() {
    categoryForm.supporter_roles = selectedSupporterRoles.value.map(r => r.id);
    
    if (editingCategory.value) {
        categoryForm.put(route('guild.ticket-categories.update', {
            guild: props.guild.id,
            id: editingCategory.value.id
        }), {
            preserveScroll: true,
            onSuccess: () => {
                closeCategoryModal();
            },
        });
    } else {
        categoryForm.post(route('guild.ticket-categories.store', { guild: props.guild.id }), {
            preserveScroll: true,
            onSuccess: () => {
                closeCategoryModal();
            },
        });
    }
}

function deleteCategory(id) {
    if (confirm('Möchtest du diese Kategorie wirklich löschen?')) {
        router.delete(route('guild.ticket-categories.delete', { guild: props.guild.id, id }), {
            preserveScroll: true,
        });
    }
}

function saveTicketPost() {
    ticketPostForm.post(route('guild.ticket-post.store', { guild: props.guild.id }), {
        preserveScroll: true,
    });
}

function resendTicketPost() {
    if (confirm('Möchtest du den Ticket-Post wirklich erneut senden? Die alte Nachricht wird nicht gelöscht.')) {
        resendingPost.value = true;
        router.post(route('guild.ticket-post.resend', { guild: props.guild.id }), {}, {
            preserveScroll: true,
            onFinish: () => {
                resendingPost.value = false;
            },
        });
    }
}
</script>
