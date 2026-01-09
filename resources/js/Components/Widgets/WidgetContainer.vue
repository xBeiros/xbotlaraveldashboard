<template>
    <div
        :class="[
            'widget-container bg-[#2f3136] rounded-lg p-4 border border-[#202225] transition-all',
            isDragging ? 'opacity-50' : 'hover:border-[#5865f2]'
        ]"
        :style="{ gridColumn: `span ${widget.column || 1}`, gridRow: `span ${widget.row || 1}` }"
    >
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-300">{{ title }}</h3>
            <div class="flex items-center gap-2">
                <button
                    @click.stop="refreshData"
                    :disabled="loading"
                    class="text-gray-400 hover:text-white transition-colors"
                    :title="t('widgets.refresh')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
                <button
                    @click.stop="removeWidget"
                    class="text-gray-400 hover:text-red-400 transition-colors"
                    :title="t('widgets.remove')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#5865f2]"></div>
        </div>
        <div v-else-if="error" class="text-red-400 text-sm text-center py-4">
            {{ error }}
        </div>
        <slot v-else :data="data" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    dataEndpoint: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['remove', 'refresh']);

const loading = ref(false);
const error = ref(null);
const data = ref(null);
const isDragging = ref(false);

const fetchData = async () => {
    loading.value = true;
    error.value = null;
    
    try {
        const response = await fetch(props.dataEndpoint);
        const result = await response.json();
        
        if (response.ok) {
            data.value = result;
        } else {
            error.value = result.error || t('widgets.error.loading');
        }
    } catch (e) {
        error.value = t('widgets.error.network');
    } finally {
        loading.value = false;
    }
};

const refreshData = () => {
    fetchData();
    emit('refresh');
};

const removeWidget = async () => {
    if (!confirm(t('widgets.confirmRemove'))) {
        return;
    }
    
    try {
        await router.delete(route('dashboard.widgets.delete', { id: props.widget.id }));
        emit('remove', props.widget.id);
    } catch (e) {
        console.error('Error removing widget:', e);
    }
};

const startDrag = (e) => {
    // Verhindere Drag, wenn auf Buttons geklickt wird
    if (e.target.closest('button')) {
        return;
    }
    isDragging.value = true;
    document.addEventListener('mouseup', () => {
        isDragging.value = false;
    }, { once: true });
};

onMounted(() => {
    fetchData();
    // Keine automatische Aktualisierung - nur bei manuellem Refresh oder Seitenreload
});
</script>

