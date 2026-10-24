import prettier from 'eslint-config-prettier/flat';
import withNuxt from './.nuxt/eslint.config.mjs';

export default withNuxt(prettier, {
  files: ['app/ui/**/*.vue'],
  rules: {
    'vue/multi-word-component-names': 'off',
    'vue/require-default-prop': 'off',
  },
});
