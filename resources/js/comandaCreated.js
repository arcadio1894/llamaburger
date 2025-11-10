// Usa el Echo y Pusher que ya inicializaste en orderCreated.js

// Tarjeta específica para comanda (mesa)
function getComandaCardCreated(t) {
    // colores distintos a delivery
    let headerClass = "bg-info"; // amarillo/naranja
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
/*function getComandaCardByStatus(ticket){
    // de momento usamos mismo card para created/processing/shipped
    return getComandaCardCreated(ticket);
}*/

// Remueve ítem (si existiera) por id exacto
function removeKanbanItemById(comandaId){
    // target numérico como string (p.ej. "13")
    const target = String(comandaId)
        .replace(/^kanban_/i, '')
        .replace(/^comanda_/i, '')
        .replace(/_(\d+)$/i, ''); // si te llega "kanban_comanda_13_84" -> "kanban_comanda_13" -> "13"

    console.log("🧹 REMOVE target:", target);

    try {
        const items = $("#kanban").jqxKanban("getItems") || [];

        items.forEach(function(item) {
            const idStr = String(item.id || '');

            // 1) Coincidencias directas
            if (idStr === `comanda_${target}` ||
                idStr === `kanban_comanda_${target}` ||
                idStr.indexOf(`kanban_comanda_${target}_`) === 0) {
                console.log("📌 Eliminando (match directo):", idStr);
                $("#kanban").jqxKanban("removeItem", item.id);
                return;
            }

            // 2) Normalización general: quitar "kanban_" inicial y sufijo "_NN"
            let norm = idStr.replace(/^kanban_/i, '');
            norm = norm.replace(/_(\d+)$/i, ''); // comanda_13_84 -> comanda_13

            // Si después de normalizar queda exactamente "comanda_13", elimino
            if (norm === `comanda_${target}`) {
                console.log("📌 Eliminando (normalizado):", idStr, "→", norm);
                $("#kanban").jqxKanban("removeItem", item.id);
            }
        });
    } catch (e) {
        console.warn('⚠️ removeKanbanItemById fallback:', e);
    }
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
            type: 'comanda',                         // <— tipo
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

// 👇 NUEVO EVENTO: cuando una comanda cambia de estado (start/ready/deliver)
window.Echo.channel('kitchenTickets')
    .listen('.comanda.updated', (e) => {
        const t = e.data; // mismo formato que el broadcastWith() del evento ComandaStatusUpdated

        console.log('🔄 Comanda actualizada:', t);

        const status = String(t.estado);
        const itemId = String(t.id); // 👈 aquí colocas esta línea
        console.log('🔄 ITEMID:', itemId);
        console.log('🔄 t.estado:', t.estado);
        const item = {
            id: "comanda_"+itemId,
            status: status,
            text: getComandaCardByStatus(t),
            content: `Comanda #${t.numero}`,
            tags: "comanda",
            type: 'comanda',
            color: "moccasin"
        };
        item[status] = status;
        console.log('🔄 item:', item);
        // Quitar tarjeta anterior si ya existía
        removeKanbanItemById(itemId);
        console.log('🔄 Agrgando:', item);
        $("#kanban").jqxKanban("addItem", item);
        console.log('🔄 Agrgado:', item);
        // Reagregar tarjeta con el nuevo estado
        /*if ($("#kanban").length && $("#kanban").data('jqxKanban')) {
            $("#kanban").jqxKanban("addItem", item);
        }*/
    });

function getComandaCardByStatus(t){
    const st = String(t.estado||'created');
    console.log(st);
    if (st === 'processing') return getComandaCardProcessing(t);
    if (st === 'shipped')    return getComandaCardShipped(t);
    return getComandaCardCreated(t);
}

function getComandaCardProcessing(t){
    // calcula textos ETA si están
    let etaTxt = '';
    console.log(t.estimated_ready_at);
    if (t.estimated_ready_at) {
        const d = new Date(t.estimated_ready_at);
        console.log(d);
        const time = d.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
        console.log(time);
        const date = d.toLocaleDateString('es-PE', {year:'numeric', month:'short', day:'2-digit'});
        console.log(date);
        //etaTxt = `<div class="mt-1"><small style="color: black !important;"><b>ETA:</b> ${date} ${time} (${t.estimated_minutes||'-'} min)</small></div>`;
        etaTxt = `<b>ETA:</b> ${date} ${time} (${t.estimated_minutes||'-'} min)`;
        console.log(etaTxt);
    }

    return `
  <div class="card card-widget widget-user pedido-card"
     data-id="${t.id}"
     data-deadline="${t.estimated_ready_at || ''}"
     data-total-min="${t.estimated_minutes || 60}"
     data-estado="${t.estado}"
     style="margin:5px;padding:5px;width:100%;min-height:120px;">
  <div class="widget-user-header bg-info eta-header" style="padding:8px; transition:background-color .3s ease;">
    <span class="widget-user-desc" style="font-size:14px">Comanda #${t.numero}</span>
    <h5 class="widget-user-username" style="font-size:.9rem;padding-top:3px">
      Mesa ${t.mesa || '-'} <br> Mozo: ${t.mozo || '-'}
    </h5>
    <div class="mt-1">
      <small class="eta-line" style="color:black!important;">${etaTxt}</small>
    </div>
  </div>
  <div class="card-footer" style="padding:8px;margin-top:22px">
    ${comandaFooterLeft(t)}
  </div>
</div>`;
}

function getComandaCardShipped(t){
    return `
    <div class="card card-widget widget-user" style="margin:5px;padding:5px;width:100%;min-height:120px;">
      <div class="widget-user-header bg-info" style="padding:8px;">
        <span class="widget-user-desc" style="font-size:14px">Comanda #${t.numero}</span>
        <h5 class="widget-user-username" style="font-size:.9rem;padding-top:3px">
          Mesa ${t.mesa || '-'} <br> Mozo: ${t.mozo || '-'}
        </h5>
      </div>
      <div class="card-footer" style="padding:8px;">
        <div class="row">
          <div class="col-sm-12">
            ${comandaFooterLeft(t)}
          </div>
          <div class="col-sm-12">
            <a href="#" class="btn btn-sm btn-success btn-block" data-comanda-entregar="${t.id || t.comanda_id}">
              <strong>ENTREGAR</strong>
            </a>
          </div>
        </div>
      </div>
    </div>`;
}