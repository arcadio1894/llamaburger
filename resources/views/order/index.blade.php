@extends('layouts.app')

@section('orders-active', 'active')

@section('text-header')
    {{--<h2 class="pt-5">
        Tus pedidos
    </h2>--}}

@endsection

@section('styles')
    <link href="{{ asset('css/reward/index.css') }}" rel="stylesheet">
    <style>
        .card{
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.10rem
        }
        .card-header:first-child{
            border-radius: calc(0.37rem - 1px) calc(0.37rem - 1px) 0 0
        }
        .card-header{
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1)
        }
        .track{
            position: relative;
            background-color: #ddd;
            height: 7px;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            margin-bottom: 60px;
            margin-top: 50px
        }
        .track .step{
            -webkit-box-flex: 1;
            -ms-flex-positive: 1;
            flex-grow: 1;
            width: 25%;
            margin-top: -18px;
            text-align: center;
            position: relative
        }
        .track .step.active:before{
            background: #FF5722
        }
        .track .step::before{
            height: 7px;
            position: absolute;
            content: "";
            width: 100%;
            left: 0;top: 18px
        }
        .track .step.active .icon{
            background: #ee5435;
            color: #fff
        }
        .track .icon{
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            position: relative;
            border-radius: 100%;
            background: #ddd
        }
        .track .step.active .text{
            font-weight: 400;
            color: #000
        }
        .track .text{
            display: block;
            margin-top: 7px
        }


        .nav-link-perfil {
            color: #007a33;
            transition: color 0.3s ease;
        }

        .nav-link-perfil.clicked {
            color: #000 !important;
        }

        /* Estilos de Milestone */
        .tab-custom-reward .nav-link.active {
            border-bottom: 3px solid #007a33;
            font-weight: bold;
            color: #000;
        }

        .tab-custom-reward .nav-link {
            color: #333;
            padding: 6px 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .milestone-content img {
            max-height: 100px;
        }

        @media (max-width: 576px) {

            .tab-custom-reward .nav-item {
                flex: 0 0 auto;
                margin: 0 4px;
            }

            .tab-custom-reward .nav-link {
                font-size: 13px;
                padding: 2px 6px;
            }

        }

        @media (max-width: 767.98px) {
            .nav-link-perfil {
                font-size: 14px;
                color: #007a33;
                text-decoration: none;
                display: inline-block;
                margin-left: 10px;
                margin-bottom: 10px;
            }

            #mobileTabs {
                display: flex;
                width: 100%;
            }

            #mobileTabs .nav-link {
                flex: 1 1 50%;
                background-color: transparent;
                padding: 10px 0;
                text-align: center;
                font-size: 14px;
                border: none;
                border-bottom: 2px solid transparent;
                color: #333;
            }

            #mobileTabs .nav-link.active {
                border-bottom: 3px solid #007a33;
                font-weight: bold;
                background-color: transparent;
                color: #000;
            }
        }

        .card-img-icon {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Si NO quieres líneas entre tarjetas en escritorio */
        @media (min-width: 768px) {
            .card-info {
                border-bottom: none !important;
            }
        }

        /* Opcional: texto más pequeño en móviles */
        @media (max-width: 767.98px) {
            .title-mobile {
                font-size: 1.1rem;
            }

            .desc-mobile {
                font-size: 0.9rem;
            }

            .without-mt {
                margin-top: 0px !important;
            }
        }
    </style>
@endsection

@section('content')

    <div class="container without-mt mt-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 d-none d-md-block">
                <div class="sidebar sidebar-main">
                    <div class="block block-collapsible-nav" id="accordion" role="tablist">
                        <div class="title block-collapsible-nav-title">
                            <a class="nav-link-perfil" href="{{ route('rewards') }}"><strong><i class="far fa-arrow-alt-circle-left"></i> Ir a Mis Premios </strong></a>

                        </div>
                        <div class="content block-collapsible-nav-content mt-3">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link nav-link-reward" href="{{ route('perfil.usuario') }}">Perfil</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-reward active" href="{{ route('orders.index') }}">Pedidos</a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs móviles visibles solo en pantallas pequeñas -->
            <div class="d-md-none mt-4 col-12 mb-5">
                <div class="text-left mb-2">
                    <a class="nav-link-perfil" href="{{ route('rewards') }}"><strong><i class="far fa-arrow-alt-circle-left"></i> Ir a Mis Premios </strong></a>
                </div>
                <div class="nav nav-tabs" id="mobileTabs" role="tablist">
                    <a class="nav-link nav-link-reward w-50 text-center" href="{{ route('perfil.usuario') }}">Perfil</a>
                    <a class="nav-link nav-link-reward active w-50 text-center" href="{{ route('orders.index') }}">Pedidos</a>
                </div>
            </div>

            <!-- Contenido de la derecha -->
            <div class="col-md-9">
                <div class="info-reclamacion mb-4">
                    <div class="row">
                        @foreach($orders as $order)
                            <div class="col-md-4" id="order-{{ $order->id }}">
                                <article class="card">
                                    <header class="card-header text-center"><strong>{{ $order->status_name }}</strong> </header>
                                    <div class="card-body">
                                        <h6 class="text-center">PEDIDO ID: #{{ $order->id }}</h6>
                                        <article class="card">
                                            <div class="card-body row">
                                                <div class="col"> <strong>Tu pedido llegará aproximadamente:</strong> <br> {{ $order->date_estimated_format ?? 'Fecha no disponible' }}</div>
                                                <div class="col"> <strong>Monto a pagar:</strong> <br> S/. {{ $order->data_totals['total_a_pagar'] }} </div>
                                            </div>
                                        </article>
                                        <div class="track">
                                            <div class="step {{ $order->active_step >= 1 ? 'active' : '' }}">
                                                <span class="icon"> <i class="far fa-file-alt"></i> </span>
                                                <span class="text"> Recibido</span>
                                            </div>
                                            <div class="step {{ $order->active_step >= 2 ? 'active' : '' }}">
                                                <span class="icon"> <i class="fas fa-fire"></i> </span>
                                                <span class="text"> Cocinando</span>
                                            </div>
                                            <div class="step {{ $order->active_step >= 3 ? 'active' : '' }}">
                                                <span class="icon"> <i class="fa fa-truck"></i> </span>
                                                <span class="text"> Enviado </span>
                                            </div>
                                            <div class="step {{ $order->active_step >= 4 ? 'active' : '' }}">
                                                <span class="icon"> <i class="fas fa-home"></i> </span>
                                                <span class="text"> Entregado</span>
                                            </div>

                                        </div>
                                        <hr>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script src="{{ asset('js/cart/cart.js') }}"></script>
    <script src="{{ asset('js/orderPusher.js') }}"></script>
@endsection
