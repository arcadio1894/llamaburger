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
    var ORDER = { items: [] }; // { id(product_id), name, qty, price, server_id?, has_options?, opciones? }
    var ALL_CATEGORIES = [];
    var SELECTED_CAT = 'all';
    var QUERY = '';

    // ====== MODAL DE OPCIONES (STATE) ====== // NEW
    var PO = { mode:'new', product:null, item:null, qty:1, groups:[], state:{} };
    function soles(v){ return formatMoney(v); }
    function sum(arr){ return arr.reduce(function(a,b){ return a+b; }, 0); }

    // ---------- Render pedido ----------
    function summarizeOptions(op){
        if (!op || !op.grupos) return '';
        var parts = [];
        op.grupos.forEach(function(g){
            if (g.tipo === 'texto') {
                if (g.valor) parts.push(/*g.descripcion + ': "' +*/ g.valor + '"');
            } else if (Array.isArray(g.selecciones) && g.selecciones.length){
                var labels = g.selecciones.map(function(s){ return s.name + (s.delta ? ' (+'+formatMoney(s.delta)+')' : ''); });
                parts.push(/*g.descripcion + ': ' +*/ labels.join(', '));
            }
        });
        return parts.join(' | ');
    }

    function renderOrder(){
        var html = ORDER.items.map(function(it){
            var opts = summarizeOptions(it.opciones);
            return ''
                + '<div class="media align-items-center">'
                + '  <div class="media-body">'
                + '    <div class="d-flex justify-content-between">'
                + '      <strong>'+ escAttr(it.name) +'</strong>'
                + '      <span>'+ formatMoney(it.price) +'</span>'
                + '    </div>'
                + (opts ? '<div class="text-muted small mt-1">'+ escAttr(opts) +'</div>' : '')
                + '    <div class="d-flex align-items-center mt-1">'
                + '      <button class="btn btn-xs btn-outline-secondary mr-1" onclick="incItem('+it.id+', -1, '+(it.server_id||'null')+')"><i class="fas fa-minus"></i></button>'
                + '      <span>'+ it.qty +'</span>'
                + '      <button class="btn btn-xs btn-outline-secondary ml-1" onclick="incItem('+it.id+', 1, '+(it.server_id||'null')+')"><i class="fas fa-plus"></i></button>'
                + (it.has_options ? ' <button class="btn btn-xs btn-outline-primary ml-2 btn-edit-options" data-server-id="'+(it.server_id||'')+'" data-product-id="'+it.id+'">Opciones</button>' : '')
                + '      <span class="ml-auto text-muted small">'+ formatMoney(it.qty * it.price) +'</span>'
                + '    </div>'
                + '  </div>'
                + '</div>';
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
            return {
                id:it.product_id,
                name:it.name,
                qty:it.qty,
                price:it.price,
                server_id:it.server_id,
                has_options: !!it.has_options,
                opciones: it.opciones || null
            };
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
            var hasOptions = !!p.has_options;

            html += ''+
                '<div class="card product-card" '+
                '     data-id="'+ p.id +'" '+
                '     data-name="'+ escAttr(name) +'" '+
                '     data-has-options="'+ (hasOptions ? '1' : '0') +'" '+
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

    // Persistente (sin opciones)
    function addToOrder(product){
        $.post('/dashboard/comandas/' + window.COMANDA_ID + '/items', {
            product_id: product.id,
            cantidad: 1
        }, function(res){
            if(!res || !res.ok) { toastr.error(res && res.msg ? res.msg : 'No se pudo agregar'); return; }

            var it = res.item; // {id, product_id, name, price, qty, opciones?}
            upsertLocalItem(it, true);
            renderOrder();
            syncTotals(res.totals);
            toastr.success('Producto agregado');
        }, 'json').fail(function(){ toastr.error('Error al agregar producto'); });
    }

    // NEW: actualizar/insertar en ORDER con opciones
    function upsertLocalItem(serverItem, isNew){
        // 1) Intenta por server_id (línea exacta)
        var idx = ORDER.items.findIndex(function(x){ return String(x.server_id) === String(serverItem.id); });

        // 2) Si no existe y NO hay opciones, intenta agrupar por product_id sin opciones
        if (idx === -1 && !serverItem.opciones) {
            idx = ORDER.items.findIndex(function(x){
                return !x.opciones && String(x.id) === String(serverItem.product_id);
            });
        }

        var rec = {
            id: serverItem.product_id,      // product_id
            name: serverItem.name,
            qty: serverItem.qty,
            price: serverItem.price,
            server_id: serverItem.id,       // <- MUY IMPORTANTE
            has_options: !!serverItem.opciones,
            opciones: serverItem.opciones || null
        };

        if (idx === -1) ORDER.items.push(rec);
        else ORDER.items[idx] = rec;
    }

    // Devuelve el unit_price base del producto (sin adicionales). Fallback: item.price.
    function getBaseUnitPrice(productId, fallback){
        for (var i=0;i<ALL_CATEGORIES.length;i++){
            var prods = ALL_CATEGORIES[i].products || [];
            for (var j=0;j<prods.length;j++){
                var p = prods[j];
                if (String(p.id) === String(productId)) {
                    // Si tu endpoint manda unit_price, úsalo. Si no, usamos productDisplayPrice(p).
                    var base = parseFloat(p.unit_price);
                    if (!isNaN(base)) return base;
                    var disp = productDisplayPrice(p);
                    if (disp != null) return Number(disp);
                }
            }
        }
        return Number(fallback || 0);
    }

    // +/- (expuesta globalmente para los botones renderizados)
    window.incItem = function(productId, d, serverId){
        if (!serverId) { toastr.warning('Item no persistido aún.'); return; }

        $.post('/dashboard/comanda-items/' + serverId + '/inc', { delta: d }, function(res){
            if(!res || !res.ok){ toastr.error(res && res.msg ? res.msg : 'No se pudo actualizar'); return; }

            var idx = ORDER.items.findIndex(function(x){ return String(x.server_id) === String(serverId); });
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

    // ====== MODAL: helpers ====== // NEW
    function fetchProductOptions(productId){
        return $.getJSON('/dashboard/products/'+productId+'/options').then(function(res){
            if(!res || !res.ok) throw new Error('No ok');
            return res.groups || [];
        });
    }

    function restoreStateFromSnapshot(opciones){
        var state = {};
        if (!opciones || !Array.isArray(opciones.grupos)) return state;
        opciones.grupos.forEach(function(g){
            if (g.tipo === 'texto') { state[g.option_id] = g.valor || ''; }
            else { state[g.option_id] = (g.selecciones||[]).map(function(s){ return s.selection_id; }); }
        });
        return state;
    }

    function calcUnitPrice(){
        var base = Number(PO.product.unit_price || 0);
        var extra = 0;
        PO.groups.forEach(function(g){
            if (g.tipo === 'texto') return;
            var ids = PO.state[g.option_id] || [];
            if (!Array.isArray(ids)) ids = [ids];
            extra += ids.map(function(id){
                var it = (g.items||[]).find(function(x){ return String(x.selection_id)===String(id); });
                return Number((it && it.delta) || 0);
            }).reduce(function(a,b){return a+b;},0);
        });
        return base + extra;
    }

    function buildSnapshot(){
        var snap = { grupos: [] };
        PO.groups.forEach(function(g){
            if (g.tipo === 'texto') {
                snap.grupos.push({
                    option_id: g.option_id,
                    descripcion: g.descripcion,
                    tipo: 'texto',
                    valor: PO.state[g.option_id] || ''
                });
            } else {
                var ids = PO.state[g.option_id] || [];
                if (!Array.isArray(ids)) ids = [ids];
                var picks = ids.map(function(id){
                    var it = (g.items||[]).find(function(x){ return String(x.selection_id)===String(id); });
                    return it ? { selection_id: it.selection_id, product_id: it.product_id, name: it.name, delta: Number(it.delta||0) } : null;
                }).filter(Boolean);
                snap.grupos.push({
                    option_id: g.option_id,
                    descripcion: g.descripcion,
                    tipo: g.tipo,
                    min: g.min, max: g.max,
                    selecciones: picks
                });
            }
        });
        return snap;
    }

    function validatePO(){
        var ok = true;
        PO.groups.forEach(function(g){
            if (g.tipo === 'texto') return;
            var ids = PO.state[g.option_id] || [];
            if (!Array.isArray(ids)) ids = [ids];
            var c = ids.length;
            var min = Number(g.min || 0);
            var max = (g.max==null) ? Infinity : Number(g.max);
            if (c < min || c > max) ok = false;
        });
        $('#po-save').prop('disabled', !ok);
        return ok;
    }

    function renderPO(){
        $('#po-title').text(PO.product.name);
        $('#po-qty').val(PO.qty || 1);

        var body = '';
        PO.groups.forEach(function(g){
            var hint = (g.min!=null && g.max!=null) ? '('+g.min+'/'+g.max+')' :
                (g.min!=null) ? '(min '+g.min+')' :
                    (g.max!=null) ? '(max '+g.max+')' : '';

            body += '<section class="group" data-gid="'+g.option_id+'">'
                + '  <div class="group__title">'+ escAttr(g.descripcion) +'</div>'
                + '  <div class="group__hint">'+ hint + (g.tipo==='texto'?' (texto libre)':'') +'</div>'
                + '  <div class="group__items">';
            if (g.tipo === 'texto') {
                var val = PO.state[g.option_id] || '';
                body += '<textarea class="po-text" data-opt="'+g.option_id+'" maxlength="'+(g.maxlen||200)+'" placeholder="Ej: sin cebolla...">'+escAttr(val)+'</textarea>';
            } else {
                (g.items||[]).forEach(function(it, ix){
                    // FALLBACKS de nombre y delta (corrige el caso en el que venía 'nombre' u 'product.name')
                    var displayName =
                        it.name ??
                        it.nombre ??
                        it.product_name ??
                        (it.product && it.product.name) ??
                        'Opción';

                    var delta = Number(it.delta ?? it.additional_price ?? 0);

                    // id único para asociar label → más “clickeable”
                    var inputId = 'po-' + g.option_id + '-' + (it.selection_id ?? it.id ?? ix);

                    var checked = Array.isArray(PO.state[g.option_id])
                        ? PO.state[g.option_id].indexOf(it.selection_id)!==-1
                        : false;

                    var inputType = (g.tipo==='picker' && Number(g.max)===1) ? 'radio' : 'checkbox';
                    var nameAttr  = 'opt_'+g.option_id;

                    body += ''
                        + '<label class="po-item" for="'+inputId+'">'
                        + '  <input id="'+inputId+'" type="'+inputType+'" class="po-pick"'
                        + '         name="'+nameAttr+'" data-opt="'+g.option_id+'"'
                        + '         value="'+(it.selection_id ?? it.id)+'" '+(checked?'checked':'')+'>'
                        + '  <span class="po-item__text">'+ escAttr(displayName) +'</span>'
                        + '  <span class="po-item__delta">'+ (delta ? ('+'+soles(delta)) : '') +'</span>'
                        + '</label>';
                });
            }
            body += '  </div></section>';
        });
        $('#po-body').html(body);

        // listeners internos
        $('#po-body').off('input.change', '.po-text, .po-pick');
        $('#po-body').on('input', '.po-text', function(){
            var opt = $(this).data('opt');
            PO.state[opt] = $(this).val();
            validatePO(); updatePOTotal();
        });
        $('#po-body').on('change', '.po-pick', function(){
            var opt = $(this).data('opt');
            var $inputs = $('input.po-pick[data-opt="'+opt+'"]');
            var g = PO.groups.find(function(x){ return String(x.option_id)===String(opt); }) || {};
            var max = (g.max==null) ? Infinity : Number(g.max);
            var vals = $inputs.filter(':checked').map(function(){ return $(this).val(); }).get().map(function(v){ return Number(v); });
            // enforce max
            if (vals.length > max) {
                // desmarcar el último check
                this.checked = false;
                vals.pop();
            }
            PO.state[opt] = vals;
            validatePO(); updatePOTotal();
        });

        updatePOTotal(); validatePO();
    }

    function updatePOTotal(){
        var unit = calcUnitPrice();
        var q = Number($('#po-qty').val()||1);
        $('#po-total').text(soles(unit * q));
        $('#po-unit').text(soles(unit));
    }

    // Evitar scroll del body cuando el modal está abierto
    function lockBody(){ document.body.style.overflow='hidden'; }
    function unlockBody(){ document.body.style.overflow=''; }

    // Recalcula alto del body del modal para ocupar el viewport restante
    function sizePOModal(){
        var $card = $('.modal__card'), $header = $card.find('.modal__header'), $footer = $card.find('.modal__footer'), $body = $card.find('.modal__body');
        var vh = (window.visualViewport && window.visualViewport.height) || window.innerHeight;
        var hH = $header.outerHeight() || 0, fH = $footer.outerHeight() || 0;
        var maxH = Math.max(240, vh - hH - fH - 24); // margen
        $body.css('max-height', maxH+'px');
    }

    function showPOModal(){
        $('#productOptionsModal').removeAttr('hidden');
        lockBody(); sizePOModal();
    }
    function hidePOModal(){
        $('#productOptionsModal').attr('hidden', true);
        unlockBody();
    }

    function openOptionsModalForNew(prod){
        PO.mode = 'new'; PO.product = prod; PO.item = null; PO.qty = 1; PO.state = {};
        fetchProductOptions(prod.id).then(function(groups){
            PO.groups = groups;
            renderPO(); showPOModal();
        }).catch(function(){ toastr.error('No se pudieron cargar opciones'); });
    }

    function openOptionsModalForEdit(item){
        PO.mode = 'edit';

        // IMPORTANTE: base sin adicionales
        var baseUnit = getBaseUnitPrice(item.id, item.price);

        PO.product = {
            id: item.id,            // product_id
            name: item.name,
            unit_price: baseUnit,   // <-- base del producto (NO el precio del ítem)
            has_options: true
        };
        PO.item = item;
        PO.qty  = item.qty;

        fetchProductOptions(item.id).then(function(groups){
            PO.groups = groups;
            PO.state  = restoreStateFromSnapshot(item.opciones); // repuebla checks
            renderPO();
            $('#po-qty').val(item.qty);
            showPOModal();
        }).catch(function(){ toastr.error('No se pudieron cargar opciones'); });
    }

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
            var hasOpt = this.dataset.hasOptions === '1';
            var prod = { id:id, name:name, unit_price: (isNaN(price) ? 0 : price), has_options: hasOpt };

            if (hasOpt) {
                openOptionsModalForNew(prod);
            } else {
                addToOrder(prod);
            }
        });

        // Botón “Opciones” en el pedido (editar)
        $(document).on('click', '.btn-edit-options', function(){
            var sid = $(this).data('server-id');
            var pid = $(this).data('product-id');
            var item = ORDER.items.find(function(x){ return String(x.server_id)===String(sid) && String(x.id)===String(pid); });
            if (!item) return;
            openOptionsModalForEdit(item);
        });

        // Modal: qty + cerrar + guardar
        $('[data-po-close]').on('click', hidePOModal);
        $('[data-po-qty-inc]').on('click', function(){ var v = Number($('#po-qty').val()||1)+1; $('#po-qty').val(v); updatePOTotal(); });
        $('[data-po-qty-dec]').on('click', function(){ var v = Math.max(1, Number($('#po-qty').val()||1)-1); $('#po-qty').val(v); updatePOTotal(); });
        $('#po-qty').on('input', updatePOTotal);

        $('#po-save').on('click', function(){
            if (!validatePO()) return;
            var snapshot = buildSnapshot();
            var qty = Number($('#po-qty').val()||1);

            if (PO.mode === 'new') {
                $.post('/dashboard/comandas/'+ window.COMANDA_ID +'/items', {
                    product_id: PO.product.id,
                    cantidad: qty,
                    opciones: JSON.stringify(snapshot)
                }, function(res){
                    if(!res || !res.ok){ toastr.error(res && res.msg ? res.msg : 'No se pudo agregar'); return; }
                    upsertLocalItem(res.item, true);
                    renderOrder();
                    syncTotals(res.totals);
                    hidePOModal();
                    toastr.success('Producto agregado');
                }, 'json').fail(function(){ toastr.error('Error al agregar'); });
            } else {
                $.post('/dashboard/comanda-items/'+ PO.item.server_id + '/update', {
                    opciones: JSON.stringify(snapshot),
                    cantidad: qty
                }, function(res){
                    if(!res || !res.ok){ toastr.error(res && res.msg ? res.msg : 'No se pudo actualizar'); return; }
                    upsertLocalItem(res.item, false);
                    renderOrder();
                    syncTotals(res.totals);
                    hidePOModal();
                    toastr.success('Opciones actualizadas');
                }, 'json').fail(function(){ toastr.error('Error al actualizar'); });
            }
        });

        // Render inicial por si acaso
        renderOrder();

        // Enviar a pagar (ambos botones)
        $(document).on('click', '#btn-send-pay, #a-send-pay', function(e) {
            // Deja que el form se envíe normalmente (redirigirá a pagos.create)
            // Solo controlamos UX para evitar doble clic.
            var $btn = $(this);

            // prevenir doble click
            if ($btn.prop('disabled')) {
                e.preventDefault();
                return false;
            }

            $btn.prop('disabled', true).text('ENVIANDO...');
            // No llames preventDefault: queremos que el form se envíe y siga la redirección
            // (si quieres, puedes agregar un pequeño spinner aquí)
        });
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

    $(window).on('resize', sizePOModal);
})(jQuery);