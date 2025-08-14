// Translation Helper - Statamic CMS style for Inertia.js
export class Translator {
    constructor(translations = {}, locale = 'en') {
        this.translations = translations;
        this.locale = locale;
    }

    /**
     * Get translation string
     * @param {string} key - Translation key (e.g., 'admin.actions.save')
     * @param {object} replacements - Values to replace in translation
     * @returns {string}
     */
    get(key, replacements = {}) {
        const keys = key.split('.');
        let translation = this.translations;

        // Navigate through nested keys
        for (const k of keys) {
            if (translation && typeof translation === 'object' && k in translation) {
                translation = translation[k];
            } else {
                return key; // Return key if translation not found
            }
        }

        // If translation is not a string, return the key
        if (typeof translation !== 'string') {
            return key;
        }

        // Replace placeholders
        return this.replacePlaceholders(translation, replacements);
    }

    /**
     * Replace placeholders in translation string
     * @param {string} translation
     * @param {object} replacements
     * @returns {string}
     */
    replacePlaceholders(translation, replacements) {
        return translation.replace(/:(\w+)/g, (match, key) => {
            return replacements[key] !== undefined ? replacements[key] : match;
        });
    }

    /**
     * Get translation with choice (pluralization)
     * @param {string} key
     * @param {number} count
     * @param {object} replacements
     * @returns {string}
     */
    choice(key, count, replacements = {}) {
        let translation = this.get(key, { ...replacements, count });
        
        // Simple pluralization logic
        if (translation.includes('|')) {
            const choices = translation.split('|');
            if (count === 0 && choices.length > 2) {
                translation = choices[0]; // Zero case
            } else if (count === 1) {
                translation = choices.length > 2 ? choices[1] : choices[0]; // Singular
            } else {
                translation = choices[choices.length - 1]; // Plural
            }
        }

        return this.replacePlaceholders(translation, { ...replacements, count });
    }

    /**
     * Check if translation exists
     * @param {string} key
     * @returns {boolean}
     */
    has(key) {
        const keys = key.split('.');
        let translation = this.translations;

        for (const k of keys) {
            if (translation && typeof translation === 'object' && k in translation) {
                translation = translation[k];
            } else {
                return false;
            }
        }

        return typeof translation === 'string';
    }

    /**
     * Get all translations for a namespace
     * @param {string} namespace
     * @returns {object}
     */
    getNamespace(namespace) {
        return this.translations[namespace] || {};
    }

    /**
     * Set locale
     * @param {string} locale
     */
    setLocale(locale) {
        this.locale = locale;
    }

    /**
     * Get current locale
     * @returns {string}
     */
    getLocale() {
        return this.locale;
    }
}

// Vue 3 Plugin
export const TranslationPlugin = {
    install(app, options) {
        const translator = new Translator(
            options.translations || {},
            options.locale || 'en'
        );

        // Provide translator instance
        app.provide('translator', translator);

        // Global properties
        app.config.globalProperties.$t = (key, replacements) => translator.get(key, replacements);
        app.config.globalProperties.$tc = (key, count, replacements) => translator.choice(key, count, replacements);
        app.config.globalProperties.$te = (key) => translator.has(key);
        app.config.globalProperties.$locale = translator.getLocale();
    }
};

// Composable for Vue 3
export function useTranslator() {
    const translator = inject('translator');
    
    if (!translator) {
        throw new Error('Translator not provided. Make sure to install TranslationPlugin.');
    }

    return {
        t: (key, replacements) => translator.get(key, replacements),
        tc: (key, count, replacements) => translator.choice(key, count, replacements),
        te: (key) => translator.has(key),
        locale: computed(() => translator.getLocale()),
        translator
    };
}

// Helper function to format currency
export function formatCurrency(amount, currency = 'EUR', locale = 'it-IT') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Helper function to format date
export function formatDate(date, options = {}, locale = 'it-IT') {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    return new Intl.DateTimeFormat(locale, { ...defaultOptions, ...options }).format(new Date(date));
}

// Helper function to format relative time
export function formatRelativeTime(date, locale = 'it-IT') {
    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
    const now = new Date();
    const target = new Date(date);
    const diff = target.getTime() - now.getTime();
    
    const units = [
        { unit: 'year', ms: 31536000000 },
        { unit: 'month', ms: 2628000000 },
        { unit: 'day', ms: 86400000 },
        { unit: 'hour', ms: 3600000 },
        { unit: 'minute', ms: 60000 },
        { unit: 'second', ms: 1000 }
    ];
    
    for (const { unit, ms } of units) {
        if (Math.abs(diff) >= ms) {
            return rtf.format(Math.round(diff / ms), unit);
        }
    }
    
    return rtf.format(0, 'second');
}

export default Translator;
