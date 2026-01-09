<template>
    <WidgetContainer
        :widget="widget"
        :title="t('widgets.leveling.title')"
        :data-endpoint="dataEndpoint"
        @remove="$emit('remove', $event)"
    >
        <template #default="{ data }">
            <div class="text-center">
                <div class="text-4xl font-bold text-[#5865f2] mb-2">
                    {{ data?.count || '0' }}
                </div>
                <p class="text-sm text-gray-400">{{ t('widgets.leveling.description') }}</p>
            </div>
        </template>
    </WidgetContainer>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import WidgetContainer from './WidgetContainer.vue';

const { t } = useI18n();

const props = defineProps({
    widget: {
        type: Object,
        required: true,
    },
    guildId: {
        type: String,
        default: null,
    },
});

const dataEndpoint = computed(() => {
    const url = route('dashboard.widgets.data', { type: 'leveling' });
    return props.guildId ? `${url}?guild_id=${props.guildId}` : url;
});
</script>

