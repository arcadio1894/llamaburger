@extends('layouts.admin')

@section('openZone')
    menu-open
@endsection

@section('activeZone')
    active
@endsection

@section('activeCreateZone')
    active
@endsection

@section('title')
    Zonas
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
    <form action="{{ route('zones.store') }}" method="POST">
        @csrf
        <div class="form-group row">
            <div class="col-md-6">
                <label for="shop_id">Tienda</label>
                <select name="shop_id" class="form-control" id="shop_id">
                    <option value=""></option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="map" style="height: 400px;"></div>

        <input type="hidden" name="coordinates" id="coordinates">

        <!-- Botones adicionales -->
        {{--<button type="button" id="clearZones" class="btn btn-danger mt-3">Eliminar Zonas</button>--}}
        @can('locales.crear_zonas')
        <button type="button" id="saveZones" class="btn btn-success mt-3">Guardar Zonas</button>
        @endcan
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
    {{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo&libraries=places&callback=initMap" async defer></script>
    --}}

    {{--<script>
        let map;
        let polygon;
        let polygonCoords = []; // Array para almacenar las coordenadas

        function initMap() {
            map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: -12.0464, lng: -77.0428 }, // Lima, Perú (ejemplo)
                zoom: 12
            });

            // Define el polígono vacío
            polygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                editable: true,
                draggable: true
            });

            polygon.setMap(map); // Agregar polígono al mapa

            // Evento para agregar puntos al hacer clic en el mapa
            map.addListener("click", function(event) {
                let newPoint = event.latLng;
                polygonCoords.push(newPoint);
                polygon.setPath(polygonCoords); // Actualiza el polígono en el mapa
                console.log("Punto agregado:", newPoint.lat(), newPoint.lng());
            });
        }

        function updateCoordinates() {
            let path = polygon.getPath();
            coordinates = [];
            path.forEach(function(point, index) {
                coordinates.push({ lat: point.lat(), lng: point.lng() });
            });
            document.getElementById('coordinates').value = JSON.stringify(coordinates);
        }
    </script>--}}
    {{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo&callback=initMap"></script>
    --}}

    <script src="{{ asset('js/zone/create.js') }}?v={{ time() }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo&callback=initMap&v=weekly" async defer></script>
@endsection
