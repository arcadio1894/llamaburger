<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            /*width: 226.8pt; !* 80mm *!*/
        }
        .text-center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            width: 100%;
            display: block;
        }
        .bold {
            font-weight: bold;
        }
        * {
            page-break-inside: avoid;
            page-break-before: auto;
            page-break-after: auto;
        }
        p {
            margin: 0;
            padding: 2px 0;
        }
        img {
            max-width: 100px; /* Ajusta el tamaño del logo según sea necesario */
            height: auto;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="text-center bold">
        <img src="{{ public_path('images/logo/logoPequeño.png') }}" alt="Logo de Restaurante">
        <p>RESTAURANTE FUEGO Y MASA</p>
        <p>RUC: 20613407287</p>
        <p>Manuel Candamo 810, Lima</p>
        <div class="line"></div>
        <p>COMANDA - {{ $comanda->id }}</p>
        <div class="line"></div>
    </div>
    <p><b>Pedido:</b> {{ $comanda->formatted_send_to_kitchen }}</p>
    <p><b>Inicio:</b> {{ $comanda->formatted_started_cooking }}</p>
    <p><b>Entrega:</b> {{ $comanda->formatted_estimated_ready }}</p>

    @foreach ($comanda->items as $detail)
        <div class="line"></div>
        {{-- Item --}}
        <div class="line"></div>
        <strong>
            <p style="font-size:18px">
                {{ $detail->nombre }}
                <span style="float:right;">{{ $detail->cantidad }}</span>
            </p>
        </strong>

        @php
            // Toma las opciones ya casteadas a array, o decodifica si viniera como string
            $ops = is_array($detail->opciones)
              ? $detail->opciones
              : (json_decode($detail->opciones ?? '[]', true) ?: []);

            $grupos = $ops['grupos'] ?? [];
        @endphp

        @foreach($grupos as $grupo)
            {{-- Si quieres mostrar el título del grupo/descripción --}}
            @if(!empty($grupo['descripcion']))
                <p style="font-size:15px; margin:2px 0;"><em>{{ $grupo['descripcion'] }}</em></p>
            @endif

            @foreach(($grupo['selecciones'] ?? []) as $sel)
                @php
                    // Algunos registros podrían tener 'nombre' en vez de 'name'
                    $label = $sel['name'] ?? $sel['nombre'] ?? '—';
                @endphp
                <p style="font-size:16px">- {{ $label }} x {{ $detail->cantidad }}</p>
            @endforeach
        @endforeach

    @endforeach

    <div class="line"></div>
    {{--<p class="text-center" style="font-size: 18px"><b>TOTAL: S/. {{ number_format($totals['total_a_pagar'], 2, '.', '') }}</b></p>
    <div class="line"></div>--}}

    <div style="border: 0.5px solid black; padding: 3px; margin-top: 10px; font-size: 18px">
        <strong>Observaciones:</strong>

    </div>
    <div class="text-center" >
        <p>¡Gracias por su compra!</p>
        <p>www.fuegoymasa.com</p>
    </div>
{{--style="border-style: solid" esto va en comanda para observaciones--}}
</body>
</html>