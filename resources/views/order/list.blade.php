@extends('layouts.admin')

@section('openOrders')
    menu-open
@endsection

@section('activeOrders')
    active
@endsection

@section('activeListOrders')
    active
@endsection

@section('title')
    Pedidos de clientes
@endsection

@section('styles-plugins')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/bootstrap-datepicker/css/bootstrap-datepicker.standalone.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.css') }}">

@endsection

@section('styles')
    <style>
        .select2-search__field{
            width: 100% !important;
        }
        .letraTabla {
            font-family: "Calibri", Arial, sans-serif; /* Utiliza Calibri si está instalado, de lo contrario, usa Arial o una fuente sans-serif similar */
            font-size: 15px; /* Tamaño de fuente 11 */
        }
        .normal-title {
            background-color: #203764; /* Color deseado para el fondo */
            color: #fff; /* Color deseado para el texto */
            text-align: center;
        }
        .cliente-title {
            background-color: #FFC000; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .trabajo-title {
            background-color: #00B050; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .documentacion-title {
            background-color: #FFC000; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .importe-title {
            background-color: #00B050; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .facturacion-title {
            background-color: #FFC000; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .abono-title {
            background-color: #00B050; /* Color deseado para el fondo */
            color: #000; /* Color deseado para el texto */
            text-align: center;
        }
        .busqueda-avanzada {
            display: none;
        }

        #btnBusquedaAvanzada {
            display: inline-block;
            text-decoration: none;
            color: #007bff;
            border-bottom: 1px solid transparent;
            transition: border-bottom 0.3s ease;
        }
        #btnBusquedaAvanzada:hover {
            border-bottom: 2px solid #007bff;
        }
        .vertical-center {
            display: flex;
            align-items: center;
        }
        .datepicker-orient-top {
            top: 100px !important;
        }
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Pedidos de Clientes</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Listado de pedidos</h5>

    {{--@can('create_referralGuide')
        <a href="{{ route('referral.guide.create') }}" class="btn btn-outline-success btn-sm float-right" > <i class="fa fa-plus font-20"></i> Nueva Guía de remisión </a>
    @endcan
    @can('download_referralGuide')
        <button type="button" id="btn-download" class="btn btn-outline-success btn-sm float-right mr-2" > <i class="fas fa-download"></i> Exportar guías</button>
    @endcan--}}
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('orders.list') }}"><i class="fa fa-archive"></i> Pedidos Clientes</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Listado</li>
    </ol>
@endsection

@section('content')
    {{--<input type="hidden" id="permissions" value="{{ json_encode($permissions) }}">--}}
    <!--begin::Form-->
    <form action="#">
        <!--begin::Card-->
        <!--begin::Input group-->
        <div class="row">
            <div class="col-md-12">
                <!-- Barra de búsqueda -->
                <div class="input-group">
                    <input type="text" id="code" class="form-control" placeholder="Código del pedido..." autocomplete="off">
                    <div class="input-group-append ">
                        <button class="btn btn-primary" type="button" id="btn-search">Buscar</button>
                        <a href="#" id="btnBusquedaAvanzada" class="vertical-center ml-3 mt-2">Búsqueda Avanzada</a>
                    </div>
                </div>

                <!-- Sección de búsqueda avanzada (inicialmente oculta) -->
                <div class="mt-3 busqueda-avanzada">
                    <!-- Aquí coloca más campos de búsqueda avanzada -->
                    <div class="row">
                        <div class="col-md-3">
                            <label for="year">Año de registro:</label>
                            <select id="year" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">TODOS</option>
                                @for ($i=0; $i<count($arrayYears); $i++)
                                    <option value="{{ $arrayYears[$i] }}">{{ $arrayYears[$i] }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="campoExtra">Fechas del pedido:</label>
                            <div class="col-md-12" id="sandbox-container">
                                <div class="input-daterange input-group" id="datepicker">
                                    <input type="text" class="form-control form-control-sm date-range-filter" id="start" name="start" autocomplete="off">
                                    <span class="input-group-addon">&nbsp;&nbsp;&nbsp; al &nbsp;&nbsp;&nbsp; </span>
                                    <input type="text" class="form-control form-control-sm date-range-filter" id="end" name="end" autocomplete="off">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Añade más campos según lo necesario -->
                </div>
            </div>
        </div>
        <!--end::Input group-->

    </form>
    <!--end::Form-->

    <!--begin::Toolbar-->
    <div class="d-flex flex-wrap flex-stack pb-7">
        <!--begin::Title-->
        <div class="d-flex flex-wrap align-items-center my-1">
            <h3 class="fw-bolder me-5 my-1"><span id="numberItems"></span> Pedidos encontrados
                <span class="text-gray-400 fs-6">por fecha de creación ↓ </span>
            </h3>
        </div>
        <!--end::Title-->
    </div>
    <!--end::Toolbar-->

    <!--begin::Tab Content-->
    <div class="tab-content">
        <!--begin::Tab pane-->
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered letraTabla table-hover table-sm mb-5">
                <thead>
                <tr class="normal-title">
                    {{--<th>ID</th>--}}
                    <th>Código</th>
                    <th>Fecha Pedido</th>
                    <th>Fecha Entrega</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Total</th>
                    <th>Metodo de Pago</th>
                    <th>Dato de Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody id="body-table">

                </tbody>
            </table>
        </div>
        <!--end::Tab pane-->
        <!--begin::Pagination-->
        <div class="d-flex flex-stack flex-wrap pt-1">
            <div class="fs-6 fw-bold text-gray-700" id="textPagination"></div>
            <!--begin::Pages-->
            <ul class="pagination" style="margin-left: auto;" id="pagination">

            </ul>
            <!--end::Pages-->
        </div>
        <!--end::Pagination-->
    </div>
    <!--end::Tab Content-->

    <template id="previous-page">
        <li class="page-item previous">
            <a href="#" class="page-link" data-item>
                <!--<i class="previous"></i>-->
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    </template>

    <template id="item-page">
        <li class="page-item" data-active>
            <a href="#" class="page-link" data-item="">5</a>
        </li>
    </template>

    <template id="next-page">
        <li class="page-item next">
            <a href="#" class="page-link" data-item>
                <!--<i class="next"></i>-->
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    </template>

    <template id="disabled-page">
        <li class="page-item disabled">
            <span class="page-link">...</span>
        </li>
    </template>

    <template id="item-table">
        <tr>
            {{--<td data-id></td>--}}
            <td data-code></td>
            <td data-date></td>
            <td data-date_delivery></td>
            <td data-customer></td>
            <td data-phone></td>
            <td data-address></td>
            <td data-total></td>
            <td data-method></td>
            <td data-data_payment></td>
            <td data-state></td>
            <td data-buttons></td>
        </tr>
    </template>

    <template id="item-table-empty">
        <tr>
            <td colspan="11" align="center">No se ha encontrado ningún dato</td>
        </tr>
    </template>

    <template id="template-active">
        {{--<button data-recibido data-id="" data-state="created" data-state_name="RECIBIDO" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Recibido"><i class="far fa-file-alt"></i></button>
        --}}{{-- El target blank es el recibido Imprimir comanda --}}
        <a href="" target="_blank" data-print_comanda data-id="" class="btn btn-outline-dark btn-sm" data-toggle="tooltip" data-placement="top" title="Imprimir comanda"><i class="fas fa-print"></i></a>
        <button data-cocinando data-id="" data-state="processing" data-state_name="COCINANDO" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-placement="top" title="Cocinando"><i class="fas fa-fire"></i></button>
        <a href="" target="_blank" data-print_nota data-id="" class="btn btn-outline-dark btn-sm" data-toggle="tooltip" data-placement="top" title="Imprimir boleta"><i class="fas fa-print"></i></a>
        <button data-enviando data-id="" data-state="shipped" data-state_name="ENVIADO" class="btn btn-outline-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Enviando"><i class="fa fa-truck"></i></button>
        <button data-completado data-id="" data-state="completed" data-state_name="COMPLETADO" class="btn btn-outline-success btn-sm" data-toggle="tooltip" data-placement="top" title="Completado"><i class="fas fa-home"></i></button>
        <button data-ver_detalles data-id="" class="btn btn-outline-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalles"><i class="fas fa-list-ol"></i></button>
        <button data-ver_ruta data-id="" data-address="" data-latitude="" data-longitude="" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Ruta"><i class="fas fa-map-marker-alt"></i></button>
        <button data-ver_ruta_map data-id="" data-address="" data-latitude="" data-longitude="" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Ruta"><i class="fas fa-map-marker-alt" style="color: #e60a0a;"></i></button>
        <button data-anular data-id="" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Anular Orden"><i class="fas fa-trash-alt"></i></button>

        <a href="" target="_blank" data-btn_whatsapp data-id="" class="btn btn-outline-success btn-sm" data-toggle="tooltip" data-placement="top" title="Enviar whatsapp"><i class="fab fa-whatsapp"></i></a>

        <button data-facturador data-id="" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Generar comprobante">
            <img src="{{ asset('images/icons/facturacion_electronica.png') }}" alt="Generar" style="width: 16px; height: 16px;">
        </button>

        {{--<button data-generar_comprobante class="btn btn-primary btn-generar-comprobante" data-order-id="">
            Generar Comprobante
        </button>
        <button data-imprimir_comprobante class="btn btn-secondary btn-imprimir-comprobante" data-order-id="">
            Descargar/Imprimir
        </button>--}}
    </template>

    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderDetailsModalLabel">Detalles del Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se cargarán los detalles dinámicamente -->
                    <div id="order-details-content"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Ruta Generada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="mapContainer" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFacturador" tabindex="-1" role="dialog" aria-labelledby="modalFacturadorLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalFacturadorLabel">Datos del comprobante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Aquí va el cuerpo dinámico -->
                    <form id="formFacturador">
                        <input type="hidden" name="order_id" id="order_id">

                        <div class="form-group">
                            <label>Tipo de comprobante:</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="invoice_type" id="radio_boleta" value="boleta">
                                <label class="form-check-label" for="radio_boleta">Boleta</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="invoice_type" id="radio_factura" value="factura">
                                <label class="form-check-label" for="radio_factura">Factura</label>
                            </div>
                        </div>

                        <!-- Datos boleta -->
                        <div id="datos_boleta" class="d-none">
                            <div class="form-group">
                                <label for="dni">DNI <span style="color:red;">*</span></label>
                                <input type="text" name="dni" id="dni" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email_invoice_boleta">Email (Opcional)</label>
                                <input type="text" name="email_invoice_boleta" id="email_invoice_boleta" class="form-control">
                            </div>
                        </div>

                        <!-- Datos factura -->
                        <div id="datos_factura" class="d-none">
                            <div class="form-group">
                                <label for="ruc">RUC <span style="color:red;">*</span></label>
                                <input type="text" name="ruc" id="ruc" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="razon_social">Razón Social <span style="color:red;">*</span></label>
                                <input type="text" name="razon_social" id="razon_social" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="direccion_fiscal">Dirección Fiscal <span style="color:red;">*</span></label>
                                <input type="text" name="direccion_fiscal" id="direccion_fiscal" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="email_invoice_factura">Email (Opcional)</label>
                                <input type="text" name="email_invoice_factura" id="email_invoice_factura" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button id="btnGuardarDatos" type="button" class="btn btn-secondary">Guardar datos</button>
                    <button id="btnGenerarComprobante" type="button" class="btn btn-warning">Generar comprobante</button>

                </div>
                <div id="downloadSection" class="mt-3 text-center d-none">
                    <a id="btnDescargarPDF" href="#" target="_blank" class="btn btn-success">
                        Descargar PDF del Comprobante
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('plugins')
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js') }}"></script>
@endsection

@section('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo"></script>
    <script>
        $(function () {
            //Initialize Select2 Elements
            $('#year').select2({
                placeholder: "Selecione año",
                allowClear: true
            });

            $(document).on("click", '[data-ver_ruta_map]', function () {
                console.log("Botón clicado"); // Asegúrate de que este mensaje aparezca en la consola
                let latitude = $(this).data("latitude");
                let longitude = $(this).data("longitude");

                if (latitude && longitude) {
                    // Construir la URL de Google Maps
                    const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}&z=15`;

                    // Abrir la URL en una nueva pestaña
                    window.open(googleMapsUrl, "_blank");
                } else {
                    alert("No se encontraron coordenadas.");
                }
            });

            $(document).on("click", '[data-ver_ruta]', function () {
                console.log("Botón clicado"); // Asegúrate de que este mensaje aparezca en la consola
                // Obtener datos del botón
                const $button = $(this);
                const latitude = $button.data("latitude");
                const longitude = $button.data("longitude");
                const address = $button.data("address");

                /*const mockLatitude = -8.118733;
                const mockLongitude = -79.006608;*/

                // Coordenadas de ejemplo para pruebas
                /*const mockLatitude = -8.115324;
                const mockLongitude = -79.030759;*/
                // Obtener la ubicación actual del usuario
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const currentLatitude = position.coords.latitude;
                            const currentLongitude = position.coords.longitude;
                            // Coordenadas de ejemplo para pruebas
                            //const mockLatitude = -8.115324;
                            //const mockLongitude = -79.030759;

                            if (latitude && longitude) {
                                // Si hay coordenadas, mostrar la ruta
                                showRoute(currentLatitude, currentLongitude, parseFloat(latitude), parseFloat(longitude));
                                //showRoute(mockLatitude, mockLongitude, parseFloat(latitude), parseFloat(longitude));
                            } else if (address) {
                                // Si no hay coordenadas, buscar la dirección
                                geocodeAddress(address, currentLatitude, currentLongitude);
                            } else {
                                alert("No hay datos suficientes para generar una ruta.");
                            }
                        },
                        function (error) {
                            console.error("Error obteniendo la ubicación:", error);
                        }
                    );
                } else {
                    alert("Geolocalización no soportada en este navegador.");
                }

                /*if (latitude && longitude) {
                    showRoute(mockLatitude, mockLongitude, parseFloat(latitude), parseFloat(longitude));
                } else if (address) {
                    geocodeAddress(address, mockLatitude, mockLongitude);
                } else {
                    alert("No hay datos suficientes para generar una ruta.");
                }*/
            });

            function geocodeAddress(address, originLat, originLng) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: address }, function (results, status) {
                    if (status === "OK") {
                        const destination = results[0].geometry.location;
                        showRoute(originLat, originLng, destination.lat(), destination.lng());
                    } else {
                        alert("No se pudo encontrar la dirección: " + status);
                    }
                });
            }

            function showRoute(originLat, originLng, destLat, destLng) {
                // Crear el mapa
                const map = new google.maps.Map(document.getElementById("mapContainer"), {
                    center: { lat: originLat, lng: originLng },
                    zoom: 14,
                });

                // Configurar servicios de direcciones
                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer();
                directionsRenderer.setMap(map);

                // Generar la ruta
                directionsService.route(
                    {
                        origin: { lat: originLat, lng: originLng },
                        destination: { lat: destLat, lng: destLng },
                        travelMode: "DRIVING",
                    },
                    function (response, status) {
                        if (status === "OK") {
                            directionsRenderer.setDirections(response);
                            // Mostrar el modal
                            $("#mapModal").modal("show");
                        } else {
                            alert("No se pudo generar la ruta: " + status);
                        }
                    }
                );
            }
        })
    </script>
    <script src="{{ asset('js/order/list.js') }}?v={{ time() }}"></script>

@endsection