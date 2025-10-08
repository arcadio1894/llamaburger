$(document).ready(function () {
    //$permissions = JSON.parse($('#permissions').val());
    //console.log($permissions);
    $('#sandbox-container .input-daterange').datepicker({
        todayBtn: "linked",
        clearBtn: true,
        language: "es",
        multidate: false,
        autoclose: true
    });

    getDataOrders(1);

    $("#btnBusquedaAvanzada").click(function(e){
        e.preventDefault();
        $(".busqueda-avanzada").slideToggle();
    });

    $(document).on('click', '[data-item]', showData);
    $("#btn-search").on('click', showDataSearch);

    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    $(document).on('click', '[data-ver_detalles]', showDetails);

    $(document).on('click', '[data-activar]', activarOrder);
});

var $formDelete;
var $modalDelete;
var $modalDecimals;
var $formDecimals;

var $permissions;
var $modalDetraction;

function activarOrder() {
    var order_id = $(this).data('id');

    $.confirm({
        icon: 'fas fa-check',
        theme: 'modern',
        closeIcon: true,
        animation: 'zoom',
        type: 'green',
        title: '¿Está seguro de activar esta order?',
        content: 'ORDEN - '+order_id,
        buttons: {
            confirm: {
                text: 'CONFIRMAR',
                action: function (e) {
                    $.ajax({
                        url: '/dashboard/activar/order/'+order_id,
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        processData:false,
                        contentType:false,
                        success: function (data) {
                            console.log(data);
                            $.alert(data.message);
                            setTimeout( function () {
                                getDataOrders(1);
                            }, 2000 )
                        },
                        error: function (data) {
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

function showDetails() {
    const orderId = $(this).data('id');

    // Realizar una solicitud AJAX para obtener los detalles del pedido
    $.ajax({
        url: `/dashboard/orders/${orderId}/details`,
        method: 'GET',
        success: function (response) {
            if (response.details) {
                // Generar dinámicamente el contenido del modal
                let content = '';
                response.details.forEach((detail, index) => {
                    content += `
                        <div class="mb-4">
                            <h6><strong>Pizza:</strong> ${detail.pizza_name} | ${detail.type} (${detail.size})</h6>
                            <p><strong>Ingredientes:</strong> ${detail.ingredients}</p>
                    `;

                    // Agregar las opciones seleccionadas
                    if (detail.options && detail.options.length > 0) {
                        content += `<p><strong>Opciones seleccionadas:</strong></p><ul>`;
                        detail.options.forEach(option => {
                            content += `
                                <li><em>${option.product_name}</em></li>
                            `;
                        });
                        content += `</ul>`;
                    }

                    content += `</div><hr />`;
                });

                // Insertar el contenido generado en el modal
                $('#order-details-content').html(content);

                // Mostrar el modal
                $('#orderDetailsModal').modal('show');
            } else {
                toastr.error('No se encontraron detalles para este pedido.', 'Error', {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "2000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                });
            }
        },
        error: function (xhr) {
            console.error('Error:', xhr.responseText);
            toastr.error('Ocurrió un error al obtener los detalles del pedido.', 'Error', {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "2000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            });
        }
    });
}

function showDataSearch() {
    getDataOrders(1)
}

function showData() {
    //event.preventDefault();
    var numberPage = $(this).attr('data-item');
    console.log(numberPage);
    getDataOrders(numberPage)
}

function getDataOrders($numberPage) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    var code = $('#code').val();
    var year = $('#year').val();
    var startDate = $('#start').val();
    var endDate = $('#end').val();

    $.get('/dashboard/get/data/orders/annulled/'+$numberPage, {
        code: code,
        year: year,
        startDate: startDate,
        endDate: endDate,
    }, function(data) {
        if ( data.data.length == 0 )
        {
            renderDataOrdersEmpty(data);
        } else {
            renderDataOrders(data);
        }


    }).fail(function(jqXHR, textStatus, errorThrown) {
        // Función de error, se ejecuta cuando la solicitud GET falla
        console.error(textStatus, errorThrown);
        if (jqXHR.responseJSON.message && !jqXHR.responseJSON.errors) {
            toastr.error(jqXHR.responseJSON.message, 'Error', {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "2000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            });
        }
        for (var property in jqXHR.responseJSON.errors) {
            toastr.error(jqXHR.responseJSON.errors[property], 'Error', {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "2000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            });
        }
    }, 'json')
        .done(function() {
            // Configuración de encabezados
            var headers = {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            };

            $.ajaxSetup({
                headers: headers
            });
        });
}

function renderDataOrdersEmpty(data) {
    var dataAccounting = data.data;
    var pagination = data.pagination;
    console.log(dataAccounting);
    console.log(pagination);

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' pedidos de clientes.');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataOrders(data) {
    var dataQuotes = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' pedidos de clientes.');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    for (let j = 0; j < dataQuotes.length ; j++) {
        renderDataTable(dataQuotes[j]);
    }

    if (pagination.currentPage > 1)
    {
        renderPreviousPage(pagination.currentPage-1);
    }

    if (pagination.totalPages > 1)
    {
        if (pagination.currentPage > 3)
        {
            renderItemPage(1);

            if (pagination.currentPage > 4) {
                renderDisabledPage();
            }
        }

        for (var i = Math.max(1, pagination.currentPage - 2); i <= Math.min(pagination.totalPages, pagination.currentPage + 2); i++)
        {
            renderItemPage(i, pagination.currentPage);
        }

        if (pagination.currentPage < pagination.totalPages - 2)
        {
            if (pagination.currentPage < pagination.totalPages - 3)
            {
                renderDisabledPage();
            }
            renderItemPage(i, pagination.currentPage);
        }

    }

    if (pagination.currentPage < pagination.totalPages)
    {
        renderNextPage(pagination.currentPage+1);
    }
}

function renderDataTableEmpty() {
    var clone = activateTemplate('#item-table-empty');
    $("#body-table").append(clone);
}

function renderDataTable(data) {
    var clone = activateTemplate('#item-table');
   /* clone.querySelector("[data-id]").innerHTML = data.id;*/
    clone.querySelector("[data-code]").innerHTML = data.code;
    clone.querySelector("[data-date]").innerHTML = data.date;
    clone.querySelector("[data-date_delivery]").innerHTML = data.date_delivery;
    clone.querySelector("[data-customer]").innerHTML = data.customer;
    clone.querySelector("[data-phone]").innerHTML = data.phone;
    clone.querySelector("[data-address]").innerHTML = data.address;
    clone.querySelector("[data-total]").innerHTML = data.total;
    clone.querySelector("[data-method]").innerHTML = data.method;
    clone.querySelector("[data-state]").innerHTML = data.state;
    clone.querySelector("[data-data_payment]").innerHTML = data.data_payment;

    var botones = clone.querySelector("[data-buttons]");

    var cloneBtnActive = activateTemplate('#template-active');

    if (hasPerm('pedidos.ver_detalles')) {
        cloneBtnActive.querySelector("[data-ver_detalles]").setAttribute("data-id", data.id);
    } else {
        let element = cloneBtnActive.querySelector("[data-ver_detalles]");
        if (element) {
            element.style.display = 'none';
        }
    }

    if (hasPerm('pedidos.activar_orden')) {
        cloneBtnActive.querySelector("[data-activar]").setAttribute("data-id", data.id);
    } else {
        let element = cloneBtnActive.querySelector("[data-activar]");
        if (element) {
            element.style.display = 'none';
        }
    }

    botones.append(cloneBtnActive);

    $("#body-table").append(clone);

    $('[data-toggle="tooltip"]').tooltip();
}

function renderPreviousPage($numberPage) {
    var clone = activateTemplate('#previous-page');
    clone.querySelector("[data-item]").setAttribute('data-item', $numberPage);
    $("#pagination").append(clone);
}

function renderDisabledPage() {
    var clone = activateTemplate('#disabled-page');
    $("#pagination").append(clone);
}

function renderItemPage($numberPage, $currentPage) {
    var clone = activateTemplate('#item-page');
    if ( $numberPage == $currentPage )
    {
        clone.querySelector("[data-item]").setAttribute('data-item', $numberPage);
        clone.querySelector("[data-active]").setAttribute('class', 'page-item active');
        clone.querySelector("[data-item]").innerHTML = $numberPage;
    } else {
        clone.querySelector("[data-item]").setAttribute('data-item', $numberPage);
        clone.querySelector("[data-item]").innerHTML = $numberPage;
    }

    $("#pagination").append(clone);
}

function renderNextPage($numberPage) {
    var clone = activateTemplate('#next-page');
    clone.querySelector("[data-item]").setAttribute('data-item', $numberPage);
    $("#pagination").append(clone);
}

function activateTemplate(id) {
    var t = document.querySelector(id);
    return document.importNode(t.content, true);
}