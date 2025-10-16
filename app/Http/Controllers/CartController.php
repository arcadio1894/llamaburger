<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderStatusEmail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\CartDetailOption;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CategoryCoupon;
use App\Models\Coupon;
use App\Models\DataGeneral;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailOption;
use App\Models\OrderDetailTopping;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ShippingDistrict;
use App\Models\Topping;
use App\Models\User;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Item;
use MercadoPago\Payment;
use MercadoPago\Preference;
use MercadoPago\SDK;

class CartController extends Controller
{
    public function __construct()
    {
        //SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN'));
        /*SDK::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN_PRO'));*/
    }

    public function getCartQuantity()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['quantity' => 0]);
        }

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        $quantity = $cart ? $cart->details->sum('quantity') : 0;

        return response()->json(['quantity' => $quantity]);
    }

    public function manage(Request $request)
    {
        $user = Auth::user();
        $product_id = $request->input('product_id');
        $product = Product::where('slug', $product_id)->first();
        $product_type_id = $request->input('product_type_id'); // Tipo de producto
        $selectedOptions = $request->input('options', []); // Opciones seleccionadas

        // Verificar si el usuario tiene un carrito pendiente
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$cart) {
            // Crear un nuevo carrito si no existe uno pendiente
            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        // Verificar si el producto con el mismo tipo ya está en el carrito
        $cartDetail = $cart->details()
            ->where('product_id', $product->id)
            ->where('product_type_id', $product_type_id)
            ->first();

        if ($cartDetail) {
            // Si existe, aumentar la cantidad
            $cartDetail->quantity += 1;
            $cartDetail->subtotal = $cartDetail->quantity * $cartDetail->price;
            $cartDetail->save();
            // TODO: Si es combo que cree un detalle mas SI las selecciones y el producto sondiferentes
            // Si son iguales entonces agrega unno mas
            // Si no es combo que haga lo actual
        } else {
            // Si no existe, agregar un nuevo detalle
            //$product = Product::find($product_id);
            $cartDetail = $cart->details()->create([
                'product_id' => $product->id,
                'product_type_id' => $product_type_id,
                'quantity' => 1,
                'price' => ProductType::find($product_type_id)->price, // Obtener el precio desde ProductType
                'subtotal' => ProductType::find($product_type_id)->price * 1, // Calcular el subtotal
            ]);
            // Guardar las opciones seleccionadas
            foreach ($selectedOptions as $optionId => $productIds) {
                foreach ((array) $productIds as $productId) {
                    $cartDetail->options()->create([
                        'option_id' => $optionId,
                        'product_id' => $productId,
                    ]);
                }
            }
        }
/*
        // Validar que $cartDetail esté correctamente definido
        if (!$cartDetail) {
            return response()->json(['error' => 'No se pudo crear el detalle del carrito.'], 500);
        }*/



        return response()->json(['redirect' => route('cart.show')]);
    }

    public function manage2(Request $request)
    {
        $user = Auth::user();
        $product_id = $request->input('product_id');

        $product = Product::where('slug', $product_id)->first();

        // Verificar si el usuario tiene un carrito pendiente
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$cart) {
            // Crear un nuevo carrito si no existe uno pendiente
            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        //$product = Product::find($product_id);

        // Verificar si el producto con el mismo tipo ya está en el carrito
        $cartDetail = $cart->details()
            ->where('product_id', $product->id)
            ->first();

        if ($cartDetail) {
            // Si existe, aumentar la cantidad
            $cartDetail->quantity += 1;
            $cartDetail->subtotal = $cartDetail->quantity * $cartDetail->price;
            $cartDetail->save();
            // TODO: Si es combo que cree un detalle mas SI las selecciones y el producto sondiferentes
            // Si son iguales entonces agrega unno mas
            // Si no es combo que haga lo actual
        } else {
            // Si no existe, agregar un nuevo detalle
            ///$product = Product::find($product_id);
            $cartDetail = $cart->details()->create([
                'product_id' => $product->id,
                'product_type_id' => null,
                'quantity' => 1,
                'price' => $product->price_default, // Obtener el precio desde ProductType
                'subtotal' => $product->price_default * 1, // Calcular el subtotal
            ]);

        }

        return response()->json(['message' => "Producto ".$product->full_name." agregado al carrito de compras."], 200);
    }

    public function manage3(Request $request)
    {
        $user = Auth::user();
        $product_id = $request->input('product_id');

        $product = Product::where('slug', $product_id)->first();

        // Verificar si el usuario tiene un carrito pendiente
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$cart) {
            // Crear un nuevo carrito si no existe uno pendiente
            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        //$product = Product::find($product_id);

        // Verificar si el producto con el mismo tipo ya está en el carrito
        $cartDetail = $cart->details()
            ->where('product_id', $product->id)
            ->first();

        if ($cartDetail) {
            // Si existe, aumentar la cantidad
            $cartDetail->quantity += 1;
            $cartDetail->subtotal = $cartDetail->quantity * $cartDetail->price;
            $cartDetail->save();
            // TODO: Si es combo que cree un detalle mas SI las selecciones y el producto sondiferentes
            // Si son iguales entonces agrega unno mas
            // Si no es combo que haga lo actual
        } else {
            // Si no existe, agregar un nuevo detalle
            //$product = Product::find($product_id);
            $cartDetail = $cart->details()->create([
                'product_id' => $product->id,
                'product_type_id' => null,
                'quantity' => 1,
                'price' => $product->price_default, // Obtener el precio desde ProductType
                'subtotal' => $product->price_default * 1, // Calcular el subtotal
            ]);

        }

        return response()->json(['redirect' => route('cart.show')]);
    }

    public function show()
    {
        $user = Auth::user();
        //$cart = Cart::with(['details.options.option', 'details.options.product'])->find($cartId);
        /*$cart = Cart::with(['details.options.option', 'details.options.product'])->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();*/
        /*return view('product.cart', compact('cart'));*/
        //return view('product.cart2');
        return view('product.cart3');
    }

    public function updateQuantity(Request $request)
    {
        $detail = CartDetail::find($request->detail_id);

        if ($detail) {
            $detail->quantity = $request->quantity;
            $detail->subtotal = $detail->quantity * $detail->product->unit_price;
            $detail->save();

            // Recalcular subtotal, taxes y total del carrito
            $cart = $detail->cart;
            $subtotalCart = $cart->subtotal_cart;
            $taxesCart = $cart->taxes_cart;
            $totalCart = $cart->total_cart;

            return response()->json([
                'success' => true,
                'subtotal_cart' => number_format($subtotalCart, 2),
                'taxes_cart' => number_format($taxesCart, 2),
                'total_cart' => number_format($totalCart, 2),
                'detail_subtotal' => number_format($detail->subtotal, 2)
            ]);
        }

        return response()->json(['success' => false], 400);
    }

    public function checkout()
    {
        $user = Auth::user();

        /*$cart = Cart::with('details.product')->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();*/

        $payment_methods = PaymentMethod::active()->orderBy('id', 'desc')->get();

        /* if (!$cart || $cart->details->isEmpty()) {
            return redirect()->route('home');
        }
        */
        if ( isset($user) )
        {
            $defaultAddress = Address::where('user_id', Auth::id())
                ->where('is_default', true)
                ->first();
        } else {
            $defaultAddress = null;
        }

        $districts = ShippingDistrict::all();

        /*return view('product.checkout', compact('cart', 'payment_methods', 'defaultAddress', 'districts'));*/
        return view('product.checkoutMovil', compact( 'payment_methods', 'defaultAddress', 'districts'));
    }

    public function checkout2()
    {
        $user = Auth::user();

        /*$cart = Cart::with('details.product')->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();*/

        $payment_methods = PaymentMethod::active()->get();

        /* if (!$cart || $cart->details->isEmpty()) {
            return redirect()->route('home');
        }
        */
        if ( isset($user) )
        {
            $defaultAddress = Address::where('user_id', Auth::id())
                ->where('is_default', true)
                ->first();
        } else {
            $defaultAddress = null;
        }

        $districts = ShippingDistrict::all();

        /*return view('product.checkout', compact('cart', 'payment_methods', 'defaultAddress', 'districts'));*/
        return view('product.checkout3', compact( 'payment_methods', 'defaultAddress', 'districts'));
    }

    public function pagar1( CheckoutRequest $request )
    {
        //dd($request);
        DB::beginTransaction();

        try {
            // Validar los datos y guardarlos
            $validatedData = $request->validated();

            $routeToRedirect = "";

            // Manejar user_id
            $userId = auth()->id();
            if ($userId) {
                //$userId = $userId;
                $routeToRedirect = route('orders.index');
            }
            if (!$userId) {
                $genericUser = User::where('name', 'generico')->first();
                if (!$genericUser) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Usuario genérico no encontrado.']);
                }
                $userId = $genericUser->id;
                $routeToRedirect = route('welcome');
            }

            $method_payment = PaymentMethod::find($validatedData['paymentMethod']);

            // Mapear tipo de pago a los nombres de las cajas
            $paymentTypeMap = [
                1 => 'efectivo',
                2 => 'bancario'
            ];

            if ( $method_payment->code == 'pos' || $method_payment->code == 'yape_plin' )
            {
                // Obtener la caja del tipo de pago
                $cashRegister = CashRegister::where('type', 'bancario')
                    ->where('status', 1) // Caja abierta
                    ->orderByDesc('opening_time')
                    ->first();

                if (!isset($cashRegister)) {
                    DB::rollBack();
                    return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                }
            } elseif ( $method_payment->code == 'efectivo' ) {
                $cashRegister = CashRegister::where('type', 'efectivo')
                    ->where('status', 1) // Caja abierta
                    ->orderByDesc('opening_time')
                    ->first();

                if (!isset($cashRegister)) {
                    DB::rollBack();
                    return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                }
            }

            $shippingAddressId = null;

            // Guardar la dirección de envío
            $isDefault = true;
            $existingDefaultAddress = Address::where('user_id', $userId)->where('is_default', true)->first();
            if ($existingDefaultAddress) {
                $isDefault = false;
            }

            $address = Address::create([
                'user_id' => $userId,
                'type' => 'shipping',
                'phone' => $validatedData['phone'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'address_line' => $validatedData['address'],
                'email' => $validatedData['email'],
                'reference' => $request->input('reference', ''),
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'is_default' => $isDefault,
                'latitude' => $request->input('latitude', null),
                'longitude' => $request->input('longitude', null),
            ]);

            $shippingAddressId = $address->id;

            // Procesar el carrito enviado
            $cart = $request->input('cart');

            // Si el carrito no es un array, decodificarlo
            if (!is_array($cart)) {
                $cart = json_decode($cart, true);
            }

            if (!$cart || !is_array($cart)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'El carrito enviado no es válido.']);
            }

            $totalAmount = $this->getTotalCart($cart);

            // Manejar el distrito y el costo de envío
            $districtId = $request->input('district');
            if (!$districtId) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Debe seleccionar un distrito para el envío.']);
            }

            $district = ShippingDistrict::findOrFail($districtId);
            $shippingCost = $district->shipping_cost;


            // Verificar el cupón
            $couponName = $request->input('coupon_name');
            $coupon = Coupon::where('name', $couponName)->first();
            $phone = preg_replace('/[^\d+]/', '', $validatedData['phone']);
            $discountAmount = 0;

            if ($coupon) {
                // Verificar si el cupón ya ha sido utilizado por el usuario
                $userCoupon = UserCoupon::where('user_id', auth()->id())
                    ->where('coupon_id', $coupon->id)
                    ->first();

                // Verificar si el número de teléfono ya utilizó el cupón
                $phoneCoupon = UserCoupon::where('phone', $phone)
                    ->where('coupon_id', $coupon->id)
                    ->first();

                if ($userCoupon && !$coupon->special) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'El cupón ya ha sido utilizado.']);
                }

                if ((!$coupon->special && $phoneCoupon)) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Sus datos ya han sido beneficiados con este cupón.']);

                }

                // Lógica para calcular el descuento
                if ($coupon->type == 'total') {
                    // Validar que no haya productos de categoría 'combo' (category_id = 3)
                    /*$hasCombo = $cart->details->contains(function ($detail) {
                        return $detail->product->category_id == 3;
                    });*/

                    $hasCombo = $this->hasCombo($cart);

                    if ($hasCombo) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                        ]);
                    }

                    // Aplicar descuento al total
                    if ($coupon->amount != 0) {
                        $discountAmount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discountAmount = ($coupon->percentage / 100) * $totalAmount;
                    }
                } elseif ($coupon->type == 'detail') {
                    // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
                    /*$onlyCombos = $cart->details->every(function ($detail) {
                        return $detail->product->category_id == 3;
                    });*/
                    $onlyCombos = $this->onlyCombos($cart);

                    if ($onlyCombos) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                        ]);
                    }

                    // Filtrar los detalles que no sean de categoría 'combo' (category_id = 3)
                    /*$eligibleDetails = $cart->details->filter(function ($detail) {
                        return $detail->product->category_id != 3;
                    });

                    // Buscar el detalle elegible con el subtotal más alto
                    $maxDetail = $eligibleDetails->sortByDesc('subtotal')->first();*/
                    $maxDetail = $this->getMaxDetail($cart);
                    //dd($maxDetail['subtotal']);
                    if ($maxDetail) {
                        if ($coupon->amount != 0) {
                            $discountAmount = $coupon->amount;
                        } elseif ($coupon->percentage != 0) {
                            $discountAmount = ($coupon->percentage / 100) * $maxDetail['subtotal'];
                        }
                    }
                } elseif ( $coupon->type == 'by_pass' ) {
                    if ($coupon->amount != 0) {
                        $discountAmount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discountAmount = ($coupon->percentage / 100) * $totalAmount;
                    }
                }
            }

            // Validacion del vuelto antes de crear la orden
            if ( $validatedData['paymentMethod'] == 2 )
            {
                $cashAmount = round((float)$request->input('cashAmount'), 2);
                $totalAmount = round((float)$totalAmount, 2);
                $discountAmount = round((float)$discountAmount, 2);
                $shippingCost = round((float)$shippingCost, 2);

                $vuelto = round($cashAmount - ($totalAmount - $discountAmount + $shippingCost), 2);

                if ($vuelto < 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'Ingrese un valor mayor a la venta.'], 422);
                }
            }

            // Crear la orden
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $shippingAddressId,
                'total_amount' => $totalAmount /*- $discountAmount + $shippingCost*/,
                'status' => 'created',
                'payment_method_id' => $validatedData['paymentMethod'],
                'amount_shipping' => $shippingCost,
                'shipping_district_id' => $districtId,
                'observations' => $request->input('observations', ''),
            ]);

            // Guardar los detalles de la orden
            foreach ($cart as $cartItem) {
                $price = 0;
                $additionalPrice = 0;

                if ($cartItem['custom'] === true) {
                    // Si el producto es custom, tomar el total directamente
                    $totalPrice = $cartItem['total'];
                    $price = $totalPrice;
                } else {

                    // Obtener el precio base del producto o tipo de producto
                    if (isset($cartItem['product_type_id']) && $cartItem['product_type_id'] != null) {
                        $productType = ProductType::find($cartItem['product_type_id']);
                        if ($productType) {
                            $price = $productType->price;
                        }
                    } else {
                        $product = Product::find($cartItem['product_id']);
                        if ($product) {
                            $price = $product->price_default;
                        }
                    }

                    // Calcular el precio adicional de las opciones
                    if (!empty($cartItem['options'])) {
                        foreach ($cartItem['options'] as $optionGroupId => $optionIds) {
                            foreach ($optionIds as $optionId) {
                                $additionalPrice += isset($optionId['additional_price']) ? $optionId['additional_price'] : 0;
                            }
                        }
                    }

                    // Sumar el precio base y el adicional
                    $totalPrice = $price + $additionalPrice;
                }

                // Crear el detalle de la orden
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem['product_id'],
                    'product_type_id' => ($cartItem['product_type_id'] == null) ? null: $cartItem['product_type_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $totalPrice,
                    'subtotal' => $cartItem['quantity'] * $price,
                ]);

                // Guardar las opciones del detalle si existen
                if (!empty($cartItem['options'])) {
                    foreach ($cartItem['options'] as $optionGroupId => $optionIds) {
                        foreach ($optionIds as $optionId) {
                            //dd($optionId);
                            OrderDetailOption::create([
                                'order_detail_id' => $orderDetail->id,
                                'option_id' => null, // Si deseas asociarlo a un `Option`, actualiza este campo.
                                'product_id' => $optionId['product_id'], // ID del producto asociado a la opción.
                            ]);
                        }
                    }
                }

                // Guardar los toppings si el producto es custom y tiene toppings
                if ($cartItem['custom'] === true && !empty($cartItem['toppings'])) {
                    foreach ($cartItem['toppings'] as $topping) {
                        OrderDetailTopping::create([
                            'order_detail_id' => $orderDetail->id,
                            'topping_id' => $topping['topping_id'],
                            'topping_name' => $topping['topping_name'],
                            'type' => $topping['type'],
                            'extra' => isset($topping['extra']) ? $topping['extra'] : 0,
                        ]);
                    }
                }
            }

            // Asociar el cupón al usuario si se aplicó
            if ($coupon) {
                UserCoupon::create([
                    'user_id' => $userId,
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discountAmount,
                    'order_id' => $order->id,
                    'phone' => $phone,
                ]);
            }



            // Selección del método de pago
            switch ($method_payment->code) {
                /*case 'mercado_pago':
                    // Procesar pago con Mercado Pago
                    $payment = new Payment();
                    $payment->transaction_amount = $totalAmount;
                    $payment->token = $request->input('token');
                    $payment->description = "Compra en tienda";
                    $payment->installments = 1;
                    $payment->payer = ["email" => $validatedData['email']];
                    $payment->payment_method_id = "visa";

                    $payment->save();

                    if ($payment->status == 'approved') {
                        $order->status = 'paid';
                        $cart->status = 'completed';
                        $cart->save();
                        $order->save();
                        DB::commit();

                        return response()->json(['success' => true, 'message' => 'Pago con Mercado Pago exitoso']);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Pago con Mercado Pago rechazado']);
                    }*/

                case 'pos':
                    // Lógica para pago POS (Ej. Marca la orden como pagada con POS)
                    //$order->status = 'paid';
                    //$order->save();
                    //$cart->status = 'proc';
                    //$cart->save();
                    $data = [
                        'nameUser' => $order->user->name,
                        'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                        'phoneUser' => $order->shipping_address->phone,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    // Agregar movimientos a la caja
                    $paymentType = 2;
                    // Obtener la caja del tipo de pago
                    $cashRegister = CashRegister::where('type', $paymentTypeMap[$paymentType])
                        ->where('status', 1) // Caja abierta
                        ->orderByDesc('opening_time')
                        ->first();

                    /*if (!isset($cashRegister)) {
                        return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                    }*/

                    // Crear el movimiento de ingreso (venta)
                    $cashMovement = CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'order_id' => $order->id,
                        'type' => 'sale', // Tipo de movimiento: venta
                        'amount' => (float)$order->amount_pay,
                        'subtype' => 'pos',
                        'description' => 'Venta registrada con tipo de pago: pos',
                        'regularize' => 0
                    ]);

                    // No Actualizar el saldo actual y el total de ventas en la caja
                    /*$cashRegister->current_balance += (float)$cashMovement->amount;
                    $cashRegister->total_sales += (float)$cashMovement->amount;
                    $cashRegister->save();*/

                    DB::commit();

                    // Obtener el correo electrónico según la lógica.
                    $email = $this->getEmailForOrder($order);

                    if ($email) {
                        // Enviar correo al cliente con el estado de la orden.
                        Mail::to($email)->send(new OrderStatusEmail($order));
                        Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                    } else {
                        Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                    }

                    return response()->json(['success' => true, 'message' => 'Pago realizado con POS', 'redirect_url' => $routeToRedirect]);

                case 'efectivo':
                    // Lógica para pago en efectivo
                    $order->payment_amount = $request->input('cashAmount');
                    $order->save();

                    $data = [
                        'nameUser' => $order->user->name,
                        'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                        'phoneUser' => $order->shipping_address->phone,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    // Agregar movimientos a la caja
                    $vuelto = (float)$request->input('cashAmount') - (float)$order->amount_pay;

                    // Obtener la caja del tipo de pago
                    $cashRegister = CashRegister::where('type', $paymentTypeMap[1])
                        ->where('status', 1) // Caja abierta
                        ->orderByDesc('opening_time')
                        ->first();

                    /*if (!isset($cashRegister)) {
                        return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                    }*/

                    // Crear el movimiento de ingreso (venta)
                    CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'order_id' => $order->id,
                        'type' => 'sale', // Tipo de movimiento: venta
                        'amount' => (float)$request->input('cashAmount'),
                        'description' => 'Venta registrada con tipo de pago: efectivo',
                    ]);

                    // Actualizar el saldo actual y el total de ventas en la caja
                    $cashRegister->current_balance += (float)$request->input('cashAmount');
                    $cashRegister->total_sales += (float)$request->input('cashAmount');
                    $cashRegister->save();

                    // Registrar el vuelto como egreso si el tipo de pago es efectivo y hay vuelto
                    if ($vuelto > 0) {
                        // Mapear el type_vuelto (la caja desde donde se dará el vuelto)
                        // Obtener la caja para el vuelto
                        /*$vueltoCashRegister = CashRegister::where('type', $paymentTypeMap[1])
                            ->where('status', 1) // Caja abierta
                            ->latest()
                            ->first();

                        if (!isset($vueltoCashRegister)) {
                            return response()->json(['message' => 'No hay caja abierta para dar el vuelto.'], 422);
                        }*/

                        // Crear el movimiento de egreso (vuelto)
                        CashMovement::create([
                            'cash_register_id' => $cashRegister->id,
                            'order_id' => $order->id,
                            'type' => 'expense', // Tipo de movimiento: egreso
                            'amount' => $vuelto,
                            'description' => 'Vuelto entregado de la venta',
                        ]);

                        // Actualizar el saldo de la caja del vuelto
                        $cashRegister->current_balance -= $vuelto;
                        $cashRegister->total_expenses += $vuelto;
                        $cashRegister->save();
                    }

                    DB::commit();

                    // Obtener el correo electrónico según la lógica.
                    $email = $this->getEmailForOrder($order);

                    if ($email) {
                        // Enviar correo al cliente con el estado de la orden.
                        Mail::to($email)->send(new OrderStatusEmail($order));
                        Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                    } else {
                        Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                    }

                    return response()->json(['success' => true, 'message' => 'Orden creada. Pago en efectivo pendiente', 'redirect_url' => $routeToRedirect]);

                case 'yape_plin':
                    // Lógica para pago con Yape o Plin
                    $operationCode = $request->input('operationCode');
                    if ($operationCode) {
                        //$order->status = 'paid';
                        //$order->save();
                        //$cart->status = 'completed';
                        //$cart->save();
                        $order->payment_code = $request->input('operationCode');
                        $order->save();

                        $data = [
                            'nameUser' => $order->user->name,
                            'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                            'phoneUser' => $order->shipping_address->phone,
                            'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                            'order' => "ORDEN - ".$order->id
                        ];

                        $telegramController = new TelegramController();
                        $telegramController->sendNotification('process', $data);

                        // Agregar movimientos a la caja
                        $paymentType = 2;
                        // Obtener la caja del tipo de pago
                        $cashRegister = CashRegister::where('type', $paymentTypeMap[$paymentType])
                            ->where('status', 1) // Caja abierta
                            ->orderByDesc('opening_time')
                            ->first();

                       /* if (!isset($cashRegister)) {
                            return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                        }*/

                        // Crear el movimiento de ingreso (venta)
                        $cashMovement = CashMovement::create([
                            'cash_register_id' => $cashRegister->id,
                            'order_id' => $order->id,
                            'type' => 'sale', // Tipo de movimiento: venta
                            'amount' => (float)$order->amount_pay,
                            'subtype' => 'yape',
                            'description' => 'Venta registrada con tipo de pago: yape/plin'
                        ]);

                        // Actualizar el saldo actual y el total de ventas en la caja
                        $cashRegister->current_balance += (float)$cashMovement->amount;
                        $cashRegister->total_sales += (float)$cashMovement->amount;
                        $cashRegister->save();

                        DB::commit();

                        // Obtener el correo electrónico según la lógica.
                        $email = $this->getEmailForOrder($order);

                        if ($email) {
                            // Enviar correo al cliente con el estado de la orden.
                            Mail::to($email)->send(new OrderStatusEmail($order));
                            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                        } else {
                            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                        }

                        return response()->json(['success' => true, 'message' => 'Pago realizado con Yape/Plin', 'redirect_url' => $routeToRedirect]);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Falta el código de operación para Yape/Plin']);
                    }

                default:
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Método de pago no válido']);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el checkout. Inténtelo nuevamente.',
                'error' => $e->getMessage(),
                'linea' => $e->getTrace(),
            ], 420);
        }
    }

    public function pagar( CheckoutRequest $request )
    {
        //dd($request);
        DB::beginTransaction();

        try {
            // Validar los datos y guardarlos
            $validatedData = $request->validated();

            $routeToRedirect = "";

            // Manejar user_id
            $userId = auth()->id();
            if ($userId) {
                //$userId = $userId;
                $routeToRedirect = route('orders.index');
            }
            if (!$userId) {
                $genericUser = User::where('name', 'generico')->first();
                if (!$genericUser) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Usuario genérico no encontrado.']);
                }
                $userId = $genericUser->id;
                $routeToRedirect = route('welcome');
            }

            $method_payment = PaymentMethod::find($validatedData['paymentMethod']);

            // Mapear tipo de pago a los nombres de las cajas
            $paymentTypeMap = [
                1 => 'efectivo',
                2 => 'bancario'
            ];

            if ( $method_payment->code == 'pos' || $method_payment->code == 'yape_plin' )
            {
                // Obtener la caja del tipo de pago
                $cashRegister = CashRegister::where('type', 'bancario')
                    ->where('status', 1) // Caja abierta
                    ->orderByDesc('opening_time')
                    ->first();

                if (!isset($cashRegister)) {
                    DB::rollBack();
                    return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                }
            } elseif ( $method_payment->code == 'efectivo' ) {
                $cashRegister = CashRegister::where('type', 'efectivo')
                    ->where('status', 1) // Caja abierta
                    ->orderByDesc('opening_time')
                    ->first();

                if (!isset($cashRegister)) {
                    DB::rollBack();
                    return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                }
            }

            $shippingAddressId = null;

            // Guardar la dirección de envío
            $isDefault = true;
            $existingDefaultAddress = Address::where('user_id', $userId)->where('is_default', true)->first();
            if ($existingDefaultAddress) {
                $isDefault = false;
            }

            $address = Address::create([
                'user_id' => $userId,
                'type' => 'shipping',
                'phone' => $validatedData['phone'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'address_line' => $validatedData['address'],
                'email' => $validatedData['email'],
                'reference' => $request->input('reference', ''),
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'is_default' => $isDefault,
                'latitude' => $request->input('latitude', null),
                'longitude' => $request->input('longitude', null),
            ]);

            $shippingAddressId = $address->id;

            // Procesar el carrito enviado
            $cart = $request->input('cart');

            // Si el carrito no es un array, decodificarlo
            if (!is_array($cart)) {
                $cart = json_decode($cart, true);
            }

            if (!$cart || !is_array($cart)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'El carrito enviado no es válido.']);
            }

            //$totalAmount = $this->getTotalCart($cart);

            $tienda = $request->input('tienda');
            $shippingCost = isset($tienda['precioEnvio']) ? $tienda['precioEnvio'] : 0;
            $shopId = isset($tienda['precioEnvio']) ? $tienda['tiendaId'] : null;
            $total = $this->getTotalCart($cart);
            //$totalWithShipping = $total + $shippingCost;

            // Verificar el cupón
            $code = $request->input('coupon_name');
            $phone = preg_replace('/[^\d+]/', '', $validatedData['phone']);

            $coupon = Coupon::where('name', $code)->where('status', 'active')->first();
            /*if (!$coupon || $code == "") {
                return response()->json([
                    'success' => false,
                    'message' => 'El código de promoción no es válido o está inactivo.',
                    'new_total' => number_format($totalWithShipping, 2),
                    'coupon_name' => ''
                ]);
            }*/
            $discountAmount = 0;
            if ($coupon) {
                $userCoupon = UserCoupon::where('user_id', auth()->id())
                    ->where('coupon_id', $coupon->id)
                    ->first();

                $phoneCoupon = UserCoupon::where('phone', $phone)
                    ->where('coupon_id', $coupon->id)
                    ->first();

                if ((!$coupon->special && $userCoupon)) {
                    $dataTotals = $this->getDataTotals($cart, $shippingCost, $discountAmount, 18);
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón ya ha sido utilizado.',

                        'total_gravada' => number_format($dataTotals['total_gravada'], 2, '.', ''),
                        'total_igv' => number_format($dataTotals['total_igv'], 2, '.', ''),
                        'total_a_pagar' => number_format($dataTotals['total_a_pagar'], 2, '.', ''),

                        'coupon_name' => ''
                    ]);
                }

                if ((!$coupon->special && $phoneCoupon)) {
                    $dataTotals = $this->getDataTotals($cart, $shippingCost, $discountAmount, 18);
                    return response()->json([
                        'success' => false,
                        'message' => 'Sus datos ya han sido beneficiados con el cupón.',
                        'total_gravada' => number_format($dataTotals['total_gravada'], 2, '.', ''),
                        'total_igv' => number_format($dataTotals['total_igv'], 2, '.', ''),
                        'total_a_pagar' => number_format($dataTotals['total_a_pagar'], 2, '.', ''),

                        'coupon_name' => ''
                    ]);
                }



                if ($coupon->type == 'by_pass') {
                    $discountAmount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? ($total/1.18) * ($coupon->percentage / 100) : 0);
                } else {
                    $cartCategories = array_unique(array_map(function ($item) {
                        $product = Product::find($item['product_id']);
                        return $product ? $product->category_id : null;
                    }, $cart));

                    $allowedCategories = CategoryCoupon::where('coupon_id', $coupon->id)->pluck('category_id')->toArray();
                    $hasAllowedCategory = !empty(array_intersect($cartCategories, $allowedCategories));

                    if (!$hasAllowedCategory) {
                        $dataTotals = $this->getDataTotals($cart, $shippingCost, $discountAmount, 18);
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no se puede aplicar a estos productos.',

                            'total_gravada' => number_format($dataTotals['total_gravada'], 2, '.', ''),
                            'total_igv' => number_format($dataTotals['total_igv'], 2, '.', ''),
                            'total_a_pagar' => number_format($dataTotals['total_a_pagar'], 2, '.', ''),

                            'coupon_name' => ''
                        ]);
                    }

                    $total = array_sum(array_map(function ($item) {
                        $product = Product::find($item['product_id']);
                        return $product && $product->category->aplica_porcentaje ? $item['total'] * $item['quantity'] : 0;
                    }, $cart));

                    if ($coupon->type == 'total') {
                        $discountAmount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? ($total/1.18) * ($coupon->percentage / 100) : 0);
                    } elseif ($coupon->type == 'detail') {
                        $maxDetail = collect($cart)->filter(function ($item) use ($allowedCategories) {
                            $product = Product::find($item['product_id']);
                            return in_array($product ? $product->category_id : null, $allowedCategories);
                        })->sortByDesc('total')->first();

                        $discountAmount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? (($maxDetail['total']/1.18) ?? 0) * ($coupon->percentage / 100) : 0);
                    }
                }
            }

            // TODO: Obtenemos el total_a_pagar
            $dataTotals = $this->getDataTotals($cart, $shippingCost, $discountAmount, 18);
            $totalAmount = $dataTotals['total_a_pagar'];

            // Validacion del vuelto antes de crear la orden
            if ( $validatedData['paymentMethod'] == 2 )
            {
                $cashAmount = round((float)$request->input('cashAmount'), 2);
                $totalAmount = round((float)$totalAmount, 2);

                // Realizamos la operación matemática y redondeamos a 2 decimales
                $vuelto = round(($cashAmount - $totalAmount), 2);

                /*dump("cashAmount");
                dump($cashAmount);
                dump("totalAmount");
                dump($totalAmount);
                dump("discountAmount");
                dump($discountAmount);
                dump("shippingCost");
                dump($shippingCost);
                dump("Vuelto");
                dump($vuelto);
                dd();
                if ( $vuelto < 0 )
                {
                    return response()->json([
                        'success' => true,
                        'message' => 'Vuelto NEGATIVO  '. $vuelto]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Vuelto POSITIVO'. $vuelto]);
                }*/

                if ($vuelto < 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'Ingrese un valor mayor a la venta.'], 422);
                }
            }

            $flames = 0;

            // Recorrer el carrito para identificar productos con reward
            foreach ($cart as $item) {
                if (isset($item['reward']) && $item['reward'] === true) {
                    $flames += (int)$item['flames']; // Sumamos las flames
                }
            }

            // TODO: VALIDACIONES DE INVOICE
            // Determinar tipo de documento SUNAT
            $type_document = null;
            $numero_documento_cliente = null;
            $nombre_cliente = null;
            $direccion_cliente = null;
            $tipo_documento_cliente = null;
            $email_cliente = null;

            // Asignar según el tipo de comprobante
            if ($request->invoice_type === 'boleta') {
                $type_document = '03'; // Boleta
                $numero_documento_cliente = $request->dni;
                $nombre_cliente = $request->first_name . ' ' . $request->last_name;
                $direccion_cliente = $request->address;
                $tipo_documento_cliente = '1'; // DNI
                $email_cliente = $request->email_invoice_boleta;
            } elseif ($request->invoice_type === 'factura') {
                $type_document = '01'; // Factura
                $numero_documento_cliente = $request->ruc;
                $nombre_cliente = $request->razon_social;
                $direccion_cliente = $request->direccion_fiscal;
                $tipo_documento_cliente = '6'; // RUC
                $email_cliente = $request->email_invoice_factura;
            }

            // Crear la orden
            $order = Order::create([
                'user_id' => $userId,
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $shippingAddressId,
                'total_amount' => $totalAmount /*- $discountAmount + $shippingCost*/,
                'status' => 'created',
                'payment_method_id' => $validatedData['paymentMethod'],
                'amount_shipping' => $shippingCost,
                //'shipping_district_id' => $districtId,
                'shipping_district_id' => null,
                'shop_id' => $shopId,
                'observations' => $request->input('observations', ''),
                'flames' => $flames,

                // Facturación
                'type_document' => $type_document,
                'numero_documento_cliente' => $numero_documento_cliente,
                'tipo_documento_cliente' => $tipo_documento_cliente,
                'nombre_cliente' => $nombre_cliente,
                'direccion_cliente' => $direccion_cliente,
                'email_cliente' => $email_cliente,

                // Los siguientes campos se llenarán más adelante cuando se genere el comprobante con Greenter
                'serie' => null,
                'numero' => null,
                'sunat_ticket' => null,
                'sunat_status' => null,
                'sunat_message' => null,
                'xml_path' => null,
                'cdr_path' => null,
                'fecha_emision' => null,
            ]);

            // Guardar los detalles de la orden
            foreach ($cart as $cartItem) {
                $price = 0;
                $additionalPrice = 0;

                if ($cartItem['custom'] === true) {
                    // Si el producto es custom, tomar el total directamente
                    $totalPrice = $cartItem['total'];
                    $price = $totalPrice;
                } else {

                    // Obtener el precio base del producto o tipo de producto
                    if (isset($cartItem['product_type_id']) && $cartItem['product_type_id'] != null) {
                        $productType = ProductType::find($cartItem['product_type_id']);
                        if ($productType) {
                            $price = $productType->price;
                        }
                    } else {
                        $product = Product::find($cartItem['product_id']);
                        if ($product) {
                            $price = $product->price_default;
                        }
                    }

                    // Calcular el precio adicional de las opciones
                    if (!empty($cartItem['options'])) {
                        foreach ($cartItem['options'] as $optionGroupId => $optionIds) {
                            foreach ($optionIds as $optionId) {
                                $additionalPrice += isset($optionId['additional_price']) ? $optionId['additional_price'] : 0;
                            }
                        }
                    }

                    // Sumar el precio base y el adicional
                    $totalPrice = $price + $additionalPrice;
                }

                // Crear el detalle de la orden
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem['product_id'],
                    'product_type_id' => ($cartItem['product_type_id'] == null) ? null: $cartItem['product_type_id'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $totalPrice,
                    'subtotal' => $cartItem['quantity'] * $totalPrice,
                ]);

                // Guardar las opciones del detalle si existen
                if (!empty($cartItem['options'])) {
                    foreach ($cartItem['options'] as $optionGroupId => $optionIds) {
                        foreach ($optionIds as $optionId) {
                            //dd($optionId);
                            OrderDetailOption::create([
                                'order_detail_id' => $orderDetail->id,
                                'option_id' => null, // Si deseas asociarlo a un `Option`, actualiza este campo.
                                'product_id' => $optionId['product_id'], // ID del producto asociado a la opción.
                            ]);
                        }
                    }
                }

                // Guardar los toppings si el producto es custom y tiene toppings
                if ($cartItem['custom'] === true && !empty($cartItem['toppings'])) {
                    foreach ($cartItem['toppings'] as $topping) {
                        OrderDetailTopping::create([
                            'order_detail_id' => $orderDetail->id,
                            'topping_id' => $topping['topping_id'],
                            'topping_name' => $topping['topping_name'],
                            'type' => $topping['type'],
                            'extra' => isset($topping['extra']) ? $topping['extra'] : 0,
                        ]);
                    }
                }
            }

            // Asociar el cupón al usuario si se aplicó
            if ($coupon) {
                UserCoupon::create([
                    'user_id' => $userId,
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discountAmount,
                    'order_id' => $order->id,
                    'phone' => $phone,
                ]);
            }



            // Selección del método de pago
            switch ($method_payment->code) {
                /*case 'mercado_pago':
                    // Procesar pago con Mercado Pago
                    $payment = new Payment();
                    $payment->transaction_amount = $totalAmount;
                    $payment->token = $request->input('token');
                    $payment->description = "Compra en tienda";
                    $payment->installments = 1;
                    $payment->payer = ["email" => $validatedData['email']];
                    $payment->payment_method_id = "visa";

                    $payment->save();

                    if ($payment->status == 'approved') {
                        $order->status = 'paid';
                        $cart->status = 'completed';
                        $cart->save();
                        $order->save();
                        DB::commit();

                        return response()->json(['success' => true, 'message' => 'Pago con Mercado Pago exitoso']);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Pago con Mercado Pago rechazado']);
                    }*/

                case 'pos':
                    // Lógica para pago POS (Ej. Marca la orden como pagada con POS)
                    //$order->status = 'paid';
                    //$order->save();
                    //$cart->status = 'proc';
                    //$cart->save();
                    $data = [
                        'nameUser' => $order->user->name,
                        'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                        'phoneUser' => $order->shipping_address->phone,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    // Agregar movimientos a la caja
                    $paymentType = 2;
                    // Obtener la caja del tipo de pago
                    $cashRegister = CashRegister::where('type', $paymentTypeMap[$paymentType])
                        ->where('status', 1) // Caja abierta
                        ->orderByDesc('opening_time')
                        ->first();

                    /*if (!isset($cashRegister)) {
                        return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                    }*/

                    // Crear el movimiento de ingreso (venta)
                    $cashMovement = CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'order_id' => $order->id,
                        'type' => 'sale', // Tipo de movimiento: venta
                        'amount' => $totalAmount,
                        'subtype' => 'pos',
                        'description' => 'Venta registrada con tipo de pago: pos',
                        'regularize' => 0
                    ]);

                    // No Actualizar el saldo actual y el total de ventas en la caja
                    /*$cashRegister->current_balance += (float)$cashMovement->amount;
                    $cashRegister->total_sales += (float)$cashMovement->amount;
                    $cashRegister->save();*/

                    DB::commit();

                    // Obtener el correo electrónico según la lógica.
                    $email = $this->getEmailForOrder($order);

                    if ($email) {
                        // Enviar correo al cliente con el estado de la orden.
                        Mail::to($email)->send(new OrderStatusEmail($order));
                        Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                    } else {
                        Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                    }

                    // Emitir el evento a Pusher
                    broadcast(new OrderCreated($order, $order->id));

                    return response()->json(['success' => true, 'message' => 'Pago realizado con POS', 'redirect_url' => $routeToRedirect]);

                case 'efectivo':
                    // Lógica para pago en efectivo
                    $order->payment_amount = $request->input('cashAmount');
                    $order->save();

                    $data = [
                        'nameUser' => $order->user->name,
                        'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                        'phoneUser' => $order->shipping_address->phone,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    // Agregar movimientos a la caja
                    $vuelto = (float)$request->input('cashAmount') - (float)$totalAmount;

                    // Obtener la caja del tipo de pago
                    $cashRegister = CashRegister::where('type', $paymentTypeMap[1])
                        ->where('status', 1) // Caja abierta
                        ->orderByDesc('opening_time')
                        ->first();

                    /*if (!isset($cashRegister)) {
                        return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                    }*/

                    // Crear el movimiento de ingreso (venta)
                    CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'order_id' => $order->id,
                        'type' => 'sale', // Tipo de movimiento: venta
                        'amount' => (float)$request->input('cashAmount'),
                        'description' => 'Venta registrada con tipo de pago: efectivo',
                    ]);

                    // Actualizar el saldo actual y el total de ventas en la caja
                    $cashRegister->current_balance += (float)$request->input('cashAmount');
                    $cashRegister->total_sales += (float)$request->input('cashAmount');
                    $cashRegister->save();

                    // Registrar el vuelto como egreso si el tipo de pago es efectivo y hay vuelto
                    if ($vuelto > 0) {
                        // Mapear el type_vuelto (la caja desde donde se dará el vuelto)
                        // Obtener la caja para el vuelto
                        /*$vueltoCashRegister = CashRegister::where('type', $paymentTypeMap[1])
                            ->where('status', 1) // Caja abierta
                            ->latest()
                            ->first();

                        if (!isset($vueltoCashRegister)) {
                            return response()->json(['message' => 'No hay caja abierta para dar el vuelto.'], 422);
                        }*/

                        // Crear el movimiento de egreso (vuelto)
                        CashMovement::create([
                            'cash_register_id' => $cashRegister->id,
                            'order_id' => $order->id,
                            'type' => 'expense', // Tipo de movimiento: egreso
                            'amount' => $vuelto,
                            'description' => 'Vuelto entregado de la venta',
                        ]);

                        // Actualizar el saldo de la caja del vuelto
                        $cashRegister->current_balance -= $vuelto;
                        $cashRegister->total_expenses += $vuelto;
                        $cashRegister->save();
                    }

                    DB::commit();

                    // Obtener el correo electrónico según la lógica.
                    $email = $this->getEmailForOrder($order);

                    if ($email) {
                        // Enviar correo al cliente con el estado de la orden.
                        Mail::to($email)->send(new OrderStatusEmail($order));
                        Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                    } else {
                        Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                    }

                    // Emitir el evento a Pusher
                    broadcast(new OrderCreated($order, $order->id));

                    return response()->json(['success' => true, 'message' => 'Orden creada. Pago en efectivo pendiente', 'redirect_url' => $routeToRedirect]);

                case 'yape_plin':
                    // Lógica para pago con Yape o Plin
                    $operationCode = $request->input('operationCode');
                    if ($operationCode) {
                        //$order->status = 'paid';
                        //$order->save();
                        //$cart->status = 'completed';
                        //$cart->save();
                        $order->payment_code = $request->input('operationCode');
                        $order->save();

                        $data = [
                            'nameUser' => $order->user->name,
                            'nameUserReal' => $order->shipping_address->first_name." ".$order->shipping_address->last_name,
                            'phoneUser' => $order->shipping_address->phone,
                            'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                            'order' => "ORDEN - ".$order->id
                        ];

                        $telegramController = new TelegramController();
                        $telegramController->sendNotification('process', $data);

                        // Agregar movimientos a la caja
                        $paymentType = 2;
                        // Obtener la caja del tipo de pago
                        $cashRegister = CashRegister::where('type', $paymentTypeMap[$paymentType])
                            ->where('status', 1) // Caja abierta
                            ->orderByDesc('opening_time')
                            ->first();

                        /* if (!isset($cashRegister)) {
                             return response()->json(['message' => 'No hay caja abierta para este tipo de pago.'], 422);
                         }*/

                        // Crear el movimiento de ingreso (venta)
                        $cashMovement = CashMovement::create([
                            'cash_register_id' => $cashRegister->id,
                            'order_id' => $order->id,
                            'type' => 'sale', // Tipo de movimiento: venta
                            'amount' => (float)$totalAmount,
                            'subtype' => 'yape',
                            'description' => 'Venta registrada con tipo de pago: yape/plin'
                        ]);

                        // Actualizar el saldo actual y el total de ventas en la caja
                        $cashRegister->current_balance += (float)$cashMovement->amount;
                        $cashRegister->total_sales += (float)$cashMovement->amount;
                        $cashRegister->save();

                        DB::commit();

                        // Obtener el correo electrónico según la lógica.
                        $email = $this->getEmailForOrder($order);

                        if ($email) {
                            // Enviar correo al cliente con el estado de la orden.
                            Mail::to($email)->send(new OrderStatusEmail($order));
                            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ');
                        } else {
                            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
                        }

                        // Emitir el evento a Pusher
                        broadcast(new OrderCreated($order, $order->id));

                        return response()->json(['success' => true, 'message' => 'Pago realizado con Yape/Plin', 'redirect_url' => $routeToRedirect]);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Falta el código de operación para Yape/Plin']);
                    }

                default:
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Método de pago no válido']);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el checkout. Inténtelo nuevamente.',
                'error' => $e->getMessage(),
                'linea' => $e->getTrace(),
            ], 420);
        }
    }

    private function getEmailForOrder($order)
    {
        // Obtener shipping_address si existe
        $shippingAddress = $order->shipping_address;

        if ($shippingAddress && !empty($shippingAddress->email)) {
            return $shippingAddress->email; // Usar el email del shipping_address si está disponible.
        }

        // Obtener email del usuario asociado a la orden si no hay shipping_address o su email está vacío.
        $user = $order->user;
        if ($user && !empty($user->email)) {
            return $user->email;
        }

        // Si no hay correo en shipping_address ni en el usuario, retornar null.
        return null;
    }

    public function pagarOriginal( CheckoutRequest $request )
    {
        DB::beginTransaction();

        try {
            // Validar los datos y guardarlos
            $validatedData = $request->validated();

            $shippingAddressId = null;

            // Verificar si 'save-info' está marcado como "on"
            //if ($request->has('save-info') && $request->input('save-info') === 'on') {
            $isDefault = true;
            $existingDefaultAddress = Address::where('user_id', auth()->id())->where('is_default', true)->first();
            if ($existingDefaultAddress) {
                $isDefault = false;
            }

            // Crear la nueva dirección
            $address = Address::create([
                'user_id' => auth()->id(),
                'type' => 'shipping',
                'phone' => $validatedData['phone'],
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
                'address_line' => $validatedData['address'],
                'reference' => $request->input('reference', ''),
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'is_default' => $isDefault
            ]);

            $shippingAddressId = $address->id;
            //}

            $cart = Cart::findOrFail($validatedData['cart_id']);
            $totalAmount = $cart->total_cart;

            // Obtener el distrito seleccionado y su costo de envío
            $districtId = $request->input('district');
            if (!$districtId) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Debe seleccionar un distrito para el envío.']);
            }

            $district = ShippingDistrict::findOrFail($districtId);
            $shippingCost = $district->shipping_cost;


            // Verificar el cupón
            $couponName = $request->input('coupon_name');
            $coupon = Coupon::where('name', $couponName)->first();

            $discountAmount = 0;

            if ($coupon) {
                // Verificar si el cupón ya ha sido utilizado por el usuario
                $userCoupon = UserCoupon::where('user_id', auth()->id())
                    ->where('coupon_id', $coupon->id)
                    ->first();

                if ($userCoupon && !$coupon->special) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'El cupón ya ha sido utilizado.']);
                }

                // Lógica para calcular el descuento
                if ($coupon->type == 'total') {
                    // Validar que no haya productos de categoría 'combo' (category_id = 3)
                    $hasCombo = $cart->details->contains(function ($detail) {
                        return $detail->product->category_id == 3;
                    });

                    if ($hasCombo) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                        ]);
                    }

                    // Aplicar descuento al total
                    if ($coupon->amount != 0) {
                        $discountAmount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discountAmount = ($coupon->percentage / 100) * $cart->total_cart;
                    }
                } elseif ($coupon->type == 'detail') {
                    // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
                    $onlyCombos = $cart->details->every(function ($detail) {
                        return $detail->product->category_id == 3;
                    });

                    if ($onlyCombos) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                        ]);
                    }

                    // Filtrar los detalles que no sean de categoría 'combo' (category_id = 3)
                    $eligibleDetails = $cart->details->filter(function ($detail) {
                        return $detail->product->category_id != 3;
                    });

                    // Buscar el detalle elegible con el subtotal más alto
                    $maxDetail = $eligibleDetails->sortByDesc('price')->first();

                    if ($maxDetail) {
                        if ($coupon->amount != 0) {
                            $discountAmount = $coupon->amount;
                        } elseif ($coupon->percentage != 0) {
                            $discountAmount = (($coupon->percentage / 100) * $maxDetail->price);
                        }
                    }
                }
            }

            // Crear la orden
            $order = Order::create([
                'user_id' => auth()->id(),
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $shippingAddressId,
                'total_amount' => $totalAmount,
                'status' => 'created',
                'payment_method_id' => $validatedData['paymentMethod'],
                'amount_shipping' => $shippingCost,
                'shipping_district_id' => $districtId,
                'observations' => $cart->observations
            ]);

            // Crear los detalles de la orden basados en el carrito
            foreach ($cart->details as $cartItem) {
                // Crear el detalle de la orden
                $orderDetail = OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_type_id' => $cartItem->product_type_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'subtotal' => $cartItem->quantity * $cartItem->price,
                ]);

                // Guardar las opciones relacionadas con este detalle
                if (!empty($cartItem->options)) {
                    foreach ($cartItem->options as $option) {
                        OrderDetailOption::create([
                            'order_detail_id' => $orderDetail->id, // Relación con el detalle de la orden
                            'option_id' => $option->option_id,           // ID de la opción seleccionada
                            'product_id' => $option->product_id,  // ID del producto asociado a la opción
                        ]);
                    }
                }
            }

            $cart->status = 'completed';
            $cart->save();

            // Guardamos la relación del cupón con el usuario, solo si se aplicó
            if ($coupon) {
                UserCoupon::create([
                    'user_id' => auth()->id(),
                    'coupon_id' => $coupon->id,
                    'discount_amount' => $discountAmount,
                    'order_id' => $order->id, // Asociar al pedido
                ]);
            }


            $method_payment = PaymentMethod::find($validatedData['paymentMethod']);

            // Selección del método de pago
            switch ($method_payment->code) {
                /*case 'mercado_pago':
                    // Procesar pago con Mercado Pago
                    $payment = new Payment();
                    $payment->transaction_amount = $totalAmount;
                    $payment->token = $request->input('token');
                    $payment->description = "Compra en tienda";
                    $payment->installments = 1;
                    $payment->payer = ["email" => $validatedData['email']];
                    $payment->payment_method_id = "visa";

                    $payment->save();

                    if ($payment->status == 'approved') {
                        $order->status = 'paid';
                        $cart->status = 'completed';
                        $cart->save();
                        $order->save();
                        DB::commit();

                        return response()->json(['success' => true, 'message' => 'Pago con Mercado Pago exitoso']);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Pago con Mercado Pago rechazado']);
                    }*/

                case 'pos':
                    // Lógica para pago POS (Ej. Marca la orden como pagada con POS)
                    //$order->status = 'paid';
                    //$order->save();
                    //$cart->status = 'proc';
                    //$cart->save();
                    $data = [
                        'nameUser' => $order->user->name,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    DB::commit();

                    return response()->json(['success' => true, 'message' => 'Pago realizado con POS', 'redirect_url' => route('orders.index')]);

                case 'efectivo':
                    // Lógica para pago en efectivo
                    $order->payment_amount = $request->input('cashAmount');
                    $order->save();

                    $data = [
                        'nameUser' => $order->user->name,
                        'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                        'order' => "ORDEN - ".$order->id
                    ];

                    $telegramController = new TelegramController();
                    $telegramController->sendNotification('process', $data);

                    DB::commit();

                    return response()->json(['success' => true, 'message' => 'Orden creada. Pago en efectivo pendiente', 'redirect_url' => route('orders.index')]);

                case 'yape_plin':
                    // Lógica para pago con Yape o Plin
                    $operationCode = $request->input('operationCode');
                    if ($operationCode) {
                        //$order->status = 'paid';
                        //$order->save();
                        //$cart->status = 'completed';
                        //$cart->save();
                        $order->payment_code = $request->input('operationCode');
                        $order->save();

                        $data = [
                            'nameUser' => $order->user->name,
                            'dateOperation' => $order->created_at->format('d M Y, g:i a'),
                            'order' => "ORDEN - ".$order->id
                        ];

                        $telegramController = new TelegramController();
                        $telegramController->sendNotification('process', $data);

                        DB::commit();

                        return response()->json(['success' => true, 'message' => 'Pago realizado con Yape/Plin', 'redirect_url' => route('orders.index')]);
                    } else {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Falta el código de operación para Yape/Plin']);
                    }

                default:
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Método de pago no válido']);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el checkout. Inténtelo nuevamente.',
                'error' => $e->getMessage(),
            ], 420);
        }
    }

    public function applyCouponOriginal(Request $request)
    {
        $code = $request->input('code');
        $cartId = $request->input('cart_id');
        $districtId = $request->input('district');  // El distrito seleccionado
        $cart = Cart::find($cartId);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito no existe.',
                'new_total' => number_format($cart->total_cart, 2) // Devolver el total sin descuento
            ]);
        }

        // Obtener el costo de envío dependiendo del distrito
        $shippingCost = 0; // Costo por defecto
        if ($districtId) {
            $district = ShippingDistrict::find($districtId); // Buscar el distrito
            if ($district) {
                $shippingCost = $district->shipping_cost; // Suponiendo que cada distrito tiene un campo 'shipping_cost'
            }
        }

        // Obtener el total con el envío
        $totalWithShipping = $cart->total_cart + $shippingCost;

        // Buscar el cupón
        $coupon = Coupon::where('name', $code)->where('status', 'active')->first();

        if (!$coupon || $code == "") {
            return response()->json([
                'success' => false,
                'message' => 'El código de promoción no es válido o está inactivo.',
                'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                'coupon_name' => '' // Limpiar el nombre del cupón
            ]);
        }

        // Validar si el cupón ya ha sido usado
        $userCoupon = UserCoupon::where('user_id', auth()->id())
            ->where('coupon_id', $coupon->id)
            ->first();

        if ($userCoupon && !$coupon->special) {
            return response()->json([
                'success' => false,
                'message' => 'El cupón ya ha sido utilizado.',
                'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                'coupon_name' => '' // Limpiar el nombre del cupón
            ]);
        }

        // Cálculo del descuento
        $total = $totalWithShipping; // Considerar el total con envío
        $discount = 0;

        if ($coupon->type == 'total') {
            // Validar que no haya productos de categoría 'combo' (category_id = 3)
            $hasCombo = $cart->details->contains(function ($detail) {
                return $detail->product->category_id == 3;
            });

            if ($hasCombo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                    'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                    'coupon_name' => '' // Limpiar el nombre del cupón
                ]);
            }

            // Aplicar el descuento al total
            if ($coupon->amount != 0) {
                $discount = $coupon->amount;
            } elseif ($coupon->percentage != 0) {
                $discount = $total * ($coupon->percentage / 100);
            }
        } elseif ($coupon->type == 'detail') {
            // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
            $onlyCombos = $cart->details->every(function ($detail) {
                return $detail->product->category_id == 3;
            });

            if ($onlyCombos) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                    'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                    'coupon_name' => '' // Limpiar el nombre del cupón
                ]);
            }



            // Filtrar los detalles que no sean de categoría 'combo' (category_id = 3)
            $eligibleDetails = $cart->details->filter(function ($detail) {
                return $detail->product->category_id != 3;
            });

            // Buscar el detalle elegible con el subtotal más alto
            $maxDetail = $eligibleDetails->sortByDesc('price')->first();

            if ($maxDetail) {
                if ($coupon->amount != 0) {
                    $discount = $coupon->amount;
                } elseif ($coupon->percentage != 0) {
                    $discount = ($maxDetail->price * ($coupon->percentage / 100));
                }
            }
        }

        // Si el descuento es mayor que el total, ajustamos el descuento
        if ($discount > $total) {
            $discount = $total;
        }

        $newTotal = $total - $discount;

        return response()->json([
            'success' => true,
            'code_name' => $coupon->name,
            'discount_display' => '-S/ ' . number_format($discount, 2),
            'new_total' => number_format($newTotal, 2),
            'message' => 'Código aplicado. No borre el código.',
            'coupon_id' => $coupon->id,
            'district' => $districtId, // Devolver el distrito
        ]);
    }

    public function calculateShipping1(Request $request)
    {
        //dd($request->all());
        $districtId = $request->district_id;
        $cart = $request->input('cart');
        $couponName = $request->coupon_name;

        // Obtener el costo de envío (0 si no hay distrito)
        $shippingCost = 0;
        if ($districtId) {
            $district = ShippingDistrict::find($districtId);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no válido.'], 400);
            }
            $shippingCost = $district->shipping_cost;
        }

        // Calcular el subtotal del carrito
        //$cart = Cart::with('details')->find($cartId);
        if (!$cart || !is_array($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito no encontrado o inválido.'], 400);
        }

        // Hallar el total del carrito
        //$total = $cart->total_cart;
        $total = $this->getTotalCart($cart);
        //dd($total);
        $discount = 0;

        // Calcular el descuento si hay un cupón
        if ($couponName) {
            $coupon = Coupon::where('name', $couponName)->first();

            // Lógica para calcular el descuento
            if ($coupon->type == 'total') {
                // Validar que no haya productos de categoría 'combo' (category_id = 3)
                /*$hasCombo = $cart->details->contains(function ($detail) {
                    return $detail->product->category_id == 3;
                });*/
                $hasCombo = $this->hasCombo($cart);

                if ($hasCombo) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                    ]);
                }

                // Aplicar descuento al total
                if ($coupon->amount != 0) {
                    $discount = $coupon->amount;
                } elseif ($coupon->percentage != 0) {
                    $discount = ($coupon->percentage / 100) * $total;
                }
            } elseif ($coupon->type == 'detail') {
                // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
                /*$onlyCombos = $cart->details->every(function ($detail) {
                    return $detail->product->category_id == 3;
                });*/

                $onlyCombos = $this->onlyCombos($cart);

                if ($onlyCombos) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                    ]);
                }

                // Filtrar los detalles que no sean de categoría 'combo' (category_id = 3)
                /*$eligibleDetails = $cart->details->filter(function ($detail) {
                    return $detail->product->category_id != 3;
                });*/

                // Buscar el detalle elegible con el subtotal más alto
                //$maxDetail = $eligibleDetails->sortByDesc('subtotal')->first();
                $maxDetail = $this->getMaxDetail($cart);

                if ($maxDetail) {
                    if ($coupon->amount != 0) {
                        $discount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discount = ($coupon->percentage / 100) * $maxDetail['subtotal'];
                    }
                }
            } elseif ( $coupon->type == 'by_pass' ) {
                if ($coupon->amount != 0) {
                    $discount = $coupon->amount;
                } elseif ($coupon->percentage != 0) {
                    $discount = ($coupon->percentage / 100) * $total;
                }
            }
        }

        // Ajustar el descuento si es mayor que el total
        if ($discount > $total) {
            $discount = $total;
        }

        // Calcular el nuevo total
        $newTotal = $total - $discount + $shippingCost;

        return response()->json([
            'success' => true,
            'shipping_cost' => (float)$shippingCost,
            'new_total' => (float)$newTotal,
        ]);
    }

    public function calculateShipping(Request $request)
    {
        $districtId = $request->district_id;
        $cart = $request->input('cart');
        $couponName = $request->coupon_name;

        // Obtener el costo de envío
        $shippingCost = 0;
        if ($districtId) {
            $district = ShippingDistrict::find($districtId);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no válido.'], 400);
            }
            $shippingCost = $district->shipping_cost;
        }

        // Validar el carrito
        if (!$cart || !is_array($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito no encontrado o inválido.'], 400);
        }

        $total = $this->getTotalCart($cart);
        $discount = 0;

        if ($couponName) {
            $coupon = Coupon::where('name', $couponName)->where('status', 'active')->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código de promoción no es válido o está inactivo.',
                ]);
            }

            // Validar si el cupón ya ha sido usado por el usuario
            $userCoupon = UserCoupon::where('user_id', auth()->id())
                ->where('coupon_id', $coupon->id)
                ->first();

            // Validar si el número de teléfono ya utilizó el cupón
            $phoneCoupon = UserCoupon::where('phone', $request->input('phone'))
                ->where('coupon_id', $coupon->id)
                ->first();

            if (!$coupon->special && ($userCoupon || $phoneCoupon)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ya ha sido utilizado.',
                ]);
            }

            if ($coupon->type == 'by_pass') {
                $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? $total * ($coupon->percentage / 100) : 0);
            } else {
                $cartCategories = array_unique(array_map(function ($item) {
                    $product = Product::find($item['product_id']);
                    return $product ? $product->category_id : null;
                }, $cart));

                $allowedCategories = CategoryCoupon::where('coupon_id', $coupon->id)->pluck('category_id')->toArray();

                $hasAllowedCategory = !empty(array_intersect($cartCategories, $allowedCategories));
                if (!$hasAllowedCategory) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón no se puede aplicar a estos productos.',
                    ]);
                }

                $total = array_sum(array_map(function ($item) {
                    $product = Product::find($item['product_id']);
                    return ($product && $product->category->aplica_porcentaje) ? ($item['total'] * $item['quantity']) : 0;
                }, $cart));

                if ($coupon->type == 'total') {
                    $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? $total * ($coupon->percentage / 100) : 0);
                } elseif ($coupon->type == 'detail') {
                    $maxDetail = null;
                    foreach ($cart as $item) {
                        $product = Product::find($item['product_id']);
                        if ($product && in_array($product->category_id, $allowedCategories)) {
                            if (!$maxDetail || $item['total'] > $maxDetail['total']) {
                                $maxDetail = $item;
                            }
                        }
                    }
                    $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0 && $maxDetail) ? $maxDetail['total'] * ($coupon->percentage / 100) : 0);
                }
            }
        }

        if ($discount > $total) {
            $discount = $total;
        }

        $newTotal = ($this->getTotalCart($cart) + $shippingCost) - $discount;
        return response()->json([
            'success' => true,
            'shipping_cost' => (float)$shippingCost,
            'new_total' => (float)$newTotal,
        ]);
    }

    public function applyCoupon1(Request $request)
    {
        //dd($request);
        $cart = json_decode($request->input('cart'), true); // Decodificar cart
        $districtId = $request->input('district');
        $code = $request->input('code');

        if (!$cart || !is_array($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito no encontrado o inválido.'], 400);
        }

        // Obtener el costo de envío dependiendo del distrito
        $shippingCost = 0; // Costo por defecto
        if ($districtId) {
            $district = ShippingDistrict::find($districtId); // Buscar el distrito
            if ($district) {
                $shippingCost = $district->shipping_cost; // Suponiendo que cada distrito tiene un campo 'shipping_cost'
            }
        }

        // Obtener el total con el envío
        $total = $this->getTotalCart($cart);
        $totalWithShipping = $total + $shippingCost;

        // Buscar el cupón
        $coupon = Coupon::where('name', $code)->where('status', 'active')->first();

        if (!$coupon || $code == "") {
            return response()->json([
                'success' => false,
                'message' => 'El código de promoción no es válido o está inactivo.',
                'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                'coupon_name' => '' // Limpiar el nombre del cupón
            ]);
        }

        // Validar si el cupón ya ha sido usado
        $userCoupon = UserCoupon::where('user_id', auth()->id())
            ->where('coupon_id', $coupon->id)
            ->first();

        // Verificar si el número de teléfono ya utilizó el cupón
        $phoneCoupon = UserCoupon::where('phone', $request->input('phone'))
            ->where('coupon_id', $coupon->id)
            ->first();

        if ((!$coupon->special && $userCoupon)) {
            return response()->json([
                'success' => false,
                'message' => 'El cupón ya ha sido utilizado.',
                'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                'coupon_name' => '' // Limpiar el nombre del cupón
            ]);
        }

        if ((!$coupon->special && $phoneCoupon)) {
            return response()->json([
                'success' => false,
                'message' => 'Sus datos ya han sido beneficiados con el cupón.',
                'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                'coupon_name' => '' // Limpiar el nombre del cupón
            ]);
        }

        // Cálculo del descuento
        //$total = $totalWithShipping; // Considerar el total con envío
        $discount = 0;

        if ($coupon->type == 'total') {
            // Validar que no haya productos de categoría 'combo' (category_id = 3)
            $hasCombo = $this->hasCombo($cart);

            if ($hasCombo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                    'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                    'coupon_name' => '' // Limpiar el nombre del cupón
                ]);
            }

            // Aplicar el descuento al total
            if ($coupon->amount != 0) {
                $discount = $coupon->amount;
            } elseif ($coupon->percentage != 0) {
                $discount = $total * ($coupon->percentage / 100);
            }
        } elseif ($coupon->type == 'detail') {
            // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
            $onlyCombos = $this->onlyCombos($cart);

            if ($onlyCombos) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                    'new_total' => number_format($totalWithShipping, 2), // Devolver el total con envío sin descuento
                    'coupon_name' => '' // Limpiar el nombre del cupón
                ]);
            }

            // Buscar el detalle elegible con el subtotal más alto
            $maxDetail = $this->getMaxDetail($cart);
            //dd($maxDetail);

            if ($maxDetail) {
                if ($coupon->amount != 0) {
                    $discount = $coupon->amount;
                } elseif ($coupon->percentage != 0) {
                    $discount = ($coupon->percentage / 100) * $maxDetail['subtotal'];//$maxDetail['subtotal'] es el precio no el subtotal
                }
            }
        } elseif ( $coupon->type == 'by_pass' ) {
            if ($coupon->amount != 0) {
                $discount = $coupon->amount;
            } elseif ($coupon->percentage != 0) {
                $discount = $total * ($coupon->percentage / 100);
            }
        }

        // Si el descuento es mayor que el total, ajustamos el descuento
        if ($discount > $total) {
            $discount = $total;
        }

        //$newTotal = $total - $discount;
        $newTotal = $totalWithShipping - $discount;
        return response()->json([
            'success' => true,
            'code_name' => $coupon->name,
            'discount_display' => '-S/ ' . number_format($discount, 2),
            'new_total' => number_format($newTotal, 2),
            'message' => 'Código aplicado. No borre el código.',
            'coupon_id' => $coupon->id,
            'district' => $districtId, // Devolver el distrito
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $cart = json_decode($request->input('cart'), true);

        $tienda = json_decode($request->input('tienda'), true);

        $districtId = $request->input('district');
        $code = $request->input('code');
        $phone = $request->input('phone');

        if (!$cart || !is_array($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito no encontrado o inválido.'], 400);
        }

        $shippingCost = 0;
        if ($tienda) {
            $shippingCost = isset($tienda['precioEnvio']) ? $tienda['precioEnvio'] : 0;
        }
        /*if ($districtId) {
            $district = ShippingDistrict::find($districtId);
            if ($district) {
                $shippingCost = $district->shipping_cost;
            }
        }*/

        $total = $this->getTotalCart($cart);
        $totalWithShipping = $total + $shippingCost;

        $coupon = Coupon::where('name', $code)->where('status', 'active')->first();
        if (!$coupon || $code == "") {

            $totals = $this->getDataTotals($cart, $shippingCost, 0, 18);
            return response()->json([
                'success' => false,
                'message' => 'El código de promoción no es válido o está inactivo.',
                'total_gravada' => number_format($totals['total_gravada'], 2, '.', ''),
                'total_igv' => number_format($totals['total_igv'], 2, '.', ''),
                'total_a_pagar' => number_format($totals['total_a_pagar'], 2, '.', ''),
                'coupon_name' => ''
            ]);
        }

        $userCoupon = UserCoupon::where('user_id', auth()->id())
            ->where('coupon_id', $coupon->id)
            ->first();

        $phoneCoupon = UserCoupon::where('phone', $phone)
            ->where('coupon_id', $coupon->id)
            ->first();

        if ((!$coupon->special && $userCoupon)) {
            $totals = $this->getDataTotals($cart, $shippingCost, 0, 18);
            return response()->json([
                'success' => false,
                'message' => 'El cupón ya ha sido utilizado.',
                'total_gravada' => number_format($totals['total_gravada'], 2, '.', ''),
                'total_igv' => number_format($totals['total_igv'], 2, '.', ''),
                'total_a_pagar' => number_format($totals['total_a_pagar'], 2, '.', ''),
                'coupon_name' => ''
            ]);
        }

        if ((!$coupon->special && $phoneCoupon)) {
            $totals = $this->getDataTotals($cart, $shippingCost, 0, 18);
            return response()->json([
                'success' => false,
                'message' => 'Sus datos ya han sido beneficiados con el cupón.',
                'total_gravada' => number_format($totals['total_gravada'], 2, '.', ''),
                'total_igv' => number_format($totals['total_igv'], 2, '.', ''),
                'total_a_pagar' => number_format($totals['total_a_pagar'], 2, '.', ''),
                'coupon_name' => ''
            ]);
        }

        $discount = 0;

        if ($coupon->type == 'by_pass') {
            $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? $total * ($coupon->percentage / 100) : 0);
        } else {
            $cartCategories = array_unique(array_map(function ($item) {
                $product = Product::find($item['product_id']);
                return $product ? $product->category_id : null;
            }, $cart));

            $allowedCategories = CategoryCoupon::where('coupon_id', $coupon->id)->pluck('category_id')->toArray();
            $hasAllowedCategory = !empty(array_intersect($cartCategories, $allowedCategories));

            if (!$hasAllowedCategory) {
                $totals = $this->getDataTotals($cart, $shippingCost, 0, 18);

                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no se puede aplicar a estos productos.',
                    'total_gravada' => number_format($totals['total_gravada'], 2, '.', ''),
                    'total_igv' => number_format($totals['total_igv'], 2, '.', ''),
                    'total_a_pagar' => number_format($totals['total_a_pagar'], 2, '.', ''),
                    'coupon_name' => ''
                ]);
            }

            $total = array_sum(array_map(function ($item) {
                $product = Product::find($item['product_id']);
                return $product && $product->category->aplica_porcentaje ? $item['total'] * $item['quantity'] : 0;
            }, $cart));

            if ($coupon->type == 'total') {
                $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? ($total/1.18) * ($coupon->percentage / 100) : 0);
            } elseif ($coupon->type == 'detail') {
                $maxDetail = collect($cart)->filter(function ($item) use ($allowedCategories) {
                    $product = Product::find($item['product_id']);
                    return in_array($product ? $product->category_id : null, $allowedCategories);
                })->sortByDesc('total')->first();

                $discount = ($coupon->amount != 0) ? $coupon->amount : (($coupon->percentage != 0) ? (($maxDetail['total']/1.18) ?? 0) * ($coupon->percentage / 100) : 0);
            }
        }

        if ($discount > $total) {
            $discount = $total;
        }

        $tax = 18;
        $totals = $this->getDataTotals($cart, $shippingCost, $discount, $tax);

        $newTotal = $totalWithShipping - $discount;

        return response()->json([
            'success' => true,
            'code_name' => $coupon->name,
            'discount_display' => '-S/ ' . number_format($discount, 2),
            'new_total' => number_format($newTotal, 2),
            'message' => 'Código aplicado. No borre el código.',
            'coupon_id' => $coupon->id,
            'district' => $districtId,
            'total_gravada' => number_format($totals['total_gravada'], 2, '.', ''),
            'total_igv' => number_format($totals['total_igv'], 2, '.', ''),
            'total_a_pagar' => number_format($totals['total_a_pagar'], 2, '.', ''),
        ]);
    }

    function getDataTotals ($cart, $shipping, $discount, $tax)
    {
        $items = [];
        $total_gravada = 0;
        $total_igv = 0;
        $taxDecimal = $tax / 100; // 0.18

        foreach ($cart as $item) {
            $valor_unitario = round($item['total'] / (1 + $taxDecimal), 6);
            $subtotal = round($valor_unitario * $item['quantity'], 6);
            $igv = round(($item['total'] * $item['quantity']) - $subtotal, 2);
            $total = round($item['total'] * $item['quantity'], 6);

            $items[] = [
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ];

            $total_gravada += $subtotal;
            $total_igv += $igv;
        }

        // Agregar costo de envío si existe
        if ($shipping > 0) {
            $valor_unitario_envio = round($shipping / (1 + $taxDecimal), 6);
            $igv_envio = round($shipping - $valor_unitario_envio, 6);

            $items[] = [
                'subtotal' => $valor_unitario_envio,
                'igv' => $igv_envio,
                'total' => $shipping
            ];

            $total_gravada += $valor_unitario_envio;
            $total_igv += $igv_envio;
        }

        // Aplicar descuento sobre la base imponible
        $total_gravada_final = round($total_gravada - $discount, 2);
        $total_igv_final = round($total_gravada_final * $taxDecimal, 2);
        $total_a_pagar_final = round($total_gravada_final * (1 + $taxDecimal), 2);

        return [
            'total_gravada' => $total_gravada_final,
            'total_igv' => $total_igv_final,
            'total_a_pagar' => $total_a_pagar_final,
            // 'items' => $items // Puedes retornarlos si los necesitas también
        ];
    }

    function getTotalCart($cart)
    {
        $total = 0;

        foreach ($cart as $item) {
            if (isset($item['custom']) && $item['custom']) {
                // Si el producto es custom, usar directamente el total del item
                $total += ($item['total']*$item['quantity']);
                continue;
            }

            // Productos normales
            $basePrice = 0;

            // Obtener el precio base del producto o tipo de producto
            if (isset($item['product_type_id']) && $item['product_type_id'] != null) {
                $productType = ProductType::find($item['product_type_id']);
                if ($productType) {
                    $basePrice = $productType->price;
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $basePrice = $product->price_default;
                }
            }

            // Calcular el total de las opciones
            $optionsTotal = 0;
            if (isset($item['options'])) {
                foreach ($item['options'] as $optionsGroup) {
                    foreach ($optionsGroup as $option) {
                        $optionsTotal += $option['additional_price'];
                    }
                }
            }

            // Sumar precio base + opciones, multiplicado por la cantidad
            $total += ($basePrice + $optionsTotal) * $item['quantity'];
        }

        return $total;
    }

    function hasCombo($cart)
    {
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->category_id == 3) {
                return true;
            }
        }
        return false;
    }

    function onlyCombos($cart)
    {
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if ($product && $product->category_id != 3) {
                return false; // Si hay al menos un producto que no es combo, retornamos false
            }
        }
        return true; // Todos los productos son combos
    }

    function getMaxDetail($cart)
    {
        $maxDetail = null;
        $maxSubtotal = 0;

        foreach ($cart as $item) {
            $subtotal = 0;

            if (isset($item['custom']) && $item['custom'] === true) {
                // Si el producto es personalizado, tomar directamente el total
                $subtotal = $item['total'];
            } else {
                $basePrice = 0;

                // Obtener el precio base del producto o tipo de producto
                if (isset($item['product_type_id']) && $item['product_type_id'] != null) {
                    $productType = ProductType::find($item['product_type_id']);
                    if ($productType) {
                        $basePrice = $productType->price;
                    }
                } else {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $basePrice = $product->price_default;
                    }
                }

                // Calcular el total de las opciones
                $optionsTotal = 0;
                if (isset($item['options'])) {
                    foreach ($item['options'] as $optionsGroup) {
                        foreach ($optionsGroup as $option) {
                            $optionsTotal += $option['additional_price'];
                        }
                    }
                }

                // Calcular el subtotal unitario incluyendo opciones
                $subtotal = $basePrice + $optionsTotal;
            }

            // Verificar si este es el mayor subtotal elegible
            $product = Product::find($item['product_id']);
            if ($product && $product->category_id != 3) { // Solo considerar productos que no sean combos
                if ($subtotal > $maxSubtotal) {
                    $maxSubtotal = $subtotal;
                    $maxDetail = [
                        'product_id' => $item['product_id'],
                        'subtotal' => $subtotal,
                        'quantity' => $item['quantity'], // Incluimos cantidad para mayor contexto si es necesario
                    ];
                }
            }
        }

        return $maxDetail;  // Devuelve el producto con el mayor subtotal (sin combos)
    }

    public function calculateShippingOriginal(Request $request)
    {
        $districtId = $request->district_id;
        $cartId = $request->cart_id;
        $couponName = $request->coupon_name;

        // Obtener el costo de envío (0 si no hay distrito)
        $shippingCost = 0;
        if ($districtId) {
            $district = ShippingDistrict::find($districtId);
            if (!$district) {
                return response()->json(['success' => false, 'message' => 'Distrito no válido.'], 400);
            }
            $shippingCost = $district->shipping_cost;
        }

        // Calcular el subtotal del carrito
        $cart = Cart::with('details')->find($cartId);
        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Carrito no encontrado.'], 400);
        }

        $total = $cart->total_cart;
        $discount = 0;

        // Calcular el descuento si hay un cupón
        if ($couponName) {
            $coupon = Coupon::where('name', $couponName)->first();

            /*if ($coupon) {
                if ($coupon->type == 'total') {
                    if ($coupon->amount != 0) {
                        $discount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discount = $total * ($coupon->percentage / 100);
                    }
                } elseif ($coupon->type == 'detail') {
                    $maxDetail = $cart->details->sortByDesc('subtotal')->first();

                    if ($maxDetail) {
                        if ($coupon->amount != 0) {
                            $discount = $coupon->amount;
                        } elseif ($coupon->percentage != 0) {
                            $discount = $maxDetail->subtotal * ($coupon->percentage / 100);
                        }
                    }
                }
            }*/
            // Lógica para calcular el descuento
            if ($coupon->type == 'total') {
                // Validar que no haya productos de categoría 'combo' (category_id = 3)
                $hasCombo = $cart->details->contains(function ($detail) {
                    return $detail->product->category_id == 3;
                });

                if ($hasCombo) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón no se puede aplicar a carritos que contengan combos.',
                    ]);
                }

                // Aplicar descuento al total
                if ($coupon->amount != 0) {
                    $discount = $coupon->amount;
                } elseif ($coupon->percentage != 0) {
                    $discount = ($coupon->percentage / 100) * $cart->total_cart;
                }
            } elseif ($coupon->type == 'detail') {
                // Verificar si todos los productos son de categoría 'combo' (category_id = 3)
                $onlyCombos = $cart->details->every(function ($detail) {
                    return $detail->product->category_id == 3;
                });

                if ($onlyCombos) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El cupón no se puede aplicar porque solo hay combos en el pedido.',
                    ]);
                }

                // Filtrar los detalles que no sean de categoría 'combo' (category_id = 3)
                $eligibleDetails = $cart->details->filter(function ($detail) {
                    return $detail->product->category_id != 3;
                });

                // Buscar el detalle elegible con el subtotal más alto
                $maxDetail = $eligibleDetails->sortByDesc('price')->first();

                if ($maxDetail) {
                    if ($coupon->amount != 0) {
                        $discount = $coupon->amount;
                    } elseif ($coupon->percentage != 0) {
                        $discount = (($coupon->percentage / 100) * $maxDetail->price);
                    }
                }
            }
        }

        // Ajustar el descuento si es mayor que el total
        if ($discount > $total) {
            $discount = $total;
        }

        // Calcular el nuevo total
        $newTotal = $total - $discount + $shippingCost;

        return response()->json([
            'success' => true,
            'shipping_cost' => (float)$shippingCost,
            'new_total' => (float)$newTotal,
        ]);
    }

    public function crearPreferencia()
    {
        // Crea un objeto de preferencia
        $preference = new Preference();

        // Crea un ítem en la preferencia
        $item = new Item();
        $item->title = 'Mi producto';
        $item->quantity = 1;
        $item->unit_price = 100.00;
        $preference->items = array($item);

        // URL de retorno
        $preference->back_urls = array(
            "success" => "https://www.fuegoymasa.com/pago-exitoso",
            "failure" => "https://www.fuegoymasa.com/pago-fallido",
            "pending" => "https://www.fuegoymasa.com/pago-pendiente"
        );
        $preference->auto_return = "approved";

        $preference->save();

        return response()->json(['id' => $preference->id]);
    }

    public function pagoExitoso(Request $request)
    {
        // Lógica para pagos exitosos
        return view('pagos.exitoso', ['data' => $request->all()]);
    }

    public function pagoFallido(Request $request)
    {
        // Lógica para pagos fallidos
        return view('pagos.fallido', ['data' => $request->all()]);
    }

    public function pagoPendiente(Request $request)
    {
        // Lógica para pagos pendientes
        return view('pagos.pendiente', ['data' => $request->all()]);
    }

    public function deleteDetail($id)
    {
        DB::beginTransaction(); // Iniciar la transacción

        try {
            // Buscar el detalle del carrito
            $detail = CartDetail::findOrFail($id);

            // Obtener el carrito asociado
            $cart = $detail->cart;

            // Eliminar los options
            $options = CartDetailOption::where('cart_detail_id', $detail->id)->get();

            foreach ( $options as $option )
            {
                $option->delete();
            }

            // Eliminar el detalle
            $detail->delete();

            // Verificar si quedan detalles en el carrito
            if ($cart->details()->count() === 0) {
                // Si no quedan detalles, eliminar el carrito
                $cart->delete();

                DB::commit(); // Confirmar la transacción

                return response()->json([
                    'status' => 'cart_deleted', // Indica que el carrito fue eliminado
                    'message' => 'El carrito está vacío y fue eliminado.',
                ]);
            }

            // Calcular los nuevos totales del carrito
            /*$subtotal = $cart->details->sum(function ($d) {
                return $d->subtotal;
            });

            $taxes = $subtotal * 0.18; // Ejemplo: suponiendo un 18% de impuestos
            $total = $subtotal + $taxes;
            $count = $cart->details->count();*/

            $subtotalCart = $cart->subtotal_cart;
            $taxesCart = $cart->taxes_cart;
            $totalCart = $cart->total_cart;

            DB::commit(); // Confirmar la transacción

            // Retornar una respuesta indicando que el detalle fue eliminado
            return response()->json([
                'status' => 'detail_deleted',
                'message' => 'El detalle fue eliminado.',
                'cart' => [
                    'subtotal' => number_format($subtotalCart, 2),
                    'taxes' => number_format($taxesCart, 2),
                    'total' => number_format($totalCart, 2),
                    'count' => $cart->details->count(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error

            // Registrar el error (opcional, para monitoreo)
            //\Log::error('Error al eliminar el detalle del carrito: ' . $e->getMessage());

            // Retornar una respuesta de error
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al eliminar el detalle del carrito. Intenta de nuevo más tarde.',
            ], 500);
        }
    }

    public function saveObservation( Request $request, $cart_id )
    {
        DB::beginTransaction();
        try {
            $cart = Cart::find($cart_id);
            $cart->observations = $request->get('observation'); // Cambiar estado a inactivo
            $cart->save();

            DB::commit();
        } catch ( \Throwable $e ) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Observación guardad con éxito.'], 200);
    }

    public function saveCustomProduct(Request $request)
    {
        $dataGeneral = DataGeneral::where('name', 'product_id_custom')->first();
        // DataGeneral
        $data_base_price_familiar = DataGeneral::where('name', 'base_price_familiar')->first();
        $data_base_price_large = DataGeneral::where('name', 'base_price_large')->first();
        $data_base_price_personal = DataGeneral::where('name', 'base_price_personal')->first();
        $data_price_adicional = DataGeneral::where('name', 'price_adicional')->first();

        $base_price_familiar = $data_base_price_familiar->valueNumber;
        $base_price_large = $data_base_price_large->valueNumber;
        $base_price_personal = $data_base_price_personal->valueNumber;
        $price_adicional = $data_price_adicional->valueNumber;

        //dd($request);
        $idProductCustom = $dataGeneral->valueNumber;

        $typePizzas = [
            'familiar' => 1,
            'large' => 2,
            'personal' => 3,
        ];

        // Obtener todos los datos enviados en la solicitud
        $data = $request->all();

        // Acceder a propiedades específicas
        $size = isset($data['size']) ? $data['size'] : null;
        $salsa = isset($data['salsa']) ? $data['salsa'] : null;
        $queso = isset($data['queso']) ? $data['queso'] : null;
        $meats = isset($data['meats']) ? $data['meats'] : [];
        $veggies = isset($data['veggies']) ? $data['veggies'] : [];

        // Uso de $size
        $type = $typePizzas[$size];

        $product = Product::find($idProductCustom);
        $productType = ProductType::with('type')->where('product_id', $product->id)
            ->where('type_id', $type)->first();

        $priceExtraSalsa = 0;
        $priceExtraQueso = 0;
        // Procesar la salsa
        if ($salsa) {
            $toppingSalsa = Topping::where('slug', 'Salsa')->first();
            if ($toppingSalsa) {
                $isSelectedSalsa = ($salsa['seleccion'] === 'Sí') ? 1 : 0;
                $typeSalsa = null;
                $extraSalsa = 0;

                if (isset($salsa['elecciones'])) {
                    $tipo = isset($salsa['elecciones']['tipo']) ? $salsa['elecciones']['tipo'] : '';
                    $extraSalsa = ($salsa['elecciones']['extra'] === 'Sí') ? 1 : 0;
                    $priceExtraSalsa = ($salsa['elecciones']['extra'] === 'Sí') ? $toppingSalsa->price_extra : 0;
                    if (preg_match('/(left|whole|right)/', $tipo, $matches)) {
                        $typeSalsa = $matches[1];
                    }
                }
            }
        }

        // Procesar el queso
        if ($queso) {
            $toppingQueso = Topping::where('slug', 'Queso')->first();
            if ($toppingQueso) {
                $isSelectedQueso = ($queso['seleccion'] === 'Sí') ? 1 : 0;
                $typeQueso = null;
                $extraQueso = 0;

                if (isset($queso['elecciones'])) {
                    $tipo = isset($queso['elecciones']['tipo']) ? $queso['elecciones']['tipo'] : '';
                    $extraQueso = ($queso['elecciones']['extra'] === 'Sí') ? 1 : 0;
                    $priceExtraQueso = ($salsa['elecciones']['extra'] === 'Sí') ? $toppingQueso->price_extra : 0;
                    if (preg_match('/(left|whole|right)/', $tipo, $matches)) {
                        $typeQueso = $matches[1];
                    }
                }
            }
        }

        // Procesar meats
        $processedMeats = [];
        foreach ($meats as $meat) {
            foreach ($meat as $slug => $details) {
                $toppingMeat = Topping::where('slug', $slug)->first();
                if ($toppingMeat) {
                    $isSelected = ($details['seleccion'] === 'Sí') ? 1 : 0;
                    $type = null;
                    $extra = 0;

                    if (isset($details['elecciones'])) {
                        $tipo = isset($details['elecciones']['tipo']) ? $details['elecciones']['tipo'] : '';
                        $extra = ($details['elecciones']['extra'] === 'Sí') ? 1 : 0;

                        if (preg_match('/(left|whole|right)/', $tipo, $matches)) {
                            $type = $matches[1];
                        }
                    }

                    $processedMeats[] = [
                        'topping_id' => $toppingMeat->id,
                        'topping_name' => $toppingMeat->name,
                        'topping_price_exception' => $toppingMeat->price_exception,
                        'topping_price_extra' => $toppingMeat->price_extra,
                        'slug' => $slug,
                        'isSelected' => $isSelected,
                        'type' => $type,
                        'extra' => $extra,
                    ];
                }
            }
        }

        // Procesar veggies
        $processedVeggies = [];
        foreach ($veggies as $veggie) {
            foreach ($veggie as $slug => $details) {
                $toppingVeggie = Topping::where('slug', $slug)->first();
                if ($toppingVeggie) {
                    $isSelected = ($details['seleccion'] === 'Sí') ? 1 : 0;
                    $type = null;
                    $extra = 0;

                    if (isset($details['elecciones'])) {
                        $tipo = isset($details['elecciones']['tipo']) ? $details['elecciones']['tipo'] : '';
                        $extra = ($details['elecciones']['extra'] === 'Sí') ? 1 : 0;

                        if (preg_match('/(left|whole|right)/', $tipo, $matches)) {
                            $type = $matches[1];
                        }
                    }

                    $processedVeggies[] = [
                        'topping_id' => $toppingVeggie->id,
                        'topping_name' => $toppingVeggie->name,
                        'topping_price_exception' => $toppingVeggie->price_exception,
                        'topping_price_extra' => $toppingVeggie->price_extra,
                        'slug' => $slug,
                        'isSelected' => $isSelected,
                        'type' => $type,
                        'extra' => $extra,
                    ];
                }
            }
        }

        $basePrice = 0;
        $total = $productType->price; // Precio base

        // Caso 1: Sin toppings seleccionados
        $extraSalsa = isset($extraSalsa) ? $priceExtraSalsa : 0;
        $extraQueso = isset($extraQueso) ? $priceExtraQueso : 0;
        // Asegurando que $isSelectedSalsa y $isSelectedQueso siempre tengan valores predeterminados
        $isSelectedSalsa = isset($isSelectedSalsa) ? $isSelectedSalsa : 0;
        $isSelectedQueso = isset($isSelectedQueso) ? $isSelectedQueso : 0;

        // Si no se seleccionaron toppings
        if (empty($processedMeats) && empty($processedVeggies) && !$isSelectedSalsa && !$isSelectedQueso) {
            // No se hace nada porque $total ya tiene el precio base
        } else {
            // Variables para manejar carnes y vegetales seleccionados
            $meatCount = count($processedMeats);
            $veggieCount = count($processedVeggies);

            // Establecer el precio base según el tamaño
            if ($size === 'familiar') {
                $basePrice = $base_price_familiar;
            } elseif ($size === 'large') {
                $basePrice = $base_price_large;
            } elseif ($size === 'personal') {
                $basePrice = $base_price_personal;
            }

            // Caso 2: Una carne y un vegetal, o dos vegetales
            if (($meatCount == 1 && $veggieCount == 1) || ($veggieCount == 2 && $meatCount == 0)) {
                //dump("Entre caso 2");
                $total = $basePrice; // Sumar el precio base al total

                // Sumar excepciones de precio de carnes y vegetales
                foreach ($processedMeats as $meat) {
                    $total += $meat['topping_price_exception'];
                    $total += $meat['extra'] ? $meat['topping_price_extra'] : 0;
                }

                foreach ($processedVeggies as $veggie) {
                    $total += $veggie['topping_price_exception'];
                    $total += $veggie['extra'] ? $veggie['topping_price_extra'] : 0;
                }
            } else {
                // Caso 3: Todas las demás combinaciones
                $total = $basePrice; // Sumar el precio base

                // Procesar carnes
                foreach ($processedMeats as $index => $meat) {
                    $total += $meat['topping_price_exception'];
                    $total += $meat['extra'] ? $meat['topping_price_extra'] : 0;

                    // +3 soles por cada carne extra a partir de la segunda
                    if ($index >= 1) {
                        if ( $meat['type'] == 'whole' )
                        {
                            $total += $price_adicional;
                        } else {
                            $total += ($price_adicional/2);
                        }
                    }
                }

                // Procesar vegetales
                if ($meatCount == 0) {
                    // Caso especial: Solo vegetales
                    foreach ($processedVeggies as $index => $veggie) {
                        $total += $veggie['topping_price_exception'];
                        $total += $veggie['extra'] ? $veggie['topping_price_extra'] : 0;

                        // +3 soles por cada vegetal extra a partir del tercero
                        if ($index >= 2) {
                            if ( $veggie['type'] == 'whole' )
                            {
                                $total += $price_adicional;
                            } else {
                                $total += ($price_adicional/2);
                            }
                        }
                    }
                } else {
                    // Caso general: Carnes y vegetales
                    foreach ($processedVeggies as $index => $veggie) {
                        $total += $veggie['topping_price_exception'];
                        $total += $veggie['extra'] ? $veggie['topping_price_extra'] : 0;

                        // +3 soles por cada vegetal extra a partir del segundo
                        if ($index >= 1) {
                            if ( $veggie['type'] == 'whole' )
                            {
                                $total += $price_adicional;
                            } else {
                                $total += ($price_adicional/2);
                            }

                        }
                    }
                }
            }

            // Caso 3: Más de una carne o vegetal
            /*if (($meatCount > 1 || $veggieCount > 1) && !(($meatCount == 1 && $veggieCount == 1) || $veggieCount == 2)) {
                dump("Entre caso 3");
                $total = $basePrice; // Sumar el precio base al total

                // Sumar carnes y considerar extras
                foreach ($processedMeats as $index => $meat) {
                    $total += $meat['topping_price_exception'];
                    $total += $meat['extra'] ? $meat['topping_price_extra'] : 0;

                    // +3 soles por cada carne extra a partir de la segunda
                    if ($index >= 1) { // Index 1 es la segunda carne
                        $total += 3;
                    }
                }

                // Sumar vegetales y considerar extras
                foreach ($processedVeggies as $index => $veggie) {
                    $total += $veggie['topping_price_exception'];
                    $total += $veggie['extra'] ? $veggie['topping_price_extra'] : 0;

                    // +3 soles por cada vegetal extra a partir del segundo
                    if ($index >= 1) { // Index 1 es el segundo vegetal
                        $total += 3;
                    }
                }
            }*/

            // Agregar costos de extras en salsa y queso
            $total += ($extraSalsa ? $priceExtraSalsa : 0) + ($extraQueso ? $priceExtraQueso : 0);
        }

        /*dump([
            'url_redirect' => route('cart.show'),
            'custom' => true,
            'options' => (object)[], // Objeto vacío como indicastes
            'product_id' => $product->id,
            'product_type_id' => $productType->id,
            'product_type_name' => $productType->type->name."(".$productType->type->size.")",
            'quantity' => 1,
            'total' => $total,
            'toppings' => [
                'salsa' => [
                    'isSelected' => isset($isSelectedSalsa) ? $isSelectedSalsa : 0,
                    'type' => isset($typeSalsa) ? $typeSalsa : null,
                    'topping_id' => isset($toppingSalsa) ? $toppingSalsa->id : null,
                    'topping_name' => isset($toppingSalsa) ? $toppingSalsa->name : null,
                    'topping_price_exception' => isset($toppingSalsa) ? $toppingSalsa->price_exception : null,
                    'topping_price_extra' => isset($toppingSalsa) ? $toppingSalsa->price_extra : null,
                    'extra' => isset($extraSalsa) ? $extraSalsa : 0,
                ],
                'queso' => [
                    'isSelected' => isset($isSelectedQueso) ? $isSelectedQueso : 0,
                    'type' => isset($typeQueso) ? $typeQueso : null,
                    'topping_id' => isset($toppingQueso) ? $toppingQueso->id : null,
                    'topping_name' => isset($toppingQueso) ? $toppingQueso->name : null,
                    'topping_price_exception' => isset($toppingQueso) ? $toppingQueso->price_exception : null,
                    'topping_price_extra' => isset($toppingQueso) ? $toppingQueso->price_extra : null,
                    'extra' => isset($extraQueso) ? $extraQueso : 0,
                ],
                'meats' => $processedMeats,
                'veggies' => $processedVeggies,
            ],
            'user_id' => (Auth::id() !== null) ? Auth::id() : null, // Devuelve el ID del usuario autenticado, o null si no está autenticado
        ]);
        dd();*/

        // Formato de respuesta
        return response()->json([
            'url_redirect' => route('cart.show'),
            'custom' => true,
            'options' => (object)[], // Objeto vacío como indicastes
            'product_id' => $product->id,
            'product_type_id' => $productType->id,
            'product_type_name' => $productType->type->name."(".$productType->type->size.")",
            'quantity' => 1,
            'total' => $total,
            'toppings' => [
                'salsa' => [
                    'isSelected' => isset($isSelectedSalsa) ? $isSelectedSalsa : 0,
                    'type' => isset($typeSalsa) ? $typeSalsa : null,
                    'topping_id' => isset($toppingSalsa) ? $toppingSalsa->id : null,
                    'topping_name' => isset($toppingSalsa) ? $toppingSalsa->name : null,
                    'topping_price_exception' => isset($toppingSalsa) ? $toppingSalsa->price_exception : null,
                    'topping_price_extra' => isset($toppingSalsa) ? $toppingSalsa->price_extra : null,
                    'extra' => isset($extraSalsa) ? $extraSalsa : 0,
                ],
                'queso' => [
                    'isSelected' => isset($isSelectedQueso) ? $isSelectedQueso : 0,
                    'type' => isset($typeQueso) ? $typeQueso : null,
                    'topping_id' => isset($toppingQueso) ? $toppingQueso->id : null,
                    'topping_name' => isset($toppingQueso) ? $toppingQueso->name : null,
                    'topping_price_exception' => isset($toppingQueso) ? $toppingQueso->price_exception : null,
                    'topping_price_extra' => isset($toppingQueso) ? $toppingQueso->price_extra : null,
                    'extra' => isset($extraQueso) ? $extraQueso : 0,
                ],
                'meats' => $processedMeats,
                'veggies' => $processedVeggies,
            ],
            'user_id' => (Auth::id() !== null) ? Auth::id() : null, // Devuelve el ID del usuario autenticado, o null si no está autenticado
        ]);

    }
}
