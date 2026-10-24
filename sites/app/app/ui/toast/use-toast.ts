import { ref } from 'vue';

export type ToastIntent = 'info' | 'success' | 'warning' | 'danger';
export type ToastVariant = 'solid' | 'soft';

export interface ToastAction {
  label: string;
  onClick: () => void;
}

export interface ToastOptions {
  title?: string;
  description?: string;
  intent?: ToastIntent;
  variant?: ToastVariant;
  duration?: number;
  action?: ToastAction;
}

export interface ToastItem extends ToastOptions {
  id: string;
  open: boolean;
}

const DEFAULT_DURATION = 5000;

const toasts = ref<ToastItem[]>([]);
let count = 0;

function remove(id: string) {
  toasts.value = toasts.value.filter((item) => item.id !== id);
}

function dismiss(id: string) {
  const item = toasts.value.find((toast) => toast.id === id);
  if (item) item.open = false;
}

function add(options: ToastOptions) {
  const id = `toast-${++count}`;
  toasts.value.push({
    id,
    open: true,
    intent: 'info',
    variant: 'soft',
    duration: DEFAULT_DURATION,
    ...options,
  });
  return { id, dismiss: () => dismiss(id) };
}

function withIntent(intent: ToastIntent) {
  return (title: string, options: Omit<ToastOptions, 'title' | 'intent'> = {}) =>
    add({ ...options, title, intent });
}

export const toast = Object.assign((options: ToastOptions) => add(options), {
  info: withIntent('info'),
  success: withIntent('success'),
  warning: withIntent('warning'),
  danger: withIntent('danger'),
});

export function useToast() {
  return { toasts, toast, dismiss, remove };
}
