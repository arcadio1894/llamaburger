<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WelcomeController;
use \Illuminate\Support\Facades\Auth;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\CartController;
use \App\Http\Controllers\OrderController;
use \App\Http\Controllers\TelegramController;
use \App\Http\Controllers\CouponController;
use \App\Http\Controllers\BusinessController;
use \App\Http\Controllers\PrintController;
use \App\Http\Controllers\TypeController;
use \App\Http\Controllers\CategoryController;
use \App\Http\Controllers\SliderController;
use \App\Http\Controllers\CashRegisterController;
use \App\Http\Controllers\FacturaController;
use App\Http\Controllers\OrdersChartController;
use \App\Http\Controllers\ReclamacionController;
use Illuminate\Support\Facades\Cache;
use \App\Http\Controllers\ShopController;
use \App\Http\Controllers\ZoneController;
use Illuminate\Http\Request;
use \App\Http\Controllers\RewardController;
use \App\Http\Controllers\ProfileController;
use \App\Http\Controllers\MilestoneController;
use \App\Http\Controllers\SalaController;
use \App\Http\Controllers\MesaController;
use \App\Http\Controllers\AtencionController;
use \App\Http\Controllers\PermissionController;
use \App\Http\Controllers\RoleController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\ComandaController;
use \App\Http\Controllers\ComandaItemController;
use \App\Http\Controllers\ProductOptionsController;
use \App\Http\Controllers\PedidoExternoController;
use \App\Http\Controllers\PagoController;
use \App\Http\Controllers\ClienteController;
use \App\Http\Controllers\BillingController;
use \App\Http\Controllers\NubeFactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {

    return view('welcome');
});*/

Route::get('/', [WelcomeController::class, 'welcome'])->name('welcome');

/*Route::get('/is-authenticated', [WelcomeController::class, 'isAuthenticated']);*/
Route::get('/auth/check', [WelcomeController::class, 'isAuthenticated'])->name('auth.check');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/menu', [WelcomeController::class, 'menu'])->name('menu');
Route::get('/nosotros', [WelcomeController::class, 'about'])->name('about');

Route::get('/reclamaciones', [ReclamacionController::class, 'reclamaciones'])->name('reclamaciones');
Route::get('/provincias/{departmentId}', [ReclamacionController::class, 'getProvinces']);
Route::get('/distritos/{provinceId}', [ReclamacionController::class, 'getDistricts']);
Route::get('/submotivos/{motivoId}', [ReclamacionController::class, 'getSubmotivos']);
Route::post('/reclamaciones/store', [ReclamacionController::class, 'store'])->name('reclamaciones.store');

Route::get('/estado/reclamos', [ReclamacionController::class, 'estadoReclamos'])->name('estado-reclamos');
Route::post('/consultar-estado-reclamo', [ReclamacionController::class, 'consultarEstado'])->name('reclamos.consultar');

/*Route::get('/producto/{id}', [ProductController::class, 'show'])->name('product.show');*/
Route::get('/producto/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/products/fill-slugs', [ProductController::class, 'fillSlugs']);
/*Route::get('/auth/check', function() {
    return response()->json(['authenticated' => auth()->check()]);
})->name('auth.check');*/
Route::get('/personaliza/tu/pizza', [ProductController::class, 'customPizza'])->name('product.custom');

Route::post('/cart/manage', [CartController::class, 'manage'])->name('cart.manage');
Route::post('/cart/manage/direct', [CartController::class, 'manage2'])->name('cart.manage2');
Route::post('/cart/manage/adicional', [CartController::class, 'manage3'])->name('cart.manage3');
Route::get('/carrito', [CartController::class, 'show'])/*->middleware('auth')*/->name('cart.show');
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])/*->middleware('auth')*/->name('cart.updateQuantity');
Route::get('/cart/quantity', [CartController::class, 'getCartQuantity'])->name('cart.quantity');

Route::get('/products/{id_product}/{product_type_id}', [ProductController::class, 'getProduct'])->name('products.get');

Route::get('/checkout', [CartController::class, 'checkout'])/*->middleware('auth')*/->name('cart.checkout');
Route::get('/checkout/v2', [CartController::class, 'checkout2'])/*->middleware('auth')*/->name('cart.checkout.v2');
Route::post('/checkout/pagar', [CartController::class, 'pagar'])
    /*->middleware('throttle:checkout')*/
    ->name('checkout.pagar');
Route::post('/checkout/crear-preferencia', [CartController::class, 'crearPreferencia'])->name('checkout.crearPreferencia');
Route::delete('/cart/delete-detail/{id}', [CartController::class, 'deleteDetail'])->name('cart.detail.delete');
Route::post('/cart/save-observation/{id}', [CartController::class, 'saveObservation'])->name('cart.save.observation');


Route::get('/apply-coupon', [CartController::class, 'applyCoupon'])->name('apply.coupon');
Route::post('/checkout/shipping', [CartController::class, 'calculateShipping']);


Route::get('/payment/success', [CartController::class, 'success'])->name('payment.success');
Route::get('/payment/failure', [CartController::class, 'failure'])->name('payment.failure');
Route::get('/payment/pending', [CartController::class, 'pending'])->name('payment.pending');

Route::get('/pago-exitoso', [CartController::class, 'pagoExitoso'])->name('pago.exitoso');
Route::get('/pago-fallido', [CartController::class, 'pagoFallido'])->name('pago.fallido');
Route::get('/pago-pendiente', [CartController::class, 'pagoPendiente'])->name('pago.pendiente');


Route::get('/get/orders/{page}', [OrderController::class, 'getOrders']);

Route::get('/api/business-hours', [BusinessController::class, 'getBusinessHours']);

Route::post('/save/custom/product', [CartController::class, 'saveCustomProduct']);

Route::get('/api/usuarios-activos', function () {
    $activeUsers = Cache::get("active_users", []);
    // Filtrar por IP para evitar muchos registros del mismo origen
    $uniqueUsers = collect($activeUsers)->unique('ip')->count();
    return response()->json(['activeUsers' => $uniqueUsers]);
});

Route::get('/api/usuarios-registrados', [WelcomeController::class, 'getRegisteredUsers']);
Route::get('/api/top-clientes', [WelcomeController::class, 'getTopClients']);

Route::get('/buscar-departamento', function (Request $request) {
    $department = \App\Models\Department::where('name', 'LIKE', "%{$request->nombre}%")->first();
    return response()->json($department);
});

Route::middleware('auth')->group(function (){
    Route::post('/broadcasting/auth', function () {
        return \Illuminate\Support\Facades\Broadcast::auth(request());
    });
    Route::prefix('dashboard')->group(function (){
        Route::get('/principal', [WelcomeController::class, 'goToDashboard'])
            ->name('dashboard.principal')
            ->middleware('permission:dashboard.view');

        // TODO: Rutas de Orders (Pedidos Admin)
        Route::get('/listado/pedidos/', [OrderController::class, 'indexAdmin'])
            ->name('orders.list')
            ->middleware('permission:pedidos.listar');

        Route::get('/listado/pedidos/anulados/', [OrderController::class, 'indexAdminAnnulled'])
            ->name('orders.list.annulled')
            ->middleware('permission:pedidos.anulados');

        Route::get('/get/data/orders/{numberPage}', [OrderController::class, 'getOrdersAdmin'])
            ->middleware('permission:pedidos.listar');

        Route::get('/get/data/orders/annulled/{numberPage}', [OrderController::class, 'getOrdersAnnulledAdmin'])
            ->middleware('permission:pedidos.annulled');

        Route::post('/change/order/state/{order}/{state}', [OrderController::class, 'changeIOrderState'])
            ->middleware('permission:pedidos.cambiar_estado');

        Route::post('/anular/order/{order}', [OrderController::class, 'anularOrder'])
            ->middleware('permission:pedidos.cambiar_estado');

        Route::post('/activar/order/{order}', [OrderController::class, 'activarOrder'])
            ->middleware('permission:pedidos.cambiar_estado');

        Route::get('/orders/{orderId}/details', [OrderController::class, 'getOrderDetails'])
            ->middleware('permission:pedidos.listar');

        Route::post('/orders/update-invoice-data', [OrderController::class, 'updateInvoiceData']);
        Route::post('/facturador/generar', [NubeFactController::class, 'generarComprobante'])->name('facturador.generar');


        // TODO: Rutas de Mantenedor de Productos (Productos Admin)
        Route::get('/listado/productos/', [ProductController::class, 'indexAdmin'])
            ->name('products.list')
            ->middleware('permission:productos.listar');

        Route::get('/get/data/products/{numberPage}', [ProductController::class, 'getDataProducts'])
            ->middleware('permission:productos.listar');

        Route::get('/listado/productos/eliminados/', [ProductController::class, 'indexAdminDeleted'])
            ->name('products.list.deleted')
            ->middleware('permission:productos.ver_eliminados');

        Route::get('/get/data/products/deleted/{numberPage}', [ProductController::class, 'getDataProductsDeleted'])
            ->middleware('permission:productos.ver_eliminados');

        Route::get('/crear/producto/', [ProductController::class, 'create'])
            ->name('product.create')
            ->middleware('permission:productos.crear');

        Route::post('/save/product/', [ProductController::class, 'store'])
            ->name('product.store')
            ->middleware('permission:productos.crear');

        Route::get('/editar/producto/{id}', [ProductController::class, 'edit'])
            ->name('product.edit')
            ->middleware('permission:productos.editar');

        Route::post('/update/product/', [ProductController::class, 'update'])
            ->name('product.update')
            ->middleware('permission:productos.editar');

        Route::post('/delete/product/', [ProductController::class, 'delete'])
            ->name('product.delete')
            ->middleware('permission:productos.eliminar');

        Route::post('/destroy/product/{id}', [ProductController::class, 'destroy'])
            ->name('product.destroy')
            ->middleware('permission:productos.eliminar');

        Route::post('/desactivar/producto/{id}', [ProductController::class, 'desactivar'])
            ->name('product.desactivar')
            ->middleware('permission:productos.desactivar');

        Route::post('/reactivar/product/{id}', [ProductController::class, 'reactivar'])
            ->name('product.reactivar')
            ->middleware('permission:productos.desactivar');

        // Mostrar listado de cupones
        Route::get('/coupons', [CouponController::class, 'index'])
            ->name('coupons.index')
            ->middleware('permission:cupones.listar');

        Route::get('/get/data/coupons/{page}', [CouponController::class, 'getDataCoupons'])
            ->middleware('permission:cupones.listar');

        Route::get('/coupons/create', [CouponController::class, 'create'])
            ->name('coupons.create')
            ->middleware('permission:cupones.crear');

        Route::post('/coupons/store', [CouponController::class, 'store'])
            ->name('coupons.store')
            ->middleware('permission:cupones.crear');

        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])
            ->name('coupons.edit')
            ->middleware('permission:cupones.editar');

        Route::post('/coupons/update', [CouponController::class, 'update'])
            ->name('coupons.update')
            ->middleware('permission:cupones.editar');

        Route::post('/cupones/cambiar-estado', [CouponController::class, 'cambiarEstado'])
            ->name('cupones.cambiar-estado')
            ->middleware('permission:cupones.cambiar_estado');

        // Mostrar listado de types
        Route::get('/types', [TypeController::class, 'index'])
            ->name('types.index')
            ->middleware('permission:type_products.listar');

        Route::get('/get/data/types/{page}', [TypeController::class, 'getDataTypes'])
            ->middleware('permission:type_products.listar');

        Route::get('/types/create', [TypeController::class, 'create'])
            ->name('types.create')
            ->middleware('permission:type_products.crear');

        Route::post('/types', [TypeController::class, 'store'])
            ->name('types.store')
            ->middleware('permission:type_products.crear');

        Route::get('/types/{type}', [TypeController::class, 'show'])
            ->name('types.show')
            ->middleware('permission:type_products.listar');

        Route::get('/types/{type}/edit/', [TypeController::class, 'edit'])
            ->name('types.edit')
            ->middleware('permission:type_products.editar');

        Route::post('/types/{type}/update', [TypeController::class, 'update'])
            ->name('types.update')
            ->middleware('permission:type_products.editar');

        Route::post('/types/destroy', [TypeController::class, 'destroy'])
            ->name('types.destroy')
            ->middleware('permission:type_products.cambiar_estado');

        // Mostrar Categorías
        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('categories.index')
            ->middleware('permission:categorias.listar');

        Route::get('/get/data/categories/{page}', [CategoryController::class, 'getDataCategories'])
            ->middleware('permission:categorias.listar');

        Route::get('/categories/create', [CategoryController::class, 'create'])
            ->name('categories.create')
            ->middleware('permission:categorias.crear');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('categories.store')
            ->middleware('permission:categorias.crear');

        Route::get('/categories/{category}', [CategoryController::class, 'show'])
            ->name('categories.show')
            ->middleware('permission:categorias.listar');

        Route::get('/categories/{category}/edit/', [CategoryController::class, 'edit'])
            ->name('categories.edit')
            ->middleware('permission:categorias.editar');

        Route::post('/categories/{category}/update', [CategoryController::class, 'update'])
            ->name('categories.update')
            ->middleware('permission:categorias.editar');

        Route::post('/categories/destroy', [CategoryController::class, 'destroy'])
            ->name('categories.destroy')
            ->middleware('permission:categorias.cambiar_estado');

        // Rutas Sliders
        Route::get('/sliders', [SliderController::class, 'index'])
            ->name('sliders.index')
            ->middleware('permission:sliders.listar');

        Route::get('/get/all/sliders', [SliderController::class, 'getSliders'])
            ->middleware('permission:sliders.listar');

        Route::post('/sliders/destroy', [SliderController::class, 'destroy'])
            ->name('sliders.destroy')
            ->middleware('permission:sliders.eliminar');

        Route::get('/sliders/create', [SliderController::class, 'create'])
            ->name('sliders.create')
            ->middleware('permission:sliders.crear');

        Route::post('/sliders', [SliderController::class, 'store'])
            ->name('sliders.store')
            ->middleware('permission:sliders.crear');

        Route::get('/editar/imagen/slider/{slider}', [SliderController::class, 'edit'])
            ->name('sliders.edit')
            ->middleware('permission:sliders.editar');

        Route::post('/sliders/update', [SliderController::class, 'update'])
            ->name('sliders.update')
            ->middleware('permission:sliders.editar');

        Route::post('/update-state/{id}', [SliderController::class, 'updateState'])
            ->middleware('permission:sliders.cambiar_estado');

        //Rutas de la caja
        Route::get('/ver/caja/{type}', [CashRegisterController::class, 'indexCashRegister'])
            ->name('index.cashRegister')
            ->middleware('permission:caja.ver_caja');

        Route::post('open/cashRegister', [CashRegisterController::class, 'openCashRegister'])
            ->name('open.cashRegister')
            ->middleware('permission:caja.abrir');

        Route::post('close/cashRegister', [CashRegisterController::class, 'closeCashRegister'])
            ->name('close.cashRegister')
            ->middleware('permission:caja.cerrar');

        Route::post('income/cashRegister', [CashRegisterController::class, 'incomeCashRegister'])
            ->name('income.cashRegister')
            ->middleware('permission:caja.crear_ingreso');

        Route::post('expense/cashRegister', [CashRegisterController::class, 'expenseCashRegister'])
            ->name('expense.cashRegister')
            ->middleware('permission:caja.crear_egreso');

        Route::post('regularize/cashRegister', [CashRegisterController::class, 'regularizeCashRegister'])
            ->name('regularize.cashRegister')
            ->middleware('permission:caja.regularize');

        Route::get('/get/data/movements/V2/{numberPage}', [CashRegisterController::class, 'getDataMovements'])
            ->middleware('permission:caja.ver_movimientos');

        Route::post('/factura/generar/{id}', [FacturaController::class, 'generarComprobante']);
        Route::get('/factura/imprimir/{id}', [FacturaController::class, 'descargarComprobante']);

        // Rutas de graficos
        Route::get('/orders/chart-data', [OrdersChartController::class, 'getChartData'])
            ->middleware('permission:dashboard.graph_type_users');

        Route::get('/promos/chart-data', [OrdersChartController::class, 'getChartDataPromo'])
            ->middleware('permission:dashboard.graph_promotions_usage');

        Route::get('/orders/chart-data-sale', [OrdersChartController::class, 'getChartDataSale'])
            ->middleware('permission:dashboard.graph_sale');

        Route::get('/orders/chart-data-utilidad', [OrdersChartController::class, 'getChartDataCashFlow'])
            ->middleware('permission:dashboard.graph_income_output');

        // Rutas de reclamos
        Route::get('/reclamos/activos', [ReclamacionController::class, 'index'])
            ->name('reclamos.index')
            ->middleware('permission:reclamos.pendientes');

        Route::get('/reclamos/finalizados', [ReclamacionController::class, 'indexFinalizados'])
            ->name('reclamos.finalizados')
            ->middleware('permission:reclamos.finalizados');

        Route::get('/get/data/reclamos/{page}', [ReclamacionController::class, 'getDataReclamos'])
            ->middleware('permission:reclamos.pendientes');

        Route::get('/reclamo/{id}/revisar', [ReclamacionController::class, 'show'])
            ->name('reclamos.show')
            ->middleware('permission:reclamos.revisar');

        Route::get('/get/data/reclamos/finalizados/{page}', [ReclamacionController::class, 'getDataReclamosFinalizados'])
            ->middleware('permission:reclamos.finalizados');

        Route::post('/reclamos/respuesta', [ReclamacionController::class, 'guardarRespuesta'])
            ->name('reclamos.guardarRespuesta')
            ->middleware('permission:reclamos.gestionar');

        Route::get('/reclamo/finalizado/{id}/revisar', [ReclamacionController::class, 'showFinalizado'])
            ->name('reclamos.show')
            ->middleware('permission:reclamos.revisar');

        // Routes KANBAN
        Route::get('/kanban/ordenes', [OrderController::class, 'indexKanban'])
            ->name('orders.kanban')
            ->middleware('permission:pedidos.kanban');

        // Ruta de prueba
        Route::get('/generar/orden', [OrderController::class, 'generarOrder'])->name('generarOrder');

        // Routes SHOP
        Route::get('/tiendas', [ShopController::class, 'index'])
            ->name('shop.index')
            ->middleware('permission:locales.listar_tiendas');

        Route::get('/get/data/shops/{page}', [ShopController::class, 'getDataShops'])
            ->middleware('permission:locales.listar_tiendas');

        Route::get('/crear/tienda', [ShopController::class, 'create'])
            ->name('shop.create')
            ->middleware('permission:locales.crear_tiendas');

        Route::post('/shop/store', [ShopController::class, 'store'])
            ->name('shop.store')
            ->middleware('permission:locales.crear_tiendas');

        Route::get('/ver/tienda/{shop}', [ShopController::class, 'show'])
            ->name('shop.show')
            ->middleware('permission:locales.crear_tiendas');

        Route::get('/modificar/tienda/{shop}', [ShopController::class, 'edit'])
            ->name('shop.edit')
            ->middleware('permission:locales.editar_tiendas');

        Route::post('/shop/update/{shop}', [ShopController::class, 'update'])
            ->name('shop.update')
            ->middleware('permission:locales.editar_tiendas');

        Route::post('/shop/{id}/cambiar-estado', [ShopController::class, 'changeState'])
            ->middleware('permission:locales.cambiar_estado_tienda');

        // Routes ZONE
        //Route::resource('zones', ZoneController::class);
        Route::get('/zonas', [ZoneController::class, 'index'])
            ->name('zones.index')
            ->middleware('permission:locales.listar_zonas');

        Route::get('/get/data/zones/{page}', [ZoneController::class, 'getDataShops'])
            ->middleware('permission:locales.listar_zonas');

        Route::get('/crear/zonas', [ZoneController::class, 'create'])
            ->name('zones.create')
            ->middleware('permission:locales.crear_zonas');

        Route::post('/zones/store', [ZoneController::class, 'store'])
            ->name('zones.store')
            ->middleware('permission:locales.crear_zonas');

        Route::post('/zones/{zone}/status', [ZoneController::class, 'changeStatus'])
            ->middleware('permission:locales.cambiar_estado_tienda');

        Route::post('/zones/{zone}/delete', [ZoneController::class, 'deleteZone'])
            ->middleware('permission:locales.gestionar_zonas');

        Route::post('/zones/{zone}/update-price', [ZoneController::class, 'updatePrice'])
            ->middleware('permission:locales.set_precio');

        Route::get('/zones/show/{zone}', [ZoneController::class, 'show'])
            ->middleware('permission:locales.ver_zona');

        Route::get('/shops/{id}', [ShopController::class, 'showShop'])
            ->middleware('permission:locales.gestionar_zonas');

        Route::get('/shops/{id}/zones', [ZoneController::class, 'getZones'])
            ->middleware('permission:locales.gestionar_zonas');

        Route::post('/shops/{id}/zones/save', [ZoneController::class, 'store'])
            ->middleware('permission:locales.gestionar_zonas');

        // TODO: RUTAS DE MILESTONE
        Route::get('/milestones', [MilestoneController::class, 'index'])
            ->name('milestones.index')
            ->middleware('permission:rewards.listar');

        Route::get('/get/data/milestones/{page}', [MilestoneController::class, 'getDataRewards'])
            ->middleware('permission:rewards.listar');

        Route::get('/crear/hito', [MilestoneController::class, 'create'])
            ->name('milestones.create')
            ->middleware('permission:rewards.crear');

        Route::post('/milestones/store', [MilestoneController::class, 'store'])
            ->name('milestones.store')
            ->middleware('permission:rewards.crear');

        Route::post('/milestones/{id}/eliminar', [MilestoneController::class, 'destroy'])
            ->middleware('permission:rewards.eliminar');

        Route::get('/modificar/hito/{id}', [MilestoneController::class, 'edit'])
            ->name('milestones.edit')
            ->middleware('permission:rewards.editar');

        Route::post('/milestones/update', [MilestoneController::class, 'update'])
            ->name('milestones.update')
            ->middleware('permission:rewards.editar');

        // TODO: Mantenedor de Salas y Mesas
        Route::get('/salas', [SalaController::class, 'index'])
            ->name('salas.index')
            ->middleware('permission:salas.list');

        Route::post('/salas', [SalaController::class, 'store'])
            ->name('salas.store')
            ->middleware('permission:salas.create');

        Route::post('/salas/{sala}/update', [SalaController::class, 'update'])
            ->name('salas.update')
            ->middleware('permission:salas.edit');

        Route::post('/salas/destroy', [SalaController::class, 'destroy'])
            ->name('salas.destroy')
            ->middleware('permission:salas.destroy');

        Route::post('/salas/restore', [SalaController::class, 'restore'])
            ->name('salas.restore')
            ->middleware('permission:salas.restore');

        Route::get('/salas/{sala}/mesas', [SalaController::class, 'mesasMozo'])
            ->name('salas.mesas')
            ->middleware('permission:mesas.list');

        Route::get('/salas/config', [SalaController::class, 'config'])
            ->name('salas.config')
            ->middleware('permission:espacios.configurar_mesas');

        Route::get('/salas/config/mesas/{sala}', [SalaController::class, 'configMesas'])
            ->name('salas.config.get.mesas')
            ->middleware('permission:espacios.configurar_mesas');

        Route::get('/salas/config/mesas/{sala}', [SalaController::class, 'configMesas'])
            ->name('salas.config.get.mesas')
            ->middleware('permission:espacios.configurar_mesas');

        // CRUD Mesas (AJAX)
        Route::post('/mesas',              [MesaController::class, 'store'])
            ->name('mesas.store')
            ->middleware('permission:mesas.create');

        Route::post('/mesas/{mesa}/update',[MesaController::class, 'update'])
            ->name('mesas.update')
            ->middleware('permission:mesas.edit');

        Route::post('/mesas/destroy',      [MesaController::class, 'destroy'])
            ->name('mesas.destroy')
            ->middleware('permission:mesas.destroy');

        Route::post('/mesas/restore',      [MesaController::class, 'restore'])
            ->name('mesas.restore')
            ->middleware('permission:mesas.restore');

        Route::post('/mesas/{mesa}/abrir',  [AtencionController::class, 'abrir'])
            ->name('atenciones.abrir')
            ->middleware('permission:mesas.abrir');

        // Redirige la URL vieja a la primera comanda
        Route::get('/atenciones/{atencion}', function (\App\Models\Atencion $atencion) {
            $first = $atencion->comandas()->orderBy('numero')->first();
            abort_if(!$first, 404);
            return redirect()->route('atenciones.comanda.show', [$atencion->id, $first->numero]);
        })->name('atenciones.show'); // mantiene compatibilidad

        // Mostrar una comanda específica (por número)
        Route::get('/atenciones/{atencion}/comanda/{numero}', [AtencionController::class, 'showComanda'])
            ->name('atenciones.comanda.show');

        // Crear una nueva comanda (siguiente número) dentro de la atención
        Route::post('/atenciones/{atencion}/comandas', [ComandaController::class, 'createNext'])
            ->name('comandas.createNext');

        // Obtener (o crear) la comanda borrador activa de una atención
        Route::post('/atenciones/{atencion}/comandas/get-or-create', [ComandaController::class, 'getOrCreateBorrador'])
            ->name('comandas.get_or_create');

        // Items
        Route::post('/comandas/{comanda}/items', [ComandaItemController::class, 'store'])
            ->name('comanda_items.store');                 // add
        Route::post('/comanda-items/{item}/inc', [ComandaItemController::class, 'increment'])
            ->name('comanda_items.increment');             // +1/-1
        Route::post('/comanda-items/{item}/destroy', [ComandaItemController::class, 'destroy'])
            ->name('comanda_items.destroy');               // quitar
        Route::post('/comanda-items/{item}/update', [ComandaItemController::class, 'update'])
            ->name('comanda_items.update');

        Route::get('/mesa/productos', [AtencionController::class, 'productos'])
            ->name('atenciones.productos');

        Route::get('/products/{product}/options', [ProductOptionsController::class, 'show'])
            ->name('products.options');

        Route::post('/atenciones/{atencion}/cerrar', [AtencionController::class, 'cerrar'])
            ->name('atenciones.cerrar')
            ->middleware('permission:mesas.cerrar');

        Route::post('/atenciones/{atencion}/facturar', [BillingController::class, 'facturar'])
            ->name('atenciones.facturar')
            ->middleware('permission:pagos.generar');

        Route::get('/invoices/{invoice}', [BillingController::class, 'show'])->name('invoices.show');

        Route::get('/mesas/{mesa}/acceso', [AtencionController::class, 'checkAcceso'])
            ->name('mesas.checkAcceso'); // vista de gestión

        Route::post('/comandas/{comanda}/send-kitchen', [ComandaController::class, 'send'])
            ->name('comandas.sendKitchen');

        Route::prefix('pedido-externo')->name('pedido.externo.')->group(function(){
            Route::get('/', [PedidoExternoController::class, 'index'])->name('index');
            Route::get('/crear', [PedidoExternoController::class, 'crear'])->name('crear');
            Route::post('/{atencion}/ir-a-pagar', [PedidoExternoController::class, 'irPagar'])->name('ir_pagar');
            Route::get('/{atencion}/comanda/{id_comanda}', [PedidoExternoController::class, 'showComanda'])
                ->name('comanda.show');
        });

        Route::post('/atenciones/{atencion}/ir-a-pagar', [AtencionController::class, 'irPagar'])
            ->name('atenciones.irPagar');

        Route::get('/atenciones/{atencion}/pago', [PagoController::class, 'create'])
            ->name('pagos.create');

        Route::post('/pagos', [PagoController::class, 'store'])
            ->name('pagos.store');

        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');

        Route::get('/permissions', [PermissionController::class, 'index'])
            ->name('permissions.index')
            ->middleware('permission:permisos.list');

        Route::get('/permissions/list', [PermissionController::class, 'listar'])
            ->name('permissions.list')
            ->middleware('permission:permisos.list');

        Route::post('/permissions', [PermissionController::class, 'store'])
            ->name('permissions.store')
            ->middleware('permission:permisos.create');

        Route::post('/permissions/{permission}/update',  [PermissionController::class, 'update'])
            ->name('permissions.update')
            ->middleware('permission:permisos.edit');

        Route::post('/permissions/{permission}/destroy', [PermissionController::class, 'destroy'])
            ->name('permissions.destroy')
            ->middleware('permission:permisos.destroy');

        Route::get('/roles',                 [RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('permission:roles.list');

        Route::get('/roles/list',            [RoleController::class, 'listas'])
            ->name('roles.list')
            ->middleware('permission:roles.list');

        Route::get('/roles/create',          [RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('permission:roles.create');

        Route::get('/roles/{role}/perms',    [RoleController::class, 'perms'])
            ->name('roles.perms')
            ->middleware('permission:roles.create');

        Route::post('/roles',                [RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('permission:roles.create');

        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->name('roles.edit')
            ->middleware('permission:roles.edit');

        Route::put('/roles/{role}',      [RoleController::class, 'update'])
            ->name('roles.update')
            ->middleware('permission:roles.edit');

        Route::post('/roles/{role}/destroy', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('permission:roles.destroy');

        Route::get('/users',            [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:usuarios.list');

        Route::get('/users/list',       [UserController::class, 'listar'])
            ->name('users.list')
            ->middleware('permission:usuarios.list');

        Route::post('/users',           [UserController::class, 'store'])
            ->name('users.store')
            ->middleware('permission:usuarios.create');

        Route::post('/users/{user}/update',  [UserController::class, 'update'])
            ->name('users.update')
            ->middleware('permission:usuarios.edit');

        Route::post('/users/{user}/destroy', [UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('permission:usuarios.destroy');

        Route::get('/users/{user}/json',     [UserController::class, 'showJson'])
            ->name('users.json')
            ->middleware('permission:usuarios.create');

        Route::post('/users/{id}/restore',   [UserController::class, 'restore'])
            ->name('users.restore')
            ->middleware('permission:usuarios.restore');

    });

    // TODO: RUTAS DE PREMIOS
    /*Route::get('/rewards/', [RewardController::class, 'index'])->name('rewards');*/
    Route::get('/reclamar/recompensa/{slug}/{id}', [RewardController::class, 'show'])
        ->name('reward.show');

    // TODO: RUTAS DE PROFILE
    Route::get('/perfil/usuario', [ProfileController::class, 'index'])->name('perfil.usuario');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');

});

// TODO: RUTAS DE PREMIOS
Route::get('/rewards/', [RewardController::class, 'index'])->name('rewards');

Route::get('/seleccionar/local', [ShopController::class, 'showLocals'])->name('showlocals');
Route::post('/buscar-tiendas', [ShopController::class, 'buscarTiendas']);

Route::get('/telegram/send', [TelegramController::class, 'sendMessage']);

Route::post('/dashboard/toggle-store-status', [BusinessController::class, 'toggleStoreStatus']);

Route::post('/dashboard/print', [PrintController::class, 'imprimir']);
/*Route::post('/print/order/{order_id}', [PrintController::class, 'printOrder']);*/
Route::get('/imprimir/recibo/{id}', [PrintController::class, 'generarRecibo']);
Route::get('/imprimir/comanda/{id}', [PrintController::class, 'generarComanda']);

Route::get('/products/initialize-days', [ProductController::class, 'initializeProductDays']);

Route::get('/reporte/cantidad-pizzas', [OrderController::class, 'reportePizzasFinde']);



Route::get('/check/sales/vs/movements', [OrdersChartController::class, 'getRegularizedSalesWithOrderAmounts']);

Route::get('/generate/recibo/prueba', [NubeFactController::class, 'generarRecibo']);

