$(document).ready(function () {

    $(document).on("click", '[data-ver_ruta_map]', verRutaMap);

    /*$.get('/api/orders', function (data) {
        let source = {
            localData: data.map(order => {
                // Normaliza el estado
                let orderStatus = order.status.trim().toLowerCase();
                // Puedes agregar validación si es necesario, asignando un valor por defecto en caso de estado no reconocido

                // Devuelve el objeto con la propiedad dinámica que coincida con el dataField
                return {
                    id: String(order.id),              // Asegura que el ID sea string
                    status: orderStatus,               // Estado normalizado
                    text: getOrderCardByStatus(order), // Renderizado inicial
                    content: `Pedido #${order.id}`,     // Obligatorio para evitar errores en addItem()
                    // Agrega una propiedad dinámica cuyo nombre es el estado
                    [orderStatus]: orderStatus
                };
            }),
            dataType: "array"
        };

        let fields = [
            { name: "id", type: "string" },
            { name: "status", type: "string" },
            { name: "text", type: "string" },
            { name: "content", type: "string" }
        ];

        let dataAdapter = new $.jqx.dataAdapter(source, { autoBind: true });

        $("#kanban").jqxKanban({
            width: '100%',
            height: 600,
            source: dataAdapter,
            columns: [
                { text: "Recibido", dataField: "created", width: 300 },
                { text: "Cocinando", dataField: "processing", width: 300 },
                { text: "En Trayecto", dataField: "shipped", width: 300 }
            ],
            resources: [ // Se agregan resources para evitar errores en _resources.length
                { id: 1, name: "Default", image: "default.png" }
            ],
            columnRenderer: function (element, collapsedElement, column) {
                element.css({
                    "min-width": "320px",
                    "max-width": "320px",
                    "text-align": "center"
                });
            },
            ready: function () {
                console.log("📌 Kanban inicializado correctamente.");
            }
        });

        // Forzar el diseño con CSS
        setTimeout(() => {
            $(".jqx-kanban-column").css({
                "display": "inline-block",
                "vertical-align": "top",
                "text-align": "center",
                "min-width": "350px",
                "max-width": "350px"
            });

            $(".jqx-kanban").css({
                "display": "flex",
                "justify-content": "center"
            });
        }, 500);
    });*/
    Promise.all([
        $.get('/api/orders'),            // ya lo tenías
        $.get('/api/kitchen/comandas')   // NUEVO endpoint
    ]).then(function([orders, comRes]){

        let localData = [];

        // 1) Orders → como ya lo hacías
        if (Array.isArray(orders)) {
            localData = localData.concat(
                orders.map(order => {
                    let orderStatus = String(order.status || '').trim().toLowerCase();
                    return {
                        id: String(order.id),
                        status: orderStatus,                           // created/processing/shipped
                        text: getOrderCardByStatus(order),             // HTML existente
                        content: `Pedido #${order.id}`,
                        [orderStatus]: orderStatus
                    };
                })
            );
        }

        // 2) Comandas → nuevas tarjetas
        if (comRes && comRes.ok && Array.isArray(comRes.tickets)) {
            localData = localData.concat(
                comRes.tickets.map(t => {
                    const status = String(t.status || 'created');
                    return {
                        id: String(t.id),                              // "comanda_57"
                        status: status,                                // created/processing/shipped
                        text: getComandaCardByStatus(t),               // HTML para comanda
                        content: `Comanda #${t.numero}`,               // texto
                        [status]: status
                    };
                })
            );
        }

        // Dummy items si no hay nada
        if (localData.length === 0) {
            localData = [
                { id: "dummy_created",    status: "created",    text: "", content: "", created: "created",    dummy: true },
                { id: "dummy_processing", status: "processing", text: "", content: "", processing: "processing", dummy: true },
                { id: "dummy_shipped",    status: "shipped",    text: "", content: "", shipped: "shipped",    dummy: true }
            ];
        }

        // Init DataAdapter + Kanban
        const fields = [
            { name: "id", type: "string" },
            { name: "status", type: "string" },
            { name: "text", type: "string" },
            { name: "content", type: "string" }
        ];

        const source = { localData, dataType: "array", dataFields: fields };
        const dataAdapter = new $.jqx.dataAdapter(source, { autoBind: true });

        $("#kanban").jqxKanban({
            width: '100%',
            height: 600,
            source: dataAdapter,
            columns: [
                { text: "Recibido",   dataField: "created",    width: 300 },
                { text: "Cocinando",  dataField: "processing", width: 300 },
                { text: "En Trayecto",dataField: "shipped",    width: 300 }
            ],
            resources: [{ id: 1, name: "Default", image: "default.png" }],
            columnRenderer: function (element, collapsedElement, column) {
                element.css({ "min-width":"320px", "max-width":"320px", "text-align":"center" });
            },
            ready: function () {
                console.log("📌 Kanban inicializado con orders + comandas.");
                setTimeout(removeDummyItems, 800);
            }
        });

        // Forzar layout como ya hacías
        setTimeout(() => {
            $(".jqx-kanban-column").css({
                "display": "inline-block",
                "vertical-align": "top",
                "text-align": "center",
                "min-width": "350px",
                "max-width": "350px"
            });
            $(".jqx-kanban").css({
                "display": "flex",
                "justify-content": "center"
            });
        }, 500);

    }).catch(function(err){
        console.error('❌ Error cargando datos iniciales del kanban', err);
    });


    $("#kanban").on("itemMoved", function (event) {
        let args = event.args;
        let itemId = args.itemId;
        let oldStatus = args.oldColumn.dataField;
        let newStatus = args.newColumn.dataField;

        console.log(`🔄 Intentando mover orden ${itemId} de ${oldStatus} a ${newStatus}`);

        // ❌ Evitar que se procese automáticamente
        event.cancel = true;

        // 🚫 Definir movimientos NO PERMITIDOS
        const movimientosInvalidos = [
            { de: "shipped", a: "processing" },
            { de: "shipped", a: "created" },
            { de: "processing", a: "created" },
            { de: "created", a: "shipped" }
        ];

        // 📌 Si el movimiento es inválido, mostrar mensaje y regresar el pedido a su estado original
        if (movimientosInvalidos.some(m => m.de === oldStatus && m.a === newStatus)) {
            $.confirm({
                title: "🚫 Movimiento No Permitido",
                content: "No puedes mover un pedido a este estado.",
                buttons: {
                    ok: {
                        text: "OK",
                        btnClass: "btn-red",
                        action: function () {
                            console.log(`↩️ Devolviendo pedido ${itemId} a ${oldStatus}.`);
                            setTimeout(() => {
                                $("#kanban").jqxKanban("removeItem", itemId);
                                renderOrder(itemId);
                            }, 50);
                        }
                    }
                }
            });
            return; // 🔴 Detener la ejecución aquí
        }

        if (oldStatus === "created" && newStatus === "processing") {
            $.confirm({
                title: "⏳ Tiempo Estimado",
                content: '<label>¿En cuántos minutos estará listo el pedido?</label>' +
                    '<input type="number" placeholder="Ejemplo: 15" class="estimated-time form-control" required />',
                buttons: {
                    aceptar: {
                        text: "Aceptar",
                        btnClass: "btn-blue",
                        action: function () {
                            let tiempoEstimado = this.$content.find(".estimated-time").val().trim();
                            if (!tiempoEstimado || isNaN(tiempoEstimado) || tiempoEstimado <= 0) {
                                $.alert("⚠️ Debes ingresar un número válido.");
                                return false;
                            }

                            // 🚀 Mostrar loader en toda la pantalla
                            $.blockUI({
                                message: '<h3>⏳ Procesando solicitud...</h3>',
                                css: {
                                    border: 'none',
                                    padding: '15px',
                                    backgroundColor: '#000',
                                    '-webkit-border-radius': '10px',
                                    '-moz-border-radius': '10px',
                                    opacity: 0.5,
                                    color: '#fff'
                                }
                            });

                            // 🗑️ Eliminar temporalmente el item
                            //$("#kanban").jqxKanban("removeItem", itemId);

                            // ✅ Enviar actualización al backend
                            $.post({
                                url: '/api/orders/update-time',
                                data: { id: itemId, estimated_time: parseInt(tiempoEstimado), status: "processing" },
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: function (response) {
                                    console.log("✅ Tiempo de cocción actualizado:", response);
                                    $.alert(`✅ Tiempo estimado guardado: ${tiempoEstimado} minutos`);

                                    // 🛑 Quitar loader
                                    $.unblockUI();

                                    // 🗑️ Eliminar temporalmente el item
                                    //$("#kanban").jqxKanban("removeItem", itemId);

                                    // 🔄 Recuperar la orden actualizada y volver a agregarla
                                    //renderOrder(itemId);
                                },
                                error: function (error) {
                                    console.error("❌ Error al actualizar el tiempo estimado:", error);
                                    $.alert("⚠️ No se pudo actualizar el tiempo.");
                                }
                            });
                        }
                    },
                    cancelar: {
                        text: "Cancelar",
                        action: function () {
                            console.log("🚫 Movimiento cancelado, devolviendo el pedido a 'Recibido'.");

                            setTimeout(() => {
                                $("#kanban").jqxKanban("removeItem", itemId);
                                renderOrder(itemId); // Recuperar y volver a agregar la orden
                            }, 50);
                        }
                    }
                }
            });

            return; // 🔴 Detener la ejecución aquí
        }

        if (oldStatus === "processing" && newStatus === "shipped") {
            let item = itemId;
            let itemIDClear = item.replace("kanban_", "");
            console.log(item);
            console.log(itemIDClear);
            $.confirm({
                title: "🚚 Seleccionar Repartidor",
                content: function () {
                    var self = this;
                    return $.ajax({
                        url: '/api/distributors', // Ruta para obtener los repartidores
                        method: 'GET'
                    }).done(function (response) {
                        let options = response.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                        self.setContent(`
                        <label>Selecciona el repartidor:</label>
                        <select class="form-control distributor-select">${options}</select>
                    `);
                    }).fail(function () {
                        self.setContent("❌ No se pudieron cargar los repartidores.");
                    });
                },
                buttons: {
                    aceptar: {
                        text: "Asignar Repartidor",
                        btnClass: "btn-green",
                        action: function () {
                            let distributorId = this.$content.find(".distributor-select").val();

                            if (!distributorId) {
                                $.alert("⚠️ Debes seleccionar un repartidor.");
                                return false;
                            }

                            // 🚀 Mostrar loader en toda la pantalla
                            $.blockUI({
                                message: '<h3>⏳ Procesando solicitud...</h3>',
                                css: {
                                    border: 'none',
                                    padding: '15px',
                                    backgroundColor: '#000',
                                    '-webkit-border-radius': '10px',
                                    '-moz-border-radius': '10px',
                                    opacity: 0.5,
                                    color: '#fff'
                                }
                            });

                            // ✅ Enviar actualización al backend con el repartidor seleccionado
                            $.post({

                                url: '/api/orders/update-distributor',
                                data: { id: itemIDClear, status: "shipped", distributor_id: distributorId },
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                success: function (response) {
                                    console.log("✅ Pedido asignado a repartidor:", response);

                                    // 🛑 Quitar loader
                                    $.unblockUI();

                                    // 🗑️ Eliminar temporalmente el item y volver a renderizarlo
                                    //$("#kanban").jqxKanban("removeItem", itemId);
                                    //renderOrder(itemIDClear);
                                },
                                error: function (error) {
                                    console.error("❌ Error al actualizar el repartidor:", error);
                                    $.alert("⚠️ No se pudo asignar el repartidor.");
                                }
                            });
                        }
                    },
                    cancelar: {
                        text: "Cancelar",
                        action: function () {
                            console.log("🚫 Movimiento cancelado, devolviendo el pedido a 'Cocinando'.");

                            setTimeout(() => {
                                $("#kanban").jqxKanban("removeItem", itemId);
                                renderOrder(itemId);
                            }, 50);
                        }
                    }
                }
            });

            return; // 🔴 Detener la ejecución aquí
        }

    });

    $(document).on('click', '[data-anular]', anularOrder);

    $(document).on("click", "[data-entregar]", function (event) {
        event.preventDefault(); // Evitar navegación
        let button = $(this);
        //console.log("📦 Entregando pedido ID:", $(this).data("id"));
        let rawItemId = limpiarItemId($(this).data("id").toString()); // Limpiar ID
        //console.log("📦 Entregando pedido ID:", rawItemId);
        let itemId = rawItemId.toString().replace("kanban_", ""); // Limpiar el ID si tiene el prefijo
        console.log("📦 Entregando pedido ID:", itemId);

        $.confirm({
            title: "📦 Confirmar Entrega",
            content: "¿Estás seguro de que este pedido ha sido entregado?",
            buttons: {
                aceptar: {
                    text: "Sí, Entregado",
                    btnClass: "btn-green",
                    action: function () {
                        // 🚀 Mostrar loader en toda la pantalla
                        $.blockUI({
                            message: '<h3>⏳ Procesando solicitud...</h3>',
                            css: {
                                border: 'none',
                                padding: '15px',
                                backgroundColor: '#000',
                                '-webkit-border-radius': '10px',
                                '-moz-border-radius': '10px',
                                opacity: 0.5,
                                color: '#fff'
                            }
                        });

                        // ✅ Enviar actualización al backend
                        $.post({
                            url: "/api/orders/entregar",
                            data: { id: itemId, status: "completed" },
                            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                            success: function (response) {
                                console.log("✅ Pedido entregado correctamente:", response);
                                console.log("✅ Pedido:", itemId);
                                // 🗑️ Eliminar directamente del DOM sin usar jqxKanban
                                //button.closest(".jqx-kanban-item").remove();
                                // 🛑 Quitar loader
                                $.unblockUI();
                                $.alert("✅ Pedido marcado como entregado.");

                            },
                            error: function (error) {
                                // 🛑 Quitar loader
                                $.unblockUI();
                                console.error("❌ Error al actualizar el pedido:", error);
                                $.alert("⚠️ No se pudo actualizar el estado del pedido.");
                            }
                        });
                    }
                },
                cancelar: {
                    text: "Cancelar",
                    action: function () {
                        console.log("🚫 Entrega cancelada.");
                    }
                }
            }
        });
    });

    $(document).on("click", "[data-tiempo]", function (event) {
        event.preventDefault(); // Evitar navegación

        let itemId = $(this).data("tiempo");

        // Obtener la información de la orden desde el backend
        $.get(`/api/order/${itemId}`, function (order) {
            if (!order.date_processing || !order.estimated_time) {
                $.alert("⚠️ No hay información de tiempo disponible para este pedido.");
                return;
            }

            // Convertir date_processing a un objeto Date
            let processingDate = new Date(order.date_processing);

            // Sumar los minutos del estimated_time
            processingDate.setMinutes(processingDate.getMinutes() + parseInt(order.estimated_time));

            // Formatear la fecha y hora en 12 horas (AM/PM)
            let options = { year: 'numeric', month: 'long', day: 'numeric' };
            let formattedDate = processingDate.toLocaleDateString('es-ES', options);

            let hours = processingDate.getHours();
            let minutes = processingDate.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12; // Convertir 0 a 12
            minutes = minutes < 10 ? '0' + minutes : minutes;
            let formattedTime = `${hours}:${minutes} ${ampm}`;

            // Mostrar el pop-up con la fecha y hora
            $.confirm({
                title: "⏰ Tiempo de Entrega",
                content: `<p style="font-size: 1.2rem; font-weight: bold;">🗓️ ${formattedDate}</p>
                      <p style="font-size: 2rem; font-weight: bold;">⏱️ ${formattedTime}</p>`,
                buttons: {
                    ok: {
                        text: "Cerrar",
                        btnClass: "btn-blue"
                    }
                }
            });

        }).fail(function () {
            $.alert("❌ No se pudo obtener la información del pedido.");
        });
    });
});

function removeDummyItems() {
    var items = $("#kanban").jqxKanban("getItems");
    console.log("📌 Items para eliminar:", items);
    items.forEach(function(item) {
        // Verifica si el id comienza con "dummy_"
        if(item.id.indexOf("dummy_") === 0) {
            console.log("📌 Eliminando item dummy con id:", item.id);
            $("#kanban").jqxKanban("removeItem", item.id);
            console.log("📌 Item eliminado.");
        }
    });
    // Opcional: refrescar el widget para forzar la actualización visual
    //$("#kanban").jqxKanban("refresh");
}

function anularOrder() {
    var order_id = $(this).data('id');
    let button = $(this);
    $.confirm({
        icon: 'fas fa-trash-alt',
        theme: 'modern',
        closeIcon: true,
        animation: 'zoom',
        type: 'red',
        title: '¿Está seguro de anular esta order?',
        content: 'ORDEN - '+order_id,
        buttons: {
            confirm: {
                text: 'CONFIRMAR',
                action: function (e) {

                    // 🚀 Mostrar loader en toda la pantalla
                    $.blockUI({
                        message: '<h3>⏳ Procesando solicitud...</h3>',
                        css: {
                            border: 'none',
                            padding: '15px',
                            backgroundColor: '#000',
                            '-webkit-border-radius': '10px',
                            '-moz-border-radius': '10px',
                            opacity: 0.5,
                            color: '#fff'
                        }
                    });


                    $.ajax({
                        url: '/dashboard/anular/order/'+order_id,
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        processData:false,
                        contentType:false,
                        success: function (data) {
                            console.log(data);
                            // 🛑 Quitar loader
                            $.unblockUI();
                            $.alert(data.message);
                            /*setTimeout( function () {
                                button.closest(".jqx-kanban-item").remove();
                            }, 500 )*/
                        },
                        error: function (data) {
                            // 🛑 Quitar loader
                            $.unblockUI();
                            $.alert("Sucedió un error en el servidor. Intente nuevamente.");
                        },
                    });
                },
            },
            cancel: {
                text: 'CANCELAR',
                action: function (e) {
                    $.alert("Cambio de estado cancelado.");
                },
            },
        },
    });

}

function renderOrder(itemId) {
    $.get(`/api/order/${itemId}`, function (order) {
        if (!order || !order.id || !order.status) {
            console.error("❌ Error: La orden no fue encontrada en la base de datos.");
            return;
        }

        console.log(order.status);

        let newOrderData = {
            id: String(order.id),
            status: order.status.trim().toLowerCase(),
            text: getOrderCardByStatus(order), // Generar HTML del card
            content: getOrderCardByStatus(order),
            tags: "pedido",
            color: ""
        };

        console.log("🔄 Recuperando y reinsertando la orden en el Kanban:", newOrderData);

        $("#kanban").jqxKanban("addItem", newOrderData);
    }).fail(function () {
        console.error("❌ Error: No se pudo recuperar la orden de la base de datos.");
    });
}

/**
 * 🔥 Función para seleccionar la plantilla adecuada según el estado del pedido
 */
function getOrderCardByStatus(order) {
    switch (order.status) {
        case "created":
            return getOrderCardCreated(order);
        case "processing":
            return getOrderCardProcessing(order);
        case "shipped":
            return getOrderCardShipped(order);
        default:
            return getOrderCardCreated(order);
    }
}

// Función para generar las tarjetas en AdminLTE
function getOrderCardCreated(order) {
    // Definir el color de fondo según el estado del pedido
    let bgColor = "bg-info";
    let url_comanda = document.location.origin + '/imprimir/comanda/' + order.id;
    let url_boleta = document.location.origin + '/imprimir/recibo/' + order.id;
    let address = ( order.shipping_address == null ) ? '': order.shipping_address.address;
    let latitude = ( order.shipping_address == null ) ? '': order.shipping_address.latitude;
    let longitude = ( order.shipping_address == null ) ? '': order.shipping_address.longitude;

    return `
    <div class="card card-widget widget-user" style="margin: 5px; padding: 5px; width: 100%; min-height: 120px;">
        <div class="widget-user-header ${bgColor}" style="padding: 8px;">
            <span class="widget-user-desc" style="font-size: 14px">Pedido #${order.id}</span>
            <h5 class="widget-user-username" style="font-size: 0.8rem; padding-top: 3px">
                ${order.order_user} <br> ${order.order_phone}
            </h5>
        </div>
       
        <div class="card-footer" style="padding: 8px;">
            <div class="row">
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="${url_comanda}" target="_blank" data-imprimir_comanda="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">COMANDA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="${url_boleta}" target="_blank" data-imprimir_boleta="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">BOLETA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="#" data-ver_ruta_map data-id="${order.id}" data-address="${address}" data-latitude="${latitude}" data-longitude="${longitude}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">VER RUTA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="#" data-anular data-id="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">ELIMINAR</h6>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
}

function getOrderCardProcessing(order) {
    // Definir el color de fondo según el estado del pedido
    let bgColor = "bg-success";
    let url_comanda = document.location.origin + '/imprimir/comanda/' + order.id;
    let url_boleta = document.location.origin + '/imprimir/recibo/' + order.id;
    let address = ( order.shipping_address == null ) ? '': order.shipping_address.address;
    let latitude = ( order.shipping_address == null ) ? '': order.shipping_address.latitude;
    let longitude = ( order.shipping_address == null ) ? '': order.shipping_address.longitude;

    // Convertir date_processing a un objeto Date
    let processingDate = new Date(order.date_processing);
    console.log(order.date_processing);

    // Sumar los minutos del estimated_time
    processingDate.setMinutes(processingDate.getMinutes() + parseInt(order.estimated_time));

    // Formatear la fecha y hora en 12 horas (AM/PM)
    let options = { year: 'numeric', month: 'long', day: 'numeric' };
    let formattedDate = "No hay fecha";
    if ( order.date_processing )
    {
        formattedDate = processingDate.toLocaleDateString('es-ES', options);
    }

    let hours = processingDate.getHours();
    let minutes = processingDate.getMinutes();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12; // Convertir 0 a 12
    minutes = minutes < 10 ? '0' + minutes : minutes;

    let formattedTime = "No hay hora";

    if ( order.date_processing )
    {
        formattedTime = `${hours}:${minutes} ${ampm}`;
    }


    return `
    <div class="card card-widget widget-user" style="margin: 5px; padding: 5px; width: 100%; min-height: 120px;">
        <div class="widget-user-header ${bgColor}" style="padding: 8px;">
            <span class="widget-user-desc" style="font-size: 14px">Pedido #${order.id}</span>
            <h5 class="widget-user-username" style="font-size: 0.8rem; padding-top: 3px">
                ${order.order_user} <br> ${order.order_phone}
            </h5>
        </div>
        <!--<div class="widget-user-image" style="width: 40px; height: 40px; margin-top: -15px;">
            <img class="img-circle elevation-2" src="/images/users/1.jpg" alt="User Avatar" style="width: 40px; height: 40px;">
        </div>-->
        <div class="card-footer" style="padding: 10px;">
            <div class="row">
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="${url_comanda}" target="_blank" data-imprimir_comanda="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">COMANDA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                       
                        <a href="${url_boleta}" target="_blank" data-imprimir_boleta="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">BOLETA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="#" data-ver_ruta_map data-id="${order.id}" data-address="${address}" data-latitude="${latitude}" data-longitude="${longitude}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">VER RUTA</h6>
                        </a>
                    </div>
                </div>
                
                <div class="col-sm-3">
                    <div class="description-block">
                        <a href="#" data-anular data-id="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">ELIMINAR</h6>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row ml-1 mt-2">
                <p style="font-size: 0.7rem; font-weight: bold; margin-bottom: 0px">🗓️ ${formattedDate}</p>
                <p style="font-size: 0.7rem; font-weight: bold; margin-left: 8px; margin-bottom: 0px">⏱️ ${formattedTime}</p>
            </div>
        </div>
    </div>`;
}

function getOrderCardShipped(order) {
    // Definir el color de fondo según el estado del pedido
    let bgColor = "bg-warning";
    let url_comanda = document.location.origin + '/imprimir/comanda/' + order.id;
    let url_boleta = document.location.origin + '/imprimir/recibo/' + order.id;
    let address = ( order.shipping_address == null ) ? '': order.shipping_address.address;
    let latitude = ( order.shipping_address == null ) ? '': order.shipping_address.latitude;
    let longitude = ( order.shipping_address == null ) ? '': order.shipping_address.longitude;

    return `
    <div class="card card-widget widget-user" style="margin: 5px; padding: 5px; width: 100%; min-height: 120px;">
        <div class="widget-user-header ${bgColor}" style="padding: 8px;">
            <span class="widget-user-desc" style="font-size: 14px">Pedido #${order.id}</span>
            <h5 class="widget-user-username" style="font-size: 0.8rem; padding-top: 3px">
                ${order.order_user} <br> ${order.order_phone}
            </h5>
        </div>
       <!-- <div class="widget-user-image" style="width: 40px; height: 40px; margin-top: -15px;">
            <img class="img-circle elevation-2" src="/images/users/1.jpg" alt="User Avatar" style="width: 40px; height: 40px;">
        </div>-->
        <div class="card-footer" style="padding: 8px;">
            <div class="row">
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="${url_comanda}" target="_blank" data-imprimir_comanda="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">COMANDA</h6>
                        </a> 
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        
                        <a href="${url_boleta}" target="_blank" data-imprimir_boleta="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">BOLETA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="#" data-ver_ruta_map data-id="${order.id}" data-address="${address}" data-latitude="${latitude}" data-longitude="${longitude}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">VER RUTA</h6>
                        </a>
                    </div>
                </div>
                <div class="col-sm-3 border-right">
                    <div class="description-block">
                        <a href="#" data-anular data-id="${order.id}">
                            <h6 class="description-header" style="font-size: 0.5rem; font-weight: bold; color: black">ELIMINAR</h6>
                        </a>
                    </div>
                </div>
               
            </div>
            <div class="row">
                <a href="#" data-entregar class="btn btn-success btn-block" data-id="${order.id}">
                    <h6 class="description-header mb-0" style="font-size: 0.8rem; font-weight: bold; color: black">ENTREGAR</h6>
                </a>
            </div>
        </div>
    </div>`;
}

function verRutaMap() {
    console.log("Botón clicado"); // Asegúrate de que este mensaje aparezca en la consola
    let latitude = $(this).data("latitude");
    let longitude = $(this).data("longitude");

    if (latitude && longitude) {
        // Construir la URL de Google Maps
        const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}&z=15`;

        // Abrir la URL en una nueva pestaña
        window.open(googleMapsUrl, "_blank");
    } else {
        alert("No se encontraron coordenadas.");
    }
}

function limpiarItemId(itemId) {
    // Verificar si tiene formato kanban_XX o kanban_XX_YY
    if (itemId.startsWith("kanban_")) {
        let partes = itemId.split("_"); // Separar por "_"
        if (partes.length >= 2) {
            return partes[1]; // Devolver solo el primer número después de "kanban_"
        }
    }
    return itemId; // Devolver el mismo ID si no tiene el formato esperado
}

function getComandaCardCreated(t) {
    let headerClass = "bg-gradient-warning";
    let url_imprimir = document.location.origin + '/imprimir/comanda-mesa/' + t.comanda_id; // ajusta ruta si difiere
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

function getComandaCardByStatus(ticket){
    // por ahora mismo template; luego personalizamos por columna
    return getComandaCardCreated(ticket);
}