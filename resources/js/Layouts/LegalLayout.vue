<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppFooter from '@/Components/AppFooter.vue';

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-gray-900">
        <header class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-4">
            <div class="max-w-4xl mx-auto px-4 flex items-center justify-between">
                <Link href="/" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    ← {{ t('common.back') }}
                </Link>
                <Link href="/" class="font-semibold text-gray-800 dark:text-gray-200">XBot</Link>
            </div>
        </header>
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>
