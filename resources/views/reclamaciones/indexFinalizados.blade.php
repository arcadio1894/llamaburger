@extends('layouts.admin')

@section('openHelpCenter')
    menu-open
@endsection

@section('activeReclamos')
    active
@endsection

@section('activeReclamosDelete')
    active
@endsection

@section('title')
    Reclamos
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
    <h1 class="page-title">Reclamos</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Listado de reclamos</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-archive"></i> Reclamos </li>
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
                    <input type="text" id="codigo" class="form-control" placeholder="Código del reclamo..." autocomplete="off">
                    <div class="input-group-append ">
                        <button class="btn btn-primary" type="button" id="btn-search">Buscar</button>
                        <a href="#" id="btnBusquedaAvanzada" class="vertical-center ml-3 mt-2">Búsqueda Avanzada</a>
                    </div>
                </div>

                <!-- Sección de búsqueda avanzada (inicialmente oculta) -->
                <div class="mt-3 busqueda-avanzada">
                    <!-- Aquí coloca más campos de búsqueda avanzada -->
                    <div class="form-row">
                        <div class="col-md-4">
                            <label for="tipo_reclamo">Tipo Reclamación:</label>
                            <select id="tipo_reclamo" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">TODOS</option>
                                <option value="Reclamo">Reclamo</option>
                                <option value="Queja">Queja</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="quote">Documento:</label>
                            <input type="text" id="documento" class="form-control form-control-sm" placeholder="11111111" autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label for="canal">Canal:</label>
                            <select id="canal" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">Seleccione canal</option>
                                <option value="web">Tienda Virtual (fuegoymasa.com)</option>
                                <option value="whatsapp">Whatsapp</option>
                                <option value="movil">Aplicación Móvil</option>
                            </select>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="motivo">Motivo:</label>
                            <select class="form-control form-control-sm select2" id="motivo" name="motivo" required>
                                <option value=""></option>
                                @foreach($motivos as $motivo)
                                    <option value="{{ $motivo->id }}">{{ $motivo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="submotivo">Submotivo:</label>
                            <select class="form-control form-control-sm select2" id="submotivo" name="submotivo" required>
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <br>

                    <!-- Añade más campos según lo necesario -->
                </div>
            </div>
        </div>

    </form>
    <!--end::Form-->

    <!--begin::Toolbar-->
    <div class="d-flex flex-wrap flex-stack pb-2">
        <!--begin::Title-->
        <div class="d-flex flex-wrap align-items-center my-1">
            <h3 class="fw-bolder me-5 my-1"><span id="numberItems"></span> Reclamos
                <span class="text-gray-400 fs-6">por fecha de creación ↓ </span>
            </h3>
        </div>
        <!--end::Title-->
    </div>
    <!--end::Toolbar-->

    <!--begin::Tab Content-->
    <div class="tab-content">
        <!--begin::Tab pane-->
        <div class="table-responsive">
            <table class="table table-bordered letraTabla table-hover table-sm mb-5">
                <thead id="header-table">
                    <tr class="normal-title">
                        <th>Código de Reclamo</th>
                        <th>Fecha de Registro</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Solución</th>
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

    <template id="item-header">

    </template>

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
            <td data-codigo></td>
            <td data-fecha></td>
            <td data-cliente></td>
            <td data-estado></td>
            <td data-solucion></td>
            <td>
                <a data-ver_reclamo href="" class="btn btn-outline-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Revisar reclamo"><i class="fas fa-eye"></i> </a>
                {{--<button data-solucionar data-reclamo_id="" class="btn btn-outline-success btn-sm" data-toggle="tooltip" data-placement="top" title="Solucionar reclamo"><i class="fas fa-check"></i> </button>
                <button data-anular data-reclamo_id="" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Anular reclamo"><i class="fas fa-times"></i> </button>
            --}}
            </td>
        </tr>
    </template>

    <template id="item-table-empty">
        <tr>
            <td colspan="8" align="center">No se ha encontrado ningún dato</td>
        </tr>
    </template>

    <div id="modalDelete" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Confirmar inhabilitación</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="formDelete" data-url="{{--{{ route('material.disable') }}--}}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="material_id" name="material_id">
                        <p>¿Está seguro de inhabilitar este material? Ya no se mostrará en los listados</p>
                        <p id="descriptionDelete"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Inhabilitar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('plugins')
    <!-- Datatables -->
    <script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>

    <script src="{{ asset('admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        $(function () {
            //Initialize Select2 Elements
            $('#tipo_reclamo').select2({
                placeholder: "Selecione Tipo Reclamo",
                allowClear: true
            });
            $('#canal').select2({
                placeholder: "Selecione Canal",
                allowClear: true
            });
            $('#motivo').select2({
                placeholder: "Selecione Motivo",
                allowClear: true
            });
            $('#submotivo').select2({
                placeholder: "Selecione Submotivo",
                allowClear: true
            });

        })
    </script>
    <script src="{{ asset('js/reclamaciones/indexFinalizados.js') }}?v={{ time() }}"></script>

@endsection