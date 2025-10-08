@extends('layouts.admin')

@section('openRewards')
    menu-open
@endsection

@section('activeRewards')
    active
@endsection

@section('activeListRewards')
    active
@endsection

@section('title')
    Hitos
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
        /* Asegura que las sugerencias aparezcan correctamente */
        .pac-container {
            z-index: 1051 !important; /* Mayor que el z-index del modal */
        }

        #selected-products .card {
            border-radius: 5px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Hitos</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Editar Hito</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('milestones.index') }}"><i class="fa fa-archive"></i> Hitos</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Editar</li>
    </ol>
@endsection

@section('content')
    <form id="formCreate" class="form-horizontal" data-url="{{ route('milestones.update') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="milestone_id" value="{{ $milestone->id }}">
        <div class="form-group row">
            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="title" class="col-12 col-form-label">Título <span class="right badge badge-danger">(*)</span></label>
                    <div class="col-sm-12">
                        <textarea name="title" class="form-control" id="title" rows="3">{{ $milestone->title }}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="description" class="col-12 col-form-label">Descripción <span class="right badge badge-danger">(*)</span></label>
                    <div class="col-sm-12">
                        <textarea name="description" class="form-control" id="description" rows="5">{{ $milestone->description }}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="image" class="col-12 col-form-label">Imagen <span class="right badge badge-danger">(*)</span></label>
                    <div class="col-sm-12">
                        <input type="file" class="form-control" name="image" id="image">
                        <img src="{{ asset('images/reward/'.$milestone->image) }}" alt="{{ $milestone->title }}" width="100px">
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="flames" class="col-12 col-form-label">Flamitas <span class="right badge badge-danger">(*)</span></label>
                    <div class="col-sm-12">
                        <input type="number" min="0" step="1" class="form-control" name="flames" id="flames" value="{{ $milestone->flames }}">
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="col-md-12">
                    <label for="products" class="col-12 col-form-label">Productos de premios <span class="right badge badge-danger">(*)</span></label>
                    <div class="input-group">
                        <select class="form-control" id="products" required>
                            <option value=""></option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <button id="btn-add-product" class="btn btn-primary" type="button">
                                Agregar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="col-md-12" id="selected-products">
                        <!-- Aquí se mostrarán los productos seleccionados -->
                        @foreach( $milestone->rewards as $milestoneReward )
                            <div class="card mb-2" data-id="{{ $milestoneReward->product_id }}" style="max-width: 100%;">
                                <div class="card-body d-flex align-items-center justify-content-between p-2">
                                    <span class="product-name flex-grow-1">{{ $milestoneReward->product->full_name }}</span>
                                    <button class="btn btn-danger btn-sm btn-remove-product ml-2" style="min-width: 32px;">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>


        <div class="row">
            <div class="col-12">
                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                @can('rewards.editar')
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar cambios del Hito</button>
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

@endsection

@section('scripts')
    <script>
        $(function () {
            //Initialize Select2 Elements
            $('#products').select2({
                placeholder: "Selecione productos",
                allowClear: true,
            });
            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });
        })
    </script>
    <script src="{{ asset('js/milestone/edit.js') }}?v={{ time() }}"></script>
@endsection
