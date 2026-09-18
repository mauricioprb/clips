import messages from '@/i18n/pt-BR';

export type TranslationKey = keyof typeof messages;
export type TranslationParams = Record<string, string | number>;

export function isTranslationKey(key: string): key is TranslationKey {
    return key in messages;
}

export function translate(key: TranslationKey, params: TranslationParams = {}): string {
    return Object.entries(params).reduce<string>(
        (message, [name, value]) => message.replaceAll(`{${name}}`, String(value)),
        messages[key],
    );
}

export function useI18n() {
    return { t: translate };
}
