import { flushPromises } from '@vue/test-utils';
import { mockNuxtImport, mountSuspended } from '@nuxt/test-utils/runtime';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import AuthLogin from '~/ui/auth-login/AuthLogin.vue';
import MonthPage from '~/pages/month/[month].vue';
import type { MonthSummary } from '~/composables/useMonth';

const { load, fill, downloadReport, saveEntry, removeEntry } = vi.hoisted(() => ({
  load: vi.fn(),
  fill: vi.fn(),
  downloadReport: vi.fn(),
  saveEntry: vi.fn(),
  removeEntry: vi.fn(),
}));

mockNuxtImport('useMonth', () => () => ({
  summary: useState<MonthSummary | null>('ui-test-month', () => null),
  load,
  fill,
  downloadReport,
  saveEntry,
  removeEntry,
}));

mockNuxtImport('useRoute', () => () => ({ params: { month: '2026-03' }, path: '/month/2026-03' }));

beforeEach(() => {
  vi.resetAllMocks();
  clearNuxtState(undefined, { reset: true });
});

describe('login', () => {
  it('keeps the login fields native and offers password recovery instead of signup', async () => {
    const wrapper = await mountSuspended(AuthLogin);
    const email = wrapper.get<HTMLInputElement>('input[name="email"]');
    const password = wrapper.get<HTMLInputElement>('input[name="password"]');

    expect(email.element.required).toBe(true);
    expect(password.attributes('autocomplete')).toBe('current-password');
    expect(wrapper.get('a[href="/forgot-password"]').text()).toBe('Esqueceu a senha?');
    expect(wrapper.text()).not.toContain('Criar conta');

    await email.setValue('pessoa@example.com');
    await password.setValue('senha-de-teste');
    await wrapper.get('form').trigger('submit');

    expect(wrapper.emitted('update:email')?.[0]).toEqual(['pessoa@example.com']);
    expect(wrapper.emitted('submit')).toHaveLength(1);

    await wrapper.setProps({ processing: true, error: 'E-mail ou senha incorretos.' });

    expect(email.element.disabled).toBe(true);
    expect(wrapper.get<HTMLButtonElement>('button[type="submit"]').element.disabled).toBe(true);
    expect(wrapper.get('[role="alert"]').text()).toContain('E-mail ou senha incorretos.');
  });
});

describe('mês', () => {
  it('offers retry after a load failure instead of an empty calendar', async () => {
    load.mockRejectedValueOnce(new Error('O Clips não respondeu. Tente de novo em instantes.'));
    const wrapper = await mountSuspended(MonthPage);
    await flushPromises();

    expect(wrapper.get('[role="alert"]').text()).toContain('O Clips não respondeu.');

    load.mockResolvedValueOnce(undefined);
    const retry = wrapper.findAll('button').find((button) => button.text() === 'Tentar novamente');
    await retry!.trigger('click');
    await flushPromises();

    expect(load).toHaveBeenCalledTimes(2);
    expect(load).toHaveBeenLastCalledWith('2026-03');
  });
});
