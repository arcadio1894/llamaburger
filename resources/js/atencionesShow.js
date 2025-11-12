import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

if (!window.Echo) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'dac24d98f58cf734beec', // ← tu key
        cluster: 'us2',
        forceTLS: true,
        encrypted: true,
    });
}

function disableEditionUI() {
    window.COMANDA_ESTADO = 'cancelada';
    // Deshabilita envío a cocina (web y móvil)
    $('#a-send-kitchen').prop('disabled', true).addClass('disabled');

    // Deshabilita sumar/restar y grid de productos por si siguen visibles
    $('.btn-inc, .btn-dec, .product-card').prop('disabled', true).addClass('disabled').css('pointer-events', 'none');

    // Inserta botón “Reactivar comanda” (web) si no existe
    if (!$('#btn-reactivar-web').length) {
        const $btnWeb = $('<button id="btn-reactivar-web" class="btn btn-outline-primary btn-block mt-2">Reactivar comanda</button>');
        $('#order-panel .card-footer').append($btnWeb);
    }

    // Inserta botón “Reactivar comanda” (móvil) si no existe
    if (!$('#btn-reactivar-movil').length) {
        const $btnMov = $('<button id="btn-reactivar-movil" class="btn btn-outline-primary btn-block mt-2">Reactivar comanda</button>');
        $('#order-aside-top').append($btnMov);
    }
}

// Suscripción a este canal de comanda
$(function () {
    const comandaId = window.COMANDA_ID;
    if (!comandaId) return;

    window.Echo.channel(`comandas.${comandaId}`)
        .subscribed(() => console.log(`Suscrito a comandas.${comandaId}`))
        .listen('.ComandaCanceled', (e) => {
            // Solo aplica si estoy en la misma comanda
            if (String(e.comandaId) !== String(window.COMANDA_ID)) return;
            disableEditionUI();
            if (window.toastr) toastr.info('Esta comanda fue cancelada.');
        });
});