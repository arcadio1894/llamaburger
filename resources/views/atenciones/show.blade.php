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
    </style>
@endsection

@section('content')
    <div class="card">
        {{--<div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Mesa:</strong> {{ $atencion->mesa->nombre }} |
                <strong>Mozo:</strong> {{ $atencion->mozo->nombre }} |
                <strong>Personas:</strong> {{ $atencion->personas }}
            </div>
            <button class="btn btn-danger" id="btn-desocupar">Desocupar mesa</button>
        </div>
        <div class="card-body">

        </div>--}}
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

    {{--<script>
        $('#btn-desocupar').on('click', function(){
            $.post(`/dashboard/atenciones/{{ $atencion->id }}/cerrar`, {}, function(resp){
                if(resp.ok){
                    toastr.success(resp.msg);
                    window.location = resp.redirect_url; // vuelve a la lista de salas/mesas
                } else {
                    toastr.error(resp.msg || 'No se pudo desocupar la mesa');
                }
            }, 'json').fail(() => toastr.error('Error'));
        });

    </script>
    <script>
        // Helpers de seguridad (ya los puedes tener)
        function safe(val, fb=''){ return (val===undefined || val===null) ? fb : val; }

        // Imagen pública (ajusta base si cambias carpeta)
        function productImageUrl(p) {
            const file = safe(p.image, '');
            if (/^https?:\/\//i.test(file)) return file;
            const basePath = '/images/products/';  // ✅ sin /public
            return file ? (basePath + file) : '/img/noimage.png';
        }

        // Precio a mostrar: mínimo de product_types si existen; si no, unit_price
        function productDisplayPrice(p) {
            let price = null;

            if (Array.isArray(p.product_types) && p.product_types.length > 0) {
                const min = p.product_types.reduce((acc, cur) => {
                    const val = parseFloat(cur.price);
                    if (isNaN(val)) return acc;
                    return acc === null ? val : Math.min(acc, val);
                }, null);
                price = min;
            } else {
                const val = parseFloat(p.unit_price);
                price = isNaN(val) ? null : val;
            }

            //if (Number(p.visibility_price_real) === 0) return null;
            return price;
        }

        // Estado global simple
        let ALL_CATEGORIES = [];
        let SELECTED_CAT = 'all';
        let QUERY = '';

        // Carga inicial
        $(function(){
            $.get('/dashboard/mesa/productos', function(res){
                if(!res.ok) return toastr.error('No se pudo cargar productos');
                ALL_CATEGORIES = res.categories || [];
                renderCategories(ALL_CATEGORIES);
                renderFiltered(); // "Todas" + sin búsqueda
            });

            // Click categoría
            $(document).on('click', '.btn-cat', function(){
                $('.btn-cat').removeClass('active');
                $(this).addClass('active');
                SELECTED_CAT = $(this).data('id');   // 'all' o id numérico
                renderFiltered();
            });

            // Búsqueda (debounce)
            const debounced = debounce(function(val){
                QUERY = (val || '').trim().toLowerCase();
                renderFiltered();
            }, 250);

            $('#prod-search').on('input', function(){
                debounced(this.value);
            });

            // Limpiar búsqueda
            $('#btn-clear-search').on('click', function(){
                $('#prod-search').val('');
                QUERY = '';
                renderFiltered();
            });
        });

        // Render categorías
        function renderCategories(cats){
            let html = `<button type="button" class="btn btn-outline-dark btn-sm btn-cat active" data-id="all">Todas</button>`;
            cats.forEach(c => {
                html += `<button type="button" class="btn btn-outline-dark btn-sm btn-cat" data-id="${c.id}">${safe(c.name,'(Sin nombre)')}</button>`;
            });
            $('#categories').html(html);
        }

        // Aplica categoría + búsqueda
        function renderFiltered(){
            let products = [];

            if (SELECTED_CAT === 'all') {
                products = ALL_CATEGORIES.flatMap(c => c.products || []);
            } else {
                const cat = ALL_CATEGORIES.find(c => String(c.id) === String(SELECTED_CAT));
                products = cat ? (cat.products || []) : [];
            }

            if (QUERY) {
                products = products.filter(p => {
                    const name = (safe(p.full_name, '') || safe(p.name,'')).toString().toLowerCase();
                    return name.includes(QUERY);
                });
            }

            renderProducts(products);
        }

        // Render productos mini
        function renderProducts(products){
            if (!Array.isArray(products) || products.length === 0) {
                $('#products').html('<div class="text-center text-muted">Sin productos</div>');
                return;
            }

            let html = '';
            products.forEach(p => {
                const name  = safe(p.full_name, safe(p.name,'Producto'));
                const img   = productImageUrl(p);
                const price = productDisplayPrice(p); // puede ser null si ocultas precio

                html += `
                  <div class="card product-card"
                       data-id="${p.id}"
                       data-name="${escAttr(name)}"
                       data-price="${price === null ? '' : price}">
                    <img src="${img}" class="card-img-top" alt="${escAttr(name)}">
                    <div class="card-body p-2 text-center">
                      <div class="name">${escAttr(name)}</div>
                      <div class="price">${price===null ? '' : ('S/ ' + Number(price).toFixed(2))}</div>
                    </div>
                  </div>`;
            });

            $('#products').html(html);
        }

        // Debounce helper
        function debounce(fn, wait){
            let t; return function(...args){
                clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait);
            }
        }

        // TODO: implementa addToOrder(productId)
        function addToOrder(product){
            const name  = product.name || product.full_name || product.title || 'Producto';
            const price = parseFloat(product.price ?? product.unit_price ?? 0) || 0;

            const idx = ORDER.items.findIndex(it => it.id === product.id);
            if (idx === -1) {
                ORDER.items.push({ id: product.id, name, qty: 1, price });
            } else {
                ORDER.items[idx].qty += 1;
            }
            renderOrder();
        }

        // Captura click en cualquier .product-card
        $(document).on('click', '.product-card', function(){
            const id    = parseInt(this.dataset.id, 10);
            const name  = this.dataset.name || 'Producto';
            const price = parseFloat(this.dataset.price);

            const prod = {
                id,
                name,
                unit_price: isNaN(price) ? 0 : price
            };

            addToOrder(prod);
        });
    </script>
    <script>
        // Estado del pedido (ejemplo simple)
        const ORDER = {
            items: [] // { id, name, qty, price }
        };

        // Añadir al pedido (llámalo desde tu click en producto)
        function addToOrder(product){
            // product: {id, full_name/name, unit_price}
            const idx = ORDER.items.findIndex(it => it.id === product.id);
            if (idx === -1) {
                ORDER.items.push({ id: product.id, name: product.full_name || product.name, qty: 1, price: parseFloat(product.unit_price) || 0 });
            } else {
                ORDER.items[idx].qty += 1;
            }
            renderOrder();
        }

        // Quitar / cambiar cantidad
        function incItem(id, d=1){
            const it = ORDER.items.find(x=>x.id===id);
            if(!it) return;
            it.qty += d;
            if (it.qty <= 0) ORDER.items = ORDER.items.filter(x=>x.id!==id);
            renderOrder();
        }

        function formatMoney(n){ return 'S/ ' + (Number(n||0)).toFixed(2); }

        // Render pedido en ambos contenedores
        function renderOrder(){
            // cuerpo
            const html = ORDER.items.map(it => `
                  <div class="media align-items-center mb-2">
                    <div class="media-body">
                      <div class="d-flex justify-content-between">
                        <strong>${it.name}</strong>
                        <span>${formatMoney(it.price)}</span>
                      </div>
                      <div class="d-flex align-items-center mt-1">
                        <button class="btn btn-xs btn-outline-secondary mr-1" onclick="incItem(${it.id}, -1)"><i class="fas fa-minus"></i></button>
                        <span>${it.qty}</span>
                        <button class="btn btn-xs btn-outline-secondary ml-1" onclick="incItem(${it.id}, +1)"><i class="fas fa-plus"></i></button>
                        <span class="ml-auto text-muted small">${formatMoney(it.qty * it.price)}</span>
                      </div>
                    </div>
                  </div>
                `).join('');

            // Totales (lógica real a tu gusto)
            const sub = ORDER.items.reduce((s,it)=> s + it.qty*it.price, 0);
            const dscto = 0;
            const igv = sub * 0.18;
            const tot = sub - dscto + igv;

            // Desktop
            $('#order-panel-body').html(html || '<div class="text-muted">Sin items</div>');
            $('#ord-subtotal').text(formatMoney(sub));
            $('#ord-discount').text(formatMoney(dscto));
            $('#ord-igv').text(formatMoney(igv));
            $('#ord-total').text(formatMoney(tot));

            // Aside
            $('#order-aside-body').html(html || '<div class="text-muted">Sin items</div>');
            $('#a-subtotal').text(formatMoney(sub));
            $('#a-discount').text(formatMoney(dscto));
            $('#a-igv').text(formatMoney(igv));
            $('#a-total').text(formatMoney(tot));

            // Badge del FAB
            $('#fab-count').text(ORDER.items.reduce((s,it)=>s+it.qty,0));
        }

        // Enviar a cocina (ambos botones apuntan a lo mismo)
        $(document).on('click', '#btn-send-kitchen, #a-send-kitchen', function(){
            if (ORDER.items.length === 0) {
                return toastr.warning('No hay productos en el pedido.');
            }
            // TODO: AJAX para enviar comanda
            toastr.success('Comanda enviada a cocina.');
        });

        // Render inicial vacío
        renderOrder();
    </script>--}}
@endsection