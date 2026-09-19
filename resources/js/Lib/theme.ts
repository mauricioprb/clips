import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

export const ufnBlue = {
    50: '#eef5fc',
    100: '#d9e8f7',
    200: '#b6d3ef',
    300: '#86b6e3',
    400: '#4f93d3',
    500: '#2a74bd',
    600: '#135fa6',
    700: '#0b4c85',
    800: '#0a3f6d',
    900: '#0b345a',
    950: '#07213b',
};

export const coolGray = {
    0: '#ffffff',
    50: '#f6f8fb',
    100: '#eef2f7',
    200: '#dfe5ee',
    300: '#c5cfdc',
    400: '#94a3b8',
    500: '#64748b',
    600: '#4b5a6e',
    700: '#334155',
    800: '#1f2a3a',
    900: '#141b26',
    950: '#0c1119',
};

export const ClipsPreset = definePreset(Aura, {
    primitive: {
        borderRadius: { none: '0', xs: '2px', sm: '4px', md: '8px', lg: '12px', xl: '16px' },
    },
    semantic: {
        primary: {
            ...ufnBlue,
            color: 'light-dark({primary.600}, {primary.400})',
            contrastColor: 'light-dark(#ffffff, {surface.950})',
            hoverColor: 'light-dark({primary.700}, {primary.300})',
            activeColor: 'light-dark({primary.800}, {primary.200})',
        },
        surface: coolGray,
    },
    components: {
        toast: {
            root: { width: '26rem' },
            success: {
                background: 'light-dark({surface.0}, {surface.900})',
                borderColor: 'light-dark({surface.200}, {surface.700})',
                color: 'light-dark({surface.900}, {surface.100})',
                detailColor: 'light-dark({surface.600}, {surface.400})',
                shadow: '0 12px 32px -12px color-mix(in srgb, {surface.950}, transparent 70%)',
                closeButton: {
                    hoverBackground: 'light-dark({surface.100}, {surface.800})',
                    focusRing: { color: '{primary.color}', shadow: 'none' },
                },
            },
        },
    },
});
