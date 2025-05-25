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

        .line2 {
            display: block;
            text-align: center;
            width: 100%;
            margin: 5px 0;
        }

        .line2::after {
            content: "***********************";
            letter-spacing: 2px;
            font-weight: bold;
            font-family: monospace;
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
        <p>NOTA DE VENTA - {{ $order->id }}</p>
        {{--<p>Nro: ORDEN - {{ $order->id }}</p>--}}
        <div class="line"></div>
    </div>
    <p><b>Fecha Pedido:</b> {{ $order->created_at->format('d/m/Y') }}</p>
    {{--<p><b>Entrega:</b> {{ $order->formatted_date }}</p>--}}
    <p><b>Cliente:</b> {{ ($order->shipping_address_id == null) ? 'Incognito':$order->shipping_address->first_name." ".$order->shipping_address->last_name  }}</p>
    <p><b>Telefono:</b> {{ ($order->shipping_address_id == null) ? 'N/A':$order->shipping_address->phone }}</p>
    <p><b>Dirección:</b> {{ ($order->shipping_address_id == null) ? 'N/A':$order->shipping_address->address_line }}</p>
    @if ($order->shipping_address_id == null)
        <p><b>Referencia:</b> ----------------------------------</p>
    @else
        <p><b>Referencia:</b> {{ ($order->shipping_address->reference == null || $order->shipping_address->reference == "") ? '----------------------------------':$order->shipping_address->reference }}</p>
    @endif
    <div class="line"></div>
    @foreach ($order->details as $detail)

        <p style="font-size: 10px"><b>{{ str_pad( ($detail->product->full_name.(( $detail->product_type_id == null ) ? '':" | ".$detail->productType->type->name)), 10, ' ', STR_PAD_LEFT)  }} x {{ $detail->quantity }}</b>  <span style="float: right;">{{ 'S/. ' . number_format($detail->price * $detail->quantity, 2) }}</span></p>
        @foreach( $detail->options as $option )
            <p>- {{ str_pad( ($option->product->full_name), 10, ' ', STR_PAD_LEFT) }}</p>
        @endforeach

        {{-- Mostrar toppings si existen --}}
        @if ($detail->toppings->isNotEmpty())
            <p style="font-style: italic;">Toppings:</p>
            @foreach ($detail->toppings as $topping)
                <p>
                    • {{ $topping->topping_name }}
                    @if ($topping->type)
                        ({{ $topping->type === 'left' ? 'A la izquierda' : ($topping->type === 'right' ? 'A la derecha' : ($topping->type === 'whole' ? 'En todo' : $topping->type)) }})
                    @endif
                    @if ($topping->extra)
                        <span>[Extra]</span>
                    @endif
                </p>
            @endforeach
        @endif
    @endforeach

    <div class="line"></div>
    <p><b>Envío:</b> <span style="float: right;">S/. {{ number_format($amount_shipping, 2, '.', '') }}</span></p>
    <p><b>Descuento:</b> <span style="float: right;">S/. {{ $discount }}</span></p>
    <p><b>Gravada:</b> <span style="float: right;">S/. {{ $totals['total_gravada'] }}</span></p>
    <p><b>IGV:</b> <span style="float: right;">S/. {{ number_format($totals['total_igv'], 2, '.', '') }}</span></p>
    <p><b>TOTAL:</b> <span style="float: right;">S/. {{ number_format($totals['total_a_pagar'], 2, '.', '') }}</span></p>
    <div class="line"></div>
    <p class="text-center" style="font-size: 18px"><b>{{($order->payment_method_id == null) ? 'Sin método de pago':$order->payment_method->name }} </b></p>
    <div class="line"></div>
    <div class="text-center" >
        <p>¡Gracias por su compra!</p>
        <p>www.fuegoymasa.com</p>
    </div>
    <div class="line2"></div>
    <div class="text-center" >
        <p>¿Ya conoces nuestra web?</p>
        <p>En <strong>www.fuegoymasa.com</strong> puedes explorar nuestro menú, descubrir <strong>promociones especiales</strong> y hacer tu pedido de manera fácil y rápida.</p>
    </div>
    <div class="line2"></div>
{{--style="border-style: solid" esto va en comanda para observaciones--}}
</body>
</html>