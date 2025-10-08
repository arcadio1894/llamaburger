// Variables globales
let map;
let marker;
let polygons = [];

$(document).ready(function () {
    $('#shop_id').select2({
        placeholder: "Seleccione tienda",
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true,
        theme: 'bootstrap4',
    });

    $("#shop_id").change(function () {
        let shopId = $(this).val();
        if (shopId) {
            loadZones(shopId);
        } else {
            $("#zonesTable").html('<tr><td colspan="4" class="text-center">Seleccione una tienda</td></tr>');
        }
    });
});

// 🗺️ Inicializar el mapa
function initMap() {
    console.log("Inicializando mapa...");

    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: -12.0464, lng: -77.0428 }, // Lima, Perú
        zoom: 12
    });

    console.log("Mapa cargado correctamente");
}

// 📌 Definir `initMap` globalmente para que Google Maps lo reconozca
window.initMap = initMap;

function loadZones(shopId) {
    $.ajax({
        url: `/dashboard/shops/${shopId}/zones`,
        method: "GET",
        success: function (zones) {
            let rows = "";
            zones.forEach((zone, index) => {
                let buttons = "";

                // 👇 solo agregar el botón si tiene permiso
                if (hasPerm('locales.ver_zona')) {
                    buttons += `<button class="btn btn-info btn-sm" onclick="viewZone(${zone.id})">Ver zona</button> `;
                }
                if (hasPerm('locales.set_precio')) {
                    buttons += `<button class="btn btn-warning btn-sm" onclick="setPrice(${zone.id}, ${zone.price})">Colocar precio</button>`;
                }

                rows += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${zone.name}</td>
                        <td>${zone.price}</td>
                        <td>${buttons}</td>
                    </tr>
                `;
            });
            $("#zonesTable").html(rows);
        }
    });
}

function viewZone(zoneId) {
    $.ajax({
        url: `/dashboard/zones/show/${zoneId}`,
        method: "GET",
        success: function (zone) {
            clearPolygons(); // Elimina cualquier zona dibujada anteriormente
            drawPolygon(zone.coordinates); // Dibuja la nueva zona en el mapa
            setShopMarker(zone.shop_latitude, zone.shop_longitude); // Ubica el marcador de la tienda
        }
    });
}

function drawPolygon(coordinates) {
    let polygon = new google.maps.Polygon({
        paths: coordinates.map(coord => ({ lat: coord.lat, lng: coord.lng })),  // Formato lat/lng
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
    });

    polygon.setMap(map);
    polygons.push(polygon);
}

function setShopMarker(lat, lng) {
    if (marker) marker.setMap(null); // Eliminar marcador anterior
    marker = new google.maps.Marker({
        position: { lat: parseFloat(lat), lng: parseFloat(lng) },
        map: map,
        title: "Ubicación de la tienda"
    });
    map.setCenter(marker.getPosition()); // Centrar mapa en la tienda
}

// ❌ Eliminar todas las zonas del mapa
function clearPolygons() {
    polygons.forEach(polygon => polygon.setMap(null));
    polygons = [];
    console.log("Todas las zonas eliminadas.");
}

function setPrice(zoneId, currentPrice) {
    $.confirm({
        title: 'Colocar Precio',
        content: `
            <form>
                <label>Ingrese el precio:</label>
                <input type="number" id="zonePrice" value="${currentPrice}" class="form-control" step="0.01">
            </form>
        `,
        buttons: {
            cancelar: function () {},
            guardar: {
                text: 'Guardar',
                btnClass: 'btn-green',
                action: function () {
                    let newPrice = $("#zonePrice").val();
                    updateZonePrice(zoneId, newPrice);
                }
            }
        }
    });
}

function updateZonePrice(zoneId, price) {
    $.ajax({
        url: `/dashboard/zones/${zoneId}/update-price`,
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            price: price
        },
        success: function () {
            $("#shop_id").trigger("change"); // Recargar la tabla
        }
    });
}