(function($){

    // ---------- Helpers ----------
    function safe(val, fb){ return (val===undefined || val===null) ? (fb===undefined?'':fb) : val; }
    function escAttr(s){
        return String(s).replace(/[&<>"']/g, function(m){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);
        });
    }
    function debounce(fn, wait){
        var t;
        return function(){
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function(){ fn.apply(ctx,args); }, wait);
        };
    }
    function formatMoney(n){ return 'S/ ' + (Number(n||0)).toFixed(2); }

    // Imagen pública
    function productImageUrl(p) {
        var file = safe(p.image, '');
        if (/^https?:\/\//i.test(file)) return file;
        var basePath = '/images/products/';
        return file ? (basePath + file) : '/img/noimage.png';
    }

    // Precio mostrado: min de product_types o unit_price
    function productDisplayPrice(p) {
        var price = null;
        if ($.isArray(p.product_types) && p.product_types.length > 0) {
            var min = null;
            for (var i=0;i<p.product_types.length;i++){
                var val = parseFloat(p.product_types[i].price);
                if (!isNaN(val)) min = (min===null) ? val : Math.min(min,val);
            }
            price = min;
        } else {
            var v = parseFloat(p.unit_price);
            price = isNaN(v) ? null : v;
        }
        return price;
    }

    // ---------- Estado global ----------
    var ORDER = { items: [] }; // { id(product_id), name, qty, price, server_id? }
    var ALL_CATEGORIES = [];
    var SELECTED_CAT = 'all';
    var QUERY = '';

    // ---------- Render pedido ----------
    function renderOrder(){
        var html = ORDER.items.map(function(it){
            return ''+
                '<div class="media align-items-center">'+
                '  <div class="media-body">'+
                '    <div class="d-flex justify-content-between">'+
                '      <strong>'+ escAttr(it.name) +'</strong>'+
                '      <span>'+ formatMoney(it.price) +'</span>'+
                '    </div>'+
                '    <div class="d-flex align-items-center mt-1">'+
                '      <button class="btn btn-xs btn-outline-secondary mr-1" onclick="incItem('+it.id+', -1, '+(it.server_id||'null')+')"><i class="fas fa-minus"></i></button>'+
                '      <span>'+ it.qty +'</span>'+
                '      <button class="btn btn-xs btn-outline-secondary ml-1" onclick="incItem('+it.id+', 1, '+(it.server_id||'null')+')"><i class="fas fa-plus"></i></button>'+
                '      <span class="ml-auto text-muted small">'+ formatMoney(it.qty * it.price) +'</span>'+
                '    </div>'+
                '  </div>'+
                '</div>';
        }).join('');

        // Precios incluyen IGV → sum es "gravado"
        var gravado   = ORDER.items.reduce(function(s,it){ return s + it.qty*it.price; }, 0);
        var descuento = 0;
        var neto      = Math.max(gravado - descuento, 0);
        var base      = +(neto / 1.18).toFixed(2);
        var igv       = +(neto - base).toFixed(2);
        var total     = neto;

        // Cuerpos
        $('#order-panel-body, #order-aside-body').html(html || '<div class="text-muted">Sin items</div>');

        // Totales (panel desktop y aside top)
        $('#ord-subtotal, #a-subtotal').text(formatMoney(base));
        $('#ord-discount, #a-discount').text(formatMoney(descuento));
        $('#ord-igv, #a-igv').text(formatMoney(igv));
        $('#ord-total, #a-total').text(formatMoney(total));

        // Badge del FAB
        $('#fab-count').text(ORDER.items.reduce(function(s,it){ return s+it.qty; },0));

        sizeOrderAsideBody(); // recalcular alto del área scrolleable
    }

    // ---------- Hidratar desde servidor ----------
    function hydrateFromServer(){
        var serverItems = window.COMANDA_ITEMS || [];
        ORDER.items = serverItems.map(function(it){
            return { id:it.product_id, name:it.name, qty:it.qty, price:it.price, server_id:it.server_id };
        });

        var t = window.COMANDA_TOTALS || null;
        if (t){
            $('#ord-subtotal, #a-subtotal').text(formatMoney(t.subtotal));
            $('#ord-discount, #a-discount').text(formatMoney(t.descuento));
            $('#ord-igv, #a-igv').text(formatMoney(t.igv));
            $('#ord-total, #a-total').text(formatMoney(t.total));
        }

        renderOrder();
    }

    // ---------- Render categorías y productos ----------
    function renderCategories(cats){
        var html = '<button type="button" class="btn btn-outline-dark btn-sm btn-cat active" data-id="all">Todas</button>';
        for (var i=0;i<cats.length;i++){
            var c = cats[i];
            html += '<button type="button" class="btn btn-outline-dark btn-sm btn-cat" data-id="'+ c.id +'">'+ escAttr(safe(c.name,'(Sin nombre)')) +'</button>';
        }
        $('#categories').html(html);
    }

    function renderFiltered(){
        var products = [];
        if (SELECTED_CAT === 'all') {
            for (var i=0;i<ALL_CATEGORIES.length;i++){
                var arr = ALL_CATEGORIES[i].products || [];
                for (var j=0;j<arr.length;j++) products.push(arr[j]);
            }
        } else {
            var cat = null;
            for (var k=0;k<ALL_CATEGORIES.length;k++){
                if (String(ALL_CATEGORIES[k].id) === String(SELECTED_CAT)){ cat = ALL_CATEGORIES[k]; break; }
            }
            products = cat ? (cat.products || []) : [];
        }

        if (QUERY) {
            var q = QUERY.toLowerCase();
            products = products.filter(function(p){
                var name = (safe(p.full_name, '') || safe(p.name,'')).toString().toLowerCase();
                return name.indexOf(q) !== -1;
            });
        }

        renderProducts(products);
    }

    function renderProducts(products){
        if (!$.isArray(products) || products.length === 0) {
            $('#products').html('<div class="text-center text-muted">Sin productos</div>');
            return;
        }
        var html = '';
        for (var i=0;i<products.length;i++){
            var p     = products[i];
            var name  = safe(p.full_name, safe(p.name,'Producto'));
            var img   = productImageUrl(p);
            var price = productDisplayPrice(p);

            html += ''+
                '<div class="card product-card" '+
                '     data-id="'+ p.id +'" '+
                '     data-name="'+ escAttr(name) +'" '+
                '     data-price="'+ (price===null ? '' : price) +'">'+
                '  <img src="'+ img +'" class="card-img-top" alt="'+ escAttr(name) +'">'+
                '  <div class="card-body p-2 text-center">'+
                '    <div class="name">'+ escAttr(name) +'</div>'+
                '    <div class="price">'+ (price===null ? '' : ('S/ ' + Number(price).toFixed(2))) +'</div>'+
                '  </div>'+
                '</div>';
        }
        $('#products').html(html);
    }

    function syncTotals(t){
        if(!t) return;
        $('#ord-subtotal, #a-subtotal').text(formatMoney(t.subtotal));
        $('#ord-discount, #a-discount').text(formatMoney(t.descuento));
        $('#ord-igv, #a-igv').text(formatMoney(t.igv));
        $('#ord-total, #a-total').text(formatMoney(t.total));
    }

    // ---------- Acciones del pedido ----------
    // (local helper no persistente)
    function addToOrderLocal(product){
        var idx = -1;
        for (var i=0;i<ORDER.items.length;i++){
            if (ORDER.items[i].id === product.id){ idx = i; break; }
        }
        if (idx === -1) {
            ORDER.items.push({ id: product.id, name: product.name || product.full_name || 'Producto', qty: 1, price: parseFloat(product.unit_price)||0 });
        } else {
            ORDER.items[idx].qty += 1;
        }
        renderOrder();
    }

    // Persistente
    function addToOrder(product){
        $.post('/dashboard/comandas/' + window.COMANDA_ID + '/items', {
            product_id: product.id,
            cantidad: 1
        }, function(res){
            if(!res || !res.ok) { toastr.error(res && res.msg ? res.msg : 'No se pudo agregar'); return; }

            var it = res.item; // {id, product_id, name, price, qty}
            var idx = -1;
            for (var i=0;i<ORDER.items.length;i++){
                if (ORDER.items[i].id === it.product_id){ idx = i; break; }
            }
            if (idx === -1) {
                ORDER.items.push({ id: it.product_id, name: it.name, qty: it.qty, price: it.price, server_id: it.id });
            } else {
                ORDER.items[idx].qty   = it.qty;
                ORDER.items[idx].price = it.price;
                ORDER.items[idx].name  = it.name;
                ORDER.items[idx].server_id = it.id;
            }

            renderOrder();
            syncTotals(res.totals);
            toastr.success('Producto agregado');
        }, 'json').fail(function(){ toastr.error('Error al agregar producto'); });
    }

    // +/- (expuesta globalmente para los botones renderizados)
    window.incItem = function(productId, d, serverId){
        if (!serverId) { toastr.warning('Item no persistido aún.'); return; }

        $.post('/dashboard/comanda-items/' + serverId + '/inc', { delta: d }, function(res){
            if(!res || !res.ok){ toastr.error(res && res.msg ? res.msg : 'No se pudo actualizar'); return; }

            var idx = -1;
            for (var i=0;i<ORDER.items.length;i++){
                if (ORDER.items[i].id === productId){ idx = i; break; }
            }
            if (idx === -1) return;

            if (res.removed) {
                ORDER.items.splice(idx, 1);
            } else {
                ORDER.items[idx].qty = res.qty;
            }

            renderOrder();
            syncTotals(res.totals);
        }, 'json').fail(function(){ toastr.error('Error al cambiar cantidad'); });
    };

    // ---------- Listeners ----------
    $(document).ready(function () {

        // CSRF para todos los AJAX
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Clases del body al abrir/cerrar aside
        $(document).on('expanded.lte.controlsidebar',  function(){
            $('body').addClass('control-sidebar-slide-open');
            sizeOrderAsideBody();
        });
        $(document).on('collapsed.lte.controlsidebar', function(){
            $('body').removeClass('control-sidebar-slide-open');
        });

        // Hidratar pedido con ítems existentes
        hydrateFromServer();

        // Desocupar mesa
        $('#btn-desocupar').on('click', function(){
            $.post('/dashboard/atenciones/'+ window.ATENCION_ID +'/cerrar', {}, function(resp){
                if(resp && resp.ok){
                    toastr.success(resp.msg || 'Mesa desocupada.');
                    if (resp.redirect_url) window.location = resp.redirect_url;
                } else {
                    toastr.error(resp && resp.msg ? resp.msg : 'No se pudo desocupar la mesa');
                }
            }, 'json').fail(function(){ toastr.error('Error'); });
        });

        // Cargar catálogo
        $.get('/dashboard/mesa/productos', function(res){
            if(!res || !res.ok) return toastr.error('No se pudo cargar productos');
            ALL_CATEGORIES = res.categories || [];
            renderCategories(ALL_CATEGORIES);
            renderFiltered();
        }, 'json').fail(function(){ toastr.error('Error al cargar catálogo'); });

        // Click categoría
        $(document).on('click', '.btn-cat', function(){
            $('.btn-cat').removeClass('active');
            $(this).addClass('active');
            SELECTED_CAT = $(this).data('id');
            renderFiltered();
        });

        // Búsqueda
        var debounced = debounce(function(val){
            QUERY = (val || '').trim();
            renderFiltered();
        }, 250);

        $('#prod-search').on('input', function(){ debounced(this.value); });
        $('#btn-clear-search').on('click', function(){ $('#prod-search').val(''); QUERY=''; renderFiltered(); });

        // Click agregar producto
        $(document).on('click', '.product-card', function(){
            var id    = parseInt(this.dataset.id, 10);
            var name  = this.dataset.name || 'Producto';
            var price = parseFloat(this.dataset.price);
            var prod = { id:id, name:name, unit_price: (isNaN(price) ? 0 : price) };
            addToOrder(prod);
        });

        // Enviar a cocina (ambos botones)
        $(document).on('click', '#btn-send-kitchen, #a-send-kitchen', function(){
            if (ORDER.items.length === 0) { toastr.warning('No hay productos en el pedido.'); return; }
            // TODO: endpoint para marcar comanda como 'enviada'
            toastr.success('Comanda enviada a cocina.');
        });

        // Render inicial por si acaso
        renderOrder();
    });

    // Calcula altura del área scrolleable (header + top consumen alto)
    function sizeOrderAsideBody(){
        var $aside  = $('#order-aside');
        var $header = $aside.find('.cs-header');
        var $top    = $aside.find('.cs-top');
        var $body   = $aside.find('.cs-body');

        var vh    = (window.visualViewport && window.visualViewport.height) || window.innerHeight;
        var hH    = $header.outerHeight() || 0;
        var tH    = $top.outerHeight() || 0;
        var bodyH = Math.max(120, vh - hH - tH);

        $body.css('height', bodyH + 'px');
    }

    $(window).on('resize', sizeOrderAsideBody);

})(jQuery);