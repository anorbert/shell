import '../css/app.css';

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-modal-open]');
    if (trigger) {
        const modal = document.querySelector(trigger.getAttribute('data-modal-open'));
        if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    const close = event.target.closest('[data-modal-close]');
    if (close) {
        const modal = close.closest('.modal');
        modal?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
    }

    if (event.target.classList.contains('modal-backdrop')) {
        const modal = event.target.closest('.modal');
        modal?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal.is-open').forEach((modal) => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        });
    }
});

window.shellAjax = async (url, options = {}) => {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            ...(options.headers || {}),
        },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        const error = new Error(payload.message || 'The request could not be completed.');
        error.status = response.status;
        error.errors = payload.errors || {};
        throw error;
    }
    return payload;
};

window.shellToast = (message, type = 'success') => {
    const region = document.getElementById('toast-region');
    if (!region) return;
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    region.appendChild(toast);
    window.setTimeout(() => toast.remove(), 4200);
};
