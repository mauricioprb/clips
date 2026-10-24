import { flushPromises } from '@vue/test-utils';
import { mockNuxtImport, mountSuspended } from '@nuxt/test-utils/runtime';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { ref } from 'vue';
import AuthLogin from '~/ui/auth-login/AuthLogin.vue';
import AuthSignup from '~/ui/auth-signup/AuthSignup.vue';
import NotesPage from '~/pages/index.vue';
import type { Note } from '~/composables/useNotes';

const { load, create, remove, logout } = vi.hoisted(() => ({
  load: vi.fn(),
  create: vi.fn(),
  remove: vi.fn(),
  logout: vi.fn(),
}));

mockNuxtImport('useAuth', () => () => ({
  user: ref({ name: 'Pessoa de teste' }),
  logout,
}));

mockNuxtImport('useNotes', () => () => ({
  notes: useState<Note[]>('ui-test-notes', () => []),
  load,
  create,
  remove,
}));

beforeEach(() => {
  vi.resetAllMocks();
  clearNuxtState(undefined, { reset: true });
});

describe('autenticação com os blocos FUSE', () => {
  it('keeps the login fields native and locks the form while processing', async () => {
    const wrapper = await mountSuspended(AuthLogin);
    const email = wrapper.get<HTMLInputElement>('input[name="email"]');
    const password = wrapper.get<HTMLInputElement>('input[name="password"]');

    expect(email.element.required).toBe(true);
    expect(password.attributes('autocomplete')).toBe('current-password');
    expect(wrapper.find('input[type="checkbox"]').exists()).toBe(false);

    await email.setValue('pessoa@example.com');
    await password.setValue('senha-de-teste');
    await wrapper.get('form').trigger('submit');

    expect(wrapper.emitted('update:email')?.[0]).toEqual(['pessoa@example.com']);
    expect(wrapper.emitted('submit')).toHaveLength(1);

    await wrapper.setProps({ processing: true, error: 'Credenciais inválidas.' });

    expect(email.element.disabled).toBe(true);
    expect(password.element.disabled).toBe(true);
    expect(wrapper.get<HTMLButtonElement>('button[type="submit"]').element.disabled).toBe(true);
    expect(wrapper.get('[role="alert"]').text()).toContain('Credenciais inválidas.');
  });

  it('preserves the signup password length and supported fields', async () => {
    const wrapper = await mountSuspended(AuthSignup);
    const password = wrapper.get<HTMLInputElement>('input[name="password"]');

    expect(wrapper.findAll('input')).toHaveLength(3);
    expect(password.element.required).toBe(true);
    expect(password.attributes('minlength')).toBe('8');
    expect(password.attributes('autocomplete')).toBe('new-password');
  });
});

describe('área de notas', () => {
  it('offers retry after a load failure instead of an empty list', async () => {
    load.mockRejectedValueOnce(new Error('Não foi possível carregar as notas.'));
    const wrapper = await mountSuspended(NotesPage);
    await flushPromises();

    expect(wrapper.get('[role="alert"]').text()).toContain('Não foi possível carregar as notas.');
    expect(wrapper.text()).not.toContain('Nenhuma nota por aqui');

    load.mockResolvedValueOnce(undefined);
    const retry = wrapper.findAll('button').find((button) => button.text() === 'Tentar novamente');
    await retry!.trigger('click');
    await flushPromises();

    expect(load).toHaveBeenCalledTimes(2);
    expect(wrapper.text()).toContain('Nenhuma nota por aqui');
  });

  it('preserves a draft when saving fails', async () => {
    create.mockRejectedValue(new Error('Não foi possível salvar.'));
    const wrapper = await mountSuspended(NotesPage);
    await flushPromises();
    const input = wrapper.get<HTMLInputElement>('input[name="title"]');
    await input.setValue('Uma nota importante');
    await wrapper.get('form').trigger('submit');
    await flushPromises();

    expect(create).toHaveBeenCalledWith('Uma nota importante');
    expect(input.element.value).toBe('Uma nota importante');
    expect(input.element.disabled).toBe(false);
    expect(wrapper.get('[role="alert"]').text()).toContain('Não foi possível salvar.');
  });
});
