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
        <p>COMANDA - {{ $order->id }}</p>
        {{--<p>Nro: ORDEN - {{ $order->id }}</p>--}}
        <div class="line"></div>
    </div>
    <p><b>Pedido:</b> {{ $order->formatted_created_date }}</p>
    <p><b>Entrega:</b> {{ $order->formatted_date }}</p>
    <p><b>Cliente:</b> {{ ($order->shipping_address_id == null) ? 'Incognito':$order->shipping_address->first_name." ".$order->shipping_address->last_name  }}</p>
    <p><b>Telefono:</b> {{ ($order->shipping_address_id == null) ? 'N/A':$order->shipping_address->phone }}</p>
    <p><b>Dirección:</b> {{ ($order->shipping_address_id == null) ? 'N/A':$order->shipping_address->address_line }}</p>
    @if ($order->shipping_address_id == null)
        <p><b>Referencia:</b> ----------------------------------</p>
    @else
        <p><b>Referencia:</b> {{ ($order->shipping_address->reference == null || $order->shipping_address->reference == "") ? '----------------------------------':$order->shipping_address->reference }}</p>
    @endif
    {{--<div class="line"></div>--}}
    @foreach ($order->details as $detail)
        <div class="line"></div>
        <strong><p style="font-size: 18px">{{ $detail->product->full_name.(( $detail->product_type_id == null ) ? '':" | ".$detail->productType->type->name."(".$detail->productType->type->size.")") }} <span style="float: right;">{{ $detail->quantity }}</span></p></strong>
        @foreach( $detail->options as $option )
            <p style="font-size: 16px">- {{ str_pad( ($option->product->full_name), 10, ' ', STR_PAD_LEFT) }} x {{ $detail->quantity }}</p>
        @endforeach

        @if ($detail->toppings->isNotEmpty())
            @php
                // Agrupamos los toppings por su tipo con una colección
                $groupedToppings = collect($detail->toppings)->groupBy(function ($topping) {
                    return $topping->type === 'whole' ? 'En todo' :
                           ($topping->type === 'left' ? 'A la izquierda' :
                           ($topping->type === 'right' ? 'A la derecha' : 'Otros'));
                });
            @endphp

            @foreach ($groupedToppings as $typeTitle => $toppings)
                <p style="font-size: 16px; font-weight: bold;">Toppings {{ $typeTitle }}</p>
                @foreach ($toppings as $topping)
                    <p style="font-size: 16px">
                        - {{ $topping->topping_name }}
                        @if ($topping->extra)
                            <span>[Extra]</span>
                        @endif
                    </p>
                @endforeach
            @endforeach
        @endif
    @endforeach
    {{-- Mostrar toppings si existen --}}
    {{--@if ($detail->toppings->isNotEmpty())
        <p style="font-size: 16px; font-style: italic;">Toppings:</p>
        @php
            $sortedToppings = collect($detail->toppings)->sortBy(function ($topping) {
                return $topping->type === 'whole' ? 1 : ($topping->type === 'left' ? 2 : ($topping->type === 'right' ? 3 : 4));
            });
        @endphp
        @foreach ($sortedToppings as $topping)
            <p style="font-size: 16px">
                • {{ $topping->topping_name }}
                @if ($topping->type)
                    ({{ $topping->type === 'whole' ? 'En todo' : ($topping->type === 'left' ? 'A la izquierda' : ($topping->type === 'right' ? 'A la derecha' : $topping->type)) }})
                @endif
                @if ($topping->extra)
                    <span>[Extra]</span>
                @endif
            </p>
        @endforeach
    @endif--}}



    <div class="line"></div>
    <p class="text-center" style="font-size: 18px"><b>TOTAL: S/. {{ number_format($totals['total_a_pagar'], 2, '.', '') }}</b></p>
    <div class="line"></div>
    <p class="text-center" style="font-size: 18px"><b>{{($order->payment_method_id == null) ? 'Sin método de pago':$order->payment_method->name }} </b></p>
    <div class="line"></div>
    <div style="border: 0.5px solid black; padding: 3px; margin-top: 10px; font-size: 18px">
        <strong>Observaciones:</strong>
        {{ $order->observations }}
    </div>
    <div class="text-center" >
        <p>¡Gracias por su compra!</p>
        <p>www.fuegoymasa.com</p>
    </div>
{{--style="border-style: solid" esto va en comanda para observaciones--}}
</body>
</html>