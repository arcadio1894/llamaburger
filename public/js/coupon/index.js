$(document).ready(function () {
    /*$permissions = JSON.parse($('#permissions').val());*/
    //console.log($permissions);

    // Función para obtener y mostrar los datos iniciales
    function initData() {
        getDataProducts(1);
    }

    // Función para obtener y mostrar los datos con los checkboxes activos y criterios de búsqueda
    function showDataSearch() {
        getDataProducts(1);
    }

    // Evento al cargar la página
    initData();

    $("#btnBusquedaAvanzada").click(function(e){
        e.preventDefault();
        $(".busqueda-avanzada").slideToggle();
    });

    $(document).on('click', '[data-item]', showData);

    $("#btn-search").on('click', showDataSearch);

    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    $formDelete = $('#formDelete');
    $formDelete.on('submit', disableMaterial);
    $modalDelete = $('#modalDelete');
    $(document).on('click', '[data-delete]', openModalDisable);

    $(document).on("click", "[data-cambiar_estado]", function () {
        let button = $(this);
        let couponId = button.attr("data-coupon_id");
        let description = button.attr("data-description");
        let currentState = button.attr("data-state");
        let newState = (currentState === "active") ? "inactive" : "active"; // Cambia active ↔ inactive
        let newStateText = (currentState === "active") ? "INACTIVO" : "ACTIVO";

        $.confirm({
            title: "Confirmar acción",
            content: `¿Está seguro de cambiar el estado de <strong>${description}</strong> a <strong>${newStateText}</strong>?`,
            type: "orange",
            buttons: {
                cancelar: {
                    text: "Cancelar",
                    action: function () {
                        // No hacer nada
                    }
                },
                confirmar: {
                    text: "Sí, cambiar",
                    btnClass: "btn-blue",
                    action: function () {
                        $.ajax({
                            url: "/dashboard/cupones/cambiar-estado", // Ruta en tu backend
                            method: "POST",
                            data: {
                                coupon_id: couponId,
                                state: newState,
                                _token: $('meta[name="csrf-token"]').attr('content') // Para Laravel CSRF
                            },
                            beforeSend: function () {
                                button.prop("disabled", true);
                            },
                            success: function (response) {
                                if (response.success) {
                                    button.attr("data-state", newState);
                                    button.toggleClass("btn-outline-danger btn-outline-success");
                                    button.find("i").toggleClass("fa-bell-slash fa-bell");
                                    initData();
                                    $.alert({
                                        title: "Éxito",
                                        content: "Estado cambiado correctamente",
                                        type: "green"
                                    });
                                } else {
                                    $.alert({
                                        title: "Error",
                                        content: response.message,
                                        type: "red"
                                    });
                                }
                            },
                            error: function () {
                                $.alert({
                                    title: "Error",
                                    content: "Hubo un problema al cambiar el estado.",
                                    type: "red"
                                });
                            },
                            complete: function () {
                                button.prop("disabled", false);
                            }
                        });
                    }
                }
            }
        });
    });
});

var $formDelete;
var $modalDelete;
var $modalImage;
var $permissions;

function openModalDisable() {
    var material_id = $(this).data('delete');
    var description = $(this).data('description');

    $modalDelete.find('[id=material_id]').val(material_id);
    $modalDelete.find('[id=descriptionDelete]').html(description);

    $modalDelete.modal('show');
}

function disableMaterial() {
    event.preventDefault();
    // Obtener la URL
    var deleteUrl = $formDelete.data('url');
    $.ajax({
        url: deleteUrl,
        method: 'POST',
        data: new FormData(this),
        processData:false,
        contentType:false,
        success: function (data) {
            console.log(data);
            toastr.success(data.message, 'Éxito',
                {
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
            $modalDelete.modal('hide');
            setTimeout( function () {
                location.reload();
            }, 2000 )
        },
        error: function (data) {
            if( data.responseJSON.message && !data.responseJSON.errors )
            {
                toastr.error(data.responseJSON.message, 'Error',
                    {
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
            for ( var property in data.responseJSON.errors ) {
                toastr.error(data.responseJSON.errors[property], 'Error',
                    {
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
            /*for ( var property in data.responseJSON.errors ) {
                toastr.error(data.responseJSON.errors[property], 'Error',
                    {
                        "closeButton": true,
                        "debug": false,
                        "newestOnTop": false,
                        "progressBar": true,
                        "positionClass": "toast-top-right",
                        "preventDuplicates": false,
                        "onclick": null,
                        "showDuration": "300",
                        "hideDuration": "1000",
                        "timeOut": "4000",
                        "extendedTimeOut": "1000",
                        "showEasing": "swing",
                        "hideEasing": "linear",
                        "showMethod": "fadeIn",
                        "hideMethod": "fadeOut"
                    });
            }*/


        },
    });
}

function showData() {
    //event.preventDefault();
    var numberPage = $(this).attr('data-item');
    getDataProducts(numberPage)
}

function getDataProducts($numberPage) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    var name = $('#full_name').val();

    $.get('/dashboard/get/data/coupons/'+$numberPage, {
        name:name,
    }, function(data) {
        if ( data.data.length == 0 )
        {
            renderDataCouponsEmpty(data);
        } else {
            renderDataCoupons(data);
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

function renderDataCouponsEmpty(data) {
    var dataAccounting = data.data;
    var pagination = data.pagination;
    console.log(dataAccounting);
    console.log(pagination);

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' cupones');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataCoupons(data, activeColumns) {
    var dataQuotes = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' productos.');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    for (let j = 0; j < dataQuotes.length ; j++) {
        renderDataTable(dataQuotes[j], activeColumns);
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

function renderDataTable(data, activeColumns) {
    var clone = document.querySelector('#item-table').content.cloneNode(true);

    // Llenar los datos en cada celda según el objeto de datos
    clone.querySelector("[data-codigo]").innerHTML = data.id;
    clone.querySelector("[data-nombre]").innerHTML = data.nombre;
    clone.querySelector("[data-descripcion]").innerHTML = data.descripcion;
    clone.querySelector("[data-precio]").innerHTML = data.precio;
    clone.querySelector("[data-porcentaje]").innerHTML = data.porcentaje;
    clone.querySelector("[data-tipo]").innerHTML = data.estado;
    clone.querySelector("[data-especial]").innerHTML = data.typeText;
    clone.querySelector("[data-estado]").innerHTML = data.specialText;

    // Configurar enlaces y botones según los permisos y datos
    if (hasPerm('cupones.editar')) {
        let url = document.location.origin + '/dashboard/coupons/' + data.id+'/edit/';
        clone.querySelector("[data-editar_coupon]").setAttribute("href", url);
    } else {
        let element = clone.querySelector("[data-editar_coupon]");
        if (element) {
            element.style.display = 'none';
        }
    }

    let button = clone.querySelector("[data-cambiar_estado]");

    // Mostrar / ocultar según permiso
    if (hasPerm('cupones.cambiar_estado')) {
        // Obtener estado
        let state = data.status; // "active" o "inactive"

        // Setear atributos
        button.setAttribute("data-coupon_id", data.id);
        button.setAttribute("data-description", data.nombre);
        button.setAttribute("data-state", state);

        // Ajustar clases e ícono según estado
        if (state === "active") {
            button.classList.add("btn-outline-success");
            button.classList.remove("btn-outline-danger");
            button.innerHTML = '<i class="fas fa-bell"></i>'; // Ícono campana
        } else {
            button.classList.add("btn-outline-danger");
            button.classList.remove("btn-outline-success");
            button.innerHTML = '<i class="fas fa-bell-slash"></i>'; // Ícono campana slash
        }

        button.style.display = ''; // aseguramos que sea visible
    } else {
        // Sin permiso → eliminar o esconder
        button.remove();
        // o: button.style.display = 'none';
    }

    // Agregar la fila clonada al cuerpo de la tabla
    $("#body-table").append(clone);

    // Inicializar tooltips si es necesario
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
