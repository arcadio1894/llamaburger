import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'dac24d98f58cf734beec', // ← tu key
    cluster: 'us2',
    forceTLS: true,
    encrypted: true,
});

window.Echo.connector.pusher.connection.bind('connected', function() {
    console.log('Conexión establecida con Pusher (mozo listener).');
});

document.addEventListener('DOMContentLoaded', () => {
    const mozoId = window.USER_ID;
    if (!mozoId) return;

    const channelName = `mozos.${mozoId}`;
    window.Echo.channel(channelName)
        .subscribed(() => console.log(`Suscrito al canal ${channelName}`))
        .listen('.ComandaReadyForPickup', (e) => {
            if (window.IS_KANBAN_PAGE) return;
            const msg = `Comanda #${e.numero}${e.mesa ? ' - ' + e.mesa : ''} lista para recojo en cocina.`;
            toastr.info(msg, '¡Listo para recojo!');
            console.log(msg);
        })
        .error(err => console.error('Error escuchando canal mozo:', err));
});