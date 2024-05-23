import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});


// Listen for notifications on the private channel
if (window.Laravel.userId) {
    window.Echo.private(`App.Models.User.${window.Laravel.userId}`)
        .listen('.database-notifications.sent', (e) => {
            // Play the notification sound
            playNotificationSound();
        });

    function playNotificationSound() {
        const audio = document.getElementById('notification-sound');
        audio.play();
    }
}