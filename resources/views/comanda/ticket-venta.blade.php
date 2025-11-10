<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 8pt; }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .line2 { text-align: center; margin: 6px 0; font-family: monospace; font-weight: bold; letter-spacing: 2px; }
        .line2::after { content: "***********************"; }
        p { margin: 0; padding: 2px 0; }
        img { max-width: 100px; height: auto; display: block; margin: 0 auto; }
        .row { display: flex; justify-content: space-between; align-items: baseline; }
        .h5 { font-size: 14px; }
        .muted { color: #555; font-size: 11px; }
        * { page-break-inside: avoid; page-break-before: auto; page-break-after: auto; }
        /* ancho típico 80mm: 226.8pt. Si tu motor lo respeta, puedes fijarlo: */
        .ticket { width: 210.8pt; margin: 0 auto; }
        .totales-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            width: 100%;
            font-size: 12px;
        }
        .totales-row span {
            flex: 1;
            text-align: left;
        }
        .totales-row strong {
            flex: 0 0 auto;
            text-align: right;
            min-width: 60px; /* asegura alineación derecha limpia */
        }
        .totales-row.h5 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 6px;
        }
    </style>
</head>
<body>
<div class="ticket">
    <div class="text-center bold">
        <img src="{{ public_path('images/logo/logoPequeño.png') }}" alt="Logo">
        <p>RESTAURANTE FUEGO Y MASA</p>
        <p>RUC: 20613407287</p>
        <p>Manuel Candamo 810, Lima</p>
        <div class="line"></div>
        <p>NOTA DE VENTA - {{ $invoice->id }}</p>
        <div class="line"></div>
    </div>

    <p><b>Fecha:</b> {{ optional($invoice->created_at)->format('d/m/Y H:i') }}</p>
    {{-- Cliente (si tienes nombre/documento en invoice) --}}
    @php
        $cliNombre = $invoice->cliente_nombre ?? 'Incognito';
        $cliDoc    = $invoice->cliente_doc_num ?? null;
    @endphp
    <p><b>Cliente:</b> {{ $cliNombre }} @if($cliDoc) ({{ $cliDoc }}) @endif</p>

    <div class="line"></div>

    {{-- Ítems: precios mostrados SIN IGV para que cuadre con el subtotal/base/igv abajo --}}
    @foreach ($invoice->items as $it)
        @php
            $qty   = (float)$it->cantidad;
            $vu    = (float)$it->valor_unitario; // sin IGV
            $totalLinea = $qty * $vu;            // sin IGV
            $nombre = $it->descripcion ?? ($it->product->full_name ?? 'Producto');
        @endphp
        <p class="bold" style="font-size: 11px;">
            {{ $nombre }} x {{ rtrim(rtrim(number_format($qty,2,'.',''), '0'),'.') }}
            <span style="float:right;">S/ {{ number_format($totalLinea, 2, '.', '') }}</span>
        </p>
        <p class="muted">V.U. (sin IGV): S/ {{ number_format($vu, 2, '.', '') }}</p>
    @endforeach

    <div class="line"></div>

    {{-- TOTALES (idéntico layout que pediste) --}}
    <p><b>Subtotal:</b> <span style="float: right;">S/. {{ number_format($totals['subtotal'], 2, '.', '') }}</span></p>
    <p><b>Descuento:</b> <span style="float: right;">- S/. {{ number_format($totals['descuento'], 2, '.', '') }}</span></p>
    <p><b>Gravada:</b> <span style="float: right;">S/. {{ number_format($totals['base'], 2, '.', '') }}</span></p>
    <p><b>IGV ({{ (int)round($totals['igvRate'] * 100) }}%):</b> <span style="float: right;">S/. {{ number_format($totals['igv'], 2, '.', '') }}</span></p>
    <p><b>Propina:</b> <span style="float: right;">+ S/. {{ number_format($totals['propina'], 2, '.', '') }}</span></p>
    <p><b>TOTAL:</b> <span style="float: right;">S/. {{ number_format($totals['total'], 2, '.', '') }}</span></p>

    {{-- Opcional: dejar el valor de IGV para trazabilidad (no será usado en JS) --}}
    <input type="hidden" id="igvRate" value="{{ $totals['igvRate'] }}">

    <div class="line"></div>

    {{-- Método de pago si lo tienes en invoice --}}
    @php
        $metodo = $invoice->payment_method_name ?? 'Sin método de pago';
    @endphp
    <p class="text-center" style="font-size: 16px;"><b>{{ $metodo }}</b></p>

    <div class="line"></div>
    <div class="text-center">
        <p>¡Gracias por su compra!</p>
        <p>www.fuegoymasa.com</p>
    </div>
    <div class="line2"></div>
    <div class="text-center">
        <p>¿Ya conoces nuestra web?</p>
        <p>En <strong>www.fuegoymasa.com</strong> puedes explorar el menú, ver <strong>promos</strong> y pedir fácil.</p>
    </div>
    <div class="line2"></div>
</div>
</body>
</html>