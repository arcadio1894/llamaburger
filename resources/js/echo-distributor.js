import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

let echoInstance = null;

export function getEcho() {
    if (echoInstance) return echoInstance;
    const cfg = window.__echoConfig || {};
    window.Pusher = Pusher;

    echoInstance = new Echo({
        broadcaster: 'pusher',
        key: cfg.key,
        cluster: cfg.cluster,
        forceTLS: !!cfg.forceTLS,
        encrypted: !!cfg.encrypted,

    });

    if (echoInstance.connector?.pusher?.connection) {
        echoInstance.connector.pusher.connection.bind('connected', function () {
            console.log('Conexión establecida con Pusher despues del echoInstance.');
        });
    }
    return echoInstance;
}

export function subscribeDistributor(distributorId, onEvent) {
    const Echo = getEcho();
    const ch = `orders.distributor.${distributorId}`;

    Echo.channel(ch)
        .subscribed(() => console.log(`Suscrito: ${ch}`))
        .listen('.OrderStatusUpdatedDistributor', (e) => onEvent && onEvent('updated', e))
        .listen('.OrderCreatedDistributor', (e) => onEvent && onEvent('created', e))
        .error(err => console.error('Echo distributor error:', err));
}

export function subscribeAdminAll(onEvent) {
    const Echo = getEcho();
    const ch = 'orders.admin';

    Echo.channel(ch)
        .subscribed(() => console.log(`Suscrito: ${ch}`))
        .listen('.OrderStatusUpdatedDistributor', (e) => onEvent && onEvent('updated', e))
        .listen('.OrderCreatedDistributor', (e) => onEvent && onEvent('created', e))
        .error(err => console.error('Echo admin error:', err));
}