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
        /* filas de items */
        .item-row { display: flex; justify-content: space-between; align-items: baseline; gap: 6px; }
        .item-left { flex: 1; }
        .item-right { flex: 0 0 auto; min-width: 70px; text-align: right; }
        .muted { color: #555; font-size: 11px; }
        .sec-title { font-weight: bold; margin-top: 4px; }
    </style>
</head>
<body>
<div class="ticket">
    <div class="text-center bold">
        <p>RESTAURANTE FUEGO Y MASA</p>
        <div class="line"></div>
        <p>PRE-CUENTA • Atención #{{ $atencion->id }}</p>
        <div class="line"></div>
    </div>

    <p><b>Fecha:</b> {{ now()->format('d/m/Y H:i') }}</p>
    @php
        $mesa = $atencion->mesa->nombre ?? ($atencion->tipo === 'externo' ? 'Externo' : '—');
        $mozo = $atencion->mozo->nombre ?? '—';
    @endphp
    <p><b>Mesa:</b> {{ $mesa }}</p>
    <p><b>Mozo:</b> {{ $mozo }}</p>

    <div class="line"></div>

    {{-- Lista de todos los ítems sin separar por comanda --}}
    @php
        $items = $atencion->comandas->flatMap->items; // combina todos los items
    @endphp

    @forelse ($items as $it)
        @php
            $qty   = (float)$it->cantidad;
            $vu    = round(($it->precio_unit/(1+$igvRate)), 2);
            $pu    = round($it->precio_unit);
            $total = round($qty * $pu, 2);
            $nombre = $it->nombre ?: ($it->product->full_name ?? 'Producto');
        @endphp
        <p class="bold" style="font-size: 11px;">
            {{ $nombre }} x {{ rtrim(rtrim(number_format($qty,2,'.',''), '0'),'.') }}
            <span style="float:right;">S/ {{ number_format($total, 2, '.', '') }}</span>
        </p>
        <p class="muted">V.U. (sin IGV): S/ {{ number_format($vu, 2, '.', '') }}</p>

        {{--<div class="item-row">
            <div class="item-left">
                <span class="bold" style="font-size:11px;">
                    {{ $nombre }} x {{ rtrim(rtrim(number_format($qty,2,'.',''), '0'), '.') }}
                </span>
            </div>
            <div class="item-right">
                <span>S/ {{ number_format($total, 2, '.', '') }}</span>
            </div>
        </div>--}}
        {{--<p class="muted">V.U.: S/ {{ number_format($vu, 2, '.', '') }}</p>--}}

        @if(!empty($it->opciones) && is_array($it->opciones))
            @foreach($it->opciones as $op)
                <p class="muted">• {{ is_array($op) ? ($op['label'] ?? json_encode($op)) : $op }}</p>
            @endforeach
        @endif
    @empty
        <p class="muted">— Sin productos registrados —</p>
    @endforelse

    <div class="line"></div>

    <div class="text-center">
        <p><b>Documento no válido como comprobante</b></p>
        <p class="muted">Pre-cuenta informativa para revisión del cliente.</p>
    </div>

    <div class="line2"></div>
</div>
</body>
</html>