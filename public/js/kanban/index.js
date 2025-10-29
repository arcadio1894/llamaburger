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
                        rawId: String(order.id),
                        status: orderStatus,                           // created/processing/shipped
                        type: 'order',                                 // <— tipo
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
                        rawId: String(t.id || t.comanda_id),
                        type: 'comanda',                               // <— tipo
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

        const ent = parseEntity(itemId); // { type, id }

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

        /*if (oldStatus === "created" && newStatus === "processing") {
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
        }*/

        // === RUTEO DE LÓGICA ===
        if (ent.type === 'order') {
            // ⬇️ Tu flujo actual intacto
            return handleOrderMove(ent.id, oldStatus, newStatus, itemId);
        } else {
            // ⬇️ Nueva lógica comanda
            return handleComandaMove(ent.id, oldStatus, newStatus, itemId);
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

    startEtaTicker();

    $(document).on("click", "[data-comanda-entregar]", function (event) {
        event.preventDefault();

        const $btn = $(this);
        let rawId = $btn.data("comanda-entregar");

        // 🔹 Si el valor viene como "comanda_5", lo limpiamos
        let comandaId = rawId.toString().replace(/comanda_/i, "");

        $.confirm({
            title: "📦 Confirmar Entrega",
            content: "¿Estás seguro de que este pedido ha sido entregado?",
            buttons: {
                aceptar: {
                    text: "Sí, Entregado",
                    btnClass: "btn-green",
                    action: function () {
                        $.blockUI({
                            message: '<h3>⏳ Procesando solicitud...</h3>',
                            css: {
                                border: 'none',
                                padding: '15px',
                                backgroundColor: '#000',
                                borderRadius: '10px',
                                opacity: 0.5,
                                color: '#fff'
                            }
                        });

                        $.ajax({
                            url: `/api/kitchen/comandas/${comandaId}/deliver`,
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                            },
                            success: function (res) {
                                $.unblockUI();
                                if (res.ok) {
                                    $.alert("✅ Pedido entregado correctamente.");

                                    // 🔥 Dispara un evento local (para quitar el card)
                                    $(document).trigger("comanda:entregada", [comandaId]);
                                } else {
                                    $.alert("⚠️ No se pudo actualizar el estado del pedido.");
                                }
                            },
                            error: function (xhr) {
                                $.unblockUI();
                                console.error("❌ Error al entregar:", xhr);
                                $.alert("⚠️ Error al entregar el pedido.");
                            }
                        });
                    }
                },
                cancelar: { text: "Cancelar" }
            }
        });
    });

    $(document).on("comanda:entregada", function (e, comandaId) {
        // Buscar tanto comanda_5 como 5 por seguridad
        const $card = $(`[data-comanda-entregar='${comandaId}'], [data-comanda-entregar='comanda_${comandaId}']`).closest(".card");
        if ($card.length) {
            $card.fadeOut(400, function () { $(this).remove(); });
        }
    });
});

function handleOrderMove(id, oldStatus, newStatus, itemDomId){
    // Copia aquí tu lógica actual para orders.
    // Puedes reutilizar exactamente tus $.confirm y $.post existentes.
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
}

function handleComandaMove(id, oldStatus, newStatus, itemDomId){
    // created -> processing : pedir minutos y guardar inicio + ETA
    if (oldStatus === "created" && newStatus === "processing") {
        $.confirm({
            title: "⏳ Tiempo de preparación",
            content: '<label>¿En cuántos minutos estará lista la comanda?</label>' +
                '<input type="number" placeholder="Ej: 12" class="eta-minutes form-control" required />',
            buttons: {
                aceptar: {
                    text: "Aceptar",
                    btnClass: "btn-blue",
                    action: function () {
                        const mins = parseInt(this.$content.find('.eta-minutes').val(), 10);
                        if (!mins || mins <= 0) { $.alert("⚠️ Ingresa minutos válidos."); return false; }

                        $.blockUI({ message: '<h3>Guardando...</h3>' });
                        $.post({
                            url: `/api/kitchen/comandas/${id}/start`,
                            data: { estimated_minutes: mins },
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(){
                                $.unblockUI();
                                toastr.success('Comanda en cocina. ETA registrada.');
                                //refreshComanda(id);
                            },
                            error: function(){
                                $.unblockUI();
                                $.alert('⚠️ No se pudo iniciar cocina.');
                            }
                        });
                    }
                },
                cancelar: { text: "Cancelar" }
            }
        });
        return;
    }

    // processing -> shipped : listo (sin repartidor)
    if (oldStatus === "processing" && newStatus === "shipped") {
        $.blockUI({ message: '<h3>Marcando como listo...</h3>' });
        $.post({
            url: `/api/kitchen/comandas/${id}/ready`,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(){
                $.unblockUI();
                toastr.success('Comanda lista. Se notificará al mozo.');
                //refreshComanda(id);
            },
            error: function(){
                $.unblockUI();
                $.alert('⚠️ No se pudo marcar como listo.');
            }
        });
        return;
    }
}

function refreshComanda(id) {
    $.get(`/api/kitchen/comandas/${id}`, function(t) {
        const status = normalizeComandaStatusToColumn(t);
        const itemId = String(t.id || t.comanda_id); // ← sin prefijo

        const newItem = {
            id: itemId,
            rawId: itemId,
            type: 'comanda',
            status: status,
            text: getComandaCardByStatus(t),
            content: `Comanda #${t.numero}`
        };
        newItem[status] = status; // jqxKanban requiere el dataField

        // eliminar si existe y reinsertar
        removeKanbanItemByIdSafe(itemId);
        if ($("#kanban").length && $("#kanban").data('jqxKanban')) {
            $("#kanban").jqxKanban("addItem", newItem);
        }
    });
}

function normalizeComandaStatusToColumn(t) {
    const raw = (t.estado || t.status || 'enviada').toString().toLowerCase();
    // mapea a dataField del Kanban
    switch (raw) {
        case 'enviada':    return 'created';
        case 'cocinando':  return 'processing';
        case 'lista':      return 'shipped';
        case 'servida':    return 'completed'; // fuera del tablero si filtras
        case 'cancelada':  return 'cancelled'; // fuera del tablero si filtras
        case 'created':    // compatabilidad antigua
        case 'processing':
        case 'shipped':
            return raw;
        default:
            return 'created';
    }
}

function removeKanbanItemByIdSafe(id) {
    try {
        const items = $("#kanban").jqxKanban("getItems") || [];
        const ids = items.map(it => String(it.id));
        const variants = [String(id), `comanda_${id}`, `kanban_${id}`];
        const existing = variants.find(v => ids.includes(v));
        if (existing) $("#kanban").jqxKanban("removeItem", existing);
    } catch (err) {
        console.warn('removeKanbanItemByIdSafe fallback:', err);
    }
}

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

function getComandaCardByStatus(t){
    const st = String(t.status||'created');
    if (st === 'processing') return getComandaCardProcessing(t);
    if (st === 'shipped')    return getComandaCardShipped(t);
    return getComandaCardCreated(t);
}

function getComandaCardProcessing(t){
    console.log("Procesando CardProcessing");
    // calcula textos ETA si están
    let etaTxt = '';
    if (t.estimated_ready_at) {
        const d = new Date(t.estimated_ready_at);
        const time = d.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'});
        const date = d.toLocaleDateString('es-PE', {year:'numeric', month:'short', day:'2-digit'});
        etaTxt = `<b>ETA:</b> ${date} ${time} (${t.estimated_minutes||'-'} min)`;
    }

    return `
  <div class="card card-widget widget-user pedido-card"
     data-id="${t.id}"
     data-deadline="${t.estimated_ready_at || ''}"
     data-total-min="${t.estimated_minutes || 60}"
     data-estado="${t.estado}"
     style="margin:5px;padding:5px;width:100%;min-height:120px;">
  <div class="widget-user-header bg-gradient-success eta-header" style="padding:8px; transition:background-color .3s ease;">
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
      <div class="widget-user-header bg-gradient-info" style="padding:8px;">
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

function comandaFooterLeft(t){
    const url_imprimir = document.location.origin + '/imprimir/comanda-mesa/' + (t.comanda_id||t.id);
    return `
  <div class="row">
    <div class="col-sm-6 border-right">
      <div class="description-block">
        <a href="${url_imprimir}" target="_blank">
          <h6 class="description-header" style="font-size:.65rem;font-weight:bold;color:black">VER COMANDA</h6>
        </a>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="description-block">
        <h6 class="description-header" style="font-size:.65rem;font-weight:bold;color:black">S/ ${Number(t.total||0).toFixed(2)}</h6>
      </div>
    </div>
  </div>`;
}

function parseEntity(itemIdOld){
    // "order_123" | "comanda_57"
    // Quitar si comienza en kanban_
    var itemId = itemIdOld.replace('kanban_','');
    if (itemId.indexOf('order_') === 0)   return { type:'order',   id: itemId.replace('order_','') };
    if (itemId.indexOf('comanda_') === 0) return { type:'comanda', id: itemId.replace('comanda_','') };
    // fallback: intenta detectar por prefijos viejos
    return { type: 'order', id: itemId.replace('kanban_','') };
}

// Util: formatea "faltan Xm Ys" o "atrasado Xm"
function fmtRemaining(ms){
    const sign = ms < 0 ? -1 : 1;
    ms = Math.abs(ms);
    const m = Math.floor(ms/60000);
    const s = Math.floor((ms%60000)/1000);
    return sign < 0 ? `atrasado ${m}m ${s}s` : `faltan ${m}m ${s}s`;
}

// Pinta el header según progreso: <90% verde, 70–90% naranja, >100% rojo
function paintEtaState(card, nowMs) {
    const header = card.querySelector('.eta-header');
    const line   = card.querySelector('.eta-line');

    const estado = card.dataset.estado; // 'cocinando', 'lista', etc.
    const deadlineStr = card.dataset.deadline;
    const totalMin = parseFloat(card.dataset.totalMin || '60');

    if (!deadlineStr || !totalMin || estado === 'lista') {
        header.classList.remove('bg-gradient-warning','bg-gradient-danger');
        header.classList.add('bg-gradient-success');
        if (line) line.textContent = '';
        return;
    }

    // 🧩 Tomamos los porcentajes desde la variable global
    const thresholds = window.KANBAN_THRESHOLDS || { warn: 0.90, danger: 1.00 };
    const warnPct = parseFloat(thresholds.warn ?? 0.90);
    const dangerPct = parseFloat(thresholds.danger ?? 1.00);

    const deadline = new Date(deadlineStr).getTime();
    const totalMs  = totalMin * 60 * 1000;

    // Calcular el momento en que cambia cada color
    const warnAt   = deadline - totalMs * (1 - warnPct);
    const dangerAt = deadline - totalMs * (1 - dangerPct);

    // 🔥 Cambios de color dinámicos
    header.classList.remove('bg-gradient-success','bg-gradient-warning','bg-gradient-danger');
    if (nowMs >= dangerAt) {
        header.classList.add('bg-gradient-danger');
    } else if (nowMs >= warnAt) {
        header.classList.add('bg-gradient-warning');
    } else {
        header.classList.add('bg-gradient-success');
    }

    // 🕓 Texto ETA y tiempo restante
    const d = new Date(deadline);
    const hhmm = d.toLocaleTimeString('es-PE',{hour:'2-digit',minute:'2-digit'});
    const ymd  = d.toLocaleDateString('es-PE',{year:'numeric',month:'short',day:'2-digit'});
    const remain = deadline - nowMs;
    if (line) line.innerHTML = `<b>ETA:</b> ${ymd} ${hhmm} (${totalMin} min)<br>— ${fmtRemaining(remain)}`;
}

// Ticker liviano sin llamadas al servidor
let etaTimer = null;
function startEtaTicker(){
    if (etaTimer) return;
    const tick = () => {
        const now = Date.now();
        document.querySelectorAll('.pedido-card').forEach(card => paintEtaState(card, now));
    };
    tick(); // primer pintado inmediato
    etaTimer = setInterval(tick, 5000); // cada 15s; puedes subir/bajar
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && etaTimer) { clearInterval(etaTimer); etaTimer = null; }
        else if (!document.hidden) { startEtaTicker(); }
    });
}


// Si usas Pusher, cuando llegue un evento de actualización (estado o ETA):
// 1) actualiza data-attributes y el HTML del card
// 2) llama a paintEtaState(card, Date.now()) para repintar de inmediato
//
// Ejemplo rápido cuando cambie una comanda:
function onComandaUpdate(t){
    const card = document.querySelector(`.pedido-card[data-id="${t.id}"]`);
    if (!card) return;
    card.dataset.deadline  = t.estimated_ready_at || '';
    card.dataset.totalMin  = t.estimated_minutes || 60;
    card.dataset.estado    = t.estado;
    // actualiza texto base si quieres (mesa/mozo/etc.)
    paintEtaState(card, Date.now());
}