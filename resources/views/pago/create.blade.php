@extends('layouts.admin')

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('styles')
    <style>
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

        @keyframes breathe-slider {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); } /* Aumenta más la escala */
            100% { transform: scale(1); }
        }

        .carousel-control-prev,
        .carousel-control-next {
            animation: breathe-slider 2s ease-in-out infinite; /* Reduce el tiempo para que sea más rápida */
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            {{-- IZQUIERDA: Items --}}
            <div class="col-12 col-lg-7 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center">
                        <a href="{{ url()->previous() }}" class="btn btn-light btn-sm mr-2">&larr;</a>
                        <img src="{{ asset('/images/checkout/restaurante.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">

                        <strong>Pedido @if($esExterno) (Externo) @else (Mesa) @endif</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="lst-items">
                            @forelse($items as $it)
                                @php
                                    $unit = (float)$it->precio_unit;
                                    $rest = (int)$it->restante;
                                @endphp

                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        @if(!$esExterno)
                                            <div class="custom-control custom-checkbox mr-2">
                                                <input type="checkbox" class="custom-control-input item-check" id="chk-{{ $it->id }}" data-id="{{ $it->id }}">
                                                <label class="custom-control-label" for="chk-{{ $it->id }}"></label>
                                            </div>
                                        @endif

                                        <div>
                                            <div class="font-weight-bold">{{ $it->nombre ?? ($it->producto->nombre ?? 'Prod #'.$it->id) }}</div>
                                            <small class="text-muted">
                                                Cant: {{ $it->cantidad }} &middot; Restante: <span class="rest-{{ $it->id }}">{{ $rest }}</span>
                                                &middot; S/ {{ number_format($unit,2) }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        {{-- Selector de qty a pagar (para Mesas: habilitado cuando se checkea; para Externo: oculto y fijo al restante) --}}
                                        @if($esExterno)
                                            <input type="number" class="form-control form-control-sm qty-input"
                                                   value="{{ $rest }}" min="0" max="{{ $rest }}"
                                                   data-id="{{ $it->id }}" readonly style="width:90px;">
                                        @else
                                            <input type="number" class="form-control form-control-sm qty-input"
                                                   value="0" min="0" max="{{ $rest }}"
                                                   data-id="{{ $it->id }}" style="width:90px;" disabled>
                                        @endif
                                        <span class="ml-2 text-nowrap">S/ <span class="line-sub" data-id="{{ $it->id }}">0.00</span></span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-3 text-center text-muted">No hay productos pendientes.</div>
                            @endforelse
                        </div>
                    </div>

                    @if($esExterno)
                        <div class="card-footer">
                            <small class="text-muted">En pedidos externos se pagan todos los productos pendientes.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- DERECHA: Detalle de Pago --}}
            <div class="col-12 col-lg-5 mb-3">
                <form id="frmPago" action="{{ route('pagos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="atencion_id" value="{{ $atencion->id }}">
                    <input type="hidden" name="items_payload" id="items_payload"> {{-- JSON serializado --}}

                    <div class="card">
                        <div class="card-header">
                            <img src="{{ asset('/images/checkout/payment-method.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 10px;">
                            <strong>Detalle de pago</strong>
                        </div>
                        <div class="card-body">
                            {{-- Cliente --}}
                            <div class="form-group">
                                <img src="{{ asset('/images/checkout/cliente.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 5px;">

                                <label>Cliente</label>
                                <div class="input-group">
                                    <select name="cliente_id" id="cliente_id" data-url="{{route('clientes.index')}}" class="form-control select2-clientes" style="width: 80%">
                                        <option value="">— Selecciona un cliente —</option>
                                        @foreach($clientes as $c)
                                            <option value="{{ $c->id }}">{{ $c->nombre }} {{ $c->num_doc ? '('.$c->num_doc.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalCliente">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Descuento --}}
                            <div class="form-row">
                                <div class="form-group col-5">
                                    <img src="{{ asset('/images/checkout/cupon-de-descuento.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 5px;">

                                    <label>Descuento</label>
                                    <select name="descuento_tipo" id="descuento_tipo" class="form-control">
                                        <option value="">Sin descuento</option>
                                        <option value="porc">% Porcentaje</option>
                                        <option value="fijo">S/ Fijo</option>
                                    </select>
                                </div>
                                <div class="form-group col-7">
                                    <label>&nbsp;</label>
                                    <input type="number" step="0.01" min="0" value="0" name="descuento_val" id="descuento_val" class="form-control">
                                </div>
                            </div>

                            {{-- Propina --}}
                            <div class="form-row">
                                <div class="form-group col-5">
                                    <img src="{{ asset('/images/checkout/propina.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 5px;">

                                    <label>Propina</label>
                                    <select name="propina_tipo" id="propina_tipo" class="form-control">
                                        <option value="">Sin propina</option>
                                        <option value="porc">% Porcentaje</option>
                                        <option value="fijo">S/ Fijo</option>
                                    </select>
                                </div>
                                <div class="form-group col-7">
                                    <label>&nbsp;</label>
                                    <input type="number" step="0.01" min="0" value="0" name="propina_val" id="propina_val" class="form-control">
                                </div>
                            </div>

                            {{-- Método de pago --}}
                            <div class="form-group">
                                <img src="{{ asset('/images/checkout/metodo-de-pago.png') }}" alt="Cupon" style="width: 30px; height: 30px; margin-right: 5px;">

                                <label>Método de pago</label>
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
                                    <label for="cashAmount">Nuestro mozo está llevando el POS para que puedas pagar con cualquier tarjeta de Débito o Crédito</label>
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
                            </div>

                            <hr>
                            {{-- Totales --}}
                            <div class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <strong>S/ <span id="subtotal">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Descuento</span>
                                <strong>- S/ <span id="desc">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Propina</span>
                                <strong>+ S/ <span id="prop">0.00</span></strong>
                            </div>
                            <div class="d-flex justify-content-between h5 mt-2">
                                <span>Total a pagar</span>
                                <strong>S/ <span id="total">0.00</span></strong>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block" id="btnPagar">
                                GENERAR COMPROBANTE — S/ <span id="btnTotal">0.00</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Agregar Cliente --}}
    <div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="frmCliente" class="modal-content" data-action="{{ route('clientes.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClienteLabel">Agregar Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-4">
                            <label>Tipo Doc.</label>
                            <select name="tipo_doc" class="form-control">
                                <option value="">—</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                        </div>
                        <div class="form-group col-8">
                            <label>N° Documento</label>
                            <input type="text" name="num_doc" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarCliente" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('plugins')
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        /*$(function () {
            //Initialize Select2 Elements
            $('#cliente_id').select2({
                placeholder: "Selecione cliente",
                allowClear: true,
            });

        })*/
    </script>
    <script>
        window.ES_EXTERNO = {!! $esExterno ? 'true' : 'false' !!};
        window.ITEMS = {!! $items->map(function($i){
        return [
            'id' => (int)$i->id,
            'precio' => (float)$i->precio_unit,
            'restante' => (int)$i->restante
        ];
    })->values()->toJson() !!};

        // Rutas para AJAX
        window.routesClientesIndex = "{{ route('clientes.index') }}";
        window.routesClientesStore = "{{ route('clientes.store') }}";
    </script>
    <script src="{{ asset('js/pago/create.js') }}"></script>
@endsection