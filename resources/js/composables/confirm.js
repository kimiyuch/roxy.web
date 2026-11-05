import { reactive } from 'vue';

// One confirm dialog for the whole app, rendered by ConfirmDialog.vue in the layout.
// `await confirm({ title, message, action: 'Delete' })` resolves to true or false.
export const confirmState = reactive({ open: false, title: '', message: '', action: 'OK', danger: true, resolve: null });

export function confirm({ title, message = '', action = 'OK', danger = true }) {
    confirmState.resolve?.(false);

    return new Promise((resolve) => {
        Object.assign(confirmState, { open: true, title, message, action, danger, resolve });
    });
}

export function settle(answer) {
    const resolve = confirmState.resolve;
    Object.assign(confirmState, { open: false, resolve: null });
    resolve?.(answer);
}
