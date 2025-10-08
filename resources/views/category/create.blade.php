@extends('layouts.admin')

@section('openCategories')
    menu-open
@endsection

@section('activeCategories')
    active
@endsection

@section('activeCreateCategories')
    active
@endsection

@section('title')
    Categorías
@endsection

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.css') }}">

@endsection

@section('styles')
    <style>
        .select2-search__field{
            width: 100% !important;
        }
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Categorías</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Crear nueva categoría</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('categories.index') }}"><i class="fa fa-archive"></i> Categorías</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Nuevo</li>
    </ol>
@endsection

@section('content')
    <form id="formCreate" class="form-horizontal" data-url="{{ route('categories.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group row">
            <div class="col-md-4">
                <label for="name" class="col-12 col-form-label">Categoría <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="name" id="name" placeholder="Categoría">
                </div>
            </div>
            <div class="col-md-8">
                <label for="size" class="col-12 col-form-label">Descripción <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="description" id="description" placeholder="Descripción de la categoría">
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-4">
                <label for="active" class="col-12 col-form-label"> Estado visibilidad </label> <br>
                <input id="active" type="checkbox" name="active" checked data-bootstrap-switch data-off-color="danger" data-on-text="VISIBLE" data-off-text="NO VISIBLE" data-on-color="success">
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                @can('categorias.crear')
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar categoría</button>
                @endcan
            </div>
        </div>
        <!-- /.card-footer -->
    </form>
@endsection

@section('plugins')
    <!-- Select2 -->
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
    <script src="{{asset('admin/plugins/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{asset('admin/plugins/summernote/lang/summernote-es-ES.js')}}"></script>

@endsection

@section('scripts')
    <script>
        $(function () {
            //Initialize Select2 Elements
            /*$('#category').select2({
                placeholder: "Selecione categoría",
                allowClear: true,
            });*/
            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });
        })
    </script>
    <script src="{{ asset('js/category/create.js') }}?v={{ time() }}"></script>
@endsection
