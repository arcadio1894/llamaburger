@extends('layouts.admin')
@section('title','Atención de ' . $atencion->mesa->nombre)

@section('styles')

    <style>
        /* ===== CATEGORÍAS + BUSCADOR ===== */
        .products-topbar { }

        .cat-strip{
            display:flex;
            gap:.375rem;
            overflow-x:auto;
            white-space:nowrap;     /* evita salto de línea */
            padding-bottom:.25rem;
            min-width:0;            /* permite encogerse en flex */
        }
        .cat-strip .btn{ white-space:nowrap; }
        .btn-cat.active{ background:#343a40; color:#fff; border-color:#343a40; }

        .search-wrap{ max-width:260px; }

        /* Desktop: buscador a la derecha */
        @media (min-width:768px){
            .products-topbar{ display:flex; flex-wrap:nowrap; align-items:center; }
            .cat-strip{ flex:1; }
        }
        /* Mobile: buscador debajo */
        @media (max-width:767.98px){
            .products-topbar{ display:flex; flex-direction:column; }
            .search-wrap{ width:100%; margin-top:.5rem; }
        }

        /* ===== GRILLA DE PRODUCTOS ===== */
        .products-grid{
            display:grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap:.5rem;
            margin-top: 12px;
        }
        .product-card{ cursor:pointer; border-radius:.5rem; }
        .product-card .card-img-top{ height:80px; object-fit:cover; }
        .product-card .name{ font-size:.8rem; line-height:1.2; min-height:2.2em; }
        .product-card .price{ font-size:.75rem; color:#6c757d; }

        /* ===== PANEL PEDIDO (desktop) ===== */
        .sticky-order{
            position: sticky;
            top: calc(56px + 8px);  /* ajusta según tu header/navbar */
            max-height: calc(100vh - 80px);
            overflow: hidden;
            display:flex;
            flex-direction:column;
        }
        #order-panel-body{
            overflow:auto;
            flex:1;                 /* asegura que el footer quede fijo en la tarjeta */
            min-height:0;
        }

        /* ===== FAB móvil ===== */
        .order-fab{
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 1051;
            box-shadow: 0 6px 16px rgba(0,0,0,.2);
            border-radius: 999px;
        }

        /* ===== CONTROL-SIDEBAR (AdminLTE) ===== */
        .control-sidebar{
            position: fixed;
            top: 0; right: 0; bottom: 0;
            height: 100dvh;              /* alto real en móviles */
            width: 360px; max-width: 92vw;
            background: #fff;
            overflow: hidden;             /* el scroll vive en .cs-body */
            z-index: 1040;
        }

        #order-aside .cs-wrap{
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            height: 100%;
        }

        /* Header */
        #order-aside .cs-header{
            padding: .75rem 1rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* TOP (totales + botón): fijo arriba, NO scrollea */
        #order-aside .cs-top{
            position: sticky; top: 0;
            z-index: 1;
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,.08);
            padding: .75rem 1rem;
        }

        /* CUERPO: SIEMPRE scrolleable (solo items) */
        #order-aside .cs-body{
            flex: 1; min-height: 0;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding: .75rem 1rem;
        }

        /* (opcional) un poquito de aire entre items */
        #order-aside .cs-body .media + .media{ margin-top:.5rem; }

        /* un poco más ancho en tablet */
        @media (min-width: 768px){
            .control-sidebar{ width: 380px; }
        }

        /* eliminar doble scroll y ocultar FAB al abrir aside */
        body.control-sidebar-slide-open{ overflow: hidden; }
        body.control-sidebar-slide-open .order-fab{ display: none !important; }

        /* ===== Modal base ===== */
        .modal{
            position:fixed; inset:0; z-index:1060;
            display:flex; align-items:flex-end; justify-content:center;
            background:rgba(0,0,0,.35);
        }
        .modal[hidden]{display:none}
        .modal__card{
            background:#fff; width:100%; max-width:720px;
            border-radius:16px 16px 0 0; overflow:hidden;
            box-shadow:0 16px 40px rgba(0,0,0,.25);
        }

        /* ===== Header ===== */
        .modal__header{
            display:flex; align-items:center; gap:8px; justify-content:space-between;
            padding:12px 14px; border-bottom:1px solid #eef1f4;
        }
        .modal__title{
            margin:0; font-size:1.05rem; font-weight:800; line-height:1.25; flex:1;
            overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;
        }
        .po-unit{
            font-size:.75rem; font-weight:700; color:#374151;
            background:#f3f4f6; border-radius:999px; padding:4px 8px; white-space:nowrap;
        }
        .modal__close{
            border:none; background:#eef2f7; border-radius:10px; padding:6px 10px; font-weight:700;
        }

        /* ===== Body (scroll only aquí) ===== */
        .modal__body{
            padding:10px 12px; overflow:auto;
            max-height: min(60dvh, 520px);
        }

        /* ===== Group ===== */
        .group{margin-bottom:12px}
        .group__title{font-weight:800; margin-bottom:4px}
        .group__hint{font-size:.82rem; color:#6b7280; margin-bottom:8px}

        /* ===== Opción (fila táctil) ===== */
        .po-item{
            display:flex; align-items:center; gap:10px;
            padding:12px; border:1px solid #e7e9ee; border-radius:12px; margin-bottom:10px;
            background:#fff; transition:.15s ease; cursor:pointer;
        }
        .po-item:hover{background:#f9fafb}
        .po-item input{width:18px; height:18px}
        .po-item__text{flex:1; line-height:1.35; font-weight:600}
        .po-item__delta{font-weight:700; white-space:nowrap; color:#374151}

        /* ===== Footer fijo ===== */
        .modal__footer{
            position:sticky; bottom:0; z-index:1;
            display:flex; align-items:center; justify-content:space-between; gap:10px;
            padding:10px 12px; border-top:1px solid #eef1f4; background:#fff;
        }
        .po-summary{
            display:flex; align-items:center; gap:6px;
            flex:1; min-width:0; flex-wrap:nowrap;
        }
        .po-summary input#po-qty{
            width:48px; text-align:center;
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:6px 0;
            font-weight:700;
        }
        .po-summary [data-po-qty-inc],
        .po-summary [data-po-qty-dec]{
            border:1px solid #d1d5db;
            background:#fff;
            border-radius:10px;
            width:34px; height:34px;
            font-weight:800;
            line-height:1;
        }
        .po-summary span.label-icon{
            font-size:1rem; color:#6b7280;
        }
        #po-total{
            font-weight:800;
            font-size:.95rem;
        }
        #po-save{
            flex-shrink:0;
            min-width:46px;
            height:42px;
            border-radius:12px;
            padding:.6rem .8rem;
            font-weight:800;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:6px;
        }
        #po-save i{font-size:1rem;}
        @media (max-width:480px){
            #po-save{min-width:100px; font-size:.9rem;}
        }

        /* ===== Ajuste “full sheet” en móviles pequeños ===== */
        @media (max-width: 480px){
            .modal{align-items:flex-end}
            .modal__card{border-radius:16px 16px 0 0}
            .modal__body{max-height: calc(70dvh - 0px);}
        }

        /* Safe area iOS */
        .modal__footer{ padding-bottom: calc(12px + env(safe-area-inset-bottom)); }
    </style>
@endsection

@section('content')
    <div class="card">

        <h4>Mesa {{ $atencion->mesa->nombre }} — Mozo: {{ $atencion->mozo->nombre }}</h4>

        <ul class="nav nav-tabs mb-2">
            @foreach($comandas as $c)
                <li class="nav-item">
                    <a class="nav-link {{ $c->id === $comanda->id ? 'active' : '' }}"
                       href="{{ route('atenciones.comanda.show', [$atencion->id, $c->numero]) }}">
                        Comanda {{ $c->numero }}
                    </a>
                </li>
            @endforeach
            <li class="nav-item ml-auto">
                <form method="POST" action="{{ route('comandas.createNext', $atencion->id) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">+ Nueva comanda</button>
                </form>
            </li>
        </ul>
    </div>

    <!-- TOPBAR: categorías + buscador -->
    <div class="products-topbar mb-2">
        <div id="categories" class="cat-strip"></div>
        <div class="search-wrap ml-md-2">
            <div class="input-group input-group-sm">
                <input id="prod-search" type="text" class="form-control" placeholder="Buscar producto..." autocomplete="off">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" id="btn-clear-search" type="button" title="Limpiar">&times;</button>
                </div>
            </div>
        </div>
    </div>

    <!-- GRID + PANEL dentro de una MISMA ROW -->
    <div class="row">
        <!-- Columna de productos -->
        <div class="col-12 col-lg-8">
            <div id="products" class="products-grid"></div>
        </div>

        <!-- Panel de pedido (solo desktop / lg+) -->
        <div class="col-12 col-lg-4 d-none d-lg-block">
            <div id="order-panel" class="card sticky-order">
                <div class="card-header py-2"><strong>Pedido</strong></div>
                <div id="order-panel-body" class="card-body p-2"></div>
                <div class="card-footer p-2">
                    <div class="d-flex justify-content-between small">
                        <span>Sub Total:</span><span id="ord-subtotal">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Descuento:</span><span id="ord-discount">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>IGV:</span><span id="ord-igv">S/ 0.00</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between font-weight-bold">
                        <span>Total:</span><span id="ord-total">S/ 0.00</span>
                    </div>
                    <button id="btn-send-kitchen" class="btn btn-dark btn-block mt-2">ENVIAR A COCINA</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FAB móvil que abre el control-sidebar -->
    <button id="fab-order"
            class="btn btn-primary btn-lg order-fab d-lg-none"
            data-widget="control-sidebar" data-slide="true" aria-label="Ver pedido">
        <i class="fas fa-receipt"></i>
        <span id="fab-count" class="badge badge-light ml-1">0</span>
    </button>

    <!-- Control Sidebar de AdminLTE (slide right) -->
    <aside class="control-sidebar control-sidebar-light" id="order-aside">
        <div class="cs-wrap">
            <div class="cs-header">
                <h5 class="mb-0">Pedido</h5>
                <button class="btn btn-sm btn-outline-secondary" data-widget="control-sidebar" data-slide="true">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- TOP: Totales + botón (queda SIEMPRE visible) -->
            <div class="cs-top" id="order-aside-top">
                <div class="small d-flex justify-content-between"><span>Sub Total:</span><span id="a-subtotal">S/ 0.00</span></div>
                <div class="small d-flex justify-content-between"><span>Descuento:</span><span id="a-discount">S/ 0.00</span></div>
                <div class="small d-flex justify-content-between"><span>IGV:</span><span id="a-igv">S/ 0.00</span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between font-weight-bold"><span>Total:</span><span id="a-total">S/ 0.00</span></div>
                <button id="a-send-kitchen" class="btn btn-dark btn-block mt-2">ENVIAR A COCINA</button>
            </div>

            <!-- BODY: lista de ítems (SCROLL) -->
            <div id="order-aside-body" class="cs-body"></div>
        </div>
    </aside>

    <!-- pon esto al final de tu Blade -->
    <div id="productOptionsModal" class="modal" hidden>
        <div class="modal__card">
            <header class="modal__header">
                <h3 id="po-title" class="modal__title">Configurar producto</h3>
                <span id="po-unit" class="po-unit">S/ 0.00</span>
                <button class="modal__close" data-po-close>&times;</button>
            </header>
            <div id="po-body" class="modal__body"></div>
            <footer class="modal__footer">
                <div class="po-summary">
                    <span class="label-icon"><i class="fas fa-sort-numeric-up"></i></span>
                    <button data-po-qty-dec>-</button>
                    <input id="po-qty" type="number" value="1" min="1">
                    <button data-po-qty-inc>+</button>
                    <strong id="po-total">S/ 0.00</strong>
                </div>
                <button id="po-save" class="btn btn-primary" disabled>
                    <i class="fas fa-check"></i>
                </button>
            </footer>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.ATENCION_ID   = {{ $atencion->id }};
        window.COMANDA_ID    = {{ $comanda->id }};
        window.COMANDA_NUM   = {{ $comanda->numero }};
        window.COMANDA_ITEMS = @json($itemsPayload);
        window.COMANDA_TOTALS= @json($totalsPayload);
    </script>

    <script src="{{ asset('js/atenciones/show.js') }}"></script>

@endsection