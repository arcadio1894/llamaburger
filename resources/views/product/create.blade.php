@extends('layouts.appAdmin2')

@section('openMaterial')
    menu-open
@endsection

@section('activeMaterial')
    active
@endsection

@section('activeCreateMaterial')
    active
@endsection

@section('title')
    Materiales
@endsection

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

@endsection

@section('styles')
    <style>
        .select2-search__field{
            width: 100% !important;
        }
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Materiales</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Crear nuevo material</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('material.indexV2') }}"><i class="fa fa-archive"></i> Materiales</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Nuevo</li>
    </ol>
@endsection

@section('content')
    <form id="formCreate" class="form-horizontal" data-url="{{ route('material.store') }}" enctype="multipart/form-data">
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
                                <label for="description">Descripción <span class="right badge badge-danger">(*)</span></label>
                                <input type="text" id="description" {{--onkeyup="mayus(this);"--}} name="description" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="unit_measure">Unidad de medida </label>
                                <select id="unit_measure" name="unit_measure" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $unitMeasures as $unitMeasure )
                                        <option value="{{ $unitMeasure->id }}">{{ $unitMeasure->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="brand">Marca </label>
                                <select id="brand" name="brand" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $brands as $brand )
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="exampler">Modelo </label>
                                <select id="exampler" name="exampler" class="form-control select2" style="width: 100%;">

                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="genero">Genero </label>
                                <select id="genero" name="genero" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $generos as $genero )
                                        <option value="{{ $genero->id }}">{{ $genero->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="talla">Talla </label>
                                <select id="talla" name="talla" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $tallas as $talla )
                                        <option value="{{ $talla->id }}">{{ $talla->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="perecible">Perecible </label>
                                <select id="perecible" name="perecible" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    <option value="s">SI</option>
                                    <option value="n">NO</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="category">Categorías </label>
                                <select id="category" name="category" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $categories as $category )
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="col-md-4">
                                <label for="subcategory">Subcategorías </label>
                                <select id="subcategory" name="subcategory" class="form-control select2" style="width: 100%;">
                                    <option></option>

                                </select>

                            </div>
                            <div class="col-md-4">
                                <label for="subcategory">Cantidad por paquete </label>
                                <div class="form-group clearfix">
                                    <div class="icheck-primary d-inline">
                                        <input type="checkbox" name="pack" id="checkboxPack">
                                        <label for="checkboxPack">Es paquete</label>
                                        <input type="number" class="form-control form-control-sm d-inline ml-2" style="width: 70px;" id="inputPack" name="inputPack" value="1" min="0">
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{--<div class="form-group row" id="feature-body" style="display: none">
                            <div class="col-md-3">
                                <label for="type">Tipo </label>
                                <select id="type" name="type" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                </select>

                            </div>
                            <div class="col-md-3">
                                <label for="subtype">Subtipo </label>
                                <select id="subtype" name="subtype" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                </select>

                            </div>
                            <div class="col-md-3">
                                <label for="warrant">Cédula </label>
                                <select id="warrant" name="warrant" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    <option value="" selected>Ninguno</option>
                                    @foreach( $warrants as $warrant )
                                        <option value="{{$warrant->id}}">{{ $warrant->name }}</option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="col-md-3">
                                <label for="quality">Calidad </label>
                                <select id="quality" name="quality" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    <option value="" selected>Ninguno</option>
                                    @foreach( $qualities as $quality )
                                        <option value="{{$quality->id}}">{{ $quality->name }}</option>
                                    @endforeach
                                </select>

                            </div>
                        </div>--}}

                        <div class="form-group">
                            <label for="name">Nombre completo</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control rounded-0" id="name" {{--onkeyup="mayus(this);"--}} name="name" readonly>
                                <span class="input-group-append">
                                    <button type="button" class="btn btn-info btn-flat" id="btn-generate"> <i class="fa fa-redo"></i> Actualizar</button>
                                </span>
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="unit_price">Precio Unitario </label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" placeholder="0.00" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
                                    this.style.borderColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'':'red'
                                    ">
                            </div>
                            <div class="col-md-4">
                                <label for="codigo">Código del producto </label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control rounded-0" id="codigo" name="codigo">
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-info btn-flat" id="btn-generateCode"> <i class="fas fa-random"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_venta">Tipo de Venta </label>
                                <select id="tipo_venta" name="tipo_venta" class="form-control select2" style="width: 100%;">
                                    <option></option>
                                    @foreach( $tipoVentas as $tipo )
                                        <option value="{{$tipo->id}}">{{ $tipo->description }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="form-group">

                            <label for="image">Imagen </label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>
                        <!--<div class="form-group">
                            <label for="serie">N° de serie </label>
                            <input type="text" id="serie" name="serie" class="form-control">
                        </div>-->
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-4">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Categoría y Stock</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="stock_max">Stock Máximo <span class="right badge badge-danger">(*)</span></label>
                            <input type="number" id="stock_max" name="stock_max" class="form-control" placeholder="0.00" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
                                    this.style.borderColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'':'red'
                                    ">
                        </div>
                        <div class="form-group">
                            <label for="stock_min">Stock Mínimo <span class="right badge badge-danger">(*)</span></label>
                            <input type="number" id="stock_min" name="stock_min" class="form-control" placeholder="0.00" min="0" value="0" step="0.01" pattern="^\d+(?:\.\d{1,2})?$" onblur="
                                    this.style.borderColor=/^\d+(?:\.\d{1,2})?$/.test(this.value)?'':'red'
                                    ">
                        </div>

                        {{--<div class="form-group row">
                            <div class="col-md-12">
                                <label for="btn-grouped"> Retacería </label> <br>
                            </div>
                            <div class="col-md-4">
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="typescrap0" name="typescrap" value="" checked>
                                    <label for="typescrap0">Ninguno
                                    </label>
                                </div>
                            </div>
                            @foreach( $typescraps as $typescrap )
                            <div class="col-md-4">
                                <div class="icheck-primary d-inline">
                                    <input type="radio" id="typescrap{{$typescrap->id}}" name="typescrap" value="{{$typescrap->id}}">
                                    <label for="typescrap{{$typescrap->id}}">{{$typescrap->name}}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="form-group">
                            <label for="brand">Marca </label>
                            <select id="brand" name="brand" class="form-control select2" style="width: 100%;">
                                <option></option>
                                @foreach( $brands as $brand )
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exampler">Modelo </label>
                            <select id="exampler" name="exampler" class="form-control select2" style="width: 100%;">

                            </select>
                        </div>--}}

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Promociones</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
                                <i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach( $discountQuantities as $discountQuantity )
                        <div class="form-group clearfix">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" name="discount[{{$discountQuantity->id}}]" id="checkboxPrimary{{$discountQuantity->id}}">
                                <label for="checkboxPrimary{{$discountQuantity->id}}">
                                    {{$discountQuantity->description}} - {{ $discountQuantity->percentage }}%
                                </label>
                                <input type="number" class="form-control form-control-sm d-inline ml-2" style="width: 70px;" id="percentageInput{{$discountQuantity->id}}" name="percentage[{{$discountQuantity->id}}]" value="{{ $discountQuantity->percentage }}" min="0" max="100"> %
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
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar material</button>
            </div>
        </div>
        <!-- /.card-footer -->
    </form>
@endsection

@section('plugins')
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        $(function () {
            //Initialize Select2 Elements
            $('#material_type').select2({
                placeholder: "Selecione tipo de material",
                allowClear: true,
            });
            $('#category').select2({
                placeholder: "Selecione categoría",
                allowClear: true,
            });
            $('#subcategory').select2({
                placeholder: "Selecione subcategoría",
                allowClear: true,
            });
            $('#brand').select2({
                placeholder: "Selecione una marca",
                allowClear: true,
            });
            $('#feature').select2({
                placeholder: "Seleccione característica",
                allowClear: true,
            });
            $('#type').select2({
                placeholder: "Elija",
                allowClear: true,
            });
            $('#subtype').select2({
                placeholder: "Elija",
                allowClear: true,
            });
            $('#warrant').select2({
                placeholder: "Elija",
                allowClear: true,
            });
            $('#quality').select2({
                placeholder: "Elija",
                allowClear: true,
            });
            $('#unit_measure').select2({
                placeholder: "Seleccione una unidad",
                allowClear: true,
            });
            $('#perecible').select2({
                placeholder: "Seleccione ",
                allowClear: true,
            });
            $('#genero').select2({
                placeholder: "Seleccione género",
                allowClear: true,
            });
            $('#talla').select2({
                placeholder: "Seleccione talla",
                allowClear: true,
            });
            $('#tipo_venta').select2({
                placeholder: "Seleccione Tipo Venta",
                allowClear: true,
            });

            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });
        })
    </script>
    <script src="{{ asset('js/material/create.js') }}?v={{ time() }}"></script>
@endsection
