$(document).ready(function () {
    // CLICK: Ver detalles
    $(document).on('click', '[data-ver_detalles]', function (e) {
        e.preventDefault();

        const url = $(this).data('url');
        if (!url) {
            $.alert('No se encontró la URL para cargar el detalle.');
            return;
        }

        $.confirm({
            title: 'Detalles del pedido',
            boxWidth: '700px',
            useBootstrap: false, // si usas bootstrap puro, pon true
            content: function () {
                const self = this;
                return $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (res) {
                    // Estructura sugerida del backend:
                    // {
                    //   ok: true,
                    //   atencion: {...},
                    //   comanda: {...},
                    //   items: [ { nombre, cantidad, precio, total, notas }, ... ]
                    // }
                    // donde procesas la respuesta:
                    const atencion = res.atencion || {};
                    const comanda  = res.comanda  || {};
                    const cliente  = res.cliente  || {};
                    const items    = res.items    || comanda.comanda_items || [];

                    const headerHtml = renderHeader(atencion, comanda, cliente);
                    const itemsHtml  = renderItemsTable(items);
                    const totalsHtml = renderTotals(res.totals || res.comanda);

                    self.setContent(`
                          <div>
                            ${headerHtml}
                            ${itemsHtml}
                            ${totalsHtml}
                          </div>
                        `);
                }).fail(function (xhr) {
                    let msg = 'No se pudieron cargar los detalles.';
                    try {
                        const json = xhr.responseJSON;
                        if (json && json.message) msg = json.message;
                    } catch (e) {}
                    self.setContent(`<div class="text-danger">${msg}</div>`);
                });
            },
            buttons: {
                cerrar: {
                    text: 'Cerrar',
                    btnClass: 'btn-blue'
                }
            }
        });
    });

});

// Util: formato moneda
function formatMoney(n) {
    const num = Number(n || 0);
    return num.toLocaleString('es-PE', { style: 'currency', currency: 'PEN' });
}

// Util: fecha y hora legibles
function formatDateTime(dt) {
    if (!dt) return '-';
    const d = new Date(dt);
    const fecha = d.toLocaleDateString('es-PE', { year:'numeric', month:'short', day:'2-digit' });
    const hora  = d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' });
    return `${fecha} ${hora}`;
}

// Render de tabla de items
function renderItemsTable(items) {
    if (!Array.isArray(items) || items.length === 0) {
        return '<div class="text-center text-muted my-2">Sin ítems</div>';
    }

    const rows = items.map((it, i) => {
        const cant   = Number(it.cantidad || it.qty || 0);
        // prioridad: valor_unitario (invoice_items) -> precio -> price
        const precio = Number(
            it.valor_unitario !== undefined ? it.valor_unitario
                : it.precio  !== undefined       ? it.precio
                : it.price   !== undefined       ? it.price
                    : 0
        );
        const total = Number(
            it.total !== undefined ? it.total : (cant * precio)
        );
        const nombre = it.nombre || it.name || it.producto || '-';
        const notas  = it.notas || it.observacion || it.observaciones || '';

        return `
          <tr>
            <td class="text-center">${i + 1}</td>
            <td>
              <div>${nombre}</div>
              ${notas ? `<small class="text-muted">Nota: ${notas}</small>` : ''}
            </td>
            <td class="text-center">${cant}</td>
            <td class="text-right">${formatMoney(precio)}</td>
            <td class="text-right font-weight-bold">${formatMoney(total)}</td>
          </tr>
        `;
    }).join('');

    return `
      <div class="table-responsive">
        <table class="table table-sm table-striped mb-0">
          <thead>
            <tr>
              <th style="width:60px" class="text-center">#</th>
              <th>Ítem</th>
              <th style="width:90px" class="text-center">Cant.</th>
              <th style="width:120px" class="text-right">Precio</th>
              <th style="width:120px" class="text-right">Importe</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
    `;
}

// Render del resumen de totales
function renderTotals(data) {
    // si viene de invoice
    if (data && typeof data === 'object' && 'base_imponible' in data) {
        const subtotal       = Number(data.subtotal || 0);
        const descuento      = Number(data.descuento || 0);
        const baseImponible  = Number(data.base_imponible || 0);
        const igv            = Number(data.igv || 0);
        const propina        = Number(data.propina || 0);
        const total          = Number(data.total || 0);

        return `
        <div class="mt-3">
          <div class="d-flex justify-content-between"><span>Subtotal:</span><strong>${formatMoney(subtotal)}</strong></div>
          <div class="d-flex justify-content-between"><span>Descuento:</span><strong>- ${formatMoney(descuento)}</strong></div>
          <div class="d-flex justify-content-between"><span>Base imponible:</span><strong>${formatMoney(baseImponible)}</strong></div>
          <div class="d-flex justify-content-between"><span>IGV (18%):</span><strong>${formatMoney(igv)}</strong></div>
          <div class="d-flex justify-content-between"><span>Propina:</span><strong>+ ${formatMoney(propina)}</strong></div>
          <hr class="my-2">
          <div class="d-flex justify-content-between h5 mb-0"><span>Total a pagar:</span><strong>${formatMoney(total)}</strong></div>
        </div>
        `;
    }

    // fallback: lo que tenías (por si viene de comanda todavía)
    const subtotal  = data?.subtotal ?? data?.sub_total ?? 0;
    const descuento = data?.descuento ?? 0;
    const igv       = data?.igv ?? 0;
    const total     = data?.total ?? 0;

    return `
    <div class="mt-3">
      <div class="d-flex justify-content-between"><span>Subtotal:</span><strong>${formatMoney(subtotal)}</strong></div>
      <div class="d-flex justify-content-between"><span>Descuento:</span><strong>${formatMoney(descuento)}</strong></div>
      <div class="d-flex justify-content-between"><span>IGV:</span><strong>${formatMoney(igv)}</strong></div>
      <hr class="my-2">
      <div class="d-flex justify-content-between h5 mb-0"><span>Total:</span><strong>${formatMoney(total)}</strong></div>
    </div>
    `;
}

// Render de cabecera con datos de atención/comanda
function renderHeader(atencion, comanda, cliente) {
    const nombre    = cliente && cliente.nombre    ? cliente.nombre    : '-';
    const documento = cliente && cliente.documento ? cliente.documento : '';
    const tipo_doc  = cliente && cliente.tipo_doc  ? cliente.tipo_doc  : '';

    const mesa = (comanda && comanda.mesa != null) ? comanda.mesa : '-';
    const mozo = (comanda && comanda.mozo != null) ? comanda.mozo : '-';

    const creada  = (atencion && atencion.created_at) ? atencion.created_at : null;
    const enviada = comanda && comanda.sent_to_kitchen_at;
    const inicio  = comanda && comanda.started_cooking_at;
    const lista   = comanda && comanda.ready_at;

    return `
    <div class="mb-2">
      <div class="h6 mb-1">Atención #${atencion && atencion.id ? atencion.id : '-'}</div>
      <div><b>Cliente:</b> ${nombre}${documento ? ` • <span class="text-muted">${tipo_doc} ${documento}</span>` : ''}</div>
      
      <div class="mt-2 small text-muted">
        ${creada  ? `<div>Creada: ${formatDateTime(creada)}</div>` : ''}
        ${enviada ? `<div>Enviada a cocina: ${formatDateTime(enviada)}</div>` : ''}
        ${inicio  ? `<div>Inició cocina: ${formatDateTime(inicio)}</div>` : ''}
        ${lista   ? `<div>Lista: ${formatDateTime(lista)}</div>` : ''}
      </div>
    </div>
  `;
}

