<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <footer class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span>© {{ new Date().getFullYear() }} XBot. {{ t('legal.allRightsReserved') }}</span>
                <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                    <Link :href="route('legal.privacy')" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        {{ t('legal.privacyLink') }}
                    </Link>
                    <Link :href="route('legal.imprint')" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        {{ t('legal.imprintLink') }}
                    </Link>
                    <Link :href="route('legal.cookies')" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        {{ t('legal.cookiesLink') }}
                    </Link>
                    <Link :href="route('legal.terms')" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                        {{ t('legal.termsLink') }}
                    </Link>
                </nav>
            </div>
        </div>
    </footer>
</template>
