<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-RLQWDGJ81N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-RLQWDGJ81N');
    </script>
    <!-- Basic -->
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1534130690591842');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=1534130690591842&ev=PageView&noscript=1"
        /></noscript>
    <!-- End Meta Pixel Code -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>LLAMABURGER | La Mejor Hamburguesa en Trujillo - Delivery Rápido y Delicioso</title>
    <meta name="description" content="Descubre las Hamburguesas más deliciosas en Trujillo. LlamaBurger ofrece delivery rápido, promociones irresistibles y calidad insuperable. ¡Haz tu pedido ahora!">
    <meta name="keywords" content="Hamburguesas Trujillo, delivery Hamburguesa Trujillo, pizzerías en Trujillo, LlamaBurger, mejor Hamburguesa Trujillo">
    <meta name="author" content="LlamaBurger">
    <meta name="robots" content="index, follow">

    <!-- Open Graph para redes sociales -->
    <meta property="og:title" content="LlamaBurger | La Mejor Hamburguesa en Trujillo">
    <meta property="og:description" content="Disfruta las mejores Hamburguesas en Trujillo. Delivery rápido y promociones exclusivas en LlamaBurger. ¡Prueba el sabor que encanta!">
    <meta property="og:image" content="https://www.fuegoymasa.com/images/Hamburguesa-destacada.jpg">
    <meta property="og:url" content="https://www.fuegoymasa.com">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_PE">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="LlamaBurger | La Mejor Hamburguesa en Trujillo">
    <meta name="twitter:description" content="Haz tu pedido online en LlamaBurger y disfruta de las mejores Hamburguesas en Trujillo. ¡Promociones especiales todos los días!">
    <meta name="twitter:image" content="https://www.fuegoymasa.com/images/Hamburguesa-destacada.jpg">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('landing/images/favicon.png') }}" type="image/x-icon">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://www.fuegoymasa.com">

    <link rel="shortcut icon" href="{{ asset('landing/images/favicon.png') }}" type="">

    <title> LlamaBurger </title>

    <!-- bootstrap core css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('landing/css/bootstrap.css') }}" />

    <!--owl slider stylesheet -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />
    <!-- nice select  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css" integrity="sha512-CruCP+TD3yXzlvvijET8wV5WxxEh5H8P4cmz0RFbKK6FlZ2sYl3AEsKlLPHbniXKSrDdFewhbmBK5skbdsASbQ==" crossorigin="anonymous" />
    <!-- font awesome style -->
    <link href="{{ asset('landing/css/fontawesome-all.min.css') }}" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="{{ asset('landing/css/style.css') }}" rel="stylesheet" />
    <!-- responsive style -->
    <link href="{{ asset('landing/css/responsive.css') }}" rel="stylesheet" />
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('plugins/toastr/toastr.min.css') }}">
    <!-- Jquery-confirm -->
    <link rel="stylesheet" href="{{ asset('plugins/jquery-confirm/jquery-confirm.min.css') }}">

    <style>
        /* Estilos generales del botón WhatsApp */
        body, html {
            height: 100%;
            overflow-x: hidden; /* Evita desplazamiento horizontal */
        }

        /* Añadir posición relativa a body */
        body {
            position: relative;
        }

        /* Ajustes para el botón de WhatsApp */
        .whatsapp-btn {
            position: fixed;
            bottom: 90px; /* Asegura que esté por encima del botón fijo */
            right: 20px;
            z-index: 10000; /* Mayor prioridad para estar encima de otros elementos */
            width: 120px;
            height: 50px;
            background-color: #25D366;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 0 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            animation: breathe 2s ease-in-out infinite;
            flex-direction: row;
            gap: 5px;
        }

        /* Estilos para el contenedor de texto */
        .whatsapp-text {
            display: flex;
            flex-direction: column; /* Organiza el texto en dos líneas */
            justify-content: center;
            align-items: flex-start; /* Alineación del texto al inicio */
            color: white;
            font-size: 12px; /* Tamaño del texto */
            font-weight: bold;
            text-align: left; /* Alineación del texto a la izquierda */
            line-height: 14px; /* Espaciado entre las líneas */
        }

        /* Estilos para cada línea de texto */
        .whatsapp-line {
            line-height: 14px; /* Reducir espacio entre líneas de texto */
        }

        /* Estilos solo al ícono de WhatsApp */
        .whatsapp-btn i {
            color: white;
            font-size: 20px; /* Tamaño del ícono */
            animation: beat 2s ease-in-out infinite;
        }

        /* Estilos de animación del contorno respirando */
        @keyframes breathe {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.5);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
            }
        }

        /* Estilos de animación del ícono latiendo */
        @keyframes beat {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .floating-cart {
            position: fixed;
            top: 90px; /* Ajustado para una posición ideal debajo del navbar */
            right: 20px;
            width: 50px; /* Reducido para hacerlo más compacto */
            height: 50px; /* Igual a width para que sea un círculo */
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            z-index: 1100; /* Asegura que el botón esté encima de otros elementos */
            text-decoration: none;
        }

        .floating-cart i {
            font-size: 20px; /* Tamaño reducido del ícono */
            margin: 0; /* Sin margen */
        }

        #quantityCart {
            font-size: 14px; /* Tamaño ajustado del número */
            font-weight: bold;
            color: white; /* Mantener el color blanco */
            margin-left: 5px; /* Espacio entre el ícono y el número */
        }

        /* Icono de fuego */
        .fuego-icon {
            width: 30px;
            height: 30px;
            animation: pulse-fuego 1.5s infinite;
        }

        .pulse-text {
            animation: pulse-fuego 1.5s infinite;
        }

        @keyframes pulse-fuego {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Opcional: efecto hover */
        .premios-btn:hover {
            text-decoration: none;
            color: #ffc107; /* dorado o como prefieras */
        }
    </style>
    @yield('styles')
</head>
<body>
<div id="app" class="sub_page">
    <div class="hero_area">
        <div class="bg-box" style="background-color: #121513">

        </div>
        <!-- header section strats -->
        <header class="header_section">
            <div class="container">
                <nav class="navbar navbar-expand-lg custom_nav-container ">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <span>
                          LlamaBurger
                        </span>
                    </a>

                    <div class="d-flex align-items-center d-lg-none">

                        <!-- Botón link que envuelve el fuego + texto -->
                        <a href="{{ route('rewards') }}" class="d-flex align-items-center mr-3 text-white text-decoration-none premios-btn">
                            <img src="{{ asset('images/icons/fire.png') }}" alt="Fuego"
                                 class="mr-2 fuego-icon" />
                            <span class="font-weight-bold pulse-text">Premios</span>
                        </a>
                        <!-- Botón hamburguesa -->
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                            <span class=""> </span>
                        </button>
                    </div>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav  mx-auto ">
                            <li class="nav-item @yield('inicio-active')">
                                <a class="nav-link" href="{{ url('/') }}">Inicio <span class="sr-only">(current)</span></a>
                            </li>
                            <li class="nav-item @yield('menu-active')">
                                <a class="nav-link" href="{{ route('home') }}">Menu</a>
                            </li>
                            <li class="nav-item @yield('about-active')">
                                <a class="nav-link" href="{{ route('about') }}">Nosotros</a>
                            </li>
                            @if (Route::has('register'))
                                @guest()
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('register') }}">Regístrate</a>
                                    </li>
                                @endguest
                            @endif

                                {{--<li class="nav-item @yield('orders-active')">
                                    <a class="nav-link" href="{{ route('orders.index') }}">Premios</a>
                                </li>--}}
                            <li class="nav-item @yield('reward-active')">
                                <a class="nav-link" href="{{ route('rewards') }}">Recompensas</a>
                            </li>

                        </ul>
                        <div class="user_option">
                            @if (Route::has('login'))
                                @auth
                                    {{--<a href="{{ url('/home') }}">Usuario</a>--}}
                                    <a href="{{ route('home') }}" class="user_link">
                                        {{ Auth::user()->name }}
                                    </a>

                                    <a title="Cerrar sesión" class="user_link" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                       document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt" style="color: #ff0000;"></i>
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    {{--<li class="nav-item dropdown">
                                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                            {{ Auth::user()->name }}
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                            <a class="dropdown-item" href="{{ route('logout') }}"
                                               onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                                {{ __('Logout') }}
                                            </a>

                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </li>--}}
                                @else
                                    <a href="{{ route('login') }}" class="user_link">
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                    </a>
                                @endguest
                            @endif

                            <a class="cart_link" href="{{ route('cart.show') }}">
                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 456.029 456.029" style="enable-background:new 0 0 456.029 456.029;" xml:space="preserve">
                                            <g>
                                                <g>
                                                    <path d="M345.6,338.862c-29.184,0-53.248,23.552-53.248,53.248c0,29.184,23.552,53.248,53.248,53.248
                                               c29.184,0,53.248-23.552,53.248-53.248C398.336,362.926,374.784,338.862,345.6,338.862z" />
                                                </g>
                                            </g>
                                    <g>
                                        <g>
                                            <path d="M439.296,84.91c-1.024,0-2.56-0.512-4.096-0.512H112.64l-5.12-34.304C104.448,27.566,84.992,10.67,61.952,10.67H20.48
                       C9.216,10.67,0,19.886,0,31.15c0,11.264,9.216,20.48,20.48,20.48h41.472c2.56,0,4.608,2.048,5.12,4.608l31.744,216.064
                       c4.096,27.136,27.648,47.616,55.296,47.616h212.992c26.624,0,49.664-18.944,55.296-45.056l33.28-166.4
                       C457.728,97.71,450.56,86.958,439.296,84.91z" />
                                        </g>
                                    </g>
                                    <g>
                                        <g>
                                            <path d="M215.04,389.55c-1.024-28.16-24.576-50.688-52.736-50.688c-29.696,1.536-52.224,26.112-51.2,55.296
                       c1.024,28.16,24.064,50.688,52.224,50.688h1.024C193.536,443.31,216.576,418.734,215.04,389.55z" />
                                        </g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                    <g>
                                    </g>
                                        </svg>
                                <span class="text-white" id="quantityCart">(0)</span>
                            </a>
                                @auth
                                    @if( Auth::user()->is_admin )
                                        <a href="{{ route('dashboard.principal') }}" class="order_online">
                                            Dashboard
                                        </a>
                                    @endif
                                @endauth
                        </div>
                    </div>
                </nav>
            </div>
        </header>
    </div>
</div>
{{--    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>--}}
<section class="">
    <div class="{{--container--}}">
        <div class="heading_container heading_center {{--pt-5--}}">
            @yield('text-header')

        </div>

        @yield('content')
    </div>
</section>

<!-- footer section -->
<footer class="footer_section">
    <div class="container">
        <div class="row">
            <div class="col-md-4 footer-col">
                <div class="footer_contact">
                    <h4>
                        Contáctanos
                    </h4>
                    <div class="contact_link_box">
                        <a href="">
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <span>
                                        Trujillo
                                    </span>
                        </a>
                        <a href="https://wa.me/51906343258?text=Hola%20FUEGO%20Y%20MASA,%20quiero%20comprar%20una%20Hamburguesa.%20%F0%9F%8D%95" target="_blank">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                            <span>
                                        Whatsapp  906-343-258
                                    </span>
                        </a>
                        <a href="mailto:fuegoymasaperu@gmail.com" target="_blank">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                            <span>
                                        fuegoymasaperu@gmail.com
                                    </span>
                        </a>

                        <a href="{{ route('reclamaciones') }}">
                            <img src="{{ asset('images/checkout/libro_reclamaciones.webp') }}" alt="libro_reclamaciones" style="width: 100px;" >

                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 footer-col">
                <div class="footer_detail">
                    <a href="" class="footer-logo">
                        LlamaBurger
                    </a>
                    <p>
                        Encendemos la pasión en cada creación, con ingredientes frescos y dedicación, para llevar calidad y sabor a cada mesa.
                    </p>
                    <div class="footer_social">
                        <a href="https://www.facebook.com/people/Fuego-y-Masa/61568065745757/" target="_blank">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href=" https://www.linkedin.com/company/fuego-y-masa" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://www.instagram.com/fuegoymasaperu/" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/51906343258?text=Hola%20FUEGO%20Y%20MASA,%20quiero%20comprar%20una%20Hamburguesa.%20%F0%9F%8D%95" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.tiktok.com/@fuegoymasa" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" style="enable-background:new 0 0 456.029 456.029;padding: 7px;">
                                <path d="M448,209.2c-5.4,0.5-10.8,0.8-16.2,0.8c-55.3,0-102.4-36.2-118.8-85.5v204c0,65.4-53,118.4-118.4,118.4c-65.4,0-118.4-53-118.4-118.4c0-63.1,49.3-114.6,111.4-118.2v62.6c-27.3,3.4-48.4,26.7-48.4,55.6c0,30.8,25,55.8,55.8,55.8c30.8,0,55.8-25,55.8-55.8V0h62.6c8.5,59,58.6,104.7,118.4,104.7V209.2z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 footer-col">
                <h4>
                    Horarios
                </h4>
                <p>
                    Lunes a Viernes
                </p>
                <p>
                    6:30 PM - 11:30 PM
                </p>

                <p>
                    Sábados y Domingos
                </p>
                <p>
                    4:00 PM - 11:30 PM
                </p>
            </div>
        </div>
        <div class="footer-info">
            <p>
                &copy; <span id="displayYear"></span> All Rights Reserved By
                <a href="https://edesce.com/">EDESCE</a><br><br>
                &copy; <span id="displayYear"></span> Distributed By
                <a href="https://edesce.com/" target="_blank">EDESCE</a>
            </p>
        </div>
    </div>
</footer>
<!-- footer section -->
@if (request()->routeIs('cart.checkout'))
<div id="business-status" style="display: none; position: fixed; top: 20px; left: 20px; background-color: rgba(255, 0, 0, 0.9); border: 1px solid #cc0000; padding: 15px; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); z-index: 9999; color: #fff;">
    <button id="close-business-status" style="background: none; border: none; font-size: 16px; font-weight: bold; color: #fff; float: right; cursor: pointer;">&times;</button>
    <p id="business-message" style="margin: 0; font-size: 14px; font-weight: bold;"></p>
</div>
@endif

@if (!request()->routeIs('rewards') && !request()->routeIs('perfil.usuario') && !request()->routeIs('orders.index'))
<a href="https://wa.me/51906343258?text=Hola%20FUEGO%20Y%20MASA,%20quiero%20comprar%20una%20Hamburguesa.%20%F0%9F%8D%95" target="_blank" class="whatsapp-btn">
            <span class="whatsapp-text">
                <span class="whatsapp-line">PIDE POR</span>
                <span class="whatsapp-line">WHATSAPP</span>
            </span>
    <i class="fab fa-whatsapp"></i>
</a>
@endif
@if (!request()->routeIs('cart.show') && !request()->routeIs('cart.checkout') && !request()->routeIs('product.custom') && !request()->routeIs('reclamaciones') && !request()->routeIs('showlocals') && !request()->routeIs('rewards') && !request()->routeIs('perfil.usuario') && !request()->routeIs('orders.index'))
    {{-- Botón flotante --}}
    <a href="{{ route('cart.show') }}" id="cartButton" class="floating-cart d-sm-none">
        <i class="fas fa-shopping-cart"></i>
        <span id="quantityCart2">(0)</span>
    </a>
@endif

<!-- jQery -->
<script src="{{ asset('landing/js/jquery-3.4.1.min.js') }}"></script>
<!-- popper js -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
</script>
<!-- bootstrap js -->
<script src="{{ asset('landing/js/bootstrap.js') }}"></script>
<!-- owl slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js">
</script>
<!-- isotope js -->
<script src="https://unpkg.com/isotope-layout@3.0.4/dist/isotope.pkgd.min.js"></script>
<!-- nice select -->
<!-- custom js -->
<script src="{{ asset('landing/js/custom.js') }}"></script>
<!-- Google Map -->
{{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCh39n5U-4IoWpsVGUHWdqB6puEkhRLdmI&callback=myMap">
</script>--}}
<!-- End Google Map -->
<!-- Toastr -->
<script src="{{ asset('plugins/toastr/toastr.min.js') }}"></script>
<!-- Jquery-confirm -->
<script src="{{ asset('plugins/jquery-confirm/jquery-confirm.min.js') }}"></script>

@yield('scripts')

<script>
    // Llamar al método para verificar horario de atención
    $.ajax({
        url: '/api/business-hours', // Cambia la ruta si es necesario
        method: 'GET',
        success: function (response) {
            if (!response.is_open) {
                // Mostrar mensaje si el negocio está cerrado
                $('#business-message').text(response.message);
                $('#business-status').fadeIn();
            }
        },
        error: function () {
            console.error('No se pudo verificar el horario de atención.');
        }
    });

    // Cerrar el mensaje al hacer clic en el botón "X"
    $('#close-business-status').on('click', function () {
        $('#business-status').fadeOut();
    });

    updateCartQuantity();

    function updateCartQuantity() {
        const authCheckUrl = '/auth/check'; // URL para verificar autenticación
        const cartQuantityUrl = '/cart/quantity'; // URL para obtener la cantidad del carrito

        $.ajax({
            url: authCheckUrl,
            type: "GET",
            success: function (response) {
                // Si no está autenticado, obtener la cantidad desde localStorage
                let cart = JSON.parse(localStorage.getItem('cart')) || [];

                // Contar el número de productos únicos
                let totalItems = cart.length;

                // Actualizar el contenido del span
                $("#quantityCart").html(`(${totalItems})`);

                $("#quantityCart2").html(`(${totalItems})`);

            },
            error: function (error) {
                console.error("Error al verificar autenticación:", error);
                $("#quantityCart").html(`(0)`);
            }
        });
    }

</script>
</body>
</html>
