$(document).ready(function () {
    // VER DETALLES (popup con jquery-confirm)
    $(document).on('click', '[data-invoice-items]', function (e) {
        e.preventDefault();
        const id = $(this).data('invoice-items');

        $.getJSON(`/dashboard/invoices/${id}/items`, function (res) {
            if (!res.ok) { $.alert('No se pudo obtener los ítems.'); return; }

            const rows = res.items.map(it => {
                const precio = (it.precio ?? 0).toFixed(2);
                const total  = (it.total  ?? (it.precio*it.cantidad)).toFixed(2);
                return `<tr>
              <td>${it.nombre}</td>
              <td style="text-align:right">${it.cantidad}</td>
              <td style="text-align:right">S/ ${precio}</td>
              <td style="text-align:right">S/ ${total}</td>
            </tr>`;
            }).join('');

            const html = `
      <div style="max-height:60vh;overflow:auto">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Producto</th><th class="text-right">Cant</th>
              <th class="text-right">Precio</th><th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;

            $.confirm({
                title: 'Productos del comprobante',
                content: html,
                boxWidth: '600px',
                useBootstrap: false,
                buttons: { cerrar: { text:'Cerrar' } }
            });
        }).fail(() => $.alert('Error consultando ítems.'));
    });

// IMPRIMIR (el anchor ya va a /invoices/{id}/print) – no requiere JS extra.

// ANULAR (confirm + POST)
    $(document).on('click', '[data-invoice-void]', function (e) {
        e.preventDefault();
        const id = $(this).data('invoice-void');

        $.confirm({
            title: '🔒 Anular comprobante',
            content: '¿Seguro que deseas anular este comprobante? Esta acción es irreversible.',
            type: 'red',
            buttons: {
                confirmar: {
                    text: 'Sí, anular',
                    btnClass: 'btn-red',
                    action: function () {
                        $.blockUI({ message: '<h3>Anulando…</h3>' });

                        $.ajax({
                            url: `/invoices/${id}/void`,
                            type: 'POST',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (res) {
                                $.unblockUI();
                                if (res.ok) {
                                    $.alert('✅ Comprobante anulado.');
                                    // Opcional: actualizar fila en UI (p.ej., desactivar botón)
                                    $(`[data-invoice-void='${id}']`).prop('disabled', true);
                                } else {
                                    $.alert('No se pudo anular.');
                                }
                            },
                            error: function () {
                                $.unblockUI();
                                $.alert('Error al anular el comprobante.');
                            }
                        });
                    }
                },
                cancelar: { text: 'Cancelar' }
            }
        });
    });
});
