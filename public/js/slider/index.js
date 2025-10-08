$(document).ready(function () {
    //console.log($permissions);
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });
    $('#dynamic-table').DataTable( {
        ajax: {
            url: "/dashboard/get/all/sliders",
            dataSrc: 'data'
        },
        bAutoWidth: false,
        "aoColumns": [
            { data: 'id' },
            { data: 'image' },
            { data: 'order' },
            { data: null,
                title: 'Tamaño',
                wrap: true,
                "render": function (item)
                {
                    var text = '';

                    if ( item.size == 's' )
                    {
                        text = text + "Pequeño";
                    } else {
                        text = text + "Grande";
                    }

                    return text;

                }},
            { data: 'link' },
            { data: null,
                title: 'Estado',
                wrap: true,
                "render": function (item)
                {
                    var text = '';

                    if ( item.active == 1 )
                    {
                        text = text + "ACTIVO";
                    } else {
                        text = text + "INACTIVO";
                    }

                    return text;

                }
            },
            { data: null,
                title: 'Acciones',
                wrap: true,
                "render": function (item)
                {
                    var text = '';
                    text = text + ' <button data-ver_image="'+item.id+'" data-image="'+item.image+'" '+
                        ' class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ver imagen "><i class="fas fa-images"></i></button>';

                    if (hasPerm('sliders.editar')) {
                        text = text + ' <a href="'+document.location.origin+ '/dashboard/editar/imagen/slider/'+item.id+
                            '" class="btn btn-outline-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Editar imagen"><i class="fa fa-pen"></i></a>';
                    }

                    if (hasPerm('sliders.eliminar')) {
                        text = text + ' <button data-delete="'+item.id+'" data-description="'+item.image+'" '+
                            ' class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar "><i class="fa fa-trash"></i></button>';
                    }

                    if (hasPerm('sliders.cambiar_estado')) {
                        text = text + ' <button data-id="'+item.id+'" data-state="'+item.active+'" data-description="'+item.image+'" '+
                            ' class="btn btn-outline-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Desactivar/Activar "><i class="far fa-bell-slash"></i></button>';
                    }

                    return text;

                } },

        ],
        "aaSorting": [],

        select: {
            style: 'single'
        },
        language: {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "search": "Buscar:",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad",
                "collection": "Colección",
                "colvisRestore": "Restaurar visibilidad",
                "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
                "copySuccess": {
                    "1": "Copiada 1 fila al portapapeles",
                    "_": "Copiadas %d fila al portapapeles"
                },
                "copyTitle": "Copiar al portapapeles",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Mostrar todas las filas",
                    "1": "Mostrar 1 fila",
                    "_": "Mostrar %d filas"
                },
                "pdf": "PDF",
                "print": "Imprimir"
            },
            "autoFill": {
                "cancel": "Cancelar",
                "fill": "Rellene todas las celdas con <i>%d<\/i>",
                "fillHorizontal": "Rellenar celdas horizontalmente",
                "fillVertical": "Rellenar celdas verticalmentemente"
            },
            "decimal": ",",
            "searchBuilder": {
                "add": "Añadir condición",
                "button": {
                    "0": "Constructor de búsqueda",
                    "_": "Constructor de búsqueda (%d)"
                },
                "clearAll": "Borrar todo",
                "condition": "Condición",
                "conditions": {
                    "date": {
                        "after": "Despues",
                        "before": "Antes",
                        "between": "Entre",
                        "empty": "Vacío",
                        "equals": "Igual a",
                        "not": "No",
                        "notBetween": "No entre",
                        "notEmpty": "No Vacio"
                    },
                    "number": {
                        "between": "Entre",
                        "empty": "Vacio",
                        "equals": "Igual a",
                        "gt": "Mayor a",
                        "gte": "Mayor o igual a",
                        "lt": "Menor que",
                        "lte": "Menor o igual que",
                        "not": "No",
                        "notBetween": "No entre",
                        "notEmpty": "No vacío"
                    },
                    "string": {
                        "contains": "Contiene",
                        "empty": "Vacío",
                        "endsWith": "Termina en",
                        "equals": "Igual a",
                        "not": "No",
                        "notEmpty": "No Vacio",
                        "startsWith": "Empieza con"
                    }
                },
                "data": "Data",
                "deleteTitle": "Eliminar regla de filtrado",
                "leftTitle": "Criterios anulados",
                "logicAnd": "Y",
                "logicOr": "O",
                "rightTitle": "Criterios de sangría",
                "title": {
                    "0": "Constructor de búsqueda",
                    "_": "Constructor de búsqueda (%d)"
                },
                "value": "Valor"
            },
            "searchPanes": {
                "clearMessage": "Borrar todo",
                "collapse": {
                    "0": "Paneles de búsqueda",
                    "_": "Paneles de búsqueda (%d)"
                },
                "count": "{total}",
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Sin paneles de búsqueda",
                "loadMessage": "Cargando paneles de búsqueda",
                "title": "Filtros Activos - %d"
            },
            "select": {
                "1": "%d fila seleccionada",
                "_": "%d filas seleccionadas",
                "cells": {
                    "1": "1 celda seleccionada",
                    "_": "$d celdas seleccionadas"
                },
                "columns": {
                    "1": "1 columna seleccionada",
                    "_": "%d columnas seleccionadas"
                }
            },
            "thousands": ".",
            "datetime": {
                "previous": "Anterior",
                "next": "Proximo",
                "hours": "Horas"
            }
        }

    } );

    $formDelete = $('#formDelete');
    //$formDelete.on('submit', destroyContract);
    $('#btn-submit').on('click', destroyImages);
    $modalDelete = $('#modalDelete');
    $(document).on('click', '[data-delete]', openModalDelete);

    $(document).on('click', '[data-image]', showImage);
    $modalImage = $('#modalImage');

    $(document).on("click", "[data-state]", function() {
        var button = $(this);
        var itemId = button.data("id");
        var currentState = button.data("state");
        var newState = currentState == 1 ? 0 : 1;
        var message = currentState == 1 ? "¿Estás seguro de desactivar este elemento?" : "¿Estás seguro de activar este elemento?";

        $.confirm({
            title: 'Confirmación',
            content: message,
            type: 'orange',
            buttons: {
                confirmar: {
                    text: 'Sí',
                    btnClass: 'btn-success',
                    action: function() {
                        $.ajax({
                            url: '/dashboard/update-state/' + itemId,
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                state: newState
                            },
                            success: function(response) {
                                if (response.success) {
                                    $.alert({
                                        title: 'Éxito',
                                        content: response.message,
                                        type: 'green',
                                        buttons: {
                                            ok: function() {
                                                location.reload();
                                            }
                                        }
                                    });
                                } else {
                                    $.alert("Error al actualizar el estado.");
                                }
                            },
                            error: function() {
                                $.alert("Hubo un problema con la solicitud.");
                            }
                        });
                    }
                },
                cancelar: {
                    text: 'No',
                    btnClass: 'btn-danger',
                    action: function() {}
                }
            }
        });
    });
});

var $formDelete;
var $modalDelete;
var $modalImage;



function showImage() {
    var image = $(this).data('image');
    let url_image = document.location.origin + '/images/slider/' + image;
    $('#image-document').attr('src', url_image);
    $modalImage.modal('show');
}

function openModalDelete() {
    var image_id = $(this).data('delete');
    var description = $(this).data('description');

    $modalDelete.find('[id=image_id]').val(image_id);
    $modalDelete.find('[id=description]').html(description);

    $modalDelete.modal('show');
}

function destroyImages() {
    event.preventDefault();
    // Obtener la URL
    $("#btn-submit").attr("disabled", true);
    var formulario = $('#formDelete')[0];
    var form = new FormData(formulario);
    var deleteUrl = $formDelete.data('url');
    $.ajax({
        url: deleteUrl,
        method: 'POST',
        data: form,
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
                    "timeOut": "1000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                });
            $modalDelete.modal('hide');
            setTimeout( function () {
                $("#btn-submit").attr("disabled", false);
                location.reload();
            }, 1000 )
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

            $("#btn-submit").attr("disabled", false);

        },
    });
}