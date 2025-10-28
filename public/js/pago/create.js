$(document).ready(function() {

    /** ----------------------------------------------------------
     *  VARIABLES GLOBALES
     *  ---------------------------------------------------------- */
    var ES_EXTERNO = window.ES_EXTERNO || false;
    var ITEMS = window.ITEMS || [];
    var pick = {}; // id => qty seleccionada
    window.TOTALES = {}; // expone totales para backend

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

    // Lee tasa IGV desde el DOM (hidden #igvRate) o usa 0.18 por defecto
    function readIgvRate() {
        var v = parseFloat($('#igvRate').val() || '0.18');
        if (isNaN(v) || v < 0) v = 0.18;
        // imprime 18% sin decimales feos
        var pct = (v * 100);
        var pctTxt = (Math.round(pct) === pct ? String(pct) : pct.toFixed(2)) + '%';
        $('#igvRateText').text(pctTxt);
        return v;
    }

    // Calcula subtotal de una línea (precio YA incluye IGV)
    function lineSubtotal(id) {
        var it = getItemById(id);
        var qty = pick[id] || 0;
        var sub = it ? (it.precio * qty) : 0; // precio impuesto-incluido
        $('.line-sub[data-id="' + id + '"]').text(fmt(sub));
    }

    /** ----------------------------------------------------------
     *  DESCUENTOS, PROPINA Y TOTALES (PRECIOS CON IGV)
     *  ---------------------------------------------------------- */
    $('#descuento_tipo,#descuento_val,#propina_tipo,#propina_val').on('input change', calc);

    function calc() {
        var subtotalBruto = 0; // con IGV

        // Subtotal: suma de líneas seleccionadas (precios tax-incl)
        for (var id in pick) {
            if (!pick.hasOwnProperty(id)) continue;
            var it = getItemById(id);
            if (it) subtotalBruto += (it.precio/1.18) * pick[id];
        }

        // Descuento (global) sobre subtotalBruto (con IGV)
        var dt = $('#descuento_tipo').val();
        var dv = parseFloat($('#descuento_val').val() || '0');
        var desc = 0;

        if (dt === 'porc') desc = subtotalBruto * (dv / 100);
        if (dt === 'fijo') desc = dv;
        if (desc < 0) desc = 0;
        if (desc > subtotalBruto) desc = subtotalBruto;

        // Consumo neto (con IGV) = subtotal - descuento
        var consumoNeto = subtotalBruto - desc;
        if (consumoNeto < 0) consumoNeto = 0;

        // Descomponer consumo neto en base + igv (precios tax-incl)
        var igvRate = readIgvRate();
        //var base = consumoNeto / (1 + igvRate);
        var base = consumoNeto;
        var igv  = base * (igvRate);

        // Propina (no gravada) — % calculado sobre consumo neto
        var pt = $('#propina_tipo').val();
        var pv = parseFloat($('#propina_val').val() || '0');
        var prop = 0;
        if (pt === 'porc') prop = consumoNeto * (pv / 100);
        if (pt === 'fijo') prop = pv;
        if (prop < 0) prop = 0;

        // Total final
        var total = base + igv + prop;

        // Render
        $('#subtotal').text(fmt(subtotalBruto));
        $('#desc').text(fmt(desc));
        $('#base').text(fmt(base));
        $('#igv').text(fmt(igv));
        $('#prop').text(fmt(prop));
        $('#total, #btnTotal').text(fmt(total));

        // Botón
        $('#btnPagar').prop('disabled', total <= 0);

        // Exponer totales (por si el backend los usa para validar/registrar)
        window.TOTALES = {
            // precios IGV-incl en subtotal/consumo
            subtotal_incl_igv: Number(fmt(subtotalBruto)),
            descuento_monto:    Number(fmt(desc)),
            consumo_neto_incl_igv: Number(fmt(consumoNeto)),
            // descomposición
            base_imponible:     Number(fmt(base)),
            igv_rate:           igvRate,
            igv_monto:          Number(fmt(igv)),
            // no gravado
            propina_monto:      Number(fmt(prop)),
            // total a cobrar
            total:              Number(fmt(total))
        };
    }

    /** ----------------------------------------------------------
     *  INICIALIZACIÓN (EXTERNO)
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

        // Agregar inputs ocultos tradicionales
        for (var i = 0; i < arr.length; i++) {
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][id]" value="' + arr[i].id + '">');
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][qty]" value="' + arr[i].qty + '">');
        }

        // Totales (por si deseas leerlos en request)
        $('#frmPago input[name^="totales["]').remove();
        var T = window.TOTALES || {};
        for (var k in T) {
            if (!T.hasOwnProperty(k)) continue;
            $('#frmPago').append('<input type="hidden" name="totales['+k+']" value="'+ T[k] +'">');
        }
    });

    /** ----------------------------------------------------------
     *  SECCIÓN DE MÉTODOS DE PAGO (SLIDER / RADIO)
     *  ---------------------------------------------------------- */

    // Desmarcar radios y seleccionar por defecto
    $('input[name="paymentMethod"]').prop('checked', false);
    $('#method_yape_plin').prop('checked', true).trigger('change');
    $('#yape-section').show();

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
    });

    /** ----------------------------------------------------------
     *  CLIENTES (Select2 + Modal)
     *  ---------------------------------------------------------- */
    function clearBillingFields() {
        $('[name="dni"]').val('');
        $('[name="ruc"]').val('');
        $('[name="razon_social"]').val('');
        $('[name="direccion_fiscal"]').val('');
        $('[name="email_invoice_boleta"]').val('');
        $('[name="email_invoice_factura"]').val('');
    }

    function currentInvoiceType() {
        return $('input[name="invoice_type"]:checked').val() || 'ninguno';
    }

    function fillBillingFromCustomer(c) {
        clearBillingFields();
        const tipoComprobante = currentInvoiceType();
        const docTipo = String(c.doc_tipo || '').toUpperCase();

        if (tipoComprobante === 'boleta') {
            if (docTipo === 'DNI' && c.dni) {
                $('[name="dni"]').val(c.dni);
                $('[name="email_invoice_boleta"]').val(c.email || '');
            }
        }

        if (tipoComprobante === 'factura') {
            if (docTipo === 'RUC' && c.ruc) {
                $('[name="ruc"]').val(c.ruc);
                $('[name="razon_social"]').val(c.razon_social || c.nombre || '');
                $('[name="direccion_fiscal"]').val(c.direccion || '');
                $('[name="email_invoice_factura"]').val(c.email || '');
            }
        }
    }

    function mapClienteToSelect2Item(item) {
        const num_doc  = item.num_doc || item.numero_documento || item.doc || '';
        const rawTipo  = item.tipo_doc || item.doc_tipo || '';
        const doc_tipo = String(rawTipo).trim().toUpperCase(); // 'DNI' | 'RUC' | ...

        return {
            id: item.id,
            text: item.nombre
                ? (item.nombre + (num_doc ? ' (' + num_doc + ')' : ''))
                : (item.razon_social || ('Cliente #' + item.id)),
            doc_tipo: doc_tipo,
            num_doc: num_doc,
            dni: (doc_tipo === 'DNI') ? (item.dni || num_doc) : '',
            ruc: (doc_tipo === 'RUC') ? (item.ruc || num_doc) : '',
            nombre: item.nombre || '',
            razon_social: item.razon_social || item.nombre || '',
            direccion: item.direccion || item.direccion_fiscal || '',
            email: item.email || item.correo || ''
        };
    }

    $('.select2-clientes').select2({
        ajax: {
            url: $('#cliente_id').data('url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return mapClienteToSelect2Item(item);
                    })
                };
            },
            cache: true
        },
        placeholder: "— Selecciona un cliente —",
        allowClear: true,
        width: 'resolve'
    });

    $('#cliente_id').on('select2:select', function (e) {
        const selected = e.params.data;
        fillBillingFromCustomer(selected);
    });

    $('#cliente_id').on('select2:clear', function () {
        clearBillingFields();
    });

    $('input[name="invoice_type"]').on('change', function () {
        if (this.value === 'boleta') {
            $('#datos_boleta').removeClass('d-none');
            $('#datos_factura').addClass('d-none');
        } else if (this.value === 'factura') {
            $('#datos_factura').removeClass('d-none');
            $('#datos_boleta').addClass('d-none');
        } else {
            $('#datos_boleta, #datos_factura').addClass('d-none');
            clearBillingFields();
        }
        const data = $('#cliente_id').select2('data');
        if (data && data.length) fillBillingFromCustomer(data[0]);
    });

    /** ----------------------------------------------------------
     *  VALIDACIÓN + CONFIRMACIÓN + SUBMIT CONTROLADO
     *  ---------------------------------------------------------- */

    function selectedPaymentMethodCode() {
        const $checked = $('input[name="paymentMethod"]:checked');
        return $checked.length ? $checked.data('code') : null;
    }

    function buildItemsArrayFromPick() {
        var arr = [];
        for (var id in pick) {
            if (!pick.hasOwnProperty(id)) continue;
            arr.push({ id: parseInt(id, 10), qty: parseInt(pick[id], 10) });
        }
        return arr;
    }

    function validateBeforePay() {
        var total = parseFloat($('#total').text() || '0');
        if (isNaN(total) || total <= 0) {
            return { ok:false, msg:'El total debe ser mayor a 0.' };
        }

        var itemsArr = buildItemsArrayFromPick();
        if (itemsArr.length === 0) {
            return { ok:false, msg:'Selecciona al menos un producto/cantidad a pagar.' };
        }

        if (!$('#cliente_id').val()) {
            return { ok:false, msg:'Selecciona un cliente antes de continuar.' };
        }

        var invType = currentInvoiceType(); // ninguno | boleta | factura
        if (invType === 'boleta') {
            var dni = ($('[name="dni"]').val() || '').trim();
            if (!dni) return { ok:false, msg:'Para BOLETA, el DNI es obligatorio.' };
        }
        if (invType === 'factura') {
            var ruc  = ($('[name="ruc"]').val() || '').trim();
            var rs   = ($('[name="razon_social"]').val() || '').trim();
            var dir  = ($('[name="direccion_fiscal"]').val() || '').trim();
            if (!ruc) return { ok:false, msg:'Para FACTURA, el RUC es obligatorio.' };
            if (!rs)  return { ok:false, msg:'Para FACTURA, la Razón Social es obligatoria.' };
            if (!dir) return { ok:false, msg:'Para FACTURA, la Dirección Fiscal es obligatoria.' };
        }

        var method = selectedPaymentMethodCode(); // 'yape_plin' | 'efectivo' | 'pos' | ...
        if (!method) return { ok:false, msg:'Selecciona un método de pago.' };

        if (method === 'yape_plin') {
            var op = ($('#operationCode').val() || '').trim();
            if (!op) return { ok:false, msg:'Ingresa el código de operación de Yape/Plin.' };
        }

        if (method === 'efectivo') {
            var cash = parseFloat($('#cashAmount').val() || '0');
            if (isNaN(cash) || cash <= 0) {
                return { ok:false, msg:'Ingresa el monto con el que paga en efectivo.' };
            }
        }

        return { ok:true, msg:'' };
    }

    function prepareHiddenItemsInputs(itemsArr) {
        $('#frmPago input[name^="items["]').remove();
        $('#items_payload').val(JSON.stringify(itemsArr));
        for (var i = 0; i < itemsArr.length; i++) {
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][id]" value="' + arr[i].id + '">');
            $('#frmPago').append('<input type="hidden" name="items[' + i + '][qty]" value="' + arr[i].qty + '">');
        }
    }

    function readTotalNumber() {
        return parseFloat(($('#total').text() || '0').replace(',', '')) || 0;
    }

    function buildFacturarPayload() {
        const invType = currentInvoiceType(); // ninguno | boleta | factura
        const total = readTotalNumber();
        const sel = $('#cliente_id').select2('data');
        const s2  = (sel && sel.length) ? sel[0] : null;

        const data = {
            tipo: invType === 'ninguno' ? 'ticket' : invType,  // ticket|boleta|factura
            customer_id: $('#cliente_id').val() || null,
            cliente_doc_tipo: null,
            cliente_doc_num:  null,
            cliente_nombre:   null,
            cliente_direccion:null,
            descuento: null,
            propina:   null,
            items: buildItemsArrayFromPick(),
            pagos: [],
            totales: window.TOTALES || null
        };

        if (invType === 'boleta') {
            data.cliente_doc_tipo = 'DNI';
            data.cliente_doc_num  = ($('[name="dni"]').val() || '').trim();
            data.cliente_nombre   = s2 ? (s2.nombre || s2.razon_social || s2.text || '') : '';
        }
        if (invType === 'factura') {
            data.cliente_doc_tipo  = 'RUC';
            data.cliente_doc_num   = ($('[name="ruc"]').val() || '').trim();
            data.cliente_nombre    = ($('[name="razon_social"]').val() || '').trim();
            data.cliente_direccion = ($('[name="direccion_fiscal"]').val() || '').trim();
        }

        const dt = $('#descuento_tipo').val();
        const dv = parseFloat($('#descuento_val').val() || '0');
        if (dt === 'porc') data.descuento = {tipo:'porc', valor: dv};
        if (dt === 'fijo') data.descuento = {tipo:'fijo', valor: dv};

        const pt = $('#propina_tipo').val();
        const pv = parseFloat($('#propina_val').val() || '0');
        if (pt === 'porc') data.propina = {tipo:'porc', valor: pv};
        if (pt === 'fijo') data.propina = {tipo:'fijo', valor: pv};

        const method = selectedPaymentMethodCode();
        if (method === 'yape_plin') {
            data.pagos.push({
                metodo: 'yape',
                monto: total,
                referencia: ($('#operationCode').val() || '').trim()
            });
        } else if (method === 'efectivo') {
            const recibido = parseFloat($('#cashAmount').val() || '0');
            const vuelto = Math.max(0, recibido - total);
            data.pagos.push({
                metodo: 'efectivo',
                monto: total,
                monto_recibido: recibido,
                vuelto: vuelto
            });
        } else if (method === 'pos') {
            data.pagos.push({
                metodo: 'tarjeta',
                monto: total,
                referencia: null
            });
        }

        return data;
    }

    // Handler del botón custom
    $('#btnPagar').off('click').on('click', function(e) {
        e.preventDefault();

        var val = validateBeforePay();
        if (!val.ok) {
            $.alert({
                title: 'Falta información',
                content: val.msg
            });
            return;
        }

        var totalTxt = $('#btnTotal').text() || $('#total').text() || '0.00';
        $.confirm({
            title: 'Confirmar pago',
            content: 'Se generará el comprobante por <b>S/ ' + totalTxt + '</b>.<br>¿Deseas continuar?',
            buttons: {
                cancelar: function(){},
                confirmar: {
                    text: 'Sí, continuar',
                    btnClass: 'btn-green',
                    action: function() {
                        $('#btnPagar').prop('disabled', true);

                        var arr = buildItemsArrayFromPick();
                        // inputs ocultos (si los usas)
                        $('#frmPago input[name^="items["]').remove();
                        $('#items_payload').val(JSON.stringify(arr));
                        for (var i = 0; i < arr.length; i++) {
                            $('#frmPago').append('<input type="hidden" name="items[' + i + '][id]" value="' + arr[i].id + '">');
                            $('#frmPago').append('<input type="hidden" name="items[' + i + '][qty]" value="' + arr[i].qty + '">');
                        }
                        // totales
                        $('#frmPago input[name^="totales["]').remove();
                        var T = window.TOTALES || {};
                        for (var k in T) {
                            if (!T.hasOwnProperty(k)) continue;
                            $('#frmPago').append('<input type="hidden" name="totales['+k+']" value="'+ T[k] +'">');
                        }

                        const payload = buildFacturarPayload();
                        const csrf = $('meta[name="csrf-token"]').attr('content');
                        const url  = $('#frmPago').data('action');

                        $.ajax({
                            url: url,
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf },
                            data: payload
                        })
                            .done(function(res){
                                if (res && res.ok) {
                                    const pdf = res.pdf_url || (res.data && res.data.enlace_del_pdf) || null;

                                    $.confirm({
                                        title: 'Comprobante generado ✅',
                                        content: 'Tu comprobante ha sido emitido correctamente.',
                                        buttons: {
                                            pdf: pdf ? {
                                                text: 'Abrir PDF',
                                                btnClass: 'btn-blue',
                                                action: function(){ window.open(pdf, '_blank'); location.reload(); }
                                            } : undefined,
                                            ok: {
                                                text: 'Aceptar',
                                                action: function(){
                                                    if (res.redirect_url) window.location.href = res.redirect_url;
                                                    else location.reload();
                                                }
                                            }
                                        }
                                    });
                                } else {
                                    const msg = (res && (res.msg || res.message)) || 'No se pudo generar el comprobante.';
                                    $.alert({ title:'Ups…', content: msg });
                                }
                            })
                            .fail(function(xhr){
                                let msg = 'Error al generar el comprobante.';
                                if (xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message)) {
                                    msg = xhr.responseJSON.msg || xhr.responseJSON.message;
                                }
                                $.alert({ title:'Error', content: msg });
                            })
                            .always(function(){
                                $('#btnPagar').prop('disabled', false);
                            });
                    }
                }
            }
        });
    });

    /** ----------------------------------------------------------
     *  SELECCIÓN INICIAL PARA MESAS (NO EXTERNO)
     *  ---------------------------------------------------------- */
    function selectAllMesaOnLoad() {
        if (ES_EXTERNO) return;

        $('.item-check').each(function () {
            const id   = $(this).data('id');
            const $qty = $('.qty-input[data-id="' + id + '"]');
            const max  = parseInt($qty.attr('max') || '0', 10);

            if (max > 0) {
                $(this).prop('checked', true);
                $qty.prop('disabled', false).val(max);
                pick[id] = max;
            } else {
                $(this).prop('checked', false);
                $qty.prop('disabled', true).val(0);
                delete pick[id];
            }
            lineSubtotal(id);
        });

        calc();
    }
    // Llamar después de registrar handlers
    selectAllMesaOnLoad();

});
