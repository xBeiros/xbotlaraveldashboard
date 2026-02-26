<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LegalLayout from '@/Layouts/LegalLayout.vue';

const { t } = useI18n();
</script>

<template>
    <LegalLayout>
        <Head :title="t('legal.imprintTitle')" />
        <div class="w-full max-w-4xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ t('legal.imprintTitle') }}</h1>
            <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 space-y-6 whitespace-pre-wrap">{{ t('legal.imprintContent') }}</div>
        </div>
    </LegalLayout>
</template>
