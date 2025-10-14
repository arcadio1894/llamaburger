// Usa el Echo y Pusher que ya inicializaste en orderCreated.js

// Tarjeta específica para comanda (mesa)
function getComandaCardCreated(t) {
    // colores distintos a delivery
    let headerClass = "bg-gradient-warning"; // amarillo/naranja
    let url_imprimir = document.location.origin + '/imprimir/comanda-mesa/' + t.comanda_id; // ajusta si tienes ruta
    return `
      <div class="card card-widget widget-user" style="margin:5px;padding:5px;width:100%;min-height:120px;">
        <div class="widget-user-header ${headerClass}" style="padding:8px;">
          <span class="widget-user-desc" style="font-size:14px">Comanda #${t.numero}</span>
          <h5 class="widget-user-username" style="font-size:.9rem;padding-top:3px">
            Mesa ${t.mesa || '-'} <br> Mozo: ${t.mozo || '-'}
          </h5>
        </div>
        <div class="card-footer" style="padding:8px;">
          <div class="row">
            <div class="col-sm-4 border-right">
              <div class="description-block">
                <a href="${url_imprimir}" target="_blank">
                <h6 class="description-header" style="font-size:.65rem;font-weight:bold;color:black">VER COMANDA</h6>
              </a>
              </div>
            </div>
            <div class="col-sm-4 border-right">
              <div class="description-block">
                <h6 class="description-header" style="font-size:.65rem;font-weight:bold;color:black">S/ ${Number(t.total||0).toFixed(2)}</h6>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="description-block">
                <a href="#" data-anular data-id="${t.comanda_id}">
                  <h6 class="description-header" style="font-size:.65rem;font-weight:bold;color:black">CANCELAR</h6>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>`;
}

// Si quieres más adelante distintos layouts por estado:
function getComandaCardByStatus(ticket){
    // de momento usamos mismo card para created/processing/shipped
    return getComandaCardCreated(ticket);
}

// Remueve ítem (si existiera) por id exacto
function removeKanbanItemById(rawId){
    try {
        let items = $("#kanban").jqxKanban("getItems") || [];
        items.forEach(function(it){
            if (String(it.id) === String(rawId)) {
                $("#kanban").jqxKanban("removeItem", it.id);
            }
        });
    } catch (e) {}
}

window.Echo.channel('kitchenTickets')
    .subscribed(() => console.log('✅ Suscrito a kitchenTickets'))
    .listen('.comanda.created', (e) => {
        const t = e.ticket;
        // Sonido opcional (reaprovecha tu helper)
        //if (typeof playNotificationSound === 'function') { playNotificationSound(); }

        const status = (t.status || 'created').toLowerCase(); // created/processing/shipped

        const item = {
            id: String(t.id),                  // "comanda_57"
            status: status,
            text: getComandaCardByStatus(t),   // HTML
            content: `Comanda #${t.numero}`,
            tags: "comanda",
            color: "moccasin"                  // borde opcional
        };
        item[status] = status;               // dataField de la columna

        // Quitar versión anterior si existiera
        removeKanbanItemById(item.id);

        // Agregar al kanban
        if ($("#kanban").length && $("#kanban").data('jqxKanban')) {
            $("#kanban").jqxKanban("addItem", item);
        }
    });