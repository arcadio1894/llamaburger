/******/ (() => { // webpackBootstrap
/*!****************************************!*\
  !*** ./resources/js/comandaCreated.js ***!
  \****************************************/
// Usa el Echo y Pusher que ya inicializaste en orderCreated.js

// Tarjeta específica para comanda (mesa)
function getComandaCardCreated(t) {
  // colores distintos a delivery
  var headerClass = "bg-info"; // amarillo/naranja
  var url_imprimir = document.location.origin + '/imprimir/comanda-mesa/' + t.comanda_id; // ajusta si tienes ruta
  return "\n      <div class=\"card card-widget widget-user\" style=\"margin:5px;padding:5px;width:100%;min-height:120px;\">\n        <div class=\"widget-user-header ".concat(headerClass, "\" style=\"padding:8px;\">\n          <span class=\"widget-user-desc\" style=\"font-size:14px\">Comanda #").concat(t.numero, "</span>\n          <h5 class=\"widget-user-username\" style=\"font-size:.9rem;padding-top:3px\">\n            Mesa ").concat(t.mesa || '-', " <br> Mozo: ").concat(t.mozo || '-', "\n          </h5>\n        </div>\n        <div class=\"card-footer\" style=\"padding:8px;\">\n          <div class=\"row\">\n            <div class=\"col-sm-4 border-right\">\n              <div class=\"description-block\">\n                <a href=\"").concat(url_imprimir, "\" target=\"_blank\">\n                <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">VER COMANDA</h6>\n              </a>\n              </div>\n            </div>\n            <div class=\"col-sm-4 border-right\">\n              <div class=\"description-block\">\n                <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">S/ ").concat(Number(t.total || 0).toFixed(2), "</h6>\n              </div>\n            </div>\n            <div class=\"col-sm-4\">\n              <div class=\"description-block\">\n                <a href=\"#\" data-anular-comanda=\"").concat(t.comanda_id, "\" data-id=\"").concat(t.comanda_id, "\">\n                  <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">CANCELAR</h6>\n                </a>\n              </div>\n            </div>\n          </div>\n        </div>\n      </div>");
}

// Si quieres más adelante distintos layouts por estado:
/*function getComandaCardByStatus(ticket){
    // de momento usamos mismo card para created/processing/shipped
    return getComandaCardCreated(ticket);
}*/

// Remueve ítem (si existiera) por id exacto
function removeKanbanItemById(comandaId) {
  // target numérico como string (p.ej. "13")
  var target = String(comandaId).replace(/^kanban_/i, '').replace(/^comanda_/i, '').replace(/_(\d+)$/i, ''); // si te llega "kanban_comanda_13_84" -> "kanban_comanda_13" -> "13"

  console.log("🧹 REMOVE target:", target);
  try {
    var items = $("#kanban").jqxKanban("getItems") || [];
    items.forEach(function (item) {
      var idStr = String(item.id || '');

      // 1) Coincidencias directas
      if (idStr === "comanda_".concat(target) || idStr === "kanban_comanda_".concat(target) || idStr.indexOf("kanban_comanda_".concat(target, "_")) === 0) {
        console.log("📌 Eliminando (match directo):", idStr);
        $("#kanban").jqxKanban("removeItem", item.id);
        return;
      }

      // 2) Normalización general: quitar "kanban_" inicial y sufijo "_NN"
      var norm = idStr.replace(/^kanban_/i, '');
      norm = norm.replace(/_(\d+)$/i, ''); // comanda_13_84 -> comanda_13

      // Si después de normalizar queda exactamente "comanda_13", elimino
      if (norm === "comanda_".concat(target)) {
        console.log("📌 Eliminando (normalizado):", idStr, "→", norm);
        $("#kanban").jqxKanban("removeItem", item.id);
      }
    });
  } catch (e) {
    console.warn('⚠️ removeKanbanItemById fallback:', e);
  }
}
window.Echo.channel('kitchenTickets').subscribed(function () {
  return console.log('✅ Suscrito a kitchenTickets');
}).listen('.comanda.created', function (e) {
  var t = e.ticket;
  // Sonido opcional (reaprovecha tu helper)
  //if (typeof playNotificationSound === 'function') { playNotificationSound(); }

  var status = (t.status || 'created').toLowerCase(); // created/processing/shipped

  var item = {
    id: String(t.id),
    // "comanda_57"
    status: status,
    text: getComandaCardByStatus(t),
    // HTML
    content: "Comanda #".concat(t.numero),
    tags: "comanda",
    type: 'comanda',
    // <— tipo
    color: "moccasin" // borde opcional
  };
  item[status] = status; // dataField de la columna

  // Quitar versión anterior si existiera
  removeKanbanItemById(item.id);

  // Agregar al kanban
  if ($("#kanban").length && $("#kanban").data('jqxKanban')) {
    $("#kanban").jqxKanban("addItem", item);
  }
});

// 👇 NUEVO EVENTO: cuando una comanda cambia de estado (start/ready/deliver)
window.Echo.channel('kitchenTickets').listen('.comanda.updated', function (e) {
  var t = e.data; // mismo formato que el broadcastWith() del evento ComandaStatusUpdated

  console.log('🔄 Comanda actualizada:', t);
  var status = String(t.estado);
  var itemId = String(t.id); // 👈 aquí colocas esta línea
  console.log('🔄 ITEMID:', itemId);
  console.log('🔄 t.estado:', t.estado);
  var item = {
    id: "comanda_" + itemId,
    status: status,
    text: getComandaCardByStatus(t),
    content: "Comanda #".concat(t.numero),
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
function getComandaCardByStatus(t) {
  var st = String(t.estado || 'created');
  console.log(st);
  if (st === 'processing') return getComandaCardProcessing(t);
  if (st === 'shipped') return getComandaCardShipped(t);
  return getComandaCardCreated(t);
}
function getComandaCardProcessing(t) {
  // calcula textos ETA si están
  var etaTxt = '';
  console.log(t.estimated_ready_at);
  if (t.estimated_ready_at) {
    var d = new Date(t.estimated_ready_at);
    console.log(d);
    var time = d.toLocaleTimeString('es-PE', {
      hour: '2-digit',
      minute: '2-digit'
    });
    console.log(time);
    var date = d.toLocaleDateString('es-PE', {
      year: 'numeric',
      month: 'short',
      day: '2-digit'
    });
    console.log(date);
    //etaTxt = `<div class="mt-1"><small style="color: black !important;"><b>ETA:</b> ${date} ${time} (${t.estimated_minutes||'-'} min)</small></div>`;
    etaTxt = "<b>ETA:</b> ".concat(date, " ").concat(time, " (").concat(t.estimated_minutes || '-', " min)");
    console.log(etaTxt);
  }
  return "\n  <div class=\"card card-widget widget-user pedido-card\"\n     data-id=\"".concat(t.id, "\"\n     data-deadline=\"").concat(t.estimated_ready_at || '', "\"\n     data-total-min=\"").concat(t.estimated_minutes || 60, "\"\n     data-estado=\"").concat(t.estado, "\"\n     style=\"margin:5px;padding:5px;width:100%;min-height:120px;\">\n  <div class=\"widget-user-header bg-info eta-header\" style=\"padding:8px; transition:background-color .3s ease;\">\n    <span class=\"widget-user-desc\" style=\"font-size:14px\">Comanda #").concat(t.numero, "</span>\n    <h5 class=\"widget-user-username\" style=\"font-size:.9rem;padding-top:3px\">\n      Mesa ").concat(t.mesa || '-', " <br> Mozo: ").concat(t.mozo || '-', "\n    </h5>\n    <div class=\"mt-1\">\n      <small class=\"eta-line\" style=\"color:black!important;\">").concat(etaTxt, "</small>\n    </div>\n  </div>\n  <div class=\"card-footer\" style=\"padding:8px;margin-top:22px\">\n    ").concat(comandaFooterLeft(t), "\n  </div>\n</div>");
}
function getComandaCardShipped(t) {
  return "\n    <div class=\"card card-widget widget-user\" style=\"margin:5px;padding:5px;width:100%;min-height:120px;\">\n      <div class=\"widget-user-header bg-info\" style=\"padding:8px;\">\n        <span class=\"widget-user-desc\" style=\"font-size:14px\">Comanda #".concat(t.numero, "</span>\n        <h5 class=\"widget-user-username\" style=\"font-size:.9rem;padding-top:3px\">\n          Mesa ").concat(t.mesa || '-', " <br> Mozo: ").concat(t.mozo || '-', "\n        </h5>\n      </div>\n      <div class=\"card-footer\" style=\"padding:8px;\">\n        <div class=\"row\">\n          <div class=\"col-sm-12\">\n            ").concat(comandaFooterLeft(t), "\n          </div>\n          <div class=\"col-sm-12\">\n            <a href=\"#\" class=\"btn btn-sm btn-success btn-block\" data-comanda-entregar=\"").concat(t.id || t.comanda_id, "\">\n              <strong>ENTREGAR</strong>\n            </a>\n          </div>\n        </div>\n      </div>\n    </div>");
}
/******/ })()
;