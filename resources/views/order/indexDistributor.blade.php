@extends('layouts.admin')

@section('openOrders')
    menu-open
@endsection

@section('activeOrders')
    active
@endsection

@section('activeListOrdersDistributors')
    active
@endsection

@section('title', 'Tablero de Órdenes (Distribuidor)')

@section('styles')
    <style>
        .track { position: relative; background-color: #ddd; height: 7px; display: flex; margin-bottom: 20px; }
        .track .step { position: relative; width: 25%; text-align: center; margin-top: -18px; }
        .track .step .icon { display: inline-block; width: 40px; height: 40px; line-height: 40px; background: #ddd; border-radius: 50%; }
        .track .step.active .icon { background: #28a745; color: #fff; }
        .track .step .text { display: block; margin-top: 7px; font-size: 0.8rem; }
    </style>
@endsection

@section('content')
    <div class="container my-4">
        <h4 class="mb-3">
            Órdenes asignadas —
            @if(!empty($isAdmin) && $isAdmin)
                Administrador (todas las órdenes)
            @else
                Distribuidor: {{ isset($distributor) && $distributor ? $distributor->name : '—' }}
            @endif
        </h4>

        <div class="row" id="orders-grid">
            @foreach($orders as $order)
                <div class="col-md-4 mb-3" id="order-{{ $order->id }}">
                    <article class="card h-100">
                        <header class="card-header text-center">
                            <strong>{{ $order->status_name }}</strong>
                        </header>

                        <div class="card-body">
                            <h6 class="text-center">PEDIDO ID: #{{ $order->id }}</h6>

                            <article class="card mb-2">
                                <div class="card-body row">
                                    <div class="col">
                                        <strong>Llegará aprox.:</strong><br>
                                        {{ $order->date_estimated_format ?? 'Fecha no disponible' }}
                                    </div>
                                    <div class="col">
                                        <strong>Monto a pagar:</strong><br>
                                        S/. {{ isset($order->data_totals['total_a_pagar']) ? $order->data_totals['total_a_pagar'] : '0.00' }}
                                    </div>
                                </div>
                            </article>

                            <div class="track mb-2">
                                <div class="step {{ $order->active_step >= 1 ? 'active' : '' }}">
                                    <span class="icon"><i class="far fa-file-alt"></i></span>
                                    <span class="text">Recibido</span>
                                </div>
                                <div class="step {{ $order->active_step >= 2 ? 'active' : '' }}">
                                    <span class="icon"><i class="fas fa-fire"></i></span>
                                    <span class="text">Cocinando</span>
                                </div>
                                <div class="step {{ $order->active_step >= 3 ? 'active' : '' }}">
                                    <span class="icon"><i class="fa fa-truck"></i></span>
                                    <span class="text">Enviado</span>
                                </div>
                                <div class="step {{ $order->active_step >= 4 ? 'active' : '' }}">
                                    <span class="icon"><i class="fas fa-home"></i></span>
                                    <span class="text">Entregado</span>
                                </div>
                            </div>

                            <hr>

                            @php
                                $address   = $order->shipping_address ? $order->shipping_address->address   : '';
                                $latitude  = $order->shipping_address ? $order->shipping_address->latitude  : '';
                                $longitude = $order->shipping_address ? $order->shipping_address->longitude : '';
                                $url_comanda = url('/imprimir/comanda/' . $order->id);
                                $url_boleta  = url('/imprimir/recibo/' . $order->id);
                                $showDeliver = strtoupper($order->status_name) === 'EN TRAYECTO';
                            @endphp
                            <br>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ $url_comanda }}" target="_blank" data-imprimir_comanda="{{ $order->id }}">
                                    <h6 class="description-header" style="font-size: .8rem; font-weight: bold; color: black">COMANDA</h6>
                                </a>

                                <a href="{{ $url_boleta }}" target="_blank" data-imprimir_boleta="{{ $order->id }}">
                                    <h6 class="description-header" style="font-size: .8rem; font-weight: bold; color: black">BOLETA</h6>
                                </a>

                                <a href="#" data-ver_ruta_map
                                   data-id="{{ $order->id }}"
                                   data-address="{{ $address }}"
                                   data-latitude="{{ $latitude }}"
                                   data-longitude="{{ $longitude }}">
                                    <h6 class="description-header" style="font-size: .8rem; font-weight: bold; color: black">VER RUTA</h6>
                                </a>
                            </div>

                            {{-- 🔹 Contenedor de acciones dinámicas para JS (necesario para insertar el botón) --}}
                            <div class="d-flex justify-content-end mt-2" data-actions>
                                @if($showDeliver)
                                    {{-- Opcional: pinta el botón desde el servidor si ya está EN TRAYECTO --}}
                                    <button class="btn btn-block btn-sm btn-success"
                                            data-entregar-order
                                            data-id="{{ $order->id }}"
                                            style="font-weight:bold;">
                                        ENTREGAR
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Flags para JS
        window.__isAdmin = {{ (!empty($isAdmin) && $isAdmin) ? 'true' : 'false' }};
        window.__distributorId = {{ (isset($distributor) && $distributor) ? (int)$distributor->id : 'null' }};

        // Config de Echo (Pusher)
        window.__echoConfig = {
            key: '{{ config("broadcasting.connections.pusher.key") ?? "dac24d98f58cf734beec" }}',
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") ?? "us2" }}',
            forceTLS: false,
            encrypted: false,

        };
    </script>

    {{-- Laravel Mix --}}
    <script src="{{ mix('js/indexDistributor.js') }}"></script>
@endsection