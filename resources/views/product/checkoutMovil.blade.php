@extends('layouts.app')

@section('menu-active', 'active')

@section('text-header')
    <h2 class="pt-5">
        Completa tu compra
    </h2>

@endsection

@section('styles')
    <link href="{{ asset('css/checkout/form-validation.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .hidden {
            display: none !important; /* Asegura que sobreescriba cualquier estilo */
        }
        /* Asegura que las sugerencias aparezcan correctamente */
        .pac-container {
            z-index: 1051 !important; /* Mayor que el z-index del modal */
        }
        .info-button {
            font-size: 18px;
            color: #007bff;
            cursor: pointer;
            margin-left: 8px;
        }

        .info-button:hover {
            color: #0056b3;
        }

        /* CARROUSEL */
        /* Asegura que cada item ocupe todo el ancho y alto del slider */
        .carousel-item {
            display: none; /* Oculta los elementos inactivos */
            align-items: center; /* Centra el contenido verticalmente */
            justify-content: center; /* Centra el contenido horizontalmente */
            width: 100%; /* Toma todo el ancho */
            height: 100%; /* Asegura que también ocupe el alto del contenedor */
            /*min-height: 200px;  Ajusta la altura mínima */
            opacity: 0; /* Ocultar con opacidad */
            transform: translateX(100%); /* Desplazados hacia la derecha inicialmente */
            transition: opacity 0.6s ease, transform 0.6s ease; /* Transición suave */
        }

        /* Solo el elemento activo será visible */
        .carousel-item.active {
            display: flex; /* Muestra el elemento activo */
            opacity: 1; /* Asegura la visibilidad */
            transition: opacity 0.6s ease; /* Agrega una transición suave */
            transform: translateX(0); /* Posición centrada */
        }

        /* Asegura que el contenedor del slider ocupe el espacio completo */
        .carousel-inner {
            width: 100%; /* El contenedor del slider debe ocupar todo el espacio */
            height: 100%; /* Asegura que los elementos ocupen el alto completo */
        }

        /* Elemento que está saliendo (anterior) */
        .carousel-item-next,
        .carousel-item-prev {
            display: flex; /* Mantener visible durante la transición */
            position: absolute; /* Evita que se superpongan en el flujo */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Deslizar el próximo elemento desde la derecha */
        .carousel-item-next {
            transform: translateX(100%); /* Comienza desde la derecha */
        }

        /* Deslizar el anterior elemento desde la izquierda */
        .carousel-item-prev {
            transform: translateX(-100%); /* Comienza desde la izquierda */
        }

        /* Animación para el próximo elemento al entrar */
        .carousel-item.active.carousel-item-left,
        .carousel-item.active.carousel-item-right {
            transform: translateX(0); /* Entra en la posición correcta */
            opacity: 1; /* Visible al final */
        }

        /* Los divs internos dentro de cada item también deben ocupar el ancho completo */
        #payment-slider .carousel-item > div {
            width: 100%; /* Los divs internos también deben ocupar el ancho completo */
            max-width: 100%; /* Evita bordes extraños */
            text-align: center; /* Centra el texto e imágenes */
        }

        /* Boton de Agregar carrito */
        /* Esconder el botón original en dispositivos móviles */
        @media (max-width: 992px) {
            .d-lg-flex {
                display: none !important;
            }
        }

        /* Div fijo para móviles */
        .mobile-fixed-cart {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.1);
            background-color: white;
            padding: 10px 15px;
            border-top: 1px solid #ddd;
        }

        .mobile-fixed-cart .btn {
            width: 100%;
            margin: 0;
        }

        @media (max-width: 768px) { /* Ocultar en pantallas pequeñas */
            #btn-submit {
                display: none;
            }
            #li_total_amount {
                display: none !important;
            }
        }

        #verifyModal .modal-dialog {
            top: 5%; /* Ajusta según sea necesario */
            transform: translateY(-10%);
        }

        #verifyModal .modal-header {
            padding: 10px 10px; /* Reduce el padding interno */
            margin-bottom: 0; /* Evita margen extra */
        }
        #verifyModal .modal-title {
            font-size: 1rem; /* Reduce el tamaño del título si es necesario */
            margin: 0; /* Evita espacio extra */
        }
        #verifyModal .modal-body {
            font-size: 0.9rem; /* Reduce ligeramente el tamaño del texto */
        }

        #verifyModal .modal-footer {
            font-size: 0.9rem; /* Reduce el tamaño del texto de los botones si es necesario */
        }

        @keyframes breathe-slider {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); } /* Aumenta más la escala */
            100% { transform: scale(1); }
        }

        .carousel-control-prev,
        .carousel-control-next {
            animation: breathe-slider 2s ease-in-out infinite; /* Reduce el tiempo para que sea más rápida */
        }

        .text-subtotal {
            font-size: 12px;
        }

        .text-full_name {
            font-size: 13px;
            width: 180px;
        }
    </style>
@endsection

@section('content')
    <div id="auth-status" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"></div>
    <div class="container formulario">
        <div class="row mt-4 mb-4 ">
            <div class="col-md-6 order-md-2">
                <h4 class="d-flex justify-content-between align-items-center mb-3">

                    <h5 class="mb-3">
                        <span class="d-flex align-items-center">
                            <img src="{{ asset('/images/checkout/cart.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                            <strong>Tu pedido</strong>
                        </span>
                    </h5>
                    {{--<span class="badge badge-secondary badge-pill">{{ count($cart->details) }}</span>--}}
                </h4>
                <ul class="list-group mb-3">

                    <div id="body-items">
                        <div id="loading-indicator" style="display: none;">
                            <p>Cargando...</p>
                        </div>
                    </div>

                    <li id="info_code" class="list-group-item d-flex justify-content-between bg-light hidden" style="display: none;">
                        <div class="text-success">
                            <h6 class="my-0">Código de promoción</h6>
                            <small id="name_code">EXAMPLECODE</small>
                        </div>
                        <span class="text-success" id="amount_code">-$5</span>
                    </li>
                    <li id="info_shipping" class="list-group-item d-flex justify-content-between bg-light hidden">
                        <div class="text-danger">
                            <h6 class="my-0">Costo de Envío</h6>
                        </div>
                        <span class="text-danger" id="amount_shipping">+$5</span>
                    </li>
                    <li id="li_gravada_amount" class="list-group-item d-flex justify-content-between">
                        <span>GRAVADA </span>
                        <strong id="gravada_amount"></strong>
                    </li>
                    <li id="li_igv_amount" class="list-group-item d-flex justify-content-between">
                        <span>IGV </span>
                        <strong id="igv_amount"></strong>
                    </li>
                    <li id="li_total_amount" class="list-group-item d-flex justify-content-between">
                        <span>TOTAL </span>
                        <strong id="total_amount"></strong>
                    </li>
                </ul>

                <!-- Acordeón -->
                <div class="accordion" id="accordionPromo">
                    <!-- Sección del acordeón -->
                    <div class="card">
                        <div class="card-header p-2" id="headingOne">
                            <p class="mb-0 d-flex justify-content-between">
                                <!-- Título del acordeón -->
                                <span class="d-flex align-items-center">
                                    <img src="{{ asset('/images/checkout/cupon.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                                    ¿Tienes un cupon?
                                </span>
                                <!-- Link de agregar -->
                                <a href="#" class="btn btn-link p-0" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Agregar
                                </a>
                            </p>
                        </div>

                        <!-- Contenido del acordeón -->
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionPromo">
                            <div class="card-body">
                                <!-- Formulario para código de promoción -->
                                <div class="input-group">
                                    <input type="text" class="form-control" id="promo_code" placeholder="Código de Promoción">
                                    <div class="input-group-append">
                                        <button type="button" id="btn-promo_code" class="btn btn-success">Aplicar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--<div class="input-group">
                    <input type="text" class="form-control" id="promo_code" placeholder="Código de Promocíon">
                    <div class="input-group-append">
                        <button type="button" id="btn-promo_code" class="btn btn-secondary">Aplicar</button>
                    </div>
                </div>--}}
                {{--<a href="{{ route('home') }}" class="btn btn-light w-100 border mt-2"> Volver al menú </a>--}}
            </div>
            <div class="col-md-6 order-md-1 mt-3">
                <h5 class="mb-3">
                    <span class="d-flex align-items-center">
                        <img src="{{ asset('/images/checkout/pizza-deliver.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                        <strong>Datos de envío</strong>
                    </span>
                </h5>
                <form class="needs-validation" novalidate id="checkoutForm">
                    @csrf
                    <input type="hidden" name="cart_id" value="{{--{{ $cart->id }}--}}">
                    <input type="hidden" name="coupon_name" id="coupon_name" value="">



                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName">Nombres</label>
                            <input type="text" class="form-control" id="firstName" name="first_name" placeholder=""
                                   value="{{ $defaultAddress ? $defaultAddress->first_name : '' }}" required>
                            <div class="invalid-feedback">
                                Sus nombres son obligatorios.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName">Apellidos</label>
                            <input type="text" class="form-control" id="lastName" name="last_name" placeholder=""
                                   value="{{ $defaultAddress ? $defaultAddress->last_name : '' }}" required>
                            <div class="invalid-feedback">
                                Sus apellidos son obligatorios.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username">Teléfono</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            </div>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder=""
                                   value="{{ $defaultAddress ? $defaultAddress->phone : '' }}" required>
                            <div class="invalid-feedback" style="width: 100%;">
                                Su teléfono es obligatorio.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email">Correo Eletrónico</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope-open"></i></span>
                            </div>
                            <input type="email" class="form-control" id="email" name="email" placeholder=""
                                   value="{{ auth()->check() ? ($defaultAddress ? ($defaultAddress->email ?: auth()->user()->email) : auth()->user()->email) : '' }}" required>
                            <div class="invalid-feedback" style="width: 100%;">
                                Su email es obligatorio.
                            </div>
                        </div>
                    </div>

                    {{--<div class="mb-3">
                        <label for="district">Seleccione su Distrito</label>
                        <select class="form-control" name="district" id="district" required>
                            <option value="" selected>Ninguno</option>
                            @foreach( $districts as $district )
                            <option value="{{ $district->id }}" data-shipping_cost="{{ $district->shipping_cost }}">{{ $district->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" style="width: 100%;">
                            Su distrito es obligatorio.
                        </div>
                    </div>--}}

                    <div class="mb-3">
                        <label for="address">Dirección</label>
                        {{--<input type="text" class="form-control" id="address" name="address" placeholder=""
                               required value="{{ $defaultAddress ? $defaultAddress->address_line : '' }}">--}}
                        <input type="text" class="form-control" id="address" name="address" placeholder=""
                               required value="" readonly>
                        {{--<button type="button" class="btn btn-link" id="btn-selectAddress">
                            Encuentra tu dirección
                        </button>--}}
                        <a href="{{ route('showlocals') }}" class="btn btn-link">
                            Cambia tu dirección
                        </a>
                        <div class="invalid-feedback">
                            Por favor ingrese su dirección de envío.
                        </div>
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <input type="hidden" id="costShipping" name="costShipping">
                        <input type="hidden" id="shopId" name="shopId">
                    </div>

                    <div class="mb-3">
                        <label for="address2">Referencia <span class="text-muted">(Optional)</span></label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="Al costado de Ittsa"
                               value="{{ $defaultAddress ? $defaultAddress->reference : '' }}">
                    </div>

                    <hr class="mb-4">

                    <div class="accordion" id="accordionInvoice">
                        <!-- Sección del acordeón -->
                        <div class="card">
                            <div class="card-header p-2" id="headingOneInvoice">
                                <p class="mb-0 d-flex justify-content-between">
                                    <!-- Título del acordeón -->
                                    <span class="d-flex align-items-center">
                                    <img src="{{ asset('/images/checkout/invoice.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                                    ¿Necesitas Boleta o Factura?
                                </span>
                                    <!-- Link de agregar -->
                                    <a href="#" class="btn btn-link p-0" data-toggle="collapse" data-target="#collapseOneInvoice" aria-expanded="false" aria-controls="collapseOneInvoice">
                                        Solicitar
                                    </a>
                                </p>
                            </div>

                            <!-- Contenido del acordeón -->
                            <div id="collapseOneInvoice" class="collapse" aria-labelledby="headingOne" data-parent="#accordionInvoice">
                                <div class="card-body">
                                    <!-- Botones radio -->
                                    <div class="form-group">
                                        <label>Tipo de comprobante:</label><br>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="invoice_type" id="radio_none" value="ninguno" checked>
                                            <label class="form-check-label" for="radio_none">Sin comprobante</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="invoice_type" id="radio_boleta" value="boleta">
                                            <label class="form-check-label" for="radio_boleta">Boleta</label>
                                        </div>

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="invoice_type" id="radio_factura" value="factura">
                                            <label class="form-check-label" for="radio_factura">Factura</label>
                                        </div>
                                    </div>

                                    <!-- Campos para Boleta -->
                                    <div id="datos_boleta" class="d-none">
                                        <div class="form-group">
                                            <label for="dni">DNI <span style="color:red;">*</span></label>
                                            <input type="text" name="dni" class="form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label for="email_invoice_boleta">Email (Opcional)</label>
                                            <input type="text" name="email_invoice_boleta" class="form-control">
                                        </div>
                                    </div>

                                    <!-- Campos para Factura -->
                                    <div id="datos_factura" class="d-none">
                                        <div class="form-group">
                                            <label for="ruc">RUC <span style="color:red;">*</span></label>
                                            <input type="text" name="ruc" class="form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label for="razon_social">Razón Social <span style="color:red;">*</span></label>
                                            <input type="text" name="razon_social" class="form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label for="direccion_fiscal">Dirección Fiscal <span style="color:red;">*</span></label>
                                            <input type="text" name="direccion_fiscal" class="form-control" >
                                        </div>
                                        <div class="form-group">
                                            <label for="email_invoice_factura">Email (Opcional)</label>
                                            <input type="text" name="email_invoice_factura" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <h5 class="mb-3" id="title-method">
                        <span class="d-flex align-items-center">
                            <img src="{{ asset('/images/checkout/payment-method.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                            <strong>Elige tu método de pago</strong>
                        </span>
                    </h5>
                    {{--<div class="d-block my-3">
                        @foreach( $payment_methods as $method )
                            <div class="custom-control custom-radio">
                                <input id="method_{{$method->code}}" name="paymentMethod" type="radio" value="{{$method->id}}" class="custom-control-input payment-method" required data-code="{{ $method->code }}">
                                <label class="custom-control-label" for="method_{{$method->code}}">{{$method->name}}</label>
                            </div>
                        @endforeach
                    </div>--}}

                    <div id="payment-slider" class="carousel slide w-100 mx-auto" data-ride="carousel" data-interval="false">
                        <div class="carousel-inner">
                            @foreach($payment_methods as $index => $method)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div id="{{$method->code}}" class="h-100 w-100 d-flex flex-column justify-content-center align-items-center">
                                    <img src="{{ asset('/images/checkout/'.$method->image) }}" alt="{{$method->name}}" style="width: 100%; height: auto; border-radius: 20px;">
                                    <input type="radio" name="paymentMethod" value="{{$method->id}}" id="method_{{$method->code}}" class="d-none" data-code="{{ $method->code }}">
                                </div>
                            </div>
                            @endforeach

                        </div>

                        <!-- Controles -->
                        <a class="carousel-control-prev" href="#payment-slider" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a class="carousel-control-next" href="#payment-slider" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Siguiente</span>
                        </a>
                    </div>

                    <!-- Sección para método de pago en POS -->
                    <div id="pos-section" style="display: none; margin-top: 15px; text-align: center">
                        <label for="cashAmount">Nuestro repartidor está llevando el POS para que puedas pagar con cualquier tarjeta de Débito o Crédito</label>
                    </div>

                    <!-- Sección para método de pago en efectivo -->
                    <div id="cash-section" style="display: none; margin-top: 15px;">
                        <label for="cashAmount">Monto con el que paga</label>
                        <input type="number" class="form-control" step="0.01" min="0" id="cashAmount" placeholder="Ingrese monto en efectivo">
                    </div>

                    <!-- Sección para método de pago Yape/Plin -->
                    <div id="yape-section" style="display: none; margin-top: 15px;">

                        <p style="font-size: 0.9rem">Paga con Yape o Plin usando nuestro número: <br><strong><span id="yape-phone" class="font-weight-bold mr-2 ">906343258</span></strong>
                            <!-- Botón para copiar al portapapeles -->
                            <button type="button" id="copy-phone" class="btn btn-link p-0" title="Copiar número">
                                <i class="fas fa-copy"></i>
                            </button>
                        </p>

                        <p>O escanea el código QR para pagar:</p>
                        <div class="text-center">
                            <img src="{{ asset('images/checkout/qr_yape.webp') }}" alt="QR para Yape/Plin" style="width: 100%; height: auto; border-radius: 20px">
                        </div>

                        <label for="operationCode">Código de operación
                            <!-- Botón de información -->
                            <button type="button" id="info-button" class="btn btn-link p-0 info-button" title="¿Dónde encuentro el número de operación?">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </label>

                        <input type="text" class="form-control" id="operationCode" placeholder="Ingrese el código de operación">
                    </div>

                    <!-- Sección para método de pago Mercado Pago -->
                    {{--<div id="mercado_pago-section" style="display: none; margin-top: 15px;">

                            <input type="hidden" name="cart_id" value="{{ $cart->id }}">
                            <!-- Información del titular de la tarjeta -->
                            <div class="mb-3">
                                <label for="address">Número de la tarjeta</label>
                                <input type="text" class="form-control" name="cardNumber" id="cardNumber" placeholder="Número de la tarjeta" data-checkout="cardNumber" required />
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="cardExpirationMonth">Mes de expiración</label>
                                        <input type="text" class="form-control" name="cardExpirationMonth" id="cardExpirationMonth" placeholder="Mes de expiración" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="cardExpirationYear">Año de expiración</label>
                                        <input type="text" class="form-control" name="cardExpirationYear" id="cardExpirationYear" placeholder="Año de expiración" required />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="securityCode">Código de seguridad</label>
                                        <input type="text" class="form-control" name="securityCode" id="securityCode" placeholder="Código de seguridad" required />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="cardholderName">Nombre del titular</label>
                                <input type="text" class="form-control" name="cardholderName" id="cardholderName" placeholder="Nombre del titular" required />
                            </div>
                            <div class="mb-3">
                                <label for="cardholderEmail">Email</label>
                                <input type="email" class="form-control" name="cardholderEmail" id="cardholderEmail" placeholder="Email" required />
                            </div>

                        <div class="mb-3">
                            <label for="identificationType">Tipo de documento</label>
                            <select class="form-control" name="identificationType" id="identificationType">
                                <option value="DNI" selected>DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carné de extranjería</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="identificationNumber">Número de documento</label>
                            <input type="text" class="form-control" name="identificationNumber" id="identificationNumber" placeholder="Número de documento" required />
                        </div>

                            <!-- Campo para cuotas -->
                            <div class="mb-3">
                                <label for="installments">Cuotas</label>
                                <select class="form-control" name="installments" id="installments" >
                                    <option value="">Seleccione cuotas</option>
                                </select>
                            </div>

                            <!-- Campo para banco emisor -->
                            <div class="mb-3">
                                <label for="issuer">Banco Emisor</label>
                                <select class="form-control" name="issuer" id="issuer" >
                                    <option value="">Seleccione el banco</option>
                                </select>
                            </div>


                            <!-- Token de tarjeta -->
                            <input type="hidden" name="token" id="token" />

                    </div>--}}
                    <hr class="mb-4">



                    <button class="btn btn-primary btn-lg btn-block button-submit" type="button" id="btn-submit">COMPRAR</button>
                </form>
            </div>
        </div>
    </div>

    <div class="mobile-fixed-cart d-lg-none">
        <button class="btn btn-danger btn-block py-3" id="btn-submit-mobile">
            COMPRAR
            <span class="h5">
                <span id="total-price-mobile"></span>
            </span>
        </button>
    </div>

    <template id="template-detail">
        <li class="list-group-item d-flex justify-content-between lh-condensed">
            <div>
                <img data-image src="{{--{{ asset('images/products/'.$detail->product->image) }}--}}" alt="{{--{{ $detail->product->full_name }}--}}" class="img-thumbnail mr-2" style="width: 50px; height: 50px; object-fit: cover;">
            </div>
            <div data-full_name class="text-full_name">
                {{--{{ $detail->product->full_name }} x{{ $detail->quantity }}--}}
            </div>
            <span class="text-subtotal" data-subtotal>{{--S/ {{ number_format($detail->subtotal, 2, '.', '') }}--}}</span>
        </li>
    </template>

    <!-- Modal de atención -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">¡Atención!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Para aplicar cupones, debes iniciar sesión en tu cuenta.</p>
                    <p>¿Aún no tienes una cuenta? ¡Regístrate ahora y aprovecha nuestras promociones exclusivas!</p>
                </div>
                <div class="modal-footer">
                    <button id="btn-login" class="btn btn-primary">Iniciar Sesión</button>
                    <button id="btn-register" class="btn btn-success">Registrarse</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyModalLabel">¡Atención! 📢</h5>
                </div>
                <div class="modal-body">
                    <p>Antes de finalizar tu compra, asegúrate de que los datos en los campos de celular <strong><span id="showPhone"></span></strong> y correo electrónico <strong><span id="showEmail"></span></strong> estén correctos.</p>

                    <p class="mb-0">✅ Estos son los medios que utilizaremos para informarte sobre el estado de tu pedido y coordinar la entrega. 🚗</p>
                </div>
                <div class="modal-footer">
                    <button id="btn-continue" class="btn btn-primary">Continuar</button>
                    <button id="btn-cancel" class="btn btn-secondary closeModalVerify">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Encuentra tu dirección</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Campo de búsqueda -->
                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Escribe tu dirección...">
                    <!-- Mapa -->
                    <div id="map" style="height: 400px; width: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="selectAddress">Seleccionar esta dirección</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Bootstrap -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Número de transacción</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ asset('images/checkout/numeroOperacion.jpg') }}" alt="Ejemplo de número de transacción" class="img-fluid w-75">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    {{--<script src="https://sdk.mercadopago.com/js/v2"></script>--}}
    {{--<script src="{{ asset('js/cart/cart.js') }}"></script>--}}
    <script src="{{ asset('js/cart/checkoutMovil.js') }}?v={{ time() }}"></script>
    <!-- Carga la API de Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo&libraries=places&callback=initAutocomplete" async defer></script>
    <script>
        let map, marker, infowindow, autocomplete;

        // Función para inicializar el mapa
        function initAutocomplete() {
            console.log("Google Maps API cargada correctamente.");

            // Inicializamos el mapa en la Plaza de Armas de Trujillo, Perú
            const trujilloLatLng = { lat: -8.1132, lng: -79.0290 }; // Coordenadas de la Plaza de Armas de Trujillo
            map = new google.maps.Map(document.getElementById("map"), {
                center: trujilloLatLng,
                zoom: 14,
            });

            // Creamos el marcador
            marker = new google.maps.Marker({
                position: trujilloLatLng,
                map: map,
                draggable: true, // Permitimos que el marcador sea arrastrado
                title: "Arrastra el marcador para cambiar la dirección"
            });

            // Creamos el infowindow
            infowindow = new google.maps.InfoWindow();

            // Mostrar el infowindow con la dirección actual del marcador
            google.maps.event.addListener(marker, "dragend", function() {
                updateMarkerPosition(marker.getPosition());
            });

            // Permitir colocar el marcador al hacer clic en el mapa
            map.addListener("click", function(event) {
                marker.setPosition(event.latLng);
                updateMarkerPosition(event.latLng);
            });

            // Inicializar el Autocomplete
            const input = $("#searchInput")[0];
            autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo("bounds", map);

            // Escuchar el evento cuando se seleccione una dirección
            autocomplete.addListener("place_changed", function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    alert("No se encontró información para esta dirección.");
                    return;
                }

                // Colocamos el marcador en la nueva dirección
                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                updateMarkerPosition(place.geometry.location);
            });

            // Evento para el botón "Seleccionar esta dirección"
            $("#selectAddress").on("click", function() {
                // Obtener la dirección y las coordenadas del marcador
                const address = $("#searchInput").val();
                const latLng = marker.getPosition();
                const latitude = latLng.lat();
                const longitude = latLng.lng();

                // Colocar los valores en los campos de entrada
                $("#address").val(address);      // Dirección en el campo de texto
                $("#latitude").val(latitude);    // Latitud en el campo oculto
                $("#longitude").val(longitude);  // Longitud en el campo oculto

                $("#addressModal").modal("hide");
            });
        }

        // Actualiza el valor del input y muestra la dirección en el infowindow
        function updateMarkerPosition(latLng) {
            // Usamos geocoding para obtener la dirección a partir de las coordenadas
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === "OK" && results[0]) {
                    const address = results[0].formatted_address;

                    // Establecemos la dirección en el input de búsqueda
                    $("#searchInput").val(address);

                    // Creamos el contenido HTML para el InfoWindow
                    const contentString = `
                        <div style="font-family: Arial, sans-serif;">
                            <div style="font-size: 14px; font-weight: bold; color: #000;">Dirección:</div>
                            <div style="font-size: 16px; font-weight: bold; color: #007BFF;">${address}</div>
                        </div>
                    `;

                    // Establecemos el contenido en el infowindow
                    infowindow.setContent(contentString);
                    infowindow.open(map, marker);

                    // Reducir el tamaño del botón de cerrar (X) después de abrir el infowindow
                    google.maps.event.addListenerOnce(infowindow, 'domready', function() {
                        // Seleccionar el botón de cerrar (la "X")
                        const closeButton = document.querySelector('.gm-ui-hover-effect');

                        // Aplicar un estilo más pequeño al botón de cierre
                        if (closeButton) {
                            closeButton.style.fontSize = '8px';  // Cambiar tamaño de la "X"
                            closeButton.style.width = '50px';     // Ajustar el tamaño del botón
                            closeButton.style.height = '50px';    // Ajustar el tamaño del botón
                        }
                    });
                }
            });
        }

        // Inicializar el mapa y la funcionalidad de autocomplete al cargar el script
        window.initAutocomplete = initAutocomplete;
    </script>

@endsection
