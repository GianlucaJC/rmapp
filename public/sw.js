// public/sw.js

// L'evento 'push' viene attivato quando il server invia una notifica.
self.addEventListener('push', function (event) {
    if (event.data) {
        const data = event.data.json();
        const title = data.title || 'Nuova Notifica';
        const options = {
            body: data.body,
            icon: data.icon || '/rmapp/images/icons/icon-192x192.png', // Icona di default
            badge: data.badge || '/rmapp/images/icons/icon-192x192.png', // Badge per Android
            data: {
                url: data.url || '/rmapp/' // URL da aprire al click (home page dell'app)
            }
        };
        event.waitUntil(self.registration.showNotification(title, options));
    }
});

// L'evento 'notificationclick' viene attivato quando l'utente clicca sulla notifica.
self.addEventListener('notificationclick', function(event) {
    // Chiude la notifica
    event.notification.close();

    // Apre l'URL associato alla notifica in una nuova finestra/tab
    event.waitUntil(clients.openWindow(event.notification.data.url));
});
