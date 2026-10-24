import { createFuseLocale, ptBR } from '~/lib/locale';

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.use(createFuseLocale(ptBR));
});
