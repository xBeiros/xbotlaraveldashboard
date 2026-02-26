<template>
  <Teleport to="body">
    <!-- Nur Information: notwendige Cookies (Session, XSRF) – kein Accept/Reject nötig -->
    <div
      v-if="showNotice"
      class="fixed bottom-0 left-0 right-0 z-[100] border-t border-gray-200 bg-gray-50 p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] dark:border-gray-700 dark:bg-gray-800 dark:shadow-[0_-4px_20px_rgba(0,0,0,0.3)]"
    >
      <div class="mx-auto flex max-w-4xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="min-w-0 flex-1 text-sm text-gray-600 dark:text-gray-400">
          {{ t('legal.cookieBannerNecessaryOnly') }}
        </p>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
          <Link
            :href="route('legal.cookies')"
            class="text-sm text-gray-500 underline hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          >
            {{ t('legal.cookieBannerMoreInfo') }}
          </Link>
          <button
            type="button"
            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white"
            @click="dismiss"
          >
            {{ t('legal.cookieBannerUnderstood') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const STORAGE_KEY = 'xbot_cookie_notice_seen';

const { t } = useI18n();
const showNotice = ref(false);

function dismiss() {
  if (typeof window === 'undefined') return;
  try {
    window.localStorage.setItem(STORAGE_KEY, '1');
    showNotice.value = false;
  } catch {
    showNotice.value = false;
  }
}

onMounted(() => {
  if (typeof window === 'undefined') return;
  try {
    showNotice.value = !window.localStorage.getItem(STORAGE_KEY);
  } catch {
    showNotice.value = true;
  }
});
</script>
