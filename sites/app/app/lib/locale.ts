import { hasInjectionContext, inject, type App, type InjectionKey } from 'vue';
import { en } from '@/lib/locales/en';

export type FuseLocale = typeof en;

const fuseLocaleKey: InjectionKey<FuseLocale> = Symbol('fuse-locale');

let appLocale: FuseLocale = en;

export function createFuseLocale(locale: FuseLocale) {
  return {
    install(app: App) {
      appLocale = locale;
      app.provide(fuseLocaleKey, locale);
    },
  };
}

export function useFuseLocale(): FuseLocale {
  return hasInjectionContext() ? inject(fuseLocaleKey, appLocale) : appLocale;
}

export { en };
export { ptBR } from '@/lib/locales/pt-BR';
