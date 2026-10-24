import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import { Button } from '~/ui/button';
import Input from '~/ui/input/Input.vue';
import ThemeToggle from '~/components/ThemeToggle.vue';

describe('FUSE UI local', () => {
  it('switches light and dark modes and stores the selected preference', async () => {
    const wrapper = mount(ThemeToggle);

    await wrapper.get('button[aria-label="Tema escuro"]').trigger('click');

    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(localStorage.getItem('fuse-ui-theme')).toBe('dark');
    expect(wrapper.get('button[aria-label="Tema escuro"]').attributes('aria-pressed')).toBe('true');

    await wrapper.get('button[aria-label="Tema claro"]').trigger('click');

    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(localStorage.getItem('fuse-ui-theme')).toBe('light');

    await wrapper.get('button[aria-label="Tema do sistema"]').trigger('click');

    expect(localStorage.getItem('fuse-ui-theme')).toBe('system');
    wrapper.unmount();
  });

  it('preserves native validation, labels and input updates', async () => {
    const wrapper = mount(Input, {
      props: {
        id: 'password',
        label: 'Senha',
        type: 'password',
        required: true,
        hint: 'Use pelo menos 8 caracteres.',
      },
      attrs: { name: 'password', autocomplete: 'new-password', minlength: 8 },
    });
    const input = wrapper.get('input');

    expect(input.element.required).toBe(true);
    expect(input.attributes('minlength')).toBe('8');
    expect(input.attributes('autocomplete')).toBe('new-password');
    expect(input.attributes('name')).toBe('password');
    expect(wrapper.get('label').attributes('for')).toBe(input.attributes('id'));
    expect(wrapper.get(`#${input.attributes('aria-describedby')}`).text()).toContain(
      '8 caracteres',
    );

    await input.setValue('senha-local');

    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['senha-local']);
  });

  it('disables submit while loading and restores it afterwards', async () => {
    const wrapper = mount(Button, {
      props: { loading: true },
      attrs: { type: 'submit' },
      slots: { default: 'Salvar' },
    });
    const button = wrapper.get('button');

    expect(button.attributes('type')).toBe('submit');
    expect(button.element.disabled).toBe(true);
    expect(button.attributes('aria-busy')).toBe('true');
    expect(button.text()).toBe('Salvar');

    await wrapper.setProps({ loading: false });

    expect(button.element.disabled).toBe(false);
    expect(button.attributes('aria-busy')).toBeUndefined();
  });
});
