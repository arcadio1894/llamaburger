import { subscribeDistributor, subscribeAdminAll } from './echo-distributor';

// Construye el HTML del card con los datos recibidos
function buildOrderCardHtml(order) {
    const total = order?.data_totals?.total_a_pagar ?? '0.00';
    const fecha = order?.date_estimated_format ?? 'Fecha no disponible';
    const address = order?.shipping_address?.address ?? '';
    const lat = order?.shipping_address?.latitude ?? '';
    const lng = order?.shipping_address?.longitude ?? '';
    const urlComanda = `${location.origin}/imprimir/comanda/${order.id}`;
    const urlBoleta  = `${location.origin}/imprimir/recibo/${order.id}`;

    return `
    <div class="col-md-4 mb-3" id="order-${order.id}">
      <article class="card h-100">
        <header class="card-header text-center">
          <strong>${order.status_name ?? ''}</strong>
        </header>
        <div class="card-body">
          <h6 class="text-center">PEDIDO ID: #${order.id}</h6>
          <article class="card mb-2">
            <div class="card-body row">
              <div class="col">
                <strong>Llegará aprox.:</strong><br>${fecha}
              </div>
              <div class="col">
                <strong>Monto a pagar:</strong><br>S/. ${total}
              </div>
            </div>
          </article>

          <div class="track mb-2">
            <div class="step ${Number(order.active_step) >= 1 ? 'active' : ''}">
              <span class="icon"><i class="far fa-file-alt"></i></span>
              <span class="text">Recibido</span>
            </div>
            <div class="step ${Number(order.active_step) >= 2 ? 'active' : ''}">
              <span class="icon"><i class="fas fa-fire"></i></span>
              <span class="text">Cocinando</span>
            </div>
            <div class="step ${Number(order.active_step) >= 3 ? 'active' : ''}">
              <span class="icon"><i class="fa fa-truck"></i></span>
              <span class="text">Enviado</span>
            </div>
            <div class="step ${Number(order.active_step) >= 4 ? 'active' : ''}">
              <span class="icon"><i class="fas fa-home"></i></span>
              <span class="text">Entregado</span>
            </div>
          </div>

          <hr>
          <br>

          <div class="d-flex justify-content-between align-items-center">
            <a href="${urlComanda}" target="_blank" data-imprimir_comanda="${order.id}">
              <h6 class="description-header" style="font-size:.8rem;font-weight:bold;color:black">COMANDA</h6>
            </a>
            <a href="${urlBoleta}" target="_blank" data-imprimir_boleta="${order.id}">
              <h6 class="description-header" style="font-size:.8rem;font-weight:bold;color:black">BOLETA</h6>
            </a>
            <a href="#" data-ver_ruta_map data-id="${order.id}" data-address="${address}" data-latitude="${lat}" data-longitude="${lng}">
              <h6 class="description-header" style="font-size:.8rem;font-weight:bold;color:black">VER RUTA</h6>
            </a>
          </div>
        </div>
      </article>
    </div>
  `;
}

// Insertar al inicio del grid o actualizar si existe
function upsertOrderCard(order) {
    const grid = document.getElementById('orders-grid');
    if (!grid) return;

    const el = document.getElementById(`order-${order.id}`);
    if (el) {
        // Si ya existe, actualizamos como "update"
        updateOrderCard({
            order: { id: order.id },
            status_name: order.status_name,
            active_step: order.active_step
        });
        return;
    }

    // Validar que sea de HOY (para no romper el filtro del backend si llegara algo viejo)
    const createdAt = order.created_at ? order.created_at.substring(0, 10) : null;
    const todayStr = new Date().toISOString().slice(0, 10);
    if (createdAt && createdAt !== todayStr) {
        return; // ignora si no es de hoy
    }

    const html = buildOrderCardHtml(order);
    grid.insertAdjacentHTML('afterbegin', html);
}

// Delegado para "VER RUTA"
function bindVerRuta() {
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-ver_ruta_map]');
        if (!el) return;

        e.preventDefault();
        const latitude = el.getAttribute('data-latitude');
        const longitude = el.getAttribute('data-longitude');

        if (latitude && longitude) {
            const url = `https://www.google.com/maps?q=${latitude},${longitude}&z=15`;
            window.open(url, '_blank');
        } else {
            alert('No se encontraron coordenadas.');
        }
    });
}

// Actualiza el card visualmente (cambios de estado)
function updateOrderCard({ order, status_name, active_step }) {
    if (!order || !order.id) return;
    const el = document.getElementById(`order-${order.id}`);
    if (!el) {
        console.warn(`No se encontró el card del pedido #${order.id} para actualizar.`);
        return;
    }

    const header = el.querySelector('.card-header');
    if (header) header.innerHTML = `<strong>${status_name || ''}</strong>`;

    const steps = el.querySelectorAll('.step');
    steps.forEach((node, idx) => {
        if (Number(active_step) > idx) node.classList.add('active');
        else node.classList.remove('active');
    });
}

function bootstrap() {
    bindVerRuta();

    const isAdmin = !!window.__isAdmin;
    const distributorId = window.__distributorId;

    const onEvent = (type, e) => {
        // e.payload = { order: {...} } por broadcastWith()
        const order = e?.order || e?.payload?.order || e; // defensa
        if (!order) return;

        if (type === 'created') {
            upsertOrderCard(order);
        } else if (type === 'updated') {
            updateOrderCard({
                order: { id: order.id },
                status_name: e.status_name || order.status_name,
                active_step: e.active_step ?? order.active_step
            });
        }
    };

    if (isAdmin) {
        subscribeAdminAll(onEvent);
    } else {
        if (!distributorId) {
            console.error('No se encontró window.__distributorId (usuario no admin sin distribuidor).');
            return;
        }
        subscribeDistributor(distributorId, onEvent);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
} else {
    bootstrap();
}