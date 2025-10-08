@extends('layouts.admin')

@section('openProducts')
    menu-open
@endsection

@section('activeProducts')
    active
@endsection

@section('activeLCreateProducts')
    active
@endsection

@section('title')
    Productos
@endsection

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.css') }}">

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
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Productos</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Crear nuevo producto</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('products.list') }}"><i class="fa fa-archive"></i> Productos</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Nuevo</li>
    </ol>
@endsection

@section('content')
    <form id="formCreate" class="form-horizontal" data-url="{{ route('product.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Datos generales</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="full_name">Nombre completo <span class="right badge badge-danger">(*)</span></label>
                                <input type="text" class="form-control rounded-0" id="full_name" name="full_name" >
                            </div>

                        </div>
                        <div class="form-group row">
                            <div class="col-md-7">
                                <label for="description">Descripción <span class="right badge badge-danger">(*)</span></label>
                                <textarea id="description" name="description" class="form-control"></textarea>
                            </div>
                            <div class="col-md-5">
                                <label for="category">Categorías <span class="right badge badge-danger">(*)</span></label>
                                <select id="category" name="category" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $categories as $category )
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-8">
                                <label for="image">Imagen <span class="right badge badge-danger">(*)</span></label>
                                <input type="file" id="image" name="image" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label for="unit_price">Precio Referencial </label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" placeholder="0.00" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
                                    this.style.borderColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'':'red'
                                    ">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="visibility_price_real" class="col-12 col-form-label">Visibilidad de precio tachado</label>
                                <input type="hidden" name="visibility_price_real" value="off">
                                <input id="visibility_price_real" type="checkbox" name="visibility_price_real" data-bootstrap-switch data-off-color="primary" data-on-text="VISIBLE" data-off-text="NO VISIBLE" data-on-color="success">

                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <label for="observations">Ingredientes </label>
                                <textarea class="textarea_ingredients" id="ingredients" name="ingredients" placeholder="Place some text here"
                                          style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-4">
                <!-- /.card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Tipos de Productos</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach( $types as $type )
                        <div class="form-group clearfix">
                            <div class="icheck-success d-inline">
                                <input type="radio" id="radioPrimary{{$type->id}}" name="defaultType" value="{{$type->id}}"><label for="radioPrimary{{$type->id}}"></label>
                            </div>
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="type[{{$type->id}}]" id="checkboxPrimary{{$type->id}}">
                                <label for="checkboxPrimary{{$type->id}}">
                                    {{$type->name}} - ( {{ $type->size }} )
                                </label>
                                <input type="number" class="form-control form-control-sm d-inline ml-2" style="width: 70px;" id="productPrice{{$type->id}}" name="productPrice[{{$type->id}}]" value="{{ $type->price }}" min="0" >

                            </div>
                        </div>
                        @endforeach

                    </div>
                    <!-- /.card-body -->
                </div>
            </div>

        </div>

        <div class="row">
            @can('productos.agregar_opciones')
            <div class="col-md-8">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Opciones del Producto</h3>

                        <div class="card-tools">
                            <button type="button" id="new-option" class="btn btn-sm btn-primary">
                                Agregar opción
                            </button>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="product-options">
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>

            </div>
            @endcan
            <div class="col-md-4">
                <!-- /.card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Dias activados</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="date_validate">Válido hasta</label>
                            <input
                                    type="text"
                                    class="form-control form-control-sm datepicker"
                                    id="date_validate"
                                    name="date_validate"
                                    placeholder="Selecciona una fecha"
                                    autocomplete="off"
                            >
                        </div>
                        @foreach($days as $day)
                            <div class="form-group clearfix">
                                <div class="icheck-primary d-inline">
                                    <input
                                            type="checkbox" checked
                                            name="days[{{ $day['number'] }}]"
                                            id="checkboxDay{{ $day['number'] }}"
                                    >
                                    <label for="checkboxDay{{ $day['number'] }}">
                                        {{ ucfirst($day['day']) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach

                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                @can('productos.crear')
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar producto</button>
                @endcan
            </div>
        </div>
        <!-- /.card-footer -->
    </form>

    <template id="template-option">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Opción</h3>
                <div class="card-tools">
                    <button type="button" data-delete_option class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>

                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block;">
                <div class="form-group row">
                    <div class="col-md-12">
                        <label >Descripción</label>
                        <input type="text" name="options[0][description]" class="form-control form-control-sm">
                    </div>

                </div>
                <div class="form-group row">
                    <div class="col-md-4">
                        <label>Cantidad</label>
                        <input type="number" name="options[0][quantity]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label>Tipo</label>
                        <select name="options[0][type]" class="form-control form-control-sm options">
                            <option value=""></option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="select">Select</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <button type="button" data-selection class="btn btn-sm btn-success btn-block"><i class="fas fa-plus"></i> Selección</button>
                    </div>
                </div>
                <div data-option_selection="option-selections">

                </div>


            </div>
        </div>
    </template>
    <template id="template-selection">
        <div class="card bg-primary mb-3">
            <div class="card-body" style="display: block;">
                <div class="form-group row">
                    <div class="col-md-8">
                        <label>Producto</label>
                        <select name="options[0][selections][0][product_id]" class="form-control form-control-sm selections">
                            <option value=""></option>
                            @foreach( $products as $product )
                                <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Precio (Opcional)</label>
                        <input type="text" name="options[0][selections][0][additional_price]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <button type="button" data-delete_selection class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('plugins')
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
    <script src="{{asset('admin/plugins/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{asset('admin/plugins/summernote/lang/summernote-es-ES.js')}}"></script>

    <script src="{{ asset('admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js') }}"></script>

@endsection

@section('scripts')
    <script>
        $(function () {
            //Initialize Select2 Elements
            $('#category').select2({
                placeholder: "Selecione categoría",
                allowClear: true,
            });
            $('.options').select2({
                placeholder: "Selecione Tipo",
                allowClear: true,
            });
            $('.selections').select2({
                placeholder: "Selecione Producto",
                allowClear: true,
            });

            /*$('#sandbox-container .input-daterange').datepicker({
                todayBtn: "linked",
                clearBtn: true,
                language: "es",
                multidate: false,
                autoclose: true
            });*/

            $('.textarea_ingredients').summernote({
                lang: 'es-ES',
                placeholder: 'Ingrese los detalles',
                tabsize: 2,
                height: 120,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link']],
                    ['view', ['codeview', 'help']]
                ]
            });

            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });
        })
    </script>
    <script src="{{ asset('js/product/create.js') }}?v={{ time() }}"></script>
@endsection
