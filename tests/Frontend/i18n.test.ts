import { describe, expect, it } from 'vitest';
import { isTranslationKey, translate } from '@/Composables/useI18n';

describe('translate', () => {
    it('replaces every placeholder', () => {
        expect(translate('day.logged', { logged: '2h', target: '4h' })).toBe('2h de 4h');
    });

    it('recognizes status keys sent by the server', () => {
        expect(isTranslationKey('status.entry-saved')).toBe(true);
        expect(isTranslationKey('status.unknown')).toBe(false);
    });
});
