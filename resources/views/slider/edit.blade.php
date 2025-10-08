@extends('layouts.admin')

@section('openSliders')
    menu-open
@endsection

@section('activeSliders')
    active
@endsection

@section('activeListSliders')
    active
@endsection

@section('title')
    Imagenes de Sliders
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
    <h1 class="page-title">Imagenes de Sliders</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Editar imagen</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('sliders.index') }}"><i class="fa fa-archive"></i> Images Sliders</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Editar</li>
    </ol>
@endsection

@section('content')
    <form id="formEdit" class="form-horizontal" data-url="{{ route('sliders.update') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="image_id" name="image_id" value="{{ $slider->id }}">
        <div class="form-group row">
            <div class="col-md-6">
                <label for="order" class="col-12 col-form-label">Orden <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="order" id="order" step="1" min="1" value="{{ $slider->order }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="size" class="col-12 col-form-label">Tamaño <span class="right badge badge-danger">(*)</span></label>
                <input type="hidden" name="size" value="off">
                <!-- Checkbox que refleja el estado basado en el tamaño actual -->
                <input id="size" type="checkbox" name="size"
                       {{ ($slider->size == 's') ? 'checked' : '' }}
                       data-bootstrap-switch
                       data-off-color="primary"
                       data-on-text="SMALL"
                       data-off-text="LARGE"
                       data-on-color="success">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-6">
                <label for="image" class="col-12 col-form-label">Imagen <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-10">
                    <input type="file" class="form-control" name="image" id="image" accept="image/*">
                    <img src="{{ asset('images/slider/'.$slider->image) }}" width="200px"  alt="{{ $slider->image }}">
                </div>
            </div>
            <div class="col-md-6">
                <label for="link" class="col-12 col-form-label">Link <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="link" id="link" value="{{ $slider->link }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                @can('sliders.editar')
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar imagen</button>
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
            $('#category').select2({
                placeholder: "Selecione categoría",
            });
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
    <script src="{{ asset('js/slider/edit.js') }}?v={{ time() }}"></script>
@endsection
