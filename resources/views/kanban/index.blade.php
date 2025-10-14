@extends('layouts.admin')

@section('openOrders')
    menu-open
@endsection

@section('activeOrders')
    active
@endsection

@section('activeKanbanOrders')
    active
@endsection

@section('title')
    Pedidos de clientes
@endsection

@section('styles-plugins')
    <link rel="stylesheet" href="{{ asset('admin/plugins/jqxwidgets/css/jqx.base.css') }}">
@endsection

@section('styles')
    <style>
        .jqx-kanban-item-avatar {
            display: none !important; /* Oculta completamente el avatar */
        }

        #kanban-container {
            white-space: nowrap; /* Evita que las columnas se vayan a otra fila */
            background: #f8f9fa; /* Fondo gris claro */
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #ddd; /* Borde para que se vea separado */
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); /* Pequeña sombra */
        }

        .jqx-kanban-column {
            display: inline-block !important; /* Fuerza que estén en línea */
            vertical-align: top;
        }

        #kanban {
            white-space: nowrap; /* Evita que las columnas se vayan a otra fila */
            height: 1000px !important;
        }

        .jqx-kanban-column {
            display: inline-block !important; /* Fuerza que estén en línea */
            vertical-align: top;
            min-width: 250px; /* Ancho mínimo para cada columna */
            max-width: 300px;
            background: #ffffff !important; /* Asegurar que las columnas sean blancas */
            border-radius: 8px;
            padding: 8px;
        }

        .widget-user-header {
            height: 65px !important;
        }

        .widget-user-image {
            left: 60% !important;
        }

        .jqx-kanban-item-footer,
        .jqx-kanban-item-keyword {
            display: none !important; /* Oculta el footer y las palabras clave */
        }

        .jqx-kanban-item-text {
            padding-left: 0px !important;
            padding-right: 2px !important;
            padding-bottom: 0px !important;
        }

    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Pedidos de Clientes</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Kanban de pedidos</h5>

@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Kanban</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="kanban"></div>
        </div>
    </div>

@endsection

@section('plugins')
    <!-- jqxKanban y dependencias -->
    <script src="{{ asset('admin/plugins/jqxwidgets/js/jqxcore.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqxwidgets/js/jqxdata.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqxwidgets/js/jqxbuttons.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqxwidgets/js/jqxsortable.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqxwidgets/js/jqxkanban.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>

@endsection

@section('scripts')
    <script src="{{ asset('js/kanban/index.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/orderCreated.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/comandaCreated.js') }}?v={{ time() }}"></script>
@endsection