$(document).ready(function () {
    /*$permissions = JSON.parse($('#permissions').val());*/
    //console.log($permissions);

    $('.custom-control-input').change(function() {
        updateData();
    });


    // Variable para almacenar los nombres clave de los checkboxes activos
    var activeColumns = getActiveColumns();

    // Función para obtener y mostrar los datos iniciales
    function initData() {
        activeColumns = getActiveColumns();
        console.log(activeColumns);
        getDataProducts(1, activeColumns);
    }

    // Función para obtener y mostrar los datos con los checkboxes actuales
    function updateData() {
        activeColumns = getActiveColumns();
        getDataProducts(1, activeColumns);
    }

    // Función para obtener y mostrar los datos con los checkboxes activos y criterios de búsqueda
    function showDataSearch() {
        activeColumns = getActiveColumns();
        getDataProducts(1, activeColumns);
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

    $('#btn-export').on('click', exportExcel);

    $modalImage = $('#modalImage');

    $formDelete = $('#formDelete');
    //$formDelete.on('submit', disableMaterial);
    $modalDelete = $('#modalDelete');
    $(document).on('click', '[data-cambiar_estado]', openModalDisable);

    $(document).on('click', '[data-image]', showImage);

    $(document).on('click', '[data-eliminar]', openModalDelete);

    $(document).on('click', '[data-desactivar]', desactivarProducto);
});

var $formDelete;
var $modalDelete;
var $modalImage;
var $permissions;

function desactivarProducto() {
    let idProduct = $(this).data('product_id');
    let description = $(this).data('description');

    $.confirm({
        title: 'Desactivación de productos',
        content: "¿Por cuánto tiempo desea desactivar el producto <strong>" + description + "</strong>?",
        theme: 'modern',
        boxWidth: '400px',
        useBootstrap: false,
        type: 'red',
        buttons: {
            '1hora': {
                text: '1 Hora',
                btnClass: 'btn-blue',
                action: function () {
                    confirmarDesactivacion(idProduct, description, 1);
                }
            },
            '2horas': {
                text: '2 Horas',
                btnClass: 'btn-blue',
                action: function () {
                    confirmarDesactivacion(idProduct, description, 2);
                }
            },
            '12horas': {
                text: '12 Horas',
                btnClass: 'btn-blue',
                action: function () {
                    confirmarDesactivacion(idProduct, description, 12);
                }
            },
            '24horas': {
                text: '24 Horas',
                btnClass: 'btn-blue',
                action: function () {
                    confirmarDesactivacion(idProduct, description, 24);
                }
            },
            'siempre': {
                text: 'SIEMPRE',
                btnClass: 'btn-dark',
                action: function () {
                    confirmarDesactivacion(idProduct, description, 'siempre');
                }
            },
            cancelar: {
                text: 'Cancelar',
                btnClass: 'btn-red',
                action: function () {
                    // No hacer nada, solo cerrar el modal
                }
            }
        }
    });
}

// Función para confirmar la desactivación antes de enviar la solicitud AJAX
function confirmarDesactivacion(idProduct, description, time) {
    $.confirm({
        title: 'Confirmación',
        content: "¿Está seguro de desactivar el producto <strong>" + description + "</strong> por " + (time === 'siempre' ? 'siempre' : time + ' horas') + "?",
        type: 'orange',
        buttons: {
            confirmar: {
                text: 'Sí, desactivar',
                btnClass: 'btn-green',
                action: function () {
                    enviarDesactivacion(idProduct, time);
                }
            },
            cancelar: {
                text: 'Cancelar',
                btnClass: 'btn-red',
                action: function () {
                    // No hacer nada, solo cerrar el modal
                }
            }
        }
    });
}

// Función para enviar la solicitud AJAX
function enviarDesactivacion(idProduct, time) {
    $.ajax({
        url: '/dashboard/desactivar/producto/' + idProduct,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            id_product: idProduct,
            time: time
        }),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            $.alert({
                title: 'Éxito',
                content: response.message,
                onClose: function () {
                    location.reload();
                }
            });
        },
        error: function (xhr, status, error) {
            $.alert('Hubo un problema al desactivar el producto.');
        }
    });
}

function openModalDelete() {

    let idProduct = $(this).data('product_id');
    let description = $(this).data('description');
    $.confirm({
        title: 'Eliminación de productos',
        content: "¿Está seguro de eliminar el producto "+description+"?",
        theme: 'modern', // Puedes probar otros temas como 'bootstrap', 'modern', 'dark'
        boxWidth: '350px', // Ajusta el ancho de la ventana
        useBootstrap: false, // Usa estilos independientes de Bootstrap
        type: 'red',
        buttons: {
            confirmar: {
                text: 'Confirmar',
                btnClass: 'btn-green',
                action: function () {
                    // Hacer una llamada AJAX para enviar los datos al backend
                    $.ajax({
                        url: '/dashboard/destroy/product/'+idProduct,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(idProduct),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Incluye el token CSRF en los encabezados
                        },
                        success: function (response) {
                            $.alert({
                                title: 'Éxito',
                                content: response.message,
                                onClose: function () {
                                    location.reload(); // Redirigir a la ruta del carrito
                                }
                            });
                        },
                        error: function (xhr, status, error) {
                            $.alert('Hubo un problema al eliminar el producto.');
                        }
                    });
                }
            },
            volver: {
                text: 'Volver',
                btnClass: 'btn-red',
                action: function () {
                    // Cerrar el modal
                }
            }
        }
    });
}
// Función para obtener los nombres clave de los checkboxes activos
function getActiveColumns() {
    var activeColumns = [];
    $('input[type="checkbox"]:checked').each(function() {
        activeColumns.push($(this).data('column'));
    });
    return activeColumns;
}

function openModalDisable() {
    var product_id = $(this).data('product_id');
    var description = $(this).data('description');
    var state = $(this).data('state');

    var title = (state === 'activo')
        ? "Cambio de estado a <strong>INACTIVO</strong>"
        : "Cambio de estado a <strong>ACTIVO</strong>";

    $.confirm({
        title: title,
        content: "¿Está seguro de cambiar el estado del producto <strong>" + description + "</strong>?",
        type: 'orange',
        buttons: {
            confirmar: {
                text: 'Sí, cambiar',
                btnClass: 'btn-green',
                action: function () {
                    cambiarEstadoProducto(product_id);
                }
            },
            cancelar: {
                text: 'Cancelar',
                btnClass: 'btn-red',
                action: function () {
                    // Solo cierra el modal
                }
            }
        }
    });
}

// Función AJAX para cambiar el estado del producto
function cambiarEstadoProducto(product_id) {
    var deleteUrl = $formDelete.data('url'); // Asegúrate de que esta variable tiene la URL correcta

    $.ajax({
        url: deleteUrl,
        method: 'POST',
        data: JSON.stringify({
            product_id: product_id,
        }),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Importante para Laravel
        },
        contentType: "application/json", // Especificamos que estamos enviando JSON
        success: function (data) {
            toastr.success(data.message, 'Éxito', {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "2000"
            });

            setTimeout(function () {
                location.reload();
            }, 2000);
        },
        error: function (data) {
            if (data.responseJSON.message && !data.responseJSON.errors) {
                toastr.error(data.responseJSON.message, 'Error');
            }
            for (var property in data.responseJSON.errors) {
                toastr.error(data.responseJSON.errors[property], 'Error');
            }
        }
    });
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
        },
    });
}

function showImage() {
    var path = $(this).data('src');
    $('#image-document').attr('src', path);
    $modalImage.modal('show');
}

function exportExcel() {
    var start  = $('#start').val();
    var end  = $('#end').val();
    var startDate   = moment(start, "DD/MM/YYYY");
    var endDate     = moment(end, "DD/MM/YYYY");

    console.log(start);
    console.log(end);
    console.log(startDate);
    console.log(endDate);

    if ( start == '' || end == '' )
    {
        console.log('Sin fechas');
        $.confirm({
            icon: 'fas fa-file-excel',
            theme: 'modern',
            closeIcon: true,
            animation: 'zoom',
            type: 'green',
            title: 'No especificó fechas',
            content: 'Si no hay fechas se descargará todos los ingresos',
            buttons: {
                confirm: {
                    text: 'DESCARGAR',
                    action: function (e) {
                        //$.alert('Descargado igual');
                        console.log(start);
                        console.log(end);

                        var query = {
                            start: start,
                            end: end
                        };

                        $.alert('Descargando archivo ...');

                        var url = "/dashboard/exportar/reporte/egresos/proveedores/?" + $.param(query);

                        window.location = url;

                    },
                },
                cancel: {
                    text: 'CANCELAR',
                    action: function (e) {
                        $.alert("Exportación cancelada.");
                    },
                },
            },
        });
    } else {
        console.log('Con fechas');
        console.log(JSON.stringify(start));
        console.log(JSON.stringify(end));

        var query = {
            start: start,
            end: end
        };

        toastr.success('Descargando archivo ...', 'Éxito',
            {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "2000",
                "timeOut": "2000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            });

        var url = "/dashboard/exportar/reporte/egresos/proveedores/?" + $.param(query);

        window.location = url;

    }

}

/*function showDataSearch() {
    getDataMaterials(1)
}*/

function showData() {
    //event.preventDefault();
    var numberPage = $(this).attr('data-item');
    console.log(numberPage);
    var activeColumns = getActiveColumns();
    getDataProducts(numberPage, activeColumns)
}

function getDataProducts($numberPage, $activeColumns) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    var full_name = $('#full_name').val();
    var category = $('#category').val();
    var code = $('#code').val();

    $.get('/dashboard/get/data/products/'+$numberPage, {
        full_name:full_name,
        category: category,
        code:code,
    }, function(data) {
        if ( data.data.length == 0 )
        {
            renderDataProductsEmpty(data);
        } else {
            renderDataProducts(data, $activeColumns);
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

function renderDataProductsEmpty(data) {
    var dataAccounting = data.data;
    var pagination = data.pagination;
    console.log(dataAccounting);
    console.log(pagination);

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' productos');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataProducts(data, activeColumns) {
    var dataQuotes = data.data;
    var pagination = data.pagination;

    $("#header-table").html('');
    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' productos.');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableHeader(activeColumns);

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

function renderDataTableHeader(activeColumns) {
    var cloneHeader = document.querySelector('#item-header').content.cloneNode(true);
    var headerRow = cloneHeader.querySelector('tr');

    headerRow.querySelectorAll('[data-column]').forEach(function(column) {
        var columnName = column.dataset.column;
        if (activeColumns.includes(columnName)) {
            column.style.display = 'table-cell';
        } else {
            column.style.display = 'none';
        }
    });

    $("#header-table").append(cloneHeader);

}

function renderDataTable(data, activeColumns) {
    var clone = document.querySelector('#item-table').content.cloneNode(true);

    // Iterar sobre cada columna en el cuerpo de la tabla y mostrar u ocultar según los checkboxes activos
    clone.querySelectorAll('[data-column]').forEach(function(column) {
        var columnName = column.dataset.column;
        if (activeColumns.includes(columnName)) {
            column.style.display = 'table-cell';
        } else {
            column.style.display = 'none';
        }
    });

    // Llenar los datos en cada celda según el objeto de datos
    clone.querySelector("[data-codigo]").innerHTML = data.codigo;
    clone.querySelector("[data-nombre]").innerHTML = data.nombre;
    clone.querySelector("[data-descripcion]").innerHTML = data.descripcion;
    clone.querySelector("[data-precio]").innerHTML = data.precio;
    clone.querySelector("[data-categoria]").innerHTML = data.categoria;
    clone.querySelector("[data-ingredientes]").innerHTML = data.ingredientes;
    clone.querySelector("[data-estado]").innerHTML = data.estado;
    clone.querySelector("[data-cambiar_estado]").setAttribute("data-cambiar_estado", data.state);
    clone.querySelector("[data-cambiar_estado]").setAttribute("data-product_id", data.id);
    clone.querySelector("[data-cambiar_estado]").setAttribute("data-state", data.textEstado);
    clone.querySelector("[data-cambiar_estado]").setAttribute("data-description", data.nombre);

    clone.querySelectorAll("[data-desactivar]").forEach(btn => {
        btn.setAttribute("data-product_id", data.id);
        btn.setAttribute("data-description", data.nombre);
    });

    clone.querySelector("[data-eliminar]").setAttribute("data-product_id", data.id);
    clone.querySelector("[data-eliminar]").setAttribute("data-description", data.nombre);

    let url_image = document.location.origin + '/images/products/' + data.image;
    clone.querySelector("[data-ver_imagen]").setAttribute("data-src", url_image);
    clone.querySelector("[data-ver_imagen]").setAttribute("data-image", data.id);

    clone.querySelector("[data-visibility_price_real]").innerHTML = data.visibility_price_real;

    // Configurar enlaces y botones según los permisos y datos
    /*if ($.inArray('update_material', $permissions) !== -1) {*/
        let url = document.location.origin + '/dashboard/editar/producto/' + data.id;
        clone.querySelector("[data-editar_product]").setAttribute("href", url);
    /*} else {
        let element = clone.querySelector("[data-editar_material]");
        if (element) {
            element.style.display = 'none';
        }
    }*/

    /*if ($.inArray('enable_material', $permissions) !== -1) {*/
        /*clone.querySelector("[data-deshabilitar]").setAttribute("data-delete", data.id);
        clone.querySelector("[data-deshabilitar]").setAttribute("data-description", data.nombre);*/
    /*} else {
        let element = clone.querySelector("[data-deshabilitar]");
        if (element) {
            element.style.display = 'none';
        }
    }*/

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
