import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const pusherKey = document.querySelector('meta[name="pusher-app-key"]')?.content;

if (pusherKey) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: document.querySelector('meta[name="pusher-app-cluster"]')?.content || 'mt1',
        forceTLS: true,
    });
}
