$(document).ready(function() {

    /** ----------------------------------------------------------
     *  VARIABLES GLOBALES
     *  ---------------------------------------------------------- */
    var ES_EXTERNO = window.ES_EXTERNO || false;
    var ITEMS = window.ITEMS || [];
    var pick = {}; // id => qty seleccionada

    /** ----------------------------------------------------------
     *  FUNCIONES AUXILIARES
     *  ---------------------------------------------------------- */
    function fmt(n) {
        return Number(n || 0).toFixed(2);
    }

    function getItemById(id) {
        for (var i = 0; i < ITEMS.length; i++) {
            if (ITEMS[i].id == id) return ITEMS[i];
        }
        return null;
    }

    /** ----------------------------------------------------------
     *  INICIALIZACIÓN
     *  ---------------------------------------------------------- */
    // Externo paga todo automáticamente
    if (ES_EXTERNO) {
        for (var i = 0; i < ITEMS.length; i++) {
            var it = ITEMS[i];
            pick[it.id] = it.restante;
            $('.line-sub[data-id="' + it.id + '"]').text(fmt(it.precio * it.restante));
        }
        calc();
    }

    /** ----------------------------------------------------------
     *  GESTIÓN DE PRODUCTOS
     *  ---------------------------------------------------------- */

    // Checkbox mesa: habilitar/deshabilitar input cantidad
    $('.item-check').on('change', function() {
        var id = $(this).data('id');
        var input = $('.qty-input[data-id="' + id + '"]');
        var max = parseInt(input.attr('max') || '0', 10);

        if (this.checked) {
            input.prop('disabled', false).val(max);
            pick[id] = max;
        } else {
            input.prop('disabled', true).val(0);
            delete pick[id];
        }

        lineSubtotal(id);
        calc();
    });

    // Input cantidad manual
    $('.qty-input').on('input', function() {
        var id = $(this).data('id');
        var v = parseInt($(this).val() || '0', 10);
        var max = parseInt($(this).attr('max') || '0', 10);

        if (v < 0) v = 0;
        if (v > max) v = max;
        $(this).val(v);

        if (ES_EXTERNO) { v = max; $(this).val(max); } // seguridad

        if (v > 0) pick[id] = v;
        else delete pick[id];

        lineSubtotal(id);
        calc();
    });

    // Calcula subtotal de una línea
    function lineSubtotal(id) {
        var it = getItemById(id);
        var qty = pick[id] || 0;
        var sub = it ? (it.precio * qty) : 0;
        $('.line-sub[data-id="' + id + '"]').text(fmt(sub));
    }

    /** ----------------------------------------------------------
     *  DESCUENTOS, PROPINA Y TOTALES
     *  ---------------------------------------------------------- */
    $('#descuento_tipo,#descuento_val,#propina_tipo,#propina_val').on('input change', calc);

    function calc() {
        var sub = 0;

        for (var id in pick) {
            if (!pick.hasOwnProperty(id)) continue;
            var it = getItemById(id);
            if (it) sub += it.precio * pick[id];
        }

        var dt = $('#descuento_tipo').val();
        var dv = parseFloat($('#descuento_val').val() || '0');
        var desc = 0;

        if (dt === 'porc') desc = sub * (dv / 100);
        if (dt === 'fijo') desc = dv;
        if (desc < 0) desc = 0;
        if (desc > sub) desc = sub;

        var pt = $('#propina_tipo').val();
        var pv = parseFloat($('#propina_val').val() || '0');
        var base = sub - desc;
        var prop = 0;

        if (pt === 'porc') prop = base * (pv / 100);
        if (pt === 'fijo') prop = pv;
        if (prop < 0) prop = 0;

        var total = base + prop;

        $('#subtotal').text(fmt(sub));
        $('#desc').text(fmt(desc));
        $('#prop').text(fmt(prop));
        $('#total, #btnTotal').text(fmt(total));
        $('#btnPagar').prop('disabled', total <= 0);
    }

    /** ----------------------------------------------------------
     *  SUBMIT DEL FORMULARIO DE PAGO
     *  ---------------------------------------------------------- */
    $('#frmPago').on('submit', function(e) {
        var arr = [];

        for (var id in pick) {
            if (!pick.hasOwnProperty(id)) continue;
            arr.push({ id: parseInt(id, 10), qty: parseInt(pick[id], 10) });
        }

        if (arr.length === 0) {
            e.preventDefault();
            alert('Selecciona al menos un producto/cantidad a pagar.');
            return false;
        }

        // Evita doble submit
        $('#btnPagar').prop('disabled', true);

        // Payload visible (JSON)
        $('#items_payload').val(JSON.stringify(arr));

        // Agregar inputs ocultos
        for (var i = 0; i < arr.length; i++) {
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][id]" value="' + arr[i].id + '">');
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][qty]" value="' + arr[i].qty + '">');
        }
    });

    /** ----------------------------------------------------------
     *  SECCIÓN DE MÉTODOS DE PAGO (SLIDER / RADIO)
     *  ---------------------------------------------------------- */

    // Desmarcar radios y seleccionar por defecto
    $('input[name="paymentMethod"]').prop('checked', false);
    $('#method_yape_plin').prop('checked', true).trigger('change');
    $('#yape-section').show();

    console.log("Carga inicial: Método POS seleccionado ->", $('#method_yape_plin').prop('checked'));

    // Cambios en slider de métodos de pago
    $('#payment-slider').on('slid.bs.carousel', function(e) {
        const activeItem = $(e.relatedTarget).find('input[type="radio"]');

        if (activeItem.length) {
            $('input[name="paymentMethod"]').prop('checked', false);
            activeItem.prop('checked', true).trigger('change');
        }

        let selectedMethod = activeItem.data('code');

        // Ocultar secciones
        $('#pos-section, #cash-section, #yape-section').hide();

        // Mostrar según método
        if (selectedMethod === 'efectivo') {
            $('#cash-section').show();
            $('#cashAmount').val("");
        } else if (selectedMethod === 'yape_plin') {
            $('#yape-section').show();
            $('#operationCode').val("");
        } else if (selectedMethod === 'pos') {
            $('#pos-section').show();
        }

        console.log(`Método seleccionado: ${selectedMethod}, Checked: ${activeItem.prop('checked')}`);
    });

    /** ----------------------------------------------------------
     *  CLIENTES (Select2 + Modal)
     *  ---------------------------------------------------------- */
    $('.select2-clientes').select2({
        ajax: {
            url: $('#cliente_id').data('url'),
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.nombre + (item.num_doc ? ' (' + item.num_doc + ')' : '')
                        };
                    })
                };
            },
            cache: true
        },
        placeholder: "— Selecciona un cliente —",
        allowClear: true,
        width: 'resolve'
    });

    // Guardar cliente desde modal
    $('#btnGuardarCliente').on('click', function() {
        var $form = $('#frmCliente');
        var url = $form.data('action');

        $.ajax({
            url: url,
            method: 'POST',
            data: $form.serialize(),
            success: function(r) {
                if (r.ok && r.cliente) {
                    // Crear opción y seleccionar
                    var option = new Option(r.cliente.display, r.cliente.id, true, true);
                    $('#cliente_id').append(option).trigger('change');

                    // Cerrar modal y limpiar
                    $('#modalCliente').modal('hide');
                    $form[0].reset();
                } else {
                    alert('Error inesperado al guardar el cliente.');
                }
            },
            error: function(xhr) {
                let msg = 'Error al guardar cliente.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });

});