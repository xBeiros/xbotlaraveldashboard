import { createI18n } from 'vue-i18n';
import de from './locales/de.json';
import en from './locales/en.json';
import tr from './locales/tr.json';

// Get language from localStorage or default to 'de'
const savedLanguage = localStorage.getItem('app_language') || 'de';

const i18n = createI18n({
    locale: savedLanguage,
    fallbackLocale: 'de',
    messages: {
        de,
        en,
        tr,
    },
    legacy: false,
});

export default i18n;

