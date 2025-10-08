var $modalOpen;
var $modalClose;
var $modalIncome;
var $modalExpense;
var $modalRegularize;

$(document).ready(function () {
    getDataMovements(1);

    $(document).on("click", '[id=btn-openCashRegister]', openCashRegister);

    $modalOpen = $("#modalOpen");

    $("#btn_open").on("click", openCaja);

    $(document).on("click", '[id=btn-closeCashRegister]', closeCashRegister);

    $modalClose = $("#modalClose");

    $("#btn_close").on("click", closeCaja);

    $(document).on("click", '[id=btn-incomeCashRegister]', incomeCashRegister);

    $modalIncome = $("#modalIncome");

    $("#btn_ingreso").on("click", ingresoCaja);

    $(document).on("click", '[id=btn-expenseCashRegister]', expenseCashRegister);

    $modalExpense = $("#modalExpense");

    $("#btn_egreso").on("click", egresoCaja);

    $(document).on("click", '[data-regularizar]', regularizeCashRegister);

    $modalRegularize = $("#modalRegularize");

    $("#btn_regularizar").on("click", regularizarCaja);

    $(document).on('click', '[data-item]', showData);

});

function regularizarCaja() {
    event.preventDefault();

    // Obtener otros datos del formulario
    let type = $('#active_regularize').val();
    let amount = $('#regularize_amount').val();
    let cash_movement_id = $('#cash_movement_id').val();

    // Armar el objeto con los datos del paquete
    let packageData = {
        cash_movement_id: cash_movement_id,
        type: type,
        amount: amount
    };
    // Enviar los datos al backend mediante AJAX
    $.ajax({
        url: $("#formRegularize").data('url'),
        method: 'POST',
        data: JSON.stringify(packageData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Mensaje: ', response);
            toastr.success(response.message, 'Éxito',
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
            $("#balance_total").val(response.balance_total);
            $("#valueBalanceTotal").html("S/."+response.balance_total);

            setTimeout( function () {
                $modalRegularize.modal('hide');
                getDataMovements(1);
            }, 1000 )

        },
        error: function(data) {
            console.error('Error al: ', data);
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

        }
    });
}

function regularizeCashRegister() {
    var cash_movement_id = $(this).data('id');

    var active = $("#active").val();

    $modalRegularize.find('[id=cash_movement_id]').val(cash_movement_id);
    $modalRegularize.find('[id=active_regularize]').val(active);

    $modalRegularize.modal('show');
}

function egresoCaja() {
    event.preventDefault();

    // Obtener otros datos del formulario
    let type = $('#active_expense').val();
    let balance_total = $('#balance_total_expense').val();
    let amount = $('#expense_amount').val();
    let description = $('#expense_description').val();

    // Armar el objeto con los datos del paquete
    let packageData = {
        type: type,
        balance_total: balance_total,
        amount: amount,
        description: description
    };
    // Enviar los datos al backend mediante AJAX
    $.ajax({
        url: $("#formExpense").data('url'),
        method: 'POST',
        data: JSON.stringify(packageData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Mensaje: ', response);
            toastr.success(response.message, 'Éxito',
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
            $("#balance_total").val(response.balance_total);
            $("#valueBalanceTotal").html("S/."+response.balance_total);

            setTimeout( function () {
                $modalExpense.modal('hide');
                getDataMovements(1);
            }, 1000 )

        },
        error: function(data) {
            console.error('Error al: ', data);
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

        }
    });
}

function expenseCashRegister() {
    var balance_total = $("#balance_total").val();
    var active = $("#active").val();

    $modalExpense.find('[id=balance_total_expense]').val(balance_total);
    $modalExpense.find('[id=active_expense]').val(active);

    $modalExpense.modal('show');
}

function ingresoCaja() {
    event.preventDefault();

    // Obtener otros datos del formulario
    let type = $('#active_income').val();
    let balance_total = $('#balance_total_income').val();
    let amount = $('#income_amount').val();
    let description = $('#income_description').val();

    // Armar el objeto con los datos del paquete
    let packageData = {
        type: type,
        balance_total: balance_total,
        amount: amount,
        description: description
    };
    // Enviar los datos al backend mediante AJAX
    $.ajax({
        url: $("#formIncome").data('url'),
        method: 'POST',
        data: JSON.stringify(packageData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Mensaje: ', response);
            toastr.success(response.message, 'Éxito',
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
            $("#balance_total").val(response.balance_total);
            $("#valueBalanceTotal").html("S/."+response.balance_total);

            setTimeout( function () {
                $modalIncome.modal('hide');
                getDataMovements(1);
            }, 1000 )

        },
        error: function(data) {
            console.error('Error al: ', data);
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

        }
    });
}

function incomeCashRegister() {
    var balance_total = $("#balance_total").val();
    var active = $("#active").val();

    $modalIncome.find('[id=balance_total_income]').val(balance_total);
    $modalIncome.find('[id=active_income]').val(active);

    $modalIncome.modal('show');
}

function closeCashRegister() {
    var balance_total = $("#balance_total").val();
    var active = $("#active").val();

    $modalClose.find('[id=balance_total_close]').val(balance_total);
    $modalClose.find('[id=active_close]').val(active);

    $modalClose.modal('show');
}

function closeCaja() {
    event.preventDefault();

    // Obtener otros datos del formulario
    let type = $('#active_close').val();
    let balance_total = $('#balance_total_close').val();

    // Armar el objeto con los datos del paquete
    let packageData = {
        type: type,
        balance_total: balance_total,
    };
    // Enviar los datos al backend mediante AJAX
    $.ajax({
        url: $("#formClose").data('url'),
        method: 'POST',
        data: JSON.stringify(packageData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Mensaje: ', response);
            toastr.success(response.message, 'Éxito',
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
            $("#balance_total").val(response.balance_total);
            $("#valueBalanceTotal").html("S/."+response.balance_total);
            // Elimina el siguiente elemento después de #label_balance
            $("#label_balance").next().remove();

            // Inserta el nuevo contenido después de #label_balance
            $("#label_balance").after(response.state);
            setTimeout( function () {
                $modalClose.modal('hide');
                // Traer los movimientos
                // Actualizar
                getDataMovements(1);
            }, 1000 )

        },
        error: function(data) {
            console.error('Error al: ', data);
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

        }
    });
}

function openCashRegister() {
    var balance_total = $("#balance_total").val();
    var active = $("#active").val();

    $modalOpen.find('[id=balance_total_open]').val(balance_total);
    $modalOpen.find('[id=active_open]').val(active);

    $modalOpen.modal('show');
}

function openCaja() {
    event.preventDefault();

    // Obtener otros datos del formulario
    let type = $('#active_open').val();
    let balance_total = $('#balance_total_open').val();

    // Armar el objeto con los datos del paquete
    let packageData = {
        type: type,
        balance_total: balance_total,
    };
    // Enviar los datos al backend mediante AJAX
    $.ajax({
        url: $("#formOpen").data('url'),
        method: 'POST',
        data: JSON.stringify(packageData),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Mensaje: ', response);
            toastr.success(response.message, 'Éxito',
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
            $("#balance_total").val(response.balance_total);
            $("#valueBalanceTotal").html("S/."+response.balance_total);
            // Elimina el siguiente elemento después de #label_balance
            $("#label_balance").next().remove();

            // Inserta el nuevo contenido después de #label_balance
            $("#label_balance").after(response.state);
            setTimeout( function () {
                $modalOpen.modal('hide');
                // Traer los movimientos
                // Actualizar
                getDataMovements(1);
            }, 1000 )

        },
        error: function(data) {
            console.error('Error al: ', data);
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

        }
    });
}

function showData() {
    //event.preventDefault();
    var numberPage = $(this).attr('data-item');
    console.log(numberPage);
    getDataMovements(numberPage)
}

function getDataMovements($numberPage) {
    $('[data-toggle="tooltip"]').tooltip('dispose').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    $.get('/dashboard/get/data/movements/V2/'+$numberPage, {
        type:$('#active').val()
    },function(data) {
        if ( data.data.length == 0 )
        {
            renderDataMovementsEmpty(data);
        } else {
            renderDataMovements(data);
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

function renderDataMovementsEmpty(data) {
    var dataAccounting = data.data;
    var pagination = data.pagination;
    console.log(dataAccounting);
    console.log(pagination);

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' movimientos');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    renderDataTableEmpty();
}

function renderDataMovements(data) {
    var dataCombos = data.data;
    var pagination = data.pagination;

    $("#body-table").html('');
    $("#pagination").html('');
    $("#textPagination").html('');
    $("#textPagination").html('Mostrando '+pagination.startRecord+' a '+pagination.endRecord+' de '+pagination.totalFilteredRecords+' movimientos.');
    $('#numberItems').html('');
    $('#numberItems').html(pagination.totalFilteredRecords);

    for (let j = 0; j < dataCombos.length ; j++) {
        renderDataTable(dataCombos[j]);
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
    clone.querySelector("[data-id]").innerHTML = data.id;
    clone.querySelector("[data-type]").innerHTML = data.type;
    clone.querySelector("[data-date]").innerHTML = data.date;
    clone.querySelector("[data-origen]").innerHTML = data.origen;
    clone.querySelector("[data-amount]").innerHTML = data.amount;
    clone.querySelector("[data-description]").innerHTML = data.description;

    // Verificar si existe un elemento <tr> dentro del clon antes de agregar la clase
    var trElement = clone.querySelector('tr');
    if (trElement) {
        // Aplicar la clase CSS dependiendo del tipo de movimiento
        if (data.type === 'Ingreso' || data.type === 'Venta') {
            trElement.classList.add('income-row'); // Agregar clase de fondo verde
        } else if (data.type === 'Egreso') {
            trElement.classList.add('expense-row'); // Agregar clase de fondo rojo claro
        } else if (data.type === 'Regularizar') {
            trElement.classList.add('regularize-row'); // Agregar clase de fondo rojo claro
        }
    } else {
        console.error('No se encontró el elemento <tr> en el clon.');
    }

    if (data.order_id != null) {
        const botones2 = clone.querySelector('[data-buttons]');

        // Clon del template
        const cloneBtn2 = activateTemplate('#template-button');

        // ---- botón imprimir boleta (permiso: caja.imprimir_boleta) ----
        const btnImprimir = cloneBtn2.querySelector('[data-print_nota]');
        if (hasPerm('caja.imprimir_boleta')) {
            btnImprimir.setAttribute('data-id', data.id);
            const url = document.location.origin + '/imprimir/recibo/' + data.order_id;
            btnImprimir.setAttribute('href', url);
            // si quieres, asegúrate de que esté visible:
            btnImprimir.style.display = '';
        } else {
            // sin permiso -> ocultar/comentar el botón
            btnImprimir.style.display = 'none';
            // o: btnImprimir.remove();
        }

        // ---- botón regularizar (permiso: caja.regularize) ----
        const regularizarBtn = cloneBtn2.querySelector('[data-regularizar]');
        if (hasPerm('caja.regularize') && data.type === 'Regularizar') {
            regularizarBtn.setAttribute('data-id', data.id);
            regularizarBtn.style.display = '';
        } else {
            regularizarBtn.style.display = 'none';
            // o: regularizarBtn.remove();
        }

        // Agregar el bloque de botones al DOM
        botones2.append(cloneBtn2);
    }

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
