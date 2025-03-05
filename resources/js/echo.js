import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

// Request permission to show notifications
if (Notification.permission === "default") {
    Notification.requestPermission();
}

// Listen for notifications on the private channel
if (window.Laravel.userId) {
    window.Echo.private(`App.Models.User.${window.Laravel.userId}`).listen(
        ".database-notifications.sent",
        (e) => {
            // Play the notification sound
            playNotificationSound();

            // Check if the user is not viewing the current tab or window
            if (document.hidden) {
                // Send a browser notification
                sendBrowserNotification(e);
            }
        }
    );

    function playNotificationSound() {
        const audio = document.getElementById("notification-sound");
        audio.play();
    }

    function sendBrowserNotification(eventData) {
        if (Notification.permission === "granted") {
            const notification = new Notification("New Notification", {
                title: "Dayz Tracker",
                body: "You have new notifications", // Customize the message from event data
                icon: "/assets/images/logo_circle.png", // Optional icon for the notification
            });

            notification.onclick = () => {
                // Optional: handle notification click
                window.focus();
                notification.close();
            };
        }
    }
}

// if (window.Laravel.userId && window.Laravel.encodedType) {
//     const userId = window.Laravel.userId;
//     const encodedType = window.Laravel.encodedType;
//     window.Echo.private(`participant.${encodedType}.${userId}`).listen(
//         ".Namu\\WireChat\\Events\\NotifyParticipant",
//         (e) => {
//             console.log("participant notification");
//             console.log(e);
//         }
//     );
// }
