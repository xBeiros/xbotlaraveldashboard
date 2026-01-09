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

const availableWidgetTypes = computed(() => [
    { value: 'members', label: t('widgets.types.members') },
    { value: 'tickets', label: t('widgets.types.tickets') },
    { value: 'giveaways', label: t('widgets.types.giveaways') },
    { value: 'leveling', label: t('widgets.types.leveling') },
]);

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

// Drag & Drop Funktionen
const onWidgetDragStart = (index) => {
    draggedWidgetIndex.value = index;
};

const onWidgetDragEnd = () => {
    draggedWidgetIndex.value = null;
    dragOverWidgetIndex.value = null;
};

const onWidgetDragOver = (event, index) => {
    event.preventDefault();
    if (draggedWidgetIndex.value !== null && draggedWidgetIndex.value !== index) {
        dragOverWidgetIndex.value = index;
    }
};

const onWidgetDrop = async (event, dropIndex) => {
    event.preventDefault();
    
    if (draggedWidgetIndex.value === null || draggedWidgetIndex.value === dropIndex) {
        dragOverWidgetIndex.value = null;
        return;
    }
    
    const oldIndex = draggedWidgetIndex.value;
    
    // Erstelle eine neue Array-Kopie
    const newArray = [...widgetsList.value];
    
    // Entferne das Element von der alten Position
    const [draggedWidget] = newArray.splice(oldIndex, 1);
    
    // Berechne die neue Position
    let newIndex = dropIndex;
    if (oldIndex < dropIndex) {
        newIndex = dropIndex - 1;
    }
    
    // Füge das Element an der neuen Position ein
    newArray.splice(newIndex, 0, draggedWidget);
    
    // Aktualisiere die Positionen
    newArray.forEach((widget, index) => {
        widget.position = index;
    });
    
    // Aktualisiere das Array
    widgetsList.value = newArray;
    
    // Speichere die neuen Positionen
    try {
        await router.post(route('dashboard.widgets.reorder'), {
            widgets: newArray.map((widget, index) => ({
                id: widget.id,
                position: index,
                column: widget.column || 1,
                row: widget.row || 1,
            })),
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (e) {
        console.error('Error reordering widgets:', e);
        // Bei Fehler: Zurück zum ursprünglichen Zustand
        widgetsList.value = [...props.widgets];
    }
    
    draggedWidgetIndex.value = null;
    dragOverWidgetIndex.value = null;
};
</script>

<template>
    <Head :title="`${guild.name} - Dashboard`" />

    <GuildLayout :guild="guild" :guilds="guilds">
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
            <div v-if="widgetsList && widgetsList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
                <div
                    v-for="(widget, index) in widgetsList"
                    :key="widget.id"
                    :draggable="true"
                    @dragstart="onWidgetDragStart(index)"
                    @dragend="onWidgetDragEnd"
                    @dragover="onWidgetDragOver($event, index)"
                    @drop="onWidgetDrop($event, index)"
                    :class="[
                        'transition-all',
                        draggedWidgetIndex === index ? 'opacity-50 cursor-grabbing' : 'cursor-grab',
                        dragOverWidgetIndex === index && draggedWidgetIndex !== index ? 'scale-105 z-10' : ''
                    ]"
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
                    <div>
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
