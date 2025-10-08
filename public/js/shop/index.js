$(document).ready(function () {
    /*$permissions = JSON.parse($('#permissions').val());*/
    //console.log($permissions);

    // Función para obtener y mostrar los datos iniciales
    getDataShops(1);

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
    $("#btn-submit").on('click', changeState);
    $modalDelete = $('#modalDelete');
    $(document).on('click', '[data-delete]', openModalDisable);


    $('#department').select2({
        placeholder: "Selecione departamento",
        allowClear: true,
        width: '100%',   // Asegura que el ancho sea el correcto
        dropdownAutoWidth: true,
        theme: 'bootstrap4',  // Especifica el tema aquí
        dropdownParent: $('#department').parent()
    });

    $('#province').select2({
        placeholder: "Selecione provincia",
        allowClear: true,
        width: '100%',   // Asegura que el ancho sea el correcto
        dropdownAutoWidth: true,
        theme: 'bootstrap4',  // Especifica el tema aquí
        dropdownParent: $('#province').parent()
    });

    $('#district').select2({
        placeholder: "Selecione distrito",
        allowClear: true,
        width: '100%',   // Asegura que el ancho sea el correcto
        dropdownAutoWidth: true,
        theme: 'bootstrap4',  // Especifica el tema aquí
        dropdownParent: $('#district').parent()
    });

    $('#status').select2({
        placeholder: "Selecione estado",
        allowClear: true,
        width: '100%',   // Asegura que el ancho sea el correcto
        dropdownAutoWidth: true,
        theme: 'bootstrap4',  // Especifica el tema aquí
        dropdownParent: $('#status').parent()
    });


    $('#department').on('change', function() {
        let departmentId = $(this).val();

        if (departmentId) {
            $.ajax({
                url: `/provincias/${departmentId}`,
                type: 'GET',
                dataType: 'json',
                success: function(provinces) {
                    $('#province').empty().append('<option value="">Seleccionar</option>');
                    $('#district').empty().append('<option value="">Seleccionar</option>');

                    $.each(provinces, function(index, province) {
                        $('#province').append(`<option value="${province.id}">${province.name}</option>`);
                    });
                }
            });
        } else {
            $('#province').empty().append('<option value="">Seleccionar</option>');
            $('#district').empty().append('<option value="">Seleccionar</option>');
        }
    });

    // Al seleccionar una provincia, cargar los distritos
    $('#province').on('change', function() {
        let provinceId = $(this).val();

        if (provinceId) {
            $.ajax({
                url: `/distritos/${provinceId}`,
                type: 'GET',
                dataType: 'json',
                success: function(districts) {
                    $('#district').empty().append('<option value="">Seleccionar</option>');

                    $.each(districts, function(index, district) {
                        $('#district').append(`<option value="${district.id}">${district.name}</option>`);
                    });
                }
            });
        } else {
            $('#district').empty().append('<option value="">Seleccionar</option>');
        }
    });
    $(document).on('click', '[data-deshabilitar]', function () {

        let id = $(this).data('id');
        let description = $(this).data('description');
        let state = $(this).data('state');

        let newState = state === 'active' ? 'inactive' : 'active';
        let actionText = state === 'active' ? 'inactivar' : 'activar';

        $.confirm({
            title: 'Confirmación',
            content: `¿Está seguro de ${actionText} la tienda <b>${description}</b>?`,
            type: 'orange',
            buttons: {
                confirmar: {
                    text: 'Sí, confirmar',
                    btnClass: 'btn-orange',
                    action: function () {
                        changeShopState(id, newState);
                    }
                },
                cancelar: {
                    text: 'Cancelar',
                    action: function () {
                        // No hacer nada
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

function changeShopState(id, newState) {
    $.ajax({
        url: `/dashboard/shop/${id}/cambiar-estado`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            state: newState
        },
        success: function (response) {
            $.alert({
                title: 'Éxito',
                content: response.message,
                type: 'green',
                buttons: {
                    ok: function () {
                        location.reload(); // Recargar la página para actualizar la lista
                    }
                }
            });
        },
        error: function () {
            $.alert({
                title: 'Error',
                content: 'Ocurrió un error, intente nuevamente.',
                type: 'red'
            });
        }
    });
}

function showDataSearch() {
    getDataShops(1);
}

function openModalDisable() {
    var category_id = $(this).data('delete');
    var description = $(this).data('description');
    var state = $(this).data('state');
    var textState = "";
    if ( state == 1 )
    {
        textState = 'INACTIVO';
    } else {
        textState = 'ACTIVO'
    }

    $modalDelete.find('[id=category_id]').val(category_id);
    $modalDelete.find('[id=title]').html(textState);
    $modalDelete.find('[id=descriptionDelete]').html(description);

    $modalDelete.modal('show');
}

function changeState() {
    event.preventDefault();
    // Obtener la URL
    var deleteUrl = $formDelete.data('url');

    $.ajax({
        url: deleteUrl,
        method: 'POST',
        data: new FormData($("#formDelete")[0]),
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
                getDataProducts(1);
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
    getDataShops(numberPage)
}

function getDataShops($numberPage) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    var name = $('#name').val();
    var department = $('#department').val();
    var province = $('#province').val();
    var district = $('#district').val();
    var status = $('#status').val();

    $.get('/dashboard/get/data/shops/'+$numberPage, {
        name:name,
        department:department,
        province:province,
        district:district,
        status:status,
    }, function(data) {
        if ( data.data.length == 0 )
        {
            renderDataShopsEmpty(data);
        } else {
            renderDataShops(data);
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

function renderDataShopsEmpty(data) {
    var dataAccounting = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' tiendas');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataShops(data, activeColumns) {
    var dataQuotes = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' tiendas.');
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
    clone.querySelector("[data-id]").innerHTML = data.id;
    clone.querySelector("[data-name]").innerHTML = data.name;
    clone.querySelector("[data-owner]").innerHTML = data.owner;
    clone.querySelector("[data-phone]").innerHTML = data.phone;
    clone.querySelector("[data-email]").innerHTML = data.email;
    clone.querySelector("[data-address]").innerHTML = data.address;
    clone.querySelector("[data-district]").innerHTML = data.district;
    clone.querySelector("[data-province]").innerHTML = data.province;
    clone.querySelector("[data-department]").innerHTML = data.department;
    clone.querySelector("[data-text_status]").innerHTML = data.statusText;

    // Configurar enlaces y botones según los permisos y datos
    if (hasPerm('locales.editar_tiendas')) {
        let url = document.location.origin + '/dashboard/modificar/tienda/' + data.id;
        clone.querySelector("[data-editar_shop]").setAttribute("href", url);
    } else {
        let element = clone.querySelector("[data-editar_shop]");
        if (element) {
            element.style.display = 'none';
        }
    }

    if (hasPerm('locales.cambiar_estado_tienda')) {
        clone.querySelector("[data-deshabilitar]").setAttribute("data-id", data.id);
        clone.querySelector("[data-deshabilitar]").setAttribute("data-description", data.name);
        clone.querySelector("[data-deshabilitar]").setAttribute("data-state", data.status);
    } else {
        let element = clone.querySelector("[data-deshabilitar]");
        if (element) {
            element.style.display = 'none';
        }
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
