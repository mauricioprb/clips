import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import ToastService from 'primevue/toastservice';
import { createApp, h, type DefineComponent } from 'vue';
import { translate } from '@/Composables/useI18n';
import primeVueLocale from '@/i18n/primevue-pt-BR';
import { ClipsPreset } from '@/Lib/theme';

const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} | ${translate('app.name')}` : translate('app.name')),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Unknown Inertia page: ${name}`);
        }

        return page();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, {
                license: import.meta.env.VITE_PRIMEUI_LICENSE,
                locale: primeVueLocale,
                theme: {
                    preset: ClipsPreset,
                    options: {
                        darkModeSelector: '.dark',
                        cssLayer: { name: 'primevue', order: 'theme, base, primevue' },
                    },
                },
            })
            .use(ToastService)
            .use(ConfirmationService)
            .mount(el);
    },
    progress: {
        color: 'var(--p-primary-color)',
        delay: 150,
    },
});
