import { cva, type VariantProps } from 'class-variance-authority';

export { default as Toaster } from './Toaster.vue';
export {
  useToast,
  toast,
  type ToastIntent,
  type ToastVariant,
  type ToastItem,
  type ToastOptions,
  type ToastAction,
} from './use-toast';

export const toastVariants = cva(
  'group pointer-events-auto relative w-full items-start gap-3 rounded-xl p-4 shadow-l [&_svg]:size-5 data-[state=open]:animate-toast-in data-[state=closed]:animate-toast-out data-[swipe=move]:translate-x-(--reka-toast-swipe-move-x) data-[swipe=move]:transition-none data-[swipe=cancel]:translate-x-0 data-[swipe=cancel]:transition-transform data-[swipe=cancel]:duration-200 data-[swipe=cancel]:ease-out-quart data-[swipe=end]:animate-toast-out',
);

export const toastViewportVariants = cva(
  'pointer-events-none fixed z-[100] flex w-full max-w-sm flex-col gap-2 p-4',
  {
    variants: {
      position: {
        'top-left': 'top-0 left-0 flex-col-reverse',
        'top-center': 'top-0 left-1/2 -translate-x-1/2 flex-col-reverse',
        'top-right': 'top-0 right-0 flex-col-reverse',
        'bottom-left': 'bottom-0 left-0',
        'bottom-center': 'bottom-0 left-1/2 -translate-x-1/2',
        'bottom-right': 'right-0 bottom-0',
      },
    },
    defaultVariants: {
      position: 'bottom-right',
    },
  },
);

export type ToastViewportVariants = VariantProps<typeof toastViewportVariants>;
