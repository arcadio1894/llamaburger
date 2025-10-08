@extends('layouts.admin')

@section('openShops')
    menu-open
@endsection

@section('activeShops')
    active
@endsection

@section('activeCreateShop')
    active
@endsection

@section('title')
    Tiendas
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
    </style>
@endsection

@section('page-header')
    <h1 class="page-title">Tiendas</h1>
@endsection

@section('page-title')
    <h5 class="card-title">Crear nueva tienda</h5>
@endsection

@section('page-breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard.principal') }}"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('shop.index') }}"><i class="fa fa-archive"></i> Tiendas</a>
        </li>
        <li class="breadcrumb-item"><i class="fa fa-plus-circle"></i> Nuevo</li>
    </ol>
@endsection

@section('content')
    <form id="formCreate" class="form-horizontal" data-url="{{ route('shop.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group row">
            <div class="col-md-3">
                <label for="name" class="col-12 col-form-label">Nombre <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="name" id="name" placeholder="Tienda">
                </div>
            </div>
            <div class="col-md-3">
                <label for="size" class="col-12 col-form-label">Telefono <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="phone" id="phone" >
                </div>
            </div>
            <div class="col-md-3">
                <label for="size" class="col-12 col-form-label">Email <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="email" id="email" >
                </div>
            </div>
            <div class="col-md-3">
                <label for="type" class="col-12 col-form-label"> Tipo de tienda <span class="right badge badge-danger">(*)</span></label> <br>
                <input type="hidden" name="type" value="off">
                <input id="type" type="checkbox" name="type" checked data-bootstrap-switch data-off-color="primary" data-on-text="PRINCIPAL" data-off-text="SUCURSAL" data-on-color="success">
            </div>

        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <label for="size" class="col-12 col-form-label">Dirección <span class="right badge badge-danger">(*)</span></label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" name="address" id="address" >
                    <button type="button" class="btn btn-link" id="btn-selectAddress">
                        Busca la dirección
                    </button>
                </div>
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <input type="hidden" id="departamento" name="departamento">
                <input type="hidden" id="provincia" name="provincia">
                <input type="hidden" id="distrito" name="distrito">
            </div>

            <div class="col-md-3">
                <label for="department">Departamento: <span class="right badge badge-danger">(*)</span></label>
                <select class="form-control" id="department" name="department" required>
                    <option value=""></option>
                    <!-- Opciones dinámicas -->
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="province">Provincia: <span class="right badge badge-danger">(*)</span></label>
                <select class="form-control" id="province" name="province" required>
                    <option value=""></option>
                    <!-- Opciones dinámicas -->
                </select>
            </div>

        </div>
        <div class="form-group row">
            <div class="col-md-3">
                <label for="district">Distrito: <span class="right badge badge-danger">(*)</span></label>
                <select class="form-control" id="district" name="district" required>
                    <option value=""></option>
                    <!-- Opciones dinámicas -->
                </select>
            </div>

            <div class="col-md-3">
                <label for="owner">Propietario: <span class="right badge badge-danger">(*)</span></label>
                <select id="owner" class="form-control form-control-sm select2" style="width: 100%;">
                    <option value=""></option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="active" class="col-12 col-form-label"> Estado de actividad <span class="right badge badge-danger">(*)</span></label> <br>
                <input type="hidden" name="active" value="off">
                <input id="active" type="checkbox" name="active" checked data-bootstrap-switch data-off-color="danger" data-on-text="ACTIVA" data-off-text="INACTIVA" data-on-color="success">
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                @can('locales.crear_tiendas')
                <button type="button" id="btn-submit" class="btn btn-outline-success float-right">Guardar tienda</button>
                @endcan
            </div>
        </div>
        <!-- /.card-footer -->
    </form>

    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mapModalLabel">Encuentra tu dirección</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Campo de búsqueda -->
                    <input id="searchInput" type="text" placeholder="Buscar dirección" autocomplete="off" class="form-control">
                    <ul id="autocomplete-results" class="list-group" style="position:absolute; z-index:1000; width:100%; display:none;"></ul><!-- Mapa -->
                    <div id="map" style="height: 400px; width: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="selectAddress">Seleccionar esta dirección</button>
                </div>
            </div>
        </div>
    </div>
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
            /*$('#owner').select2({
                placeholder: "Selecione propietario",
                allowClear: true,
            });*/
            $("input[data-bootstrap-switch]").each(function(){
                $(this).bootstrapSwitch();
            });
        })
    </script>
    <script src="{{ asset('js/shop/create.js') }}?v={{ time() }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDBG5pTai_rF775fdoi3-9X8K462l1-aMo&libraries=places&callback=initAutocomplete" async defer></script>

    <script>
        let map, marker, infowindow, autocomplete;

        // Función para inicializar el mapa
        function initAutocomplete() {
            console.log("Google Maps API cargada correctamente.");

            // Inicializamos el mapa en la Plaza de Armas de Trujillo, Perú
            const trujilloLatLng = { lat: -8.1132, lng: -79.0290 }; // Coordenadas de la Plaza de Armas de Trujillo
            map = new google.maps.Map(document.getElementById("map"), {
                center: trujilloLatLng,
                zoom: 14,
            });

            // Creamos el marcador
            marker = new google.maps.Marker({
                position: trujilloLatLng,
                map: map,
                draggable: true, // Permitimos que el marcador sea arrastrado
                title: "Arrastra el marcador para cambiar la dirección"
            });

            // Creamos el infowindow
            infowindow = new google.maps.InfoWindow();

            // Mostrar el infowindow con la dirección actual del marcador
            google.maps.event.addListener(marker, "dragend", function() {
                updateMarkerPosition(marker.getPosition());
            });

            // Permitir colocar el marcador al hacer clic en el mapa
            map.addListener("click", function(event) {
                marker.setPosition(event.latLng);
                updateMarkerPosition(event.latLng);
            });

            // Inicializar el Autocomplete
            const input = $("#searchInput")[0];
            autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo("bounds", map);

            // Escuchar el evento cuando se seleccione una dirección
            autocomplete.addListener("place_changed", function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) {
                    alert("No se encontró información para esta dirección.");
                    return;
                }

                // Colocamos el marcador en la nueva dirección
                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                updateMarkerPosition(place.geometry.location);
            });

            // Evento para el botón "Seleccionar esta dirección"
            $("#selectAddress").on("click", function() {
                // Obtener la dirección y las coordenadas del marcador
                let address = $("#searchInput").val();
                const latLng = marker.getPosition();
                const latitude = latLng.lat();
                const longitude = latLng.lng();

                // Colocar los valores en los campos de entrada
                $("#address").val(address);      // Dirección en el campo de texto
                $("#latitude").val(latitude);    // Latitud en el campo oculto
                $("#longitude").val(longitude);  // Longitud en el campo oculto

                // Extraer el departamento, provincia y distrito de la dirección
                getAddressDetails(latLng);

                $("#addressModal").modal("hide");
            });
        }

        // Actualiza el valor del input y muestra la dirección en el infowindow
        function updateMarkerPosition(latLng) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === "OK" && results[0]) {
                    let address = results[0].formatted_address;

                    // Extraer departamento, provincia y distrito
                    let department = "";
                    let province = "";
                    let district = "";

                    results[0].address_components.forEach(component => {
                        if (component.types.includes("administrative_area_level_1")) {
                            department = component.long_name; // Departamento
                        }
                        if (component.types.includes("administrative_area_level_2")) {
                            province = component.long_name; // Provincia
                        }
                        if (component.types.includes("administrative_area_level_3") ||
                            component.types.includes("locality") ||
                            component.types.includes("sublocality")) {
                            district = component.long_name; // Distrito
                        }
                    });

                    // Colocar los valores en los inputs
                    $("#searchInput").val(address);
                    $("#address").val(address);
                    $("#latitude").val(latLng.lat());
                    $("#longitude").val(latLng.lng());
                    $("#departamento").val(department);
                    $("#provincia").val(province);
                    $("#distrito").val(district);

                    // Mostrar InfoWindow con la dirección
                    const contentString = `
                <div style="font-family: Arial, sans-serif;">
                    <div style="font-size: 14px; font-weight: bold; color: #000;">Dirección:</div>
                    <div style="font-size: 16px; font-weight: bold; color: #007BFF;">${address}</div>
                    <div style="font-size: 14px; color: #333;">${district}, ${province}, ${department}</div>
                </div>
            `;
                    infowindow.setContent(contentString);
                    infowindow.open(map, marker);
                }
            });
        }

        // Inicializar el mapa y la funcionalidad de autocomplete al cargar el script
        window.initAutocomplete = initAutocomplete;

        function getAddressDetails(latLng) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === "OK" && results[0]) {
                    let departmentName = "";
                    let provinceName = "";
                    let districtName = "";

                    // Buscar en los componentes de la dirección los datos de ubicación
                    results[0].address_components.forEach(component => {
                        if (component.types.includes("administrative_area_level_1")) {
                            departmentName = component.long_name; // Departamento
                        }
                        if (component.types.includes("administrative_area_level_2")) {
                            provinceName = component.long_name; // Provincia
                        }
                        if (component.types.includes("administrative_area_level_3") ||
                            component.types.includes("locality") ||
                            component.types.includes("sublocality")) {
                            districtName = component.long_name; // Distrito
                        }
                    });

                    console.log("📍 Departamento:", departmentName);
                    console.log("🏛 Provincia:", provinceName);
                    console.log("📌 Distrito:", districtName);

                    // Llamar a la función para actualizar los selects en la base de datos
                    updateLocationSelectors(departmentName, provinceName, districtName);
                }
            });
        }

        function updateLocationSelectors(departmentName, provinceName, districtName) {
            console.log(departmentName);
            console.log(provinceName);
            console.log(districtName);
            // 1️⃣ Buscar el departamento en la BD
            $.ajax({
                url: `/buscar-departamento?nombre=${encodeURIComponent(departmentName)}`,
                type: "GET",
                dataType: "json",
                success: function(department) {
                    if (department) {
                        console.log("✅ Departamento encontrado:", department);
                        $("#department").val(department.id).trigger("change"); // Seleccionar departamento y disparar cambio

                        // 2️⃣ Esperar a que se carguen las provincias antes de continuar
                        setTimeout(() => {
                            $.ajax({
                                url: `/provincias/${department.id}`,
                                type: "GET",
                                dataType: "json",
                                success: function(provinces) {
                                    $("#province").empty().append('<option value="">Seleccionar</option>');
                                    $("#district").empty().append('<option value="">Seleccionar</option>');

                                    $.each(provinces, function(index, province) {
                                        $("#province").append(`<option value="${province.id}">${province.name}</option>`);
                                    });

                                    // 3️⃣ Seleccionar la provincia correcta
                                    let selectedProvince = provinces.find(p => p.name.trim().toLowerCase() === provinceName.trim().toLowerCase());

                                    console.log(provinces);
                                    if (selectedProvince) {
                                        console.log("✅ Provincia encontrada:", selectedProvince);
                                        $("#province").val(selectedProvince.id).trigger("change"); // Seleccionar provincia y disparar cambio

                                        // 4️⃣ Esperar a que se carguen los distritos antes de continuar
                                        setTimeout(() => {
                                            $.ajax({
                                                url: `/distritos/${selectedProvince.id}`,
                                                type: "GET",
                                                dataType: "json",
                                                success: function(districts) {
                                                    $("#district").empty().append('<option value="">Seleccionar</option>');

                                                    $.each(districts, function(index, district) {
                                                        $("#district").append(`<option value="${district.id}">${district.name}</option>`);
                                                    });

                                                    // 5️⃣ Seleccionar el distrito correcto
                                                    let selectedDistrict = districts.find(d => d.name.toLowerCase() === districtName.toLowerCase());
                                                    if (selectedDistrict) {
                                                        console.log("✅ Distrito encontrado:", selectedDistrict);
                                                        $("#district").val(selectedDistrict.id);
                                                    }
                                                }
                                            });
                                        }, 500); // ⏳ Pequeña espera para asegurar que las provincias ya cargaron
                                    }
                                }
                            });
                        }, 500); // ⏳ Espera para evitar que la petición de provincias ocurra antes de la selección del departamento
                    }
                }
            });
        }
    </script>
    {{--<script>
        let map, marker, infowindow, autocomplete;

        // Función para inicializar el mapa
        function initAutocomplete() {
            console.log("✅ Google Maps API cargada correctamente.");

            // Inicializamos el mapa en la Plaza de Armas de Trujillo, Perú
            const trujilloLatLng = { lat: -8.1132, lng: -79.0290 };
            map = new google.maps.Map(document.getElementById("map"), {
                center: trujilloLatLng,
                zoom: 14,
            });

            // Creamos el marcador
            marker = new google.maps.Marker({
                position: trujilloLatLng,
                map: map,
                draggable: true,
                title: "Arrastra el marcador para cambiar la dirección"
            });

            // Creamos el infowindow
            infowindow = new google.maps.InfoWindow();

            // Mostrar el infowindow con la dirección actual del marcador
            google.maps.event.addListener(marker, "dragend", function () {
                updateMarkerPosition(marker.getPosition());
            });

            // Permitir colocar el marcador al hacer clic en el mapa
            map.addListener("click", function (event) {
                marker.setPosition(event.latLng);
                updateMarkerPosition(event.latLng);
            });

            // ✅ Verificar si el input de búsqueda está presente
            const input = document.getElementById("searchInput");

            if (input) {
                console.log("📌 Input de búsqueda detectado:", input);

                // Detectar si el usuario está escribiendo en el input
                input.addEventListener("input", function(event) {
                    console.log("📝 Texto ingresado:", event.target.value);
                });

                // Inicializar Autocomplete con restricciones a Perú y solo direcciones
                autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ["geocode"],
                    componentRestrictions: { country: "PE" }
                });

                autocomplete.bindTo("bounds", map);

                // Escuchar el evento cuando se seleccione una dirección
                autocomplete.addListener("place_changed", function () {
                    console.log("🔍 Lugar seleccionado:", autocomplete.getPlace());
                    const place = autocomplete.getPlace();

                    if (!place.geometry) {
                        alert("No se encontró información para esta dirección.");
                        return;
                    }

                    // Colocamos el marcador en la nueva dirección
                    marker.setPosition(place.geometry.location);
                    map.setCenter(place.geometry.location);
                    updateMarkerPosition(place.geometry.location);
                });

            } else {
                console.warn("❌ No se encontró el campo de búsqueda.");
            }

            // Evento para el botón "Seleccionar esta dirección"
            $("#selectAddress").on("click", function () {
                const address = $("#searchInput").val();
                const latLng = marker.getPosition();
                const latitude = latLng.lat();
                const longitude = latLng.lng();

                // Colocar los valores en los campos de entrada
                $("#address").val(address);
                $("#latitude").val(latitude);
                $("#longitude").val(longitude);

                $("#addressModal").modal("hide");
            });

            // 🔄 Reconfigurar Autocomplete si hay problemas
            setTimeout(() => {
                if (!autocomplete) {
                    console.log("🔄 Reiniciando Autocomplete...");
                    const input = document.getElementById("searchInput");
                    if (input) {
                        autocomplete = new google.maps.places.Autocomplete(input, {
                            types: ["geocode"],
                            componentRestrictions: { country: "PE" }
                        });
                        console.log("✅ Autocomplete reiniciado correctamente.");
                    } else {
                        console.warn("❌ No se encontró el input de búsqueda.");
                    }
                }
            }, 1500);
        }

        // Actualiza el valor del input y muestra la dirección en el infowindow
        function updateMarkerPosition(latLng) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === "OK" && results[0]) {
                    const address = results[0].formatted_address;

                    // Extraer departamento, provincia y distrito
                    let department = "";
                    let province = "";
                    let district = "";

                    results[0].address_components.forEach(component => {
                        if (component.types.includes("administrative_area_level_1")) {
                            department = component.long_name; // Departamento
                        }
                        if (component.types.includes("administrative_area_level_2")) {
                            province = component.long_name; // Provincia
                        }
                        if (component.types.includes("administrative_area_level_3") ||
                            component.types.includes("locality") ||
                            component.types.includes("sublocality")) {
                            district = component.long_name; // Distrito
                        }
                    });

                    // Colocar los valores en los inputs
                    $("#address").val(address);
                    $("#latitude").val(latLng.lat());
                    $("#longitude").val(latLng.lng());
                    $("#departamento").val(department);
                    $("#provincia").val(province);
                    $("#distrito").val(district);

                    // Mostrar InfoWindow con la dirección
                    const contentString = `
                <div style="font-family: Arial, sans-serif;">
                    <div style="font-size: 14px; font-weight: bold; color: #000;">Dirección:</div>
                    <div style="font-size: 16px; font-weight: bold; color: #007BFF;">${address}</div>
                    <div style="font-size: 14px; color: #333;">${district}, ${province}, ${department}</div>
                </div>
            `;
                    infowindow.setContent(contentString);
                    infowindow.open(map, marker);
                }
            });
        }

        // Inicializar el mapa y la funcionalidad de autocomplete al cargar el script
        window.initAutocomplete = initAutocomplete;

    </script>--}}
@endsection
