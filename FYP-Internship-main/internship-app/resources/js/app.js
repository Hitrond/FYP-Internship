import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function updateUnreadNotificationBadges() {
    const badges = [...document.querySelectorAll('[data-notification-count]')];

    if (badges.length === 0) {
        return;
    }

    const currentCount = Number.parseInt(badges[0].dataset.count || '0', 10);
    const nextCount = Number.isNaN(currentCount) ? 1 : currentCount + 1;

    badges.forEach((badge) => {
        badge.dataset.count = String(nextCount);
        badge.textContent = nextCount > 99 ? '99+' : String(nextCount);
        badge.classList.remove('hidden');
    });
}

function showRealtimeNotification(notification) {
    const container = document.querySelector('[data-realtime-notifications]');

    if (!container) {
        return;
    }

    const levelClasses = {
        danger: 'border-rose-200 bg-rose-50 text-rose-950',
        warning: 'border-amber-200 bg-amber-50 text-amber-950',
        success: 'border-emerald-200 bg-emerald-50 text-emerald-950',
        info: 'border-indigo-200 bg-white text-slate-900',
    };
    const toast = document.createElement('div');
    const content = document.createElement('a');
    const title = document.createElement('strong');
    const message = document.createElement('span');
    const closeButton = document.createElement('button');
    const fallbackUrl = container.dataset.notificationsUrl || '/notifications';

    toast.className = `pointer-events-auto flex items-start gap-3 rounded-2xl border p-4 shadow-xl ${levelClasses[notification.level] || levelClasses.info}`;
    toast.setAttribute('role', 'status');

    content.className = 'min-w-0 flex-1';
    content.href = fallbackUrl;

    try {
        const requestedUrl = new URL(notification.url || fallbackUrl, window.location.origin);

        if (requestedUrl.origin === window.location.origin) {
            content.href = requestedUrl.href;
        }
    } catch {
        content.href = fallbackUrl;
    }

    title.className = 'block text-sm font-bold';
    title.textContent = notification.title || 'New notification';
    message.className = 'mt-1 block text-sm opacity-80';
    message.textContent = notification.message || 'Open the notification centre to view the update.';

    closeButton.type = 'button';
    closeButton.className = 'shrink-0 rounded-lg px-2 py-1 text-lg leading-none opacity-60 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current';
    closeButton.setAttribute('aria-label', 'Dismiss notification');
    closeButton.textContent = '\u00d7';
    closeButton.addEventListener('click', () => toast.remove());

    content.append(title, message);
    toast.append(content, closeButton);
    container.prepend(toast);

    window.setTimeout(() => toast.remove(), 10000);
}

function initialiseRealtimeNotifications() {
    const userId = document.querySelector('meta[name="authenticated-user-id"]')?.content;

    if (!userId || !window.Echo) {
        return;
    }

    window.Echo
        .private(`App.Models.User.${userId}`)
        .notification((notification) => {
            updateUnreadNotificationBadges();
            showRealtimeNotification(notification);
        });
}

initialiseRealtimeNotifications();
