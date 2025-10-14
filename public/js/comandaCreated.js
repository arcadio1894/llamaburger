/******/ (() => { // webpackBootstrap
/*!****************************************!*\
  !*** ./resources/js/comandaCreated.js ***!
  \****************************************/
// Usa el Echo y Pusher que ya inicializaste en orderCreated.js

// Tarjeta específica para comanda (mesa)
function getComandaCardCreated(t) {
  // colores distintos a delivery
  var headerClass = "bg-gradient-warning"; // amarillo/naranja
  var url_imprimir = document.location.origin + '/imprimir/comanda-mesa/' + t.comanda_id; // ajusta si tienes ruta
  return "\n      <div class=\"card card-widget widget-user\" style=\"margin:5px;padding:5px;width:100%;min-height:120px;\">\n        <div class=\"widget-user-header ".concat(headerClass, "\" style=\"padding:8px;\">\n          <span class=\"widget-user-desc\" style=\"font-size:14px\">Comanda #").concat(t.numero, "</span>\n          <h5 class=\"widget-user-username\" style=\"font-size:.9rem;padding-top:3px\">\n            Mesa ").concat(t.mesa || '-', " <br> Mozo: ").concat(t.mozo || '-', "\n          </h5>\n        </div>\n        <div class=\"card-footer\" style=\"padding:8px;\">\n          <div class=\"row\">\n            <div class=\"col-sm-4 border-right\">\n              <div class=\"description-block\">\n                <a href=\"").concat(url_imprimir, "\" target=\"_blank\">\n                <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">VER COMANDA</h6>\n              </a>\n              </div>\n            </div>\n            <div class=\"col-sm-4 border-right\">\n              <div class=\"description-block\">\n                <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">S/ ").concat(Number(t.total || 0).toFixed(2), "</h6>\n              </div>\n            </div>\n            <div class=\"col-sm-4\">\n              <div class=\"description-block\">\n                <a href=\"#\" data-anular data-id=\"").concat(t.comanda_id, "\">\n                  <h6 class=\"description-header\" style=\"font-size:.65rem;font-weight:bold;color:black\">CANCELAR</h6>\n                </a>\n              </div>\n            </div>\n          </div>\n        </div>\n      </div>");
}

// Si quieres más adelante distintos layouts por estado:
function getComandaCardByStatus(ticket) {
  // de momento usamos mismo card para created/processing/shipped
  return getComandaCardCreated(ticket);
}

// Remueve ítem (si existiera) por id exacto
function removeKanbanItemById(rawId) {
  try {
    var items = $("#kanban").jqxKanban("getItems") || [];
    items.forEach(function (it) {
      if (String(it.id) === String(rawId)) {
        $("#kanban").jqxKanban("removeItem", it.id);
      }
    });
  } catch (e) {}
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
/******/ })()
;