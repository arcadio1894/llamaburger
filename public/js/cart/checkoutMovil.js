/*const mp = new MercadoPago('TEST-43619ea9-0d2d-4976-afba-ba9a8f261549', { locale: 'es-PE', debug: true }); // Reemplaza con tu llave pública de Mercado Pago

const cardForm = mp.cardForm({
    amount: '1.00', // Cambia por el monto de la transacción
    autoMount: true,
    form: {
        id: 'checkoutForm', // Este ID debe coincidir con el del formulario
        cardholderName: { id: 'cardholderName', placeholder: 'Nombre del titular' },
        cardholderEmail: { id: 'cardholderEmail', placeholder: 'Correo electrónico' },
        cardNumber: { id: 'cardNumber', placeholder: 'Número de tarjeta' },
        cardExpirationMonth: { id: 'cardExpirationMonth', placeholder: 'MM' },
        cardExpirationYear: { id: 'cardExpirationYear', placeholder: 'AA' },
        securityCode: { id: 'securityCode', placeholder: 'CVV' },
        installments: { id: 'installments', placeholder: 'Cuotas' },
        issuer: { id: 'issuer', placeholder: 'Banco' },
        identificationType: { id: 'identificationType', placeholder: 'Tipo de documento' },
        identificationNumber: { id: 'identificationNumber', placeholder: 'Número de documento' }
    },
    callbacks: {
        onFormMounted: error => {
            if (error) console.warn('Error al montar formulario:', error);
        },
        onSubmit: event => {
            event.preventDefault();
            const formData = cardForm.getCardFormData();
            console.log(formData);

            if (!formData.token) {
                toastr.error('No se pudo generar el token. Verifica los datos ingresados.', 'Error');
                return;
            }

            // Envía los datos al backend
            submitFormAjax({
                token: formData.token,
                installments: formData.installments || '1',
                issuerId: formData.issuerId || 'default',
                paymentMethodId: formData.paymentMethodId
            });
        }
    }
});*/

$(document).ready(function() {
    // Obtener los datos de la tienda seleccionada desde localStorage
    const tiendaSeleccionada = localStorage.getItem('tiendaSeleccionada');

    if (tiendaSeleccionada) {
        const tienda = JSON.parse(tiendaSeleccionada);

        // Rellenar los campos con los valores obtenidos
        $('#address').val(tienda.direccionCliente);
        $('#latitude').val(tienda.latitudCliente);
        $('#longitude').val(tienda.longitudCliente);
        $('#costShipping').val(tienda.precioEnvio);
        $('#shopId').val(tienda.tiendaId);

        // Actualizar el costo de envío en la vista
        if (tienda.precioEnvio) {
            $('#amount_shipping').text(`+S/.${tienda.precioEnvio}`);
            $('#info_shipping').removeClass('hidden'); // Mostrar el elemento si estaba oculto
        }
    }

    loadCheckout();

    // Obtén el carrito del localStorage y parsea el JSON
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');

    // Verifica si todos los productos tienen reward en true
    if (cart.length > 0 && cart.every(item => item.reward === true)) {
        // Ocultar la sección deseada (ejemplo: #section-to-hide)
        $('#payment-slider').hide();
        $('#title-method').hide();

        // Cambiar el texto (ejemplo: #text-to-change)
        $('#btn-submit').text('RECLAMAR');
        $('#btn-submit-mobile').text('RECLAMAR');
    } else {
        $('#payment-slider').show();
        $('#title-method').show();
        //$('#btn-submit').text('COMPRAR');
        //$('#btn-submit-mobile').text('COMPRAR');

        // 1️⃣ Desmarcar todos los radios primero
        $('input[name="paymentMethod"]').prop('checked', false);

        // 2️⃣ Marcar POS como seleccionado por defecto y forzar el evento change
        $('#method_yape_plin').prop('checked', true).trigger('change');

        // 3️⃣ Mostrar la sección del POS al inicio
        $('#yape-section').show();

        // 4️⃣ Verificar que POS está seleccionado correctamente en la consola
        console.log("Carga inicial: Método POS seleccionado ->", $('#method_yape_plin').prop('checked'));

    }


    // 5️⃣ Detectar cambios de slide y actualizar el método seleccionado
    $('#payment-slider').on('slid.bs.carousel', function (e) {
        const activeItem = $(e.relatedTarget).find('input[type="radio"]');

        if (activeItem.length) {
            $('input[name="paymentMethod"]').prop('checked', false); // Desmarcar todos
            activeItem.prop('checked', true).trigger('change'); // Marcar el correcto y disparar evento
        }

        let selectedMethod = activeItem.data('code');

        // Ocultar todas las secciones
        $('#pos-section, #cash-section, #yape-section').hide();

        // Mostrar la sección correspondiente
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

    $('#copy-phone').on('click', function () {
        let phoneNumber = $('#yape-phone').text().trim();

        // Verificar si navigator.clipboard está disponible
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(phoneNumber).then(function () {
                $.confirm({
                    title: 'Copiado!',
                    content: 'El número: <strong>' + phoneNumber + '</strong> fue copiado.',
                    type: 'green',
                    typeAnimated: true,
                    buttons: {
                        ok: {
                            text: 'OK',
                            btnClass: 'btn-green',
                            action: function () {}
                        }
                    }
                });
            }).catch(function (err) {
                console.error('Error al copiar el número: ', err);
            });
        } else {
            // Método alternativo usando un input temporal
            let tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(phoneNumber).select();
            document.execCommand('copy');
            tempInput.remove();

            $.confirm({
                title: 'Copiado!',
                content: 'El número: <strong>' + phoneNumber + '</strong> fue copiado.',
                type: 'green',
                typeAnimated: true,
                buttons: {
                    ok: {
                        text: 'OK',
                        btnClass: 'btn-green',
                        action: function () {}
                    }
                }
            });
        }
    });


    // Ocultamos inicialmente el li del código de promoción
    $('#info_code').addClass('hidden'); // Ocultar

    $('#btn-login').click(function () {
        window.location.href = `/login?redirect_to=checkout`;
    });

    $('#btn-register').click(function () {
        window.location.href = `/register?redirect_to=checkout`;
    });

    // Acción al presionar el botón "Aplicar"
    $('#btn-promo_code').click(function () {
        // Mostrar modal para loguearse o registrarse
        // Variable definida desde Blade para saber si el usuario está autenticado
        let isAuthenticated = $('#auth-status').data('authenticated');
        console.log($('#auth-status').data('authenticated'));
        if (isAuthenticated == 0) {
            $('#authModal').modal('show');
            return;
        }

        let phone = $("#phone").val().replace(/\s+/g, '').replace(/[^0-9+]/g, '');
        if ( phone == "") {
            $.confirm({
                title: '',
                content: 'Por favor ingrese su número telefónico.',
                type: 'orange',
                buttons: {
                    ok: {
                        text: 'Entendido',
                        btnClass: 'btn-orange',
                    }
                }
            });
            return; // Salir de la función si no se ha seleccionado un tamaño
        }

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let tiendaSeleccionada = JSON.parse(localStorage.getItem('tiendaSeleccionada')) || [];
        $('#info_code').addClass('hidden'); // Ocultar
        $('#coupon_name').val(""); // Limpiar el campo del nombre del cupón
        const promoCode = $('#promo_code').val();
        const cartId = $('input[name="cart_id"]').val(); // Obtener cart_id
        const district = $('#district').val(); // Obtener el distrito elegido (ajustar el selector si es diferente)

        // Realizamos la solicitud al servidor para validar el código
        $.get('/apply-coupon', {
            code: promoCode,
            cart: JSON.stringify(cart), // Serializar el objeto cart
            district: district,
            phone: phone,
            tienda: JSON.stringify(tiendaSeleccionada)
        }, function (response) {
            if (response.success) {
                // Si el código es válido, actualizamos los elementos
                $('#info_code').removeClass('hidden'); // Mostrar
                $('#name_code').text(response.code_name); // Nombre del código
                $('#amount_code').text(response.discount_display); // Monto de descuento

                $('#gravada_amount').text(`S/ ${response.total_gravada}`); // Nuevo total con envío y descuento
                $('#igv_amount').text(`S/ ${response.total_igv}`); // Nuevo total con envío y descuento
                $('#total_amount').text(`S/ ${response.total_a_pagar}`); // Nuevo total con envío y descuento


                $('#coupon_name').val(response.code_name); // Nombre del código

                $('#total-price-mobile').text(`S/ ${response.total_a_pagar}`);
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
            } else {
                // Si hay error, ocultamos el código y restauramos el total con envío
                $('#info_code').addClass('hidden');

                $('#gravada_amount').text(`S/ ${response.total_gravada}`); // Nuevo total con envío y descuento
                $('#igv_amount').text(`S/ ${response.total_igv}`); // Nuevo total con envío y descuento
                $('#total_amount').text(`S/ ${response.total_a_pagar}`); // Nuevo total con envío y descuento

                $('#coupon_name').val(""); // Limpiar el nombre del cupón

                $('#total-price-mobile').text(`S/ ${response.total_a_pagar}`);

                toastr.error(response.message, 'Error',
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
        }).fail(function () {
            toastr.error('Ocurrió un error al procesar el código de promoción.', 'Error',
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
        });
    });

    /*$('.payment-method').on('change', function() {
        let selectedMethod = $(this).data('code');

        // Ocultar ambas secciones al cambiar la selección
        $('#cash-section, #yape-section').hide();

        // Mostrar la sección correspondiente al método de pago
        if (selectedMethod === 'efectivo') {
            $('#cash-section').show();
            $('#cashAmount').val("");
        } else if (selectedMethod === 'yape_plin') {
            $('#yape-section').show();
            $('#operationCode').val("");
        }  /!*else if (selectedMethod === 'mercado_pago') {
            $('#mercado_pago-section').show();
            $('#cardExpirationMonth').val("");
            $('#cardExpirationYear').val("");
            $('#securityCode').val("");
            $('#cardholderName').val("");
            $('#cardholderEmail').val("");
        }*!/
    });*/

    // Al hacer clic en el botón de enviar
    $('#btn-continue').on('click', function(event) {
        event.preventDefault();
        $('#btn-continue').text("Procesando pago...");
        $('#btn-submit').attr("disabled", true);
        $('#btn-continue').attr("disabled", true);
        $('#btn-cancel').attr("disabled", true);
        // Llamar al método para verificar horario de atención
        $.ajax({
            url: '/api/business-hours', // Cambia la ruta si es necesario
            method: 'GET',
            success: function (response) {
                if (!response.is_open) {
                    // Mostrar mensaje si el negocio está cerrado
                    $('#business-message').text(response.message);
                    $('#business-status').fadeIn();
                    $('#btn-submit').attr("disabled", true); // Deshabilitar el botón
                    $('#btn-cancel').attr("disabled", false);
                    $('#btn-continue').text("Continuar").attr("disabled", false); // Restaurar el botón
                    return; // Detener el flujo del código
                } else {
                    procesarFormulario(); // Llamar a la función para validar y enviar el formulario
                    // Si el negocio está abierto, continuar con el flujo
                    //$('#btn-submit').attr("disabled", false); // Habilitar el botón
                }
            },
            error: function () {
                console.error('No se pudo verificar el horario de atención.');
                $('#btn-submit').attr("disabled", false); // Permitir envío si ocurre un error inesperado
                $('#btn-continue').text("Continuar").attr("disabled", false);
            }
        });

        // Cerrar el mensaje al hacer clic en el botón "X"
        $('#close-business-status').on('click', function () {
            $('#business-status').fadeOut();
            $('#btn-submit').attr("disabled", false); // Habilitar el botón de envío
        });

    });

    $('#district').on('change', function () {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const districtId = $(this).val();
        /*const cartId = $('input[name="cart_id"]').val();*/
        const cartId = cart;
        const couponName = $('#coupon_name').val();

        let phone = $("#phone").val().replace(/\s+/g, '').replace(/[^0-9+]/g, '');
        if ( phone == "") {
            $.confirm({
                title: '',
                content: 'Por favor ingrese su número telefónico.',
                type: 'orange',
                buttons: {
                    ok: {
                        text: 'Entendido',
                        btnClass: 'btn-orange',
                    }
                }
            });
            return; // Salir de la función si no se ha seleccionado un tamaño
        }

        if (!districtId) {
            // Caso cuando se selecciona "Ninguno"
            $('#info_shipping').addClass('hidden'); // Ocultar costo de envío
            const defaultShippingCost = 0;

            $.ajax({
                url: '/checkout/shipping',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Incluye el token CSRF en los encabezados
                },
                contentType: 'application/json', // Indicamos que enviamos JSON
                data: JSON.stringify({
                    district_id: null,
                    cart: cart,
                    coupon_name: couponName,
                    phone: phone
                    /*_token: $('meta[name="csrf-token"]').attr('content')*/
                }),
                success: function (data) {
                    if (data.success) {
                        // Restablecer el costo de envío
                        $('#amount_shipping').text(`+ S/ ${defaultShippingCost.toFixed(2)}`);
                        // Actualizar el total
                        $('#total_amount').text(`S/ ${data.new_total.toFixed(2)}`);

                        $('#total-price-mobile').text(`S/ ${data.new_total.toFixed(2)}`);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error inesperado.', 'Error');
                }
            });
        } else {
            // Caso normal
            $('#info_shipping').addClass('hidden'); // Ocultar mientras carga
            $.ajax({
                url: '/checkout/shipping',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Incluye el token CSRF en los encabezados
                },
                contentType: 'application/json', // Indicamos que enviamos JSON
                data: JSON.stringify({
                    district_id: districtId,
                    cart: cart,
                    coupon_name: couponName,
                    phone: phone
                    /*_token: $('meta[name="csrf-token"]').attr('content')*/
                }),
                success: function (data) {
                    if (data.success) {
                        $('#info_shipping').removeClass('hidden');
                        $('#amount_shipping').text(`+ S/ ${data.shipping_cost.toFixed(2)}`);
                        $('#total_amount').text(`S/ ${data.new_total.toFixed(2)}`);

                        $('#total-price-mobile').text(`S/ ${data.new_total.toFixed(2)}`);
                    } else {
                        toastr.error(data.message, 'Error');
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error inesperado.', 'Error');
                }
            });
        }
    });

    $(".closeModalVerify").on('click', function () {
        $('#verifyModal').modal('hide');
        $('#btn-submit').attr("disabled", false);

        // btn-continue y cancel
        $('#btn-continue').attr("disabled", false);
        $('#btn-cancel').attr("disabled", false);
    });

    $('#btn-submit, #btn-submit-mobile').click(function () {
        var phone = $("#phone").val();
        var email = $("#email").val();
        $('#showPhone').html(phone);
        $('#showEmail').html(email);
        $('#verifyModal').modal({
            backdrop: 'static', // Desactiva el clic fuera del modal
            keyboard: false     // Desactiva el cierre con la tecla Esc
        });
        $('#btn-submit').attr("disabled", true);

        $('#btn-continue').attr("disabled", false);
        $('#btn-cancel').attr("disabled", false);
    });

    $('#verifyModal').on('hidden.bs.modal', function () {
        // Reactivar el botón
        $('#btn-submit').prop('disabled', false);
    });

    $("#btn-selectAddress").on('click', function () {
        console.log("Abri modal");
        $("#addressModal").modal("show");
    });

    $("#info-button").on('click', function () {
        console.log("Abri modal");
        $("#infoModal").modal("show");
    });

    $('input[name="invoice_type"]').on('change', function() {
        let tipo = $(this).val();

        if (tipo === 'boleta') {
            $('#datos_boleta').removeClass('d-none');
            $('#datos_factura').addClass('d-none');
        } else if (tipo === 'factura') {
            $('#datos_factura').removeClass('d-none');
            $('#datos_boleta').addClass('d-none');
        } else {
            // Ninguno seleccionado
            $('#datos_boleta').addClass('d-none');
            $('#datos_factura').addClass('d-none');

            // Limpiar inputs
            $('#datos_boleta input, #datos_factura input').val('');
        }
    });
});

const TAX_RATE = 0.18; // IGV (18%)
const TAX_RATE_100 = 18;

async function fetchProduct(productId, productTypeId) {
    try {
        const response = await $.ajax({
            url: `/products/${productId}/${productTypeId}`,
            type: 'GET',
        });
        return response;
    } catch (error) {
        console.error(`Error al obtener producto ${productId}:`, error);
        return null;
    }
}

function showLoading() {
    $("#loading-indicator").show(); // Muestra el indicador de carga
}

function hideLoading() {
    $("#loading-indicator").hide(); // Oculta el indicador de carga
}

async function loadCheckout() {
    showLoading(); // Mostrar indicador de carga

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = 0;

    $('#body-items').empty(); // Limpiar el contenedor antes de renderizar

    const itemPromises = cart.map(async (item, index) => {
        // Si el producto es custom
        if (item.custom) {
            const clone = activateTemplate('#template-detail');
            let subtotal = item.total * item.quantity;
            // Usar imagen y nombre genérico para productos custom
            let urlImage = document.location.origin + '/images/icons/default_custom_image.png';
            clone.querySelector("[data-image]").setAttribute('src', urlImage);
            clone.querySelector("[data-full_name]").innerHTML = `Producto Personalizado x${item.quantity}`;
            clone.querySelector("[data-subtotal]").innerHTML = `S/. ${subtotal.toFixed(2)}`;

            // Sumar al total general
            total += subtotal;

            return clone;
        }

        // Para productos normales, obtener los datos desde el servidor
        const product = await fetchProduct(item.product_id, item.product_type_id);
        if (!product) return null; // Si no conseguimos el producto, lo omitimos

        const clone = activateTemplate('#template-detail');
        let subtotal = product.price * item.quantity;

        // Incluir el precio de las opciones seleccionadas
        if (item.options && Object.keys(item.options).length > 0) {
            const options = Object.values(item.options).flat();

            const optionTotal = options.reduce((sum, option) => {
                return sum + option.additional_price;
            }, 0);

            subtotal += optionTotal * item.quantity; // Sumamos el precio total de las opciones
        }

        total += subtotal; // Actualizamos el total general

        // Rellenar los datos del producto en el clon
        const urlImage = document.location.origin + '/images/products/' + product.image_url;
        clone.querySelector("[data-image]").setAttribute('src', urlImage);
        clone.querySelector("[data-full_name]").innerHTML = `${product.name} x${item.quantity}`;
        clone.querySelector("[data-subtotal]").innerHTML = `S/. ${subtotal.toFixed(2)}`;

        return clone;
    });

    // Esperamos a que todas las promesas de los productos se resuelvan
    const productClones = await Promise.all(itemPromises);

    // Filtramos nulls y agregamos los clones de productos al DOM
    productClones.filter(Boolean).forEach(clone => {
        $('#body-items').append(clone);
    });

    // Obtener el costo de envío desde localStorage
    let shippingCost = 0;
    const tiendaSeleccionada = localStorage.getItem('tiendaSeleccionada');

    if (tiendaSeleccionada) {
        const tienda = JSON.parse(tiendaSeleccionada);
        shippingCost = parseFloat(tienda.precioEnvio) || 0;
    }

    // Sumar el costo de envío al total
    total += shippingCost;

    // Calculamos y renderizamos el resumen
    const taxesCart = total - (total / (1 + TAX_RATE));
    const subtotalCart = total - taxesCart;

    /*
    * return {
        total_gravada: total_gravada,
        total_igv: total_igv,
        total_a_pagar: total_a_pagar
    };
    * */
    let totals =getDataTotals(cart, shippingCost, TAX_RATE_100);
    console.log(totals);

    $("#gravada_amount").html(`S/. ${totals.total_gravada.toFixed(2)}`);
    $("#igv_amount").html(`S/. ${totals.total_igv.toFixed(2)}`);
    $("#total_amount").html(`S/. ${totals.total_a_pagar.toFixed(2)}`);

    $("#total-price-mobile").html(`S/. ${totals.total_a_pagar.toFixed(2)}`);

    hideLoading(); // Ocultar indicador de carga
}

function getDataTotals(cart, shipping, tax) {
    let items = [];
    let total_gravada = 0;
    let total_igv = 0;

    const taxDecimal = tax / 100; // 0.18

    cart.forEach(item => {
        let valor_unitario = +(item.total / (1 + taxDecimal)).toFixed(6);
        let subtotal = +(valor_unitario * item.quantity).toFixed(6);
        let igv = +((item.total * item.quantity) - subtotal).toFixed(2);
        let total = +(item.total * item.quantity).toFixed(6);

        items.push({
            subtotal: subtotal,
            igv: igv,
            total: total
        });

        total_gravada += subtotal;
        total_igv += igv;
    });

    // Agregar envío si existe
    if (shipping > 0) {
        let valor_unitario_envio = +(shipping / (1 + taxDecimal)).toFixed(6);
        let igv_envio = +(shipping - valor_unitario_envio).toFixed(6);

        items.push({
            subtotal: valor_unitario_envio,
            igv: igv_envio,
            total: shipping
        });

        total_gravada += valor_unitario_envio;
        total_igv += igv_envio;
    }

    // Redondear totales finales
    let discount = 0;
    let total_gravada_final = +(total_gravada - discount).toFixed(2);
    let total_igv_final = +(total_gravada_final * taxDecimal).toFixed(2);
    let total_a_pagar_final = +(total_gravada_final * (1 + taxDecimal)).toFixed(2);

    console.log(items);

    return {
        total_gravada: total_gravada_final,
        total_igv: total_igv_final,
        total_a_pagar: total_a_pagar_final
    };
}

/*async function loadCheckout() {
    showLoading(); // Mostrar indicador de carga

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let total = 0;

    $('#body-items').empty(); // Limpiar el contenedor antes de renderizar

    const itemPromises = cart.map(async (item, index) => {
        const product = await fetchProduct(item.product_id, item.product_type_id);
        if (!product) return null; // Si no conseguimos el producto, lo omitimos

        const clone = activateTemplate('#template-detail');
        const subtotal = product.price * item.quantity;
        total += subtotal;

        // Rellenar los datos del producto en el clon
        const urlImage = document.location.origin + '/images/products/' + product.image_url;
        clone.querySelector("[data-image]").setAttribute('src', urlImage);
        clone.querySelector("[data-full_name]").innerHTML = `${product.name} x${item.quantity}`;
        clone.querySelector("[data-subtotal]").innerHTML = `S/. ${subtotal.toFixed(2)}`;

        return clone;
    });

    // Esperamos a que todas las promesas de los productos se resuelvan
    const productClones = await Promise.all(itemPromises);

    // Filtramos nulls y agregamos los clones de productos al DOM
    productClones.filter(Boolean).forEach(clone => {
        $('#body-items').append(clone);
    });

    // Calculamos y renderizamos el resumen
    const taxesCart = total - (total / (1 + TAX_RATE));
    const subtotalCart = total - taxesCart;

    $("#subtotal_amount").html(`S/. ${subtotalCart.toFixed(2)}`);
    $("#taxes_amount").html(`S/. ${taxesCart.toFixed(2)}`);
    $("#total_amount").html(`S/. ${total.toFixed(2)}`);

    hideLoading(); // Ocultar indicador de carga
}*/
/*function loadCheckout() {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    let total = 0;

    // Almacenar todas las promesas
    const itemPromises = cart.map((item, index) => {
        return new Promise((resolve) => {
            var clone2 = activateTemplate('#template-detail');

            $.ajax({
                url: `/products/${item.product_id}/${item.product_type_id}`,
                type: 'GET',
                success: function (product) {
                    // Calcular subtotal del producto
                    const subtotal = product.price * item.quantity;
                    total += subtotal;

                    // Renderizar datos del producto
                    let url_image = document.location.origin + '/images/products/' + product.image_url;
                    clone2.querySelector("[data-image]").setAttribute('src', url_image);
                    clone2.querySelector("[data-full_name]").innerHTML = product.name + " x" + item.quantity;
                    clone2.querySelector("[data-subtotal]").innerHTML = "S/. " + subtotal.toFixed(2);

                    // Resolver la promesa con el clon creado
                    resolve(clone2);
                },
                error: function () {
                    console.error(`Error al obtener datos del producto ${item.product_id}`);
                    resolve(clone2); // Resolver incluso si hay un error
                }
            });
        });
    });

    // Procesar todas las promesas de los items
    Promise.all(itemPromises).then((clones) => {
        clones.forEach(clone => {
            $('#body-items').append(clone);
        });

        // Renderizar el resumen después de procesar todos los productos
        const taxes_cart = total - (total / (1 + TAX_RATE));
        const subtotal_cart = total - taxes_cart;

        console.log("Total calculado:", total); // <-- Verifica este valor
        $("#total_amount").html("S/. " + total.toFixed(2));

    });
}*/

function procesarFormulario() {
    // Validar el formulario
    let form = $('#checkoutForm');
    if (form[0].checkValidity() === false) {
        event.stopPropagation();
        form.addClass('was-validated');

        // Restaurar los botones a su estado original
        $('#btn-continue').text("Continuar").attr("disabled", false);
        $('#btn-cancel').attr("disabled", false);
        $('#btn-submit').attr("disabled", false);

        $('#verifyModal').modal('hide');

        // Mostrar mensajes de error (opcional, si usas Toast o alert)
        toastr.error('Por favor, corrige los errores en el formulario.', 'Error', {
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
    } else {

        let cart = JSON.parse(localStorage.getItem('cart') || '[]');

        // Verifica si todos los productos tienen reward en true
        if (cart.length > 0 && cart.every(item => item.reward === true)) {
            submitFormAjax({ paymentMethod: 1 });
        } else {
            // Verificar el método de pago seleccionado y validar campos adicionales
            let selectedMethod = $("input[name='paymentMethod']:checked").attr('id');

            if (selectedMethod === 'method_pos') {
                // Enviar directamente si es "POS"
                submitFormAjax();
            } else if (selectedMethod === 'method_efectivo') {
                // Validar si el monto ingresado es mayor a cero
                let cashAmount = $('#cashAmount').val();
                console.log(cashAmount);
                if (parseFloat(cashAmount) > 0 || cashAmount !== "" ) {
                    console.log('Enviamos formulario');
                    submitFormAjax({ cashAmount: cashAmount });
                } else {
                    //alert('Por favor, ingrese un monto válido en efectivo.');
                    toastr.error('Por favor, ingrese un monto válido en efectivo.', 'Error',
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
                    // Restaurar los botones a su estado original
                    $('#btn-continue').text("Continuar").attr("disabled", false);
                    $('#btn-cancel').attr("disabled", false);
                    $('#btn-submit').attr("disabled", false);

                    $('#verifyModal').modal('hide');
                }
            } else if (selectedMethod === 'method_yape_plin') {
                // Validar si el código de operación está presente
                let operationCode = $('#operationCode').val();
                if (operationCode) {
                    submitFormAjax({ operationCode: operationCode });
                } else {
                    //alert('Por favor, ingrese el código de operación para Yape o Plin.');
                    toastr.error('Por favor, ingrese el código de operación para Yape o Plin.', 'Error',
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
                    // Restaurar los botones a su estado original
                    $('#btn-continue').text("Continuar").attr("disabled", false);
                    $('#btn-cancel').attr("disabled", false);
                    $('#btn-submit').attr("disabled", false);

                    $('#verifyModal').modal('hide');
                }
            } else if (selectedMethod === 'method_mercado_pago') {

                /*// Validar si los campos necesarios tienen valores
                const installments = $('#installments').val();
                const issuer = $('#issuer').val();

                if (!installments) {
                    $('#installments').val('1'); // Valor por defecto de una cuota
                }

                if (!issuer) {
                    $('#issuer').val('default'); // Asegúrate de usar un ID válido para el banco
                }

                // Extrae los datos del formulario de tarjeta
                console.log(cardForm);
                const { token, issuerId, paymentMethodId } = cardForm.getCardFormData();
                console.log({ token, issuerId, paymentMethodId });

                if (!token) {
                    toastr.error('No se pudo generar el token. Verifica los datos ingresados Erorr.', 'Error');
                    return;
                }

                if (!issuerId) {
                    toastr.error('Por favor selecciona el banco.', 'Error');
                    return;
                }

                if (!$('#installments').val()) {
                    toastr.error('Por favor selecciona las cuotas.', 'Error');
                    return;
                }

                // Envía los datos al backend
                submitFormAjax({
                    token: token,
                    installments: $('#installments').val(),
                    issuerId: issuerId,
                    paymentMethodId: paymentMethodId
                });*/
                /*$.ajax({
                    url: '/crear-preferencia',
                    method: 'POST',
                    success: function(data) {
                        const mp = new MercadoPago('APP_USR-39c9eccc-78c3-42fc-b730-f1ddab6d5f39', {
                            locale: 'es-PE'
                        });
                        mp.checkout({
                            preference: {
                                id: data.id
                            },
                            autoOpen: true // Abre el checkout automáticamente
                        });
                    },
                    error: function(error) {
                        console.error('Error al crear la preferencia:', error);
                    }
                });*/

            }
        }


    }
}

function submitFormAjax(extraData = {}) {
    let tipo = $('input[name="invoice_type"]:checked').val();

    if (tipo === 'boleta') {
        let dni = $('input[name="dni"]').val().trim();
        if (dni === '' || dni.length !== 8 || isNaN(dni)) {
            toastr.error('Debe ingresar un DNI válido de 8 dígitos');
            return;
        }
    } else if (tipo === 'factura') {
        let ruc = $('input[name="ruc"]').val().trim();
        let razon = $('input[name="razon_social"]').val().trim();
        let direccion = $('input[name="direccion_fiscal"]').val().trim();

        if (ruc === '' || ruc.length !== 11 || isNaN(ruc)) {
            toastr.error('Debe ingresar un RUC válido de 11 dígitos');
            return;
        }
        if (razon === '') {
            toastr.error('Debe ingresar la Razón Social');
            return;
        }
        if (direccion === '') {
            toastr.error('Debe ingresar la Dirección Fiscal');
            return;
        }
    }

    let formData = $('#checkoutForm').serializeArray(); // Serializa los datos del formulario
    // Convertimos el formData a un objeto para poder agregar extraData
    let dataObj = {};
    formData.forEach(item => {
        dataObj[item.name] = item.value;
    });

    // Recuperar el carrito desde localStorage
    let cart = localStorage.getItem('cart'); // Asume que está guardado con la clave 'cart'
    let observations = localStorage.getItem('observations');

    let tienda = localStorage.getItem('tiendaSeleccionada');

    if (cart) {
        try {
            // Parsear el carrito
            cart = JSON.parse(cart); // Convertir el JSON en objeto
            tienda = JSON.parse(tienda);
            // Validar y parsear las observaciones (si existen)
            if (observations && observations.trim() !== "") {
                try {
                    observations = JSON.parse(observations); // Intentar parsear como JSON
                } catch (error) {
                    // Si no es un JSON válido, asumir que es una cadena de texto simple
                    console.warn("Las observaciones no son un JSON válido, se tratarán como texto simple.");
                }
            } else {
                observations = null; // Si no hay observaciones, establecer como null
            }

            // Agregar los datos al objeto de datos
            dataObj.cart = cart;
            dataObj.observations = observations;

            dataObj.tienda = tienda;

        } catch (error) {
            console.error("Error al parsear el carrito desde localStorage:", error);
            toastr.error('Error con el carrito. Por favor, verifica tu compra.', 'Error');
            return; // Detener si el carrito no es válido
        }
    } else {
        toastr.warning('El carrito está vacío. Agrega productos para continuar.', 'Advertencia');
        return; // Detener si el carrito no existe
    }

    // Añadir datos adicionales al objeto de datos
    $.extend(dataObj, extraData);

    $.ajax({
        url: '/checkout/pagar', // Ruta configurada en Laravel
        type: 'POST',
        data: JSON.stringify(dataObj), // Enviar como JSON
        contentType: 'application/json', // Asegúrate de que el backend reciba JSON
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), // Token CSRF
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(data) {
            if (data.success) {
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
                        "hideDuration": "2000",
                        "timeOut": "2000",
                        "extendedTimeOut": "1000",
                        "showEasing": "swing",
                        "hideEasing": "linear",
                        "showMethod": "fadeIn",
                        "hideMethod": "fadeOut"
                    });
                // Limpiar las claves específicas del Local Storage
                localStorage.removeItem('cart');
                localStorage.removeItem('observations');
                setTimeout( function () {
                    //location.reload();
                    $("#verifyModal").modal('hide');
                    $('#btn-submit').attr("disabled", false);
                    $('#btn-continue').attr("disabled", false);
                    window.location.href = data.redirect_url;
                }, 2000 )
            } else {

                toastr.error('Hubo un problema con tu compra. Intenta de nuevo.', 'Error',
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
                $("#verifyModal").modal('hide');
                $('#btn-submit').attr("disabled", false);
                $('#btn-continue').attr("disabled", false);
            }
        },
        error: function(data) {
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

            $("#verifyModal").modal('hide');
            $('#btn-submit').attr("disabled", false);
            $('#btn-continue').attr("disabled", false);
        }
    });
}

function activateTemplate(id) {
    var t = document.querySelector(id);
    return document.importNode(t.content, true);
}