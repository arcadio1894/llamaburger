$(document).ready(function () {
    /*$permissions = JSON.parse($('#permissions').val());*/
    //console.log($permissions);

    // Función para obtener y mostrar los datos iniciales
    getDataRewards(1);

    $(document).on('click', '[data-item]', showData);

    $("#btn-search").on('click', showDataSearch);

    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    $(document).on('click', '[data-eliminar]', function () {

        let id = $(this).data('id');
        let description = $(this).data('title');

        let actionText = 'eliminar';

        $.confirm({
            title: 'Confirmación',
            content: `¿Está seguro de ${actionText} el hito <b>${description}</b>?`,
            type: 'red',
            buttons: {
                confirmar: {
                    text: 'Sí, confirmar',
                    btnClass: 'btn-danger',
                    action: function () {
                        deleteMilestone(id);
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

function deleteMilestone(id) {
    $.ajax({
        url: `/dashboard/milestones/${id}/eliminar`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success: function (response) {
            $.alert({
                title: 'Éxito',
                content: response.message,
                type: 'green',
                buttons: {
                    ok: function () {
                        getDataRewards(1); // Recargar la página para actualizar la lista
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
    getDataRewards(1);
}

function showData() {
    //event.preventDefault();
    var numberPage = $(this).attr('data-item');
    getDataRewards(numberPage)
}

function getDataRewards($numberPage) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    $.get('/dashboard/get/data/milestones/'+$numberPage, function(data) {
        if ( data.data.length == 0 )
        {
            renderDataRewardsEmpty(data);
        } else {
            renderDataRewards(data);
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

function renderDataRewardsEmpty(data) {
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' hitos');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataRewards(data, activeColumns) {
    var dataQuotes = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' hitos.');
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

function renderDataTable(data) {
    var clone = document.querySelector('#item-table').content.cloneNode(true);

    // Llenar los datos en cada celda según el objeto de datos
    clone.querySelector("[data-id]").innerHTML = data.id;
    clone.querySelector("[data-title]").innerHTML = data.title;
    clone.querySelector("[data-description]").innerHTML = data.description;
    clone.querySelector("[data-flames]").innerHTML = data.flames;

    // Configurar enlaces y botones según los permisos y datos
    if (hasPerm('rewards.editar')) {
        let url = document.location.origin + '/dashboard/modificar/hito/' + data.id;
        clone.querySelector("[data-editar_milestone]").setAttribute("href", url);
    } else {
        let element = clone.querySelector("[data-editar_milestone]");
        if (element) {
            element.style.display = 'none';
        }
    }

    if (hasPerm('rewards.eliminar')) {
        clone.querySelector("[data-eliminar]").setAttribute("data-id", data.id);
        clone.querySelector("[data-eliminar]").setAttribute("data-title", data.title);
    } else {
        let element = clone.querySelector("[data-eliminar]");
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
