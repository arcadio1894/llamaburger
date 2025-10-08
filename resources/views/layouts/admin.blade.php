<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Dashboard sin areas -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ config('app.name', 'Fuego y Masa') }} | @yield('title')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('admin/dist/img/logo_dashboard.ico') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('admin/plugins/pace-progress/themes/black/pace-theme-flat-top.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('admin/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/jquery-confirm/jquery-confirm.min.css') }}">

    @yield('styles-plugins')

    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">

    <style>
        .dropdown-item.active, .dropdown-item:active{
            background-color: #ffffff !important;
        }

        .btn i {
            width: 1em; /* Ajusta el tamaño según sea necesario */
            height: 1em;
        }

        #body-notifications {
            max-height: 300px; /* Establece la altura máxima del contenedor para activar el scroll */
            overflow: auto;    /* Añade un scroll si el contenido supera la altura máxima */
        }
    </style>
    @yield('styles')

    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini pace-primary layout-fixed layout-navbar-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            {{--<li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link" style="color: red"> Tipo de cambio </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link" style="color: blue" id="tasaCompra"></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link" style="color: green" id="tasaVenta"></a>
            </li>--}}
            {{--<li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Contact</a>
            </li>--}}
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Messages Dropdown Menu -->
            {{--<li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{asset('images/users/'.Auth::user()->image)}}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                </h3>
                                <p class="text-sm">Call me whenever you can...</p>
                                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                            </div>
                        </div>
                        <!-- Message End -->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                </div>
            </li>--}}
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" id="showNotifications">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-danger navbar-badge" id="total_notifications"></span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header" id="quantity_notifications"></span>
                    <div class="dropdown-divider"></div>
                    <div id="body-notifications">

                    </div>
                    <template id="notification-unread">
                        <div class="dropdown-item" >
                            <p class="text-sm">
                                <i class="fas fa-envelope mr-2 text-danger"></i>
                                <span data-message="message" class="text-danger">Nueva cotizacion creada por Operador fgdfgdfgdfg</span>
                                <span class="float-right text-muted text-sm" data-time>Hace 3 mins</span>
                            </p>
                            <br>
                            <a href="#" style="margin-top: 20px" data-read data-content >
                                <span class="float-left text-sm">Marcar como leído</span>
                            </a>
                            <a href="#" style="margin-top: 20px" data-go>
                                <span class="float-right text-sm">Ir</span>
                            </a>
                        </div>
                    </template>
                    <template id="notification-read">
                        <div class="dropdown-item">
                            <p class="text-sm">
                                <i class="fas fa-envelope mr-2"></i>
                                <span data-message="message">Nueva cotizacion creada por Operador fgdfgdfgdfg</span>
                                <span class="float-right text-muted text-sm" data-time>Hace 3 mins</span>
                            </p>
                            {{--<a href="#" style="margin-top: 20px" data-read>
                                <span class="float-left text-sm">Marcar como leído</span>
                            </a>--}}
                            <br>
                            <a href="#" style="margin-top: 20px" data-go>
                                <span class="float-right text-sm">Ir</span>
                            </a>
                        </div>
                    </template>

                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer" id="read-all">Marcar todos como leídos</a>
                </div>
            </li>
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="{{asset('images/users/no_image.png')}}" class="user-image img-circle elevation-2" alt="User Image">
                    <span class="d-none d-md-inline">{{Auth::user()->name}}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <!-- User image -->
                    <li class="user-header bg-primary">
                        <img src="{{asset('images/users/no_image.png')}}" class="img-circle elevation-2" alt="User Image">

                        <p>
                            {{ Auth::user()->name }}
                            <small>Member since Nov. 2012</small>
                        </p>
                    </li>
                    <!-- Menu Body -->
                    {{--<li class="user-body">
                    <div class="row">
                        <div class="col-4 text-center">
                            <a href="#">Followers</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#">Sales</a>
                        </div>
                        <div class="col-4 text-center">
                            <a href="#">Friends</a>
                        </div>
                    </div>
                        <!-- /.row -->
                    </li>--}}
                    <!-- Menu Footer-->
                    <li class="user-footer">
                        <a href="#" class="btn btn-default btn-flat">Perfil</a>
                        <a class="btn btn-default btn-flat float-right" href="{{ route('logout') }}" onclick="event.preventDefault();
                             document.getElementById('logout-form').submit();">
                            <i class="fa fa-power-off"></i>
                            Cerrar sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>

        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ url('/') }}" class="brand-link">
            <img src="{{ asset('admin/dist/img/logo_dashboard.png') }}" alt="ERP Logo" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">Fuego y Masa</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="{{asset('images/users/no_image.png')}}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="{{ route('dashboard.principal') }}" class="d-block">Dashboard</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    {{--@can('access_permission')
                    <li class="nav-header">ADMINISTRADOR</li>
                    <li class="nav-item has-treeview @yield('openAccess')">

                        <a href="#" class="nav-link @yield('activeAccess')">
                            <i class="nav-icon fas fa-eye-slash"></i>
                            <p>
                                Accesos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('list_permission')
                            <li class="nav-item">
                                <a href="{{ route('permission.index') }}" class="nav-link @yield('activePermissions')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Permisos</p>
                                </a>
                            </li>
                            @endcan
                            @can('list_role')
                            <li class="nav-item">
                                <a href="{{ route('role.index') }}" class="nav-link @yield('activeRoles')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>
                            @endcan
                            @can('list_user')
                                <li class="nav-item">
                                    <a href="{{ route('user.index') }}" class="nav-link @yield('activeUser')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Usuarios Activos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('user.indexEnable') }}" class="nav-link @yield('activeUserEnable')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Usuarios Eliminados</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @canany('list_customer', 'list_contactName', 'list_supplier')
                    <li class="nav-header">MANTENEDORES</li>
                    @endcanany
                    @can('list_customer')
                    <li class="nav-item has-treeview @yield('openCustomer')">

                        <a href="#" class="nav-link @yield('activeCustomer')">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>
                                Clientes
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('list_customer')
                            <li class="nav-item">
                                <a href="{{ route('customer.index') }}" class="nav-link @yield('activeListCustomer')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar clientes</p>
                                </a>
                            </li>
                            @endcan
                            @can('create_customer')
                            <li class="nav-item">
                                <a href="{{ route('customer.create') }}" class="nav-link @yield('activeCreateCustomer')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear clientes</p>
                                </a>
                            </li>
                            @endcan
                            @can('destroy_customer')
                            <li class="nav-item">
                                <a href="{{ route('customer.indexrestore') }}" class="nav-link @yield('activeRestoreCustomer')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Restaurar clientes</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('list_contactName')
                    <li class="nav-item has-treeview @yield('openContactName')">
                        <a href="#" class="nav-link @yield('activeContactName')">
                            <i class="nav-icon fas fa-address-book"></i>
                            <p>
                                Contactos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('list_contactName')
                                <li class="nav-item">
                                    <a href="{{ route('contactName.index') }}" class="nav-link @yield('activeListContactName')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listar contactos</p>
                                    </a>
                                </li>
                            @endcan
                            @can('create_contactName')
                                <li class="nav-item">
                                    <a href="{{ route('contactName.create') }}" class="nav-link @yield('activeCreateContactName')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Crear contacto</p>
                                    </a>
                                </li>
                            @endcan
                            @can('destroy_contactName')
                                <li class="nav-item">
                                    <a href="{{ route('contactName.indexrestore') }}" class="nav-link @yield('activeRestoreContactName')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Restaurar contactos</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('list_supplier')
                    <li class="nav-item has-treeview @yield('openSupplier')">
                        <a href="#" class="nav-link @yield('activeSupplier')">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                Proveedores
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('list_supplier')
                            <li class="nav-item">
                                <a href="{{ route('supplier.index') }}" class="nav-link @yield('activeListSupplier')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar proveedores</p>
                                </a>
                            </li>
                            @endcan
                            @can('create_supplier')
                            <li class="nav-item">
                                <a href="{{ route('supplier.create') }}" class="nav-link @yield('activeCreateSupplier')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear proveedores</p>
                                </a>
                            </li>
                            @endcan
                            @can('destroy_supplier')
                                <li class="nav-item">
                                    <a href="{{ route('supplier.indexrestore') }}" class="nav-link @yield('activeRestoreSupplier')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Restaurar proveedores</p>
                                    </a>
                                </li>
                            @endcan
                            --}}{{--@can('assign_supplier')
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Proveedores y materiales</p>
                                </a>
                            </li>
                            @endcan--}}{{--
                        </ul>
                    </li>
                    @endcan--}}
                    @can('accesos.enable')
                    <li class="nav-header">ADMINISTRADOR</li>
                    @endcan
                    @can('accesos.enable')
                    <li class="nav-item has-treeview @yield('openAccess')">

                        <a href="#" class="nav-link @yield('activeAccess')">
                            <i class="nav-icon fas fa-eye-slash"></i>
                            <p>
                                Accesos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('accesos.permissions')
                                <li class="nav-item">
                                    <a href="{{ route('permissions.index') }}" class="nav-link @yield('activePermissions')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Permisos</p>
                                    </a>
                                </li>
                            @endcan
                            @can('accesos.roles')
                                <li class="nav-item">
                                    <a href="{{ route('roles.index') }}" class="nav-link @yield('activeRoles')">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Roles</p>
                                    </a>
                                </li>
                            @endcan
                            @can('accesos.users')
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link @yield('activeUser')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Usuarios Activos</p>
                                </a>
                            </li>
                            @endcan

                        </ul>
                    </li>
                    @endcan
                    @can('pedidos.enable')
                    <li class="nav-header">ORDENES</li>
                    @endcan
                    @can('pedidos.enable')
                    <li class="nav-item has-treeview @yield('openOrders')">
                        <a href="#" class="nav-link @yield('activeOrders')">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>
                                Pedidos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('pedidos.kanban')
                            <li class="nav-item">
                                <a href="{{route('orders.kanban')}}" class="nav-link @yield('activeKanbanOrders')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Kanban pedidos</p>
                                </a>
                            </li>
                            @endcan
                            @can('pedidos.listar')
                            <li class="nav-item">
                                <a href="{{route('orders.list')}}" class="nav-link @yield('activeListOrders')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar pedidos</p>
                                </a>
                            </li>
                            @endcan
                            @can('pedidos.anulados')
                            <li class="nav-item">
                                <a href="{{route('orders.list.annulled')}}" class="nav-link @yield('activeListOrdersAnnulled')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Pedidos anulados</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('caja.enable')
                    <li class="nav-header">CAJA</li>
                    @endcan
                    @can('caja.enable')
                    <li class="nav-item has-treeview @yield('openCashRegister')">
                        <a href="#" class="nav-link @yield('activeCashRegister')">
                            <i class="nav-icon fas fa-truck-loading"></i>
                            <p>
                                MODULO DE CAJA
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('caja.ver_caja')
                            <li class="nav-item">
                                <a href="{{ route('index.cashRegister', 'efectivo') }}" class="nav-link @yield('activeCashRegisterEfectivo')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Caja Efectivo</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('index.cashRegister', 'bancario') }}" class="nav-link @yield('activeCashRegisterBancario')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Caja Bancario</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('locales.enable_tiendas')
                    <li class="nav-header">MODULO DE LOCALES</li>
                    @endcan
                    @can('locales.enable_tiendas')
                    <li class="nav-item has-treeview @yield('openShops')">
                        <a href="#" class="nav-link @yield('activeShop')">
                            <i class="nav-icon fas fa-store"></i>
                            <p>
                                LOCALES
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('locales.listar_tiendas')
                            <li class="nav-item">
                                <a href="{{ route('shop.index') }}" class="nav-link @yield('activeListShop')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado de tiendas</p>
                                </a>
                            </li>
                            @endcan
                            @can('locales.crear_tiendas')
                            <li class="nav-item">
                                <a href="{{ route('shop.create') }}" class="nav-link @yield('activeCreateShop')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear Tiendas</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('locales.enable_zonas')
                    <li class="nav-item has-treeview @yield('openZone')">
                        <a href="#" class="nav-link @yield('activeZone')">
                            <i class="nav-icon fas fa-store"></i>
                            <p>
                                ZONAS
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('locales.crear_zonas')
                            <li class="nav-item">
                                <a href="{{ route('zones.create') }}" class="nav-link @yield('activeCreateZone')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear Zona</p>
                                </a>
                            </li>
                            @endcan
                            @can('locales.gestionar_zonas')
                            <li class="nav-item">
                                <a href="{{ route('zones.index') }}" class="nav-link @yield('activeListZone')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Gestionar Zonas</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('espacios.enable_salas')
                    <li class="nav-header">ESPACIOS</li>
                    @endcan
                    @can('espacios.enable_salas')
                    <li class="nav-item has-treeview @yield('openSalas')">
                        <a href="#" class="nav-link @yield('activeSalas')">
                            <i class="nav-icon fas fa-utensils"></i>
                            <p>
                                Salas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('espacios.enable_salas')
                            <li class="nav-item">
                                <a href="{{route('salas.index')}}" class="nav-link @yield('activeListSalas')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Mesas</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('mantenedores.enable')
                    <li class="nav-header">MANTENEDORES</li>
                    @endcan

                    @can('sliders.enable')
                    <li class="nav-item has-treeview @yield('openSliders')">
                        <a href="#" class="nav-link @yield('activeSliders')">
                            <i class="nav-icon fas fa-images"></i>
                            <p>
                                Slider
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('sliders.listar')
                            <li class="nav-item">
                                <a href="{{route('sliders.index')}}" class="nav-link @yield('activeListSliders')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado imágenes</p>
                                </a>
                            </li>
                            @endcan
                            @can('sliders.crear')
                            <li class="nav-item">
                                <a href="{{route('sliders.create')}}" class="nav-link @yield('activeCreateSliders')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear Imagen</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('cupones.enable')
                    <li class="nav-item has-treeview @yield('openCoupons')">
                        <a href="#" class="nav-link @yield('activeCoupons')">
                            <i class="nav-icon fas fa-ticket-alt"></i>
                            <p>
                                Cupones
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('cupones.listar')
                            <li class="nav-item">
                                <a href="{{route('coupons.index')}}" class="nav-link @yield('activeListCoupons')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar cupones</p>
                                </a>
                            </li>
                            @endcan
                            @can('cupones.crear')
                            <li class="nav-item">
                                <a href="{{route('coupons.create')}}" class="nav-link @yield('activeCreateCoupons')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear cupones</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('productos.enable')
                    <li class="nav-item has-treeview @yield('openProducts')">
                        <a href="#" class="nav-link @yield('activeProducts')">
                            <i class="nav-icon fas fa-pizza-slice"></i>
                            <p>
                                Productos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('productos.listar')
                            <li class="nav-item">
                                <a href="{{route('products.list')}}" class="nav-link @yield('activeListProducts')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar productos</p>
                                </a>
                            </li>
                            @endcan
                            @can('productos.crear')
                            <li class="nav-item">
                                <a href="{{route('product.create')}}" class="nav-link @yield('activeLCreateProducts')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear productos</p>
                                </a>
                            </li>
                            @endcan
                            @can('productos.ver_eliminados')
                            <li class="nav-item">
                                <a href="{{route('products.list.deleted')}}" class="nav-link @yield('activeListDeletedProducts')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Productos eliminados</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('type_products.enable')
                    <li class="nav-item has-treeview @yield('openTypes')">
                        <a href="#" class="nav-link @yield('activeTypes')">
                            <i class="nav-icon fas fa-list"></i>
                            <p>
                                Tipos de Productos
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('type_products.listar')
                            <li class="nav-item">
                                <a href="{{route('types.index')}}" class="nav-link @yield('activeListTypes')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar tipos</p>
                                </a>
                            </li>
                            @endcan
                            @can('type_products.crear')
                            <li class="nav-item">
                                <a href="{{route('types.create')}}" class="nav-link @yield('activeCreateTypes')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear tipo</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('categorias.enable')
                    <li class="nav-item has-treeview @yield('openCategories')">
                        <a href="#" class="nav-link @yield('activeCategories')">
                            <i class="nav-icon fas fa-tags"></i>
                            <p>
                                Categorías
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('categorias.listar')
                            <li class="nav-item">
                                <a href="{{route('categories.index')}}" class="nav-link @yield('activeListCategories')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listar Categorías</p>
                                </a>
                            </li>
                            @endcan
                            @can('categorias.crear')
                            <li class="nav-item">
                                <a href="{{route('categories.create')}}" class="nav-link @yield('activeCreateCategories')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Crear Categoría</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                    @can('rewards.enable')
                    <li class="nav-item has-treeview @yield('openRewards')">
                        <a href="#" class="nav-link @yield('activeRewards')">
                            <i class="nav-icon fas fa-fire"></i>
                            <p>
                                Rewards
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('rewards.listar')
                            <li class="nav-item">
                                <a href="{{route('milestones.index')}}" class="nav-link @yield('activeListRewards')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Listado Hitos</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan

                    @can('reclamos.enable')
                    <li class="nav-header">CENTRO DE AYUDA</li>
                    @endcan
                    @can('reclamos.enable')
                    <li class="nav-item has-treeview @yield('openHelpCenter')">
                        <a href="#" class="nav-link @yield('activeReclamos')">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p>
                                RECLAMOS
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('reclamos.pendientes')
                            <li class="nav-item">
                                <a href="{{route('reclamos.index')}}" class="nav-link @yield('activeReclamosIndex')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reclamos pendientes</p>
                                </a>
                            </li>
                            @endcan
                            @can('reclamos.finalizados')
                            <li class="nav-item">
                                <a href="{{route('reclamos.finalizados')}}" class="nav-link @yield('activeReclamosDelete')">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Reclamos finalizados</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endcan
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        @yield('page-header')
                        {{--<h1 class="m-0 text-dark">Starter Page</h1>--}}

                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        @yield('page-breadcrumb')
                        {{--<ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Starter Page</li>
                        </ol>--}}

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    @yield('page-title')
                    {{--<h5 class="card-title">Card header</h5>--}}
                </div>
                <div class="card-body" id="content-body">
                    @yield('content')
                    {{--<h5 class="card-title">Card title</h5>--}}
                </div>
                {{--<div class="card-footer text-muted">
                    <a href="#" class="btn btn-primary">Card link</a>
                    <a href="#" class="card-link">Another link</a>
                </div>--}}
            </div>
            @yield('content-report')
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- Default to the left -->
        <strong>Copyright &copy; <script>document.write(new Date().getFullYear());</script> <a href="https://www.edesce.com/">EDESCE</a>.</strong> Todos los derechos reservados.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/plugins/pace-progress/pace.min.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('admin/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('admin/plugins/jquery-confirm/jquery-confirm.min.js') }}"></script>
<script src="{{ asset('admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
@yield('plugins')

<!-- AdminLTE App -->
<script src="{{ asset('admin/dist/js/adminlte.min.js') }}"></script>
{{--<script src="{{ asset('/js/layout/admin2.js') }}"></script>--}}

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

</script>
@php
    $__perms = auth()->check()
        ? auth()->user()->getAllPermissions()->pluck('name')->toArray()  // o getPermissionsViaRoles()
        : [];
@endphp

<script>
    // disponible en TODO el sitio y cualquier parcial inyectado por AJAX
    window.PERMISSIONS = @json($__perms);
</script>
<script>
    function hasPerm(name){
        if (!Array.isArray(window.PERMISSIONS)) return false;
        return window.PERMISSIONS.indexOf(name) !== -1;
    }
    function hasAny(perms){
        if (!Array.isArray(perms) || perms.length===0) return false;
        return perms.some(hasPerm);
    }
    function hasAll(perms){
        if (!Array.isArray(perms) || perms.length===0) return false;
        return perms.every(hasPerm);
    }
    function escAttr(s){
        return String(s).replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;'
        }[m]));
    }
</script>

@yield('scripts')

</body>
</html>
