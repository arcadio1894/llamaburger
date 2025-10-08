@extends('layouts.admin')

@section('openSalas')
    menu-open
@endsection

@section('activeSalas')
    active
@endsection

@section('activeListSalas')
    active
@endsection

@section('title')
    Mesas
@endsection

@section('styles-plugins')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

@endsection

@section('styles')
    <style>
        /* grid 4 por fila (Bootstrap 4) */
        .mesas-grid .col-mesa { padding: 6px; }
        .mesa-card {
            border: 1px solid #e9ecef; border-radius: .5rem;
            padding: .8rem .9rem; display:flex; justify-content:space-between; align-items:center;
            transition: background .12s ease-in-out; cursor: pointer; min-height: 64px;
        }
        .mesa-card:hover { background: #f8f9fa; }
        .mesa-card .left { display:flex; align-items:center; }
        .mesa-card .icon { width:22px; text-align:center; opacity:.8; margin-right:.4rem; }
        .mesa-card .name { font-weight: 600; }
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
            <!-- Barra de búsqueda -->
            <div class="input-group flex-grow-1">
                <input type="text" id="name" class="form-control" placeholder="Buscar mesa..." autocomplete="off">
                <button class="btn btn-primary" type="button" id="btn-search">Buscar</button>
            </div>

            <!-- Botón de configuración -->
            @can('espacios.configurar_mesas')
            <a href="{{ route('salas.config') }}" class="btn btn-outline-success ml-1"><i class="fas fa-cogs"></i></a>
            @endcan
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
    {{--<input type="hidden" id="permissions" value="{{ json_encode($permissions) }}">--}}

    {{-- Botonera de Salas --}}
    <div class="d-flex flex-wrap">
        @foreach($salas as $sala)
            <button
                    type="button"
                    class="btn btn-outline-primary btn-sm btn-sala {{ $firstSala && $firstSala->id === $sala->id ? 'active' : '' }}"
                    data-sala-id="{{ $sala->id }}"
            >
                {{ $sala->nombre }}
            </button>
        @endforeach
    </div>

    {{-- Mesas de la sala activa --}}
    <div id="mesas-wrap" class="mt-3">
        @include('sala.partials.mesas-list', ['mesas' => $mesas])
    </div>


@endsection

@section('plugins')
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
    <script>
        window.MOZOS = @json($mozos->map(function($m){
            return ['id' => $m->id, 'nombre' => $m->nombre];
        }));

        window.CURRENT_MOZO_ID = @json(optional($currentMozo)->id);
    </script>
    <script src="{{ asset('js/sala/index.js') }}"></script>

@endsection