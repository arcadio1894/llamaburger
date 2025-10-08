@extends('layouts.admin')

@section('openProducts')
    menu-open
@endsection

@section('activeProducts')
    active
@endsection

@section('activeListDeletedProducts')
    active
@endsection

@section('title')
    Productos
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
    <h1 class="page-title">Productos</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Listado de productos</h5>
    @can('productos.crear')
        <a href="{{ route('product.create') }}" class="btn btn-outline-success btn-sm float-right" > <i class="fa fa-plus font-20"></i> Nuevo producto </a>
    @endcan
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-archive"></i> Productos </li>
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
                    <input type="text" id="full_name" class="form-control" placeholder="Nombre del producto..." autocomplete="off">
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
                            <label for="category">Categoría:</label>
                            <select id="category" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">TODOS</option>
                                @for ($i=0; $i<count($arrayCategories); $i++)
                                    <option value="{{ $arrayCategories[$i]['id'] }}">{{ $arrayCategories[$i]['name'] }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="quote">Código:</label>
                            <input type="text" id="code" class="form-control form-control-sm" placeholder="791" autocomplete="off">

                        </div>

                    </div>

                    <br>

                    <!-- Añade más campos según lo necesario -->
                </div>
            </div>
        </div>
        <!--end::Input group-->
        <!--begin:Action-->
        {{--<div class="col-md-1">
            <label for="btn-search">&nbsp;</label><br>
            <button type="button" id="btn-search" class="btn btn-primary me-5">Buscar</button>
        </div>--}}

    </form>
    <!--end::Form-->

    <div class="row mt-3">
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="codigo" class="custom-control-input" id="customSwitch1">
            <label class="custom-control-label" for="customSwitch1">Código</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="nombre" class="custom-control-input" id="customSwitch2">
            <label class="custom-control-label" for="customSwitch2">Nombre</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="descripcion" class="custom-control-input" id="customSwitch3">
            <label class="custom-control-label" for="customSwitch3">Descripcion</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="precio" class="custom-control-input" id="customSwitch4">
            <label class="custom-control-label" for="customSwitch4">Precio</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="categoria" class="custom-control-input" id="customSwitch5">
            <label class="custom-control-label" for="customSwitch5">Categoría</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="ingredientes" class="custom-control-input" id="customSwitch6">
            <label class="custom-control-label" for="customSwitch6">Ingredientes</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="estado" class="custom-control-input" id="customSwitch7">
            <label class="custom-control-label" for="customSwitch7">Estado</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="imagen" class="custom-control-input" id="customSwitch8">
            <label class="custom-control-label" for="customSwitch8">Imagen</label>
        </div>
        <div class="col-md-2 custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
            <input type="checkbox" checked data-column="visibility_price_real" class="custom-control-input" id="customSwitch9">
            <label class="custom-control-label" for="customSwitch9">Ver Precio Tachado</label>
        </div>
    </div>

    <!--begin::Toolbar-->
    <div class="d-flex flex-wrap flex-stack pb-2">
        <!--begin::Title-->
        <div class="d-flex flex-wrap align-items-center my-1">
            <h3 class="fw-bolder me-5 my-1"><span id="numberItems"></span> Productos
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
        <tr class="normal-title">
            <th data-column="codigo" data-codigo>Código</th>
            <th data-column="nombre" data-nombre>Nombre</th>
            <th data-column="descripcion" data-descripcion>Descripción</th>
            <th data-column="precio" data-precio>Precio</th>
            <th data-column="categoria" data-categoria>Categoría</th>
            <th data-column="ingredientes" data-ingredientes>Ingredientes</th>
            <th data-column="estado" data-estado>Estado</th>
            <th data-column="imagen" data-imagen>Imagen</th>
            <th data-column="visibility_price_real" data-visibility_price_real>Ver Precio Tachado</th>
            <th></th>
        </tr>
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
            <td data-column="codigo" data-codigo>Código</td>
            <td data-column="nombre" data-nombre>Nombre</td>
            <td data-column="descripcion" data-descripcion>Descripción</td>
            <td data-column="precio" data-precio>Precio</td>
            <td data-column="categoria" data-categoria>Categoría</td>
            <td data-column="ingredientes" data-ingredientes>Ingredientes</td>
            <td data-column="estado" data-estado>Estado</td>
            <td data-column="imagen" data-imagen>
                <button data-ver_imagen data-src="" data-image="" class="btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Imagen"><i class="fa fa-image"></i></button>
            </td>
            <td data-column="visibility_price_real" data-visibility_price_real>Ver precio Tachado</td>
            <td>
                <button data-reactivar="" data-product_id="" data-description="" class="btn btn-outline-success btn-sm" data-toggle="tooltip" data-placement="top" title="Reactivar productos"><i class="fas fa-recycle"></i> </button>
            </td>
        </tr>
    </template>

    <template id="item-table-empty">
        <tr>
            <td colspan="8" align="center">No se ha encontrado ningún dato</td>
        </tr>
    </template>

    {{--@can('enable_material')--}}
        <div id="modalDelete" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal_title"></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="formDelete" data-url="{{ route('product.delete') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" id="product_id" name="product_id">
                            <p>¿Está seguro de cambiar el estado este material? </p>
                            <p id="descriptionDelete"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Cambiar estado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {{--@endcan--}}

    <div id="modalImage" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Visualización de la imagen</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <img id="image-document" src="" alt="" width="80%">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
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
            /*$('#retaceria').select2({
                placeholder: "Selecione Retacería",
                allowClear: true
            });*/

        })
    </script>
    <script src="{{ asset('js/product/indexV2Deleted.js') }}"></script>

@endsection