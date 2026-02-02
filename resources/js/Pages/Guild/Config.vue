<script setup>
import GuildLayout from '@/Layouts/GuildLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import MembersWidget from '@/Components/Widgets/MembersWidget.vue';
import TicketsWidget from '@/Components/Widgets/TicketsWidget.vue';
import GiveawaysWidget from '@/Components/Widgets/GiveawaysWidget.vue';
import LevelingWidget from '@/Components/Widgets/LevelingWidget.vue';

const { t } = useI18n();

const props = defineProps({
    guild: Object,
    guilds: Array,
    widgets: {
        type: Array,
        default: () => [],
    },
    addOns: {
        type: Object,
        default: () => ({}),
    },
});

// Reaktive Widgets-Liste für Drag & Drop
const widgetsList = ref([...props.widgets]);

// Aktualisiere widgetsList wenn props.widgets sich ändert
watch(() => props.widgets, (newWidgets) => {
    widgetsList.value = [...newWidgets];
}, { immediate: true, deep: true });

const showAddWidgetModal = ref(false);
const selectedWidgetType = ref(null);
const draggedWidgetIndex = ref(null);
const dragOverWidgetIndex = ref(null);

const allWidgetTypes = [
    { value: 'members', label: t('widgets.types.members') },
    { value: 'tickets', label: t('widgets.types.tickets') },
    { value: 'giveaways', label: t('widgets.types.giveaways') },
    { value: 'leveling', label: t('widgets.types.leveling') },
];

// Filtere bereits hinzugefügte Widget-Typen heraus
const availableWidgetTypes = computed(() => {
    const existingTypes = widgetsList.value.map(w => w.type);
    return allWidgetTypes.filter(widget => !existingTypes.includes(widget.value));
});

const getWidgetComponent = (type) => {
    switch (type) {
        case 'members':
            return MembersWidget;
        case 'tickets':
            return TicketsWidget;
        case 'giveaways':
            return GiveawaysWidget;
        case 'leveling':
            return LevelingWidget;
        default:
            return null;
    }
};

const addWidget = async () => {
    if (!selectedWidgetType.value) {
        return;
    }
    
    if (!props.guild?.id) {
        console.error('Guild ID fehlt');
        alert('Fehler: Server-ID nicht gefunden');
        return;
    }
    
    router.post(route('dashboard.widgets.store'), {
        widget_type: selectedWidgetType.value,
        guild_id: props.guild.id,
        position: props.widgets.length,
        column: 1,
        row: 1,
    }, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            showAddWidgetModal.value = false;
            selectedWidgetType.value = null;
        },
        onError: (errors) => {
            console.error('Error adding widget:', errors);
            if (errors.message) {
                alert(errors.message);
            } else if (typeof errors === 'string') {
                alert(errors);
            } else {
                alert('Fehler beim Hinzufügen des Widgets');
            }
        }
    });
};

const removeWidget = (widgetId) => {
    router.reload({ only: ['widgets'] });
};

// Add-On Toggle
const toggleAddOn = async (addonType, enabled) => {
    try {
        router.post(route('guild.addon.toggle', { guild: props.guild.id }), {
            addon_type: addonType,
            enabled: !enabled,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['addOns'],
        });
    } catch (e) {
        console.error('Error toggling add-on:', e);
    }
};

// Drag & Drop Funktionen
const onWidgetDragStart = (event, index) => {
    draggedWidgetIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/html', event.target);
    // Füge eine Klasse zum Body hinzu, um Drag-Styling zu aktivieren
    document.body.classList.add('dragging-widget');
};

const onWidgetDragEnd = () => {
    // Aktualisiere die Positionen nur wenn tatsächlich verschoben wurde
    if (draggedWidgetIndex.value !== null && dragOverWidgetIndex.value !== null) {
        const oldIndex = draggedWidgetIndex.value;
        const newIndex = dragOverWidgetIndex.value;
        
        if (oldIndex !== newIndex) {
            // Erstelle eine neue Array-Kopie
            const newArray = [...widgetsList.value];
            
            // Entferne das Element von der alten Position
            const [draggedWidget] = newArray.splice(oldIndex, 1);
            
            // Berechne die neue Position
            let insertIndex = newIndex;
            if (oldIndex < newIndex) {
                insertIndex = newIndex - 1;
            }
            
            // Füge das Element an der neuen Position ein
            newArray.splice(insertIndex, 0, draggedWidget);
            
            // Aktualisiere die Positionen
            newArray.forEach((widget, index) => {
                widget.position = index;
            });
            
            // Aktualisiere das Array sofort für flüssige UI
            widgetsList.value = newArray;
            
            // Speichere die neuen Positionen im Hintergrund (ohne Page-Reload)
            router.post(route('dashboard.widgets.reorder'), {
                widgets: newArray.map((widget, index) => ({
                    id: widget.id,
                    position: index,
                    column: widget.column || 1,
                    row: widget.row || 1,
                })),
                guild_id: props.guild.id,
            }, {
                preserveState: true,
                preserveScroll: true,
                only: ['widgets'], // Nur Widgets aktualisieren, nicht die ganze Seite
                onError: (errors) => {
                    console.error('Error reordering widgets:', errors);
                    // Bei Fehler: Zurück zum ursprünglichen Zustand
                    widgetsList.value = [...props.widgets];
                }
            });
        }
    }
    
    draggedWidgetIndex.value = null;
    dragOverWidgetIndex.value = null;
    document.body.classList.remove('dragging-widget');
};

const onWidgetDragOver = (event, index) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    
    if (draggedWidgetIndex.value !== null && draggedWidgetIndex.value !== index) {
        dragOverWidgetIndex.value = index;
    }
};

const onWidgetDragLeave = (event, index) => {
    // Nur zurücksetzen, wenn wir wirklich das Element verlassen (nicht nur ein Child)
    const rect = event.currentTarget.getBoundingClientRect();
    const x = event.clientX;
    const y = event.clientY;
    
    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
        if (dragOverWidgetIndex.value === index) {
            dragOverWidgetIndex.value = null;
        }
    }
};
</script>

<template>
    <Head :title="`${guild.name} - Dashboard`" />

    <GuildLayout :guild="guild" :guilds="guilds" :add-ons="addOns">
        <div class="p-8">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-500/20 border border-green-500 rounded-lg text-green-400">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 p-4 bg-red-500/20 border border-red-500 rounded-lg text-red-400">
                {{ $page.props.flash.error }}
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">Dashboard</h1>
                <button
                    @click="showAddWidgetModal = true"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('widgets.addWidget') }}
                </button>
            </div>
            
            <!-- Widget Grid -->
            <div v-if="widgetsList && widgetsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-8">
                <div
                    v-for="(widget, index) in widgetsList"
                    :key="`widget-${widget.id}-${widget.position}`"
                    :draggable="true"
                    @dragstart="onWidgetDragStart($event, index)"
                    @dragend="onWidgetDragEnd"
                    @dragover="onWidgetDragOver($event, index)"
                    @dragleave="onWidgetDragLeave($event, index)"
                    :class="[
                        'transition-all duration-200 ease-in-out',
                        draggedWidgetIndex === index ? 'opacity-30 scale-95 cursor-grabbing z-50' : 'cursor-grab',
                        dragOverWidgetIndex === index && draggedWidgetIndex !== index ? 'scale-105 z-40 border-2 border-[#5865f2] rounded-lg' : '',
                        draggedWidgetIndex !== null && draggedWidgetIndex !== index && dragOverWidgetIndex !== index ? 'opacity-100' : ''
                    ]"
                    style="will-change: transform;"
                >
                    <component
                        :is="getWidgetComponent(widget.type)"
                        :widget="widget"
                        :guild-id="guild.id"
                        @remove="removeWidget"
                    />
                </div>
            </div>
            
            <div v-else class="text-center py-12 mb-8 bg-[#2f3136] rounded-lg border border-[#202225]">
                <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h2 class="text-xl font-semibold text-white mb-2">{{ t('widgets.noWidgets') }}</h2>
                <p class="text-gray-400 mb-4">{{ t('widgets.noWidgetsDescription') }}</p>
                <button
                    @click="showAddWidgetModal = true"
                    class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors"
                >
                    {{ t('widgets.addFirstWidget') }}
                </button>
            </div>
            
            <!-- Add-Ons Section – Karten wie im Bild -->
            <div class="mt-8">
                <h2 class="text-lg font-bold mb-3 text-white">{{ t('addOns.title') }}</h2>
                <p class="text-gray-400 mb-4 text-xs">{{ t('addOns.description') }}</p>
                <div class="grid gap-12 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 justify-items-start">
                    <div
                        v-for="(addOn, type) in addOns"
                        :key="type"
                        class="group cursor-pointer w-[345px] h-[260px] p-6 rounded-lg bg-dark-800 hover:bg-dark-900 grid grid-cols-1 gap-4 transition-all duration-200 hover:shadow-lg flex flex-col"
                        @click="toggleAddOn(type, addOn.enabled)"
                    >
                        <div class="flex items-start justify-between">
                            <!-- Icon-Box (wie Referenz: brand-dark, hover brand-hover) -->
                            <div class="transition-all duration-200 w-[60px] h-[56px] min-w-[60px] rounded-lg flex items-center justify-center bg-brand-dark group-hover:bg-brand-hover group-hover:bg-opacity-60 text-[#b3eeff]">
                                <svg v-if="type === 'team_management'" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <svg v-else-if="type === 'faction_management'" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                <svg v-else class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                            </div>
                            <!-- Badge "Neu!" wenn aktiv (wie Referenz: success-default) -->
                            <span
                                v-if="addOn.isNew"
                                class="inline-flex items-center justify-center rounded-full bg-success-default/20 text-success-default text-xs px-2 py-0.5 font-medium whitespace-nowrap"
                            >
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.938 2.798c-.17-1.066-1.705-1.066-1.876 0A2.727 2.727 0 012.8 5.062c-1.066.17-1.066 1.705 0 1.875A2.727 2.727 0 015.062 9.2c.17 1.067 1.706 1.067 1.876 0A2.727 2.727 0 019.2 6.937c1.066-.17 1.066-1.705 0-1.875a2.727 2.727 0 01-2.263-2.264z"/></svg>
                                {{ t('addOns.new') }}
                            </span>
                            <span v-else class="w-0 h-0 overflow-hidden" aria-hidden="true">.</span>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ addOn.name }}</p>
                            <p class="text-xs text-dark-300 mt-1.5 line-clamp-2">{{ addOn.description }}</p>
                        </div>
                        <!-- Button: inaktiv = bg-white/10, aktiv = brand mit Check -->
                        <div class="mt-auto">
                            <button
                                type="button"
                                class="relative flex shrink-0 rounded-lg transition-all duration-200 items-center justify-center gap-2 w-full text-base px-4 py-2"
                                :class="addOn.enabled
                                    ? 'bg-brand-default/10 text-brand-default hover:bg-brand-default/20'
                                    : 'bg-white/10 text-white hover:bg-white/20'"
                                @click.stop="toggleAddOn(type, addOn.enabled)"
                            >
                                <template v-if="addOn.enabled">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ t('addOns.active') }}</span>
                                </template>
                                <template v-else>
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>{{ t('addOns.activate') }}</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add Widget Modal -->
        <div
            v-if="showAddWidgetModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="showAddWidgetModal = false"
        >
            <div class="bg-[#2f3136] rounded-lg p-6 w-full max-w-md border border-[#202225]">
                <h3 class="text-xl font-bold mb-4">{{ t('widgets.addWidget') }}</h3>
                
                <div class="space-y-4">
                    <div v-if="availableWidgetTypes.length === 0">
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-400">{{ t('widgets.allWidgetsAdded') }}</p>
                        </div>
                    </div>
                    <div v-else>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('widgets.selectType') }}
                        </label>
                        <select
                            v-model="selectedWidgetType"
                            class="w-full bg-[#36393f] border border-[#202225] rounded px-3 py-2 text-white"
                        >
                            <option value="">{{ t('widgets.selectTypePlaceholder') }}</option>
                            <option
                                v-for="type in availableWidgetTypes"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                    </div>
                    
                    <div class="flex gap-3 justify-end mt-6">
                        <button
                            @click="showAddWidgetModal = false"
                            class="px-4 py-2 bg-[#36393f] hover:bg-[#40444b] text-white rounded transition-colors"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            @click="addWidget"
                            :disabled="!selectedWidgetType"
                            class="px-4 py-2 bg-[#5865f2] hover:bg-[#4752c4] text-white rounded transition-colors disabled:opacity-50"
                        >
                            {{ t('common.add') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuildLayout>
</template>
