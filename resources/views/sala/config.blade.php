@extends('layouts.admin')

@section('openSalas') menu-open @endsection
@section('activeSalas') active @endsection
@section('activeListSalas') active @endsection

@section('title') Mesas @endsection

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('styles')
    <style>
        /* chips/fichas */
        .chip {
            border:1px solid #e9ecef;
            border-radius:.5rem;
            background:#fff;
            padding:.55rem .75rem;
            position:relative
        }
        .chip:hover{
            background:#f8f9fa
        }
        .chip + .chip{
            margin-left:1px
        }
        .chip .edit-btn{
            position:absolute;
            right:.35rem;
            top:.35rem
        }
        .chip.active{
            box-shadow:0 0 0 .2rem rgba(0,123,255,.15);
            background:#eef6ff
        }
        .grid-wrap{
            display:flex;
            flex-wrap:wrap;
            gap:6px
        }
        .sala-chip{
            min-width:170px
        }
        .mesa-chip{
            min-width:150px
        }
        .chip.deleted{
            background:#f1f2f4;
            color:#6c757d;
            border-color:#e0e0e0;
            text-decoration: line-through;
        }
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Salas y Mesas</h1>
@endsection

@section('page-title')
    <div class="row align-items-center">
        <div class="col-12 col-md-7 mb-2 mb-md-0">
            <h5 class="card-title">Listado de Salas</h5>
        </div>

        <div class="col-12 col-md-5 d-flex">
            <div class="input-group flex-grow-1">
                <input type="text" id="name" class="form-control" placeholder="Buscar mesa..." autocomplete="off">
                <button class="btn btn-primary" type="button" id="btn-search">Buscar</button>
            </div>
        </div>
    </div>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-archive"></i> Espacios </li>
    </ol>
@endsection

@section('content')
    {{-- Botonera/Chips de Salas --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="grid-wrap" id="salas-wrap">
            @foreach($salas as $sala)
                @include('sala.partials.sala-chip', [
                    'sala'   => $sala,
                    'active' => $firstSala && $firstSala->id === $sala->id
                ])
            @endforeach
        </div>

        <button class="btn btn-sm btn-outline-primary" id="btn-add-sala">
            <i class="fas fa-plus"></i> Agregar Sala
        </button>
    </div>

    {{-- Contenedor de Mesas (render inicial = firstSala) --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Mesas</span>
            <button class="btn btn-sm btn-outline-primary" id="btn-add-mesa">
                <i class="fas fa-plus"></i> Crear Mesa
            </button>
        </div>
        <div class="card-body">
            <div id="mesas-wrap" class="grid-wrap">
                @include('sala.partials.mesas-grid', ['mesas' => $mesas])
            </div>
        </div>
    </div>

    {{-- Modal crear/editar Sala (sencillo; puedes migrarlo a un partial) --}}
    {{--<div class="modal fade" id="modal-sala" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-sala-title">Crear Sala</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="form-sala">@csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input name="nombre" class="form-control" required maxlength="120">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <input name="descripcion" class="form-control" maxlength="500">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                        <button class="btn btn-default" data-dismiss="modal" type="button">Cancelar</button>
                    </div>
                </form>
            </div></div>
    </div>--}}
@endsection

@section('plugins')
    <script src="{{ asset('admin/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('js/sala/config.js') }}?v={{ time() }}"></script>
@endsection