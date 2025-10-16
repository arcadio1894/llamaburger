<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Events\OrderStatusAnulled;
use App\Events\OrderStatusUpdated;
use App\Mail\OrderStatusEmail;
use App\Mail\OrderStatusEmailAnulled;
use App\Models\Address;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\DataGeneral;
use App\Models\Order;
use App\Models\ProductType;
use App\Models\Reward;
use App\Models\ShippingDistrict;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        // Obtiene los pedidos con paginación (10 pedidos por página, puedes ajustarlo)
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // Retorna la vista con los pedidos paginados
        return view('order.index', compact('orders'));
    }

    public function indexKanban()
    {
        return view('kanban.index');
    }

    public function getOrders()
    {

    }

    public function indexAdmin()
    {
        $registros = Order::all();

        $arrayYears = $registros->pluck('created_at')->map(function ($date) {
            return Carbon::parse($date)->format('Y');
        })->unique()->toArray();

        $arrayYears = array_values($arrayYears);

        return view('order.list', compact('arrayYears'));

    }

    public function indexAdminAnnulled()
    {
        $registros = Order::all();

        $arrayYears = $registros->pluck('created_at')->map(function ($date) {
            return Carbon::parse($date)->format('Y');
        })->unique()->toArray();

        $arrayYears = array_values($arrayYears);

        return view('order.listAnnulled', compact('arrayYears'));

    }

    public function getOrdersAdmin(Request $request, $pageNumber = 1)
    {
        $perPage = 10;
        $code = $request->input('code');
        $year = $request->input('year');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if ( $startDate == "" || $endDate == "" )
        {
            $query = Order::where('state_annulled', 0)->orderBy('created_at', 'DESC');
        } else {
            $fechaInicio = Carbon::createFromFormat('d/m/Y', $startDate);
            $fechaFinal = Carbon::createFromFormat('d/m/Y', $endDate);

            $query = Order::whereDate('created_at', '>=', $fechaInicio)
                ->whereDate('created_at', '<=', $fechaFinal)
                ->where('state_annulled', 0)
                ->orderBy('created_at', 'DESC');
        }

        // Aplicar filtros si se proporcionan
        if ($code != "") {
            $query->where('id', 'LIKE', '%'.$code.'%');

        }

        if ($year != "") {
            $query->whereYear('created_at', $year);

        }

        $totalFilteredRecords = $query->count();
        $totalPages = ceil($totalFilteredRecords / $perPage);

        $startRecord = ($pageNumber - 1) * $perPage + 1;
        $endRecord = min($totalFilteredRecords, $pageNumber * $perPage);

        $orders = $query->skip(($pageNumber - 1) * $perPage)
            ->take($perPage)
            ->get();

        $arrayGuides = [];

        foreach ( $orders as $order )
        {
            $direccion = Address::find($order->shipping_address_id);
            $distrito = ShippingDistrict::find($order->shipping_district_id);
            $cliente = $direccion->first_name." ".$direccion->last_name;
            $address = $direccion->address_line. " - ".( (!isset($distrito)) ? 'N/A':$distrito->name);

            $telefono = $direccion->phone;

            // Elimina espacios en blanco alrededor del número, por si acaso
            $telefono = trim($telefono);

            // Verifica si ya empieza con +, si no lo tiene, lo agregamos
            if (strpos($telefono, '+') !== 0) {
                $telefono = '+51' . $telefono;
            }

            // Preparamos el texto del mensaje
            $text = 'Hola ' . $cliente . ' te escribimos de parte de Fuego y Masa para confirmar tu pedido para la direccion ' . $address;

            // Codificamos el texto para que funcione bien en la URL de WhatsApp
            //$textoCodificado = urlencode($text);

            // Generamos la URL para enviar el mensaje
            $url = "https://api.whatsapp.com/send?phone=" . str_replace('+', '', $telefono) . "&text=" . $text;

            //Hola {{Nombre del Cliente}} te escribimos de parte de Fuego y Masa para confirmar tu pedido para la direccion {{Direccion}}
            $text = 'Hola '. $cliente .' te escribimos de parte de Fuego y Masa para confirmar tu pedido para la direccion '.$address;
            //$url = 'https://api.whatsapp.com/send?phone='.$direccion->phone.'&text='.$text;

            $tipo_comprobante = null;
            if ($order->type_document == '01')
            {
                $tipo_comprobante = 'factura';
            } elseif ( $order->type_document == '03' ) {
                $tipo_comprobante = 'boleta';
            }

            $tipo_documento_cliente = null;
            if ($order->tipo_documento_cliente == 1)
            {
                $tipo_documento_cliente = 'dni';
            } elseif ( $order->tipo_documento_cliente == 6 ) {
                $tipo_documento_cliente = 'ruc';
            }

            $totals = $order->data_totals;
            //dd($totals);

            array_push($arrayGuides, [
                "id" => $order->id,
                "code" => "ORDEN - ".$order->id,
                "date" => ($order->created_at != null) ? $order->formatted_created_date : "",
                "date_delivery" => ($order->created_at != null) ? $order->formatted_date : "",
                "customer" => $direccion->first_name." ".$direccion->last_name,
                "phone" => $telefono,
                "address" => $direccion->address_line. " - ".( (!isset($distrito)) ? 'N/A':$distrito->name),
                "referencia" => (!isset($direccion->reference)) ? 'Sin Referencia':$direccion->reference,
                "latitude" => $direccion->latitude,
                "longitude" => $direccion->longitude,
                //"total" => $order->amount_pay
                "total" => number_format($totals['total_a_pagar'], 2, '.', ''),
                "method" => ($order->payment_method_id == null) ? 'Sin método de pago':$order->payment_method->name ,
                "state" => $order->status_name,
                "data_payment" => $order->data_payment,
                "url" => $url,
                "nombre_cliente" => $order->nombre_cliente,
                "tipo_documento_cliente" => $tipo_documento_cliente,
                "numero_documento_cliente" => $order->numero_documento_cliente,
                "direccion_cliente" => $order->direccion_cliente,
                "email_cliente" => $order->email_cliente,
                "tipo_comprobante" => $tipo_comprobante
            ]);
        }

        $pagination = [
            'currentPage' => (int)$pageNumber,
            'totalPages' => (int)$totalPages,
            'startRecord' => $startRecord,
            'endRecord' => $endRecord,
            'totalRecords' => $totalFilteredRecords,
            'totalFilteredRecords' => $totalFilteredRecords
        ];

        return ['data' => $arrayGuides, 'pagination' => $pagination];
    }

    public function getOrdersAnnulledAdmin(Request $request, $pageNumber = 1)
    {
        $perPage = 10;
        $code = $request->input('code');
        $year = $request->input('year');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if ( $startDate == "" || $endDate == "" )
        {
            $query = Order::where('state_annulled', 1)->orderBy('created_at', 'DESC');
        } else {
            $fechaInicio = Carbon::createFromFormat('d/m/Y', $startDate);
            $fechaFinal = Carbon::createFromFormat('d/m/Y', $endDate);

            $query = Order::whereDate('created_at', '>=', $fechaInicio)
                ->whereDate('created_at', '<=', $fechaFinal)
                ->where('state_annulled', 1)
                ->orderBy('created_at', 'DESC');
        }

        // Aplicar filtros si se proporcionan
        if ($code != "") {
            $query->where('id', 'LIKE', '%'.$code.'%');

        }

        if ($year != "") {
            $query->whereYear('created_at', $year);

        }

        $totalFilteredRecords = $query->count();
        $totalPages = ceil($totalFilteredRecords / $perPage);

        $startRecord = ($pageNumber - 1) * $perPage + 1;
        $endRecord = min($totalFilteredRecords, $pageNumber * $perPage);

        $orders = $query->skip(($pageNumber - 1) * $perPage)
            ->take($perPage)
            ->get();

        $arrayGuides = [];

        foreach ( $orders as $order )
        {
            $direccion = Address::find($order->shipping_address_id);
            $distrito = ShippingDistrict::find($order->shipping_district_id);
            array_push($arrayGuides, [
                "id" => $order->id,
                "code" => "ORDEN - ".$order->id,
                "date" => ($order->created_at != null) ? $order->formatted_created_date : "",
                "date_delivery" => ($order->created_at != null) ? $order->formatted_date : "",
                "customer" => $direccion->first_name." ".$direccion->last_name,
                "phone" => $direccion->phone,
                "address" => $direccion->address_line. " - ".( (!isset($distrito)) ? 'N/A':$distrito->name),
                "latitude" => $direccion->latitude,
                "longitude" => $direccion->longitude,
                "total" => $order->amount_pay,
                "method" => ($order->payment_method_id == null) ? 'Sin método de pago':$order->payment_method->name ,
                "state" => $order->status_name,
                "data_payment" => $order->data_payment,
            ]);
        }

        $pagination = [
            'currentPage' => (int)$pageNumber,
            'totalPages' => (int)$totalPages,
            'startRecord' => $startRecord,
            'endRecord' => $endRecord,
            'totalRecords' => $totalFilteredRecords,
            'totalFilteredRecords' => $totalFilteredRecords
        ];

        return ['data' => $arrayGuides, 'pagination' => $pagination];
    }

    public function changeIOrderState($order_id, $state)
    {
        DB::beginTransaction();
        try {

            $order = Order::find($order_id);
            $order->status = $state;
            $order->save();

            $order2 = Order::find($order_id);

            // Obtener el correo electrónico según la lógica.
            $email = $this->getEmailForOrder($order2);

            if ($email) {
                // Enviar correo al cliente con el estado de la orden.
                Mail::to($email)->send(new OrderStatusEmail($order2));
                Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ' . $state);
            } else {
                Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
            }

            Log::info('Emitiendo evento para la orden:', $order2->toArray());
            broadcast(new OrderStatusUpdated($order2));

            DB::commit();
        } catch ( \Throwable $e ) {
            DB::rollBack();
            Log::error('Error cambiando estado de la orden: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json(['message' => 'Cambio de estado realizado con éxito'], 200);
    }

    public function anularOrder($order_id)
    {
        $id = $order_id;
        // Verificar si el id comienza con "kanban_"
        if (strpos($order_id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($order_id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        DB::beginTransaction();
        try {

            $order = Order::find($id);

            if (!$order) {
                return response()->json(['message' => 'Orden no encontrada'], 422);
            }

            $order->state_annulled = 1;
            $order->save();

            // Obtener el correo electrónico según la lógica.
            $email = $this->getEmailForOrder($order);

            if ($email) {
                // Enviar correo al cliente con el estado de la orden.
                Mail::to($email)->send(new OrderStatusEmailAnulled($order));
                Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: Rechazado');
            } else {
                Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
            }

            Log::info('Emitiendo evento para la orden:', $order->toArray());
            broadcast(new OrderStatusUpdated($order));
            broadcast(new OrderCreated($order, $order_id));
            /*Log::info('Emitiendo evento para la orden:', $order->toArray());*/

            // Cambios en los movimientos
            // Revertir los movimientos de caja asociados a la orden
            $movements = CashMovement::where('order_id', $order->id)->get();
            foreach ($movements as $movement) {
                // Si es un movimiento de tipo "sale"
                if ($movement->type === 'sale') {
                    // Caso de pago POS (no pago directo)
                    if ($movement->subtype === 'pos') {
                        if ($movement->regularize == 0) {
                            // No se regularizó: se elimina el movimiento
                            $movement->delete();
                        } elseif ($movement->regularize == 1) {
                            // Si se regularizó, se crea un movimiento inverso de tipo "expense"
                            CashMovement::create([
                                'cash_register_id' => $movement->cash_register_id,
                                'order_id'         => $order->id,
                                'type'             => 'expense',
                                'amount'           => $movement->amount,
                                'description'      => 'Reversión de venta (POS regularizado) por anulación de orden',
                                'subtype'          => $movement->subtype,
                                'regularize'       => $movement->regularize
                            ]);
                            $cashRegister = CashRegister::find($movement->cash_register_id);
                            $cashRegister->current_balance -= $movement->amount;
                            $cashRegister->total_sales    -= $movement->amount;
                            $cashRegister->total_incomes  -= $movement->amount;
                            $cashRegister->total_expenses += $movement->amount;
                            $cashRegister->save();
                        }
                    } else {
                        // Para ventas normales, se revierte creando un movimiento de tipo "expense"
                        CashMovement::create([
                            'cash_register_id' => $movement->cash_register_id,
                            'order_id'         => $order->id,
                            'type'             => 'expense',
                            'amount'           => $movement->amount,
                            'description'      => 'Reversión de venta por anulación de orden',
                            'subtype'          => $movement->subtype,
                            'regularize'       => $movement->regularize
                        ]);
                        $cashRegister = CashRegister::find($movement->cash_register_id);
                        $cashRegister->current_balance -= $movement->amount;
                        $cashRegister->total_sales    -= $movement->amount;
                        $cashRegister->total_incomes  -= $movement->amount;
                        $cashRegister->total_expenses += $movement->amount;
                        $cashRegister->save();
                    }
                }
                // Si es un movimiento de tipo "expense" (por ejemplo, el vuelto)
                elseif ($movement->type === 'expense') {
                    // Se revierte creando un movimiento de tipo "income"
                    CashMovement::create([
                        'cash_register_id' => $movement->cash_register_id,
                        'order_id'         => $order->id,
                        'type'             => 'income',
                        'amount'           => $movement->amount,
                        'description'      => 'Reversión de gasto (vuelto) por anulación de orden',
                        'subtype'          => $movement->subtype,
                        'regularize'       => $movement->regularize
                    ]);
                    $cashRegister = CashRegister::find($movement->cash_register_id);
                    $cashRegister->current_balance += $movement->amount;
                    $cashRegister->total_incomes  += $movement->amount;
                    $cashRegister->total_expenses -= $movement->amount;
                    $cashRegister->save();
                }
            }

            DB::commit();

            return response()->json(['message' => 'Orden anulada con éxito'], 200);

        } catch ( \Throwable $e ) {
            DB::rollBack();
            Log::error('Error anulando la orden: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }

    public function activarOrder($order_id)
    {
        DB::beginTransaction();
        try {

            $order = Order::find($order_id);
            $order->state_annulled = 0;
            $order->save();

            Log::info('Emitiendo evento para la orden:', $order->toArray());

            DB::commit();

            return response()->json(['message' => 'Orden anulada con éxito'], 200);

        } catch ( \Throwable $e ) {
            DB::rollBack();
            Log::error('Error anulando la orden: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
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

    public function getOrderDetails($orderId)
    {

        $order = Order::with([
            'details.productType.type',
            'details.product',
            'details.options.option',
            'details.options.product',
        ])->find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        $details = $order->details->map(function ($detail) {
            return [
                'pizza_name' => $detail->product->full_name,
                'quantity' => $detail->quantity,
                'type' => ($detail->product_type_id == null) ? 'N/A': $detail->productType->type->name,
                'size' => ($detail->product_type_id == null) ? 'N/A': $detail->productType->type->size,
                'ingredients' => ($detail->product->ingredients == null) ? 'N/A':$detail->product->ingredients,
                'options' => $detail->options->map(function ($option) {
                    return [
                        'product_name' => ($option->product->full_name == null) ? 'N/A': $option->product->full_name,
                    ];
                }),
            ];
        });

        return response()->json(['details' => $details], 200);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        // Verificar si el id comienza con "kanban_"
        if (strpos($id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        // Buscar la orden en la base de datos
        $order = Order::find($id);

        // Verificar si la orden existe
        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        // Devolver la orden en formato JSON
        return response()->json($order);
    }

    public function updateTime(Request $request)
    {

        $request->validate([
            /*'id' => 'required|exists:orders,id',*/
            'estimated_time' => 'required|integer|min:1',
            'status' => 'required|string'
        ]);

        $id = $request->id;
        // Verificar si el id comienza con "kanban_"
        if (strpos($request->id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($request->id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        $order = Order::findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 422);
        }

        $order->estimated_time = $request->estimated_time; // Guardar el tiempo estimado
        $order->date_processing = Carbon::now('America/Lima');
        $order->status = $request->status; // Cambiar estado a "processing"
        $order->save();

        $order2 = Order::find($id);

        // Obtener el correo electrónico según la lógica.
        $email = $this->getEmailForOrder($order2);

        if ($email) {
            // Enviar correo al cliente con el estado de la orden.
            Mail::to($email)->send(new OrderStatusEmail($order2));
            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ' . $order2->status);
        } else {
            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
        }

        Log::info('Emitiendo evento para la orden:', $order2->toArray());
        broadcast(new OrderStatusUpdated($order2));
        broadcast(new OrderCreated($order2, $request->id));

        return response()->json([
            'message' => 'Tiempo estimado actualizado correctamente',
            'order' => $order,
            'id_kanban' => $request->id
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            /*'id' => 'required|exists:orders,id',*/
            'status' => 'required|string'
        ]);

        $id = "";
        // Verificar si el id comienza con "kanban_"
        if (strpos($request->id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($request->id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        $order = Order::findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 422);
        }

        $order->status = $request->status;
        $order->save();

        $order2 = Order::find($id);

        // Obtener el correo electrónico según la lógica.
        $email = $this->getEmailForOrder($order2);

        if ($email) {
            // Enviar correo al cliente con el estado de la orden.
            Mail::to($email)->send(new OrderStatusEmail($order2));
            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ' . $order2->status);
        } else {
            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
        }

        Log::info('Emitiendo evento para la orden:', $order2->toArray());
        broadcast(new OrderStatusUpdated($order2));

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'order' => $order
        ]);
    }

    public function updateDistributor(Request $request)
    {
        $request->validate([
            /*'id' => 'required|exists:orders,id',*/
            'status' => 'required|string|in:shipped',
            'distributor_id' => 'required|exists:distributors,id'
        ]);

        $id = $request->id;
        // Verificar si el id comienza con "kanban_"
        if (strpos($request->id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($request->id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        $order = Order::findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 422);
        }

        $order->status = $request->status;
        $order->distributor_id = $request->distributor_id;
        $order->save();

        $order2 = Order::find($id);

        // Obtener el correo electrónico según la lógica.
        $email = $this->getEmailForOrder($order2);

        if ($email) {
            // Enviar correo al cliente con el estado de la orden.
            Mail::to($email)->send(new OrderStatusEmail($order2));
            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ' . $order2->status);
        } else {
            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
        }

        Log::info('Emitiendo evento para la orden:', $order2->toArray());
        broadcast(new OrderStatusUpdated($order2));
        broadcast(new OrderCreated($order2, $request->id));

        // 🚀 Retornar la orden actualizada para su renderización
        return response()->json($order);
    }

    public function entregarOrder(Request $request)
    {
        $request->validate([
            /*'id' => 'required|exists:orders,id',*/
            'status' => 'required|string'
        ]);
        $id = $request->id;
        // Verificar si el id comienza con "kanban_"
        if (strpos($request->id, 'kanban_') === 0) {
            // Remover el prefijo "kanban_"
            $id = substr($request->id, strlen('kanban_'));
            // Buscar la posición del segundo guion bajo (si existe)
            $pos = strpos($id, '_');
            if ($pos !== false) {
                // Extraer solo la parte antes del segundo guion bajo
                $id = substr($id, 0, $pos);
            }
        }

        $order = Order::findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 422);
        }

        $order->status = $request->status;
        $order->save();

        // TODO: Logica para restar las flamitas
        // 1. Obtener los datos necesarios
        $flames = $order->flames;
        $user = User::find($order->user_id);
        $order_id = $order->id;
        $total = $order->amount_pay;

        // 2. Obtener el valor del multiplicador y la conversión
        $dataMultiplicador = DataGeneral::where('name', 'multiplier_flames')->first();
        $multiplicador = isset($dataMultiplicador->valueNumber) ? $dataMultiplicador->valueNumber : 1;
        $dataConversion = DataGeneral::where('name', 'conversion_flames')->first();
        $conversion = isset($dataConversion->valueNumber) ? $dataConversion->valueNumber : 10;

        // -------------------------
        // Paso 1: Generar Flames (si el total es mayor a 0)
        // -------------------------
        if ($total > 0) {
            // Calcular las flamas generadas
            $flames_generated = floor(($total / $conversion) * $multiplicador);

            if ($flames_generated > 0) {
                // Calcular la fecha de expiración (último día del mismo mes del siguiente año)
                $expiration_date = Carbon::now()->addYear()->endOfMonth();

                // Crear el Reward
                Reward::create([
                    'user_id' => $user->id,
                    'order_id' => $order_id,
                    'flames' => $flames_generated,
                    'expiration_date' => $expiration_date,
                    'state' => 'active',
                ]);

                // Actualizar las flamas del usuario
                $user->flames += $flames_generated;
                $user->save();
            }
        }

        // -------------------------
        // Paso 2: Restar Flames si existen en la orden
        // -------------------------
        if ($flames > 0) {
            // Restar las flamas del usuario
            $user->flames -= $flames;
            $user->save();

            // Obtener los rewards del usuario, ordenados por fecha de creación (los más antiguos primero)
            $rewards = Reward::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingFlames = $flames;

            foreach ($rewards as $reward) {
                if ($remainingFlames <= 0) break;

                if ($reward->flames <= $remainingFlames) {
                    // Si el reward tiene menos o igual flamas que las que se deben restar, se elimina
                    $remainingFlames -= $reward->flames;
                    $reward->delete();
                } else {
                    // Si el reward tiene más flamas que las que se deben restar, solo se resta la cantidad necesaria
                    $reward->flames -= $remainingFlames;
                    $reward->save();
                    $remainingFlames = 0;
                }
            }
        }

        $order2 = Order::find($id);

        // Obtener el correo electrónico según la lógica.
        $email = $this->getEmailForOrder($order2);

        if ($email) {
            // Enviar correo al cliente con el estado de la orden.
            Mail::to($email)->send(new OrderStatusEmail($order2));
            Log::info('Correo enviado a: ' . $email . ' con el estado de la orden: ' . $order2->status);
        } else {
            Log::warning('No se encontró un correo electrónico para enviar el estado de la orden.');
        }

        Log::info('Emitiendo evento para la orden:', $order2->toArray());
        broadcast(new OrderStatusUpdated($order2));
        broadcast(new OrderCreated($order2, $request->id));

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'order' => $order
        ]);
    }

    public function edit(Order $order)
    {
        //
    }

    public function update(Request $request, Order $order)
    {
        //
    }

    public function destroy(Order $order)
    {
        //
    }

    public function generarOrder()
    {
        $orden = Order::find(90);

        broadcast(new OrderCreated($orden, 90));

        return "Orden agregada";
    }

    public function reportePizzasFinde()
    {
        $startDate = Carbon::create(2025, 1, 3); // Desde el 1 de enero de 2025
        $endDate = Carbon::now(); // Hasta hoy
        //$endDate = Carbon::create(2025, 2, 23);
        // Obtiene las órdenes creadas en fines de semana (sábados y domingos) dentro del rango de fechas
        $orders = Order::whereDate('created_at', '>=',$startDate)
            ->whereDate('created_at', '<=',$endDate)
            ->whereRaw('WEEKDAY(created_at) IN (4,5,6)') // Solo viernes (4), sábado (5) y domingo (6)
            ->with(['details.options']) // Solo cargamos opciones porque usaremos directamente product_type_id
            ->where('state_annulled', 0)
            ->get();

        //dump(count($orders));

        $semanas = [];

        foreach ($orders as $index2 => $order) {
            // Determina el inicio (sábado) y fin (domingo) de la semana de la orden
            $weekStart = Carbon::parse($order->created_at)->startOfWeek(Carbon::FRIDAY);
            $weekEnd = $weekStart->copy()->addDays(2); // Sábado + Domingo
            $semanaKey = $weekStart->toDateString() . ' al ' . $weekEnd->toDateString();

            // Inicializa la estructura para la semana si no existe
            if (!isset($semanas[$semanaKey])) {
                $semanas[$semanaKey] = [
                    'id' => count($semanas) + 1,
                    'semana' => $semanaKey,
                    'cantFamiliar' => 0,
                    'cantGrande' => 0,
                    'cantPersonal' => 0,
                ];
            }
            //dump("Orden: ". ($index2+1));
            foreach ($order->details as $index => $detail) {
                //dump("Detalle: ". ($index+1));
                //dump($detail->product->full_name);
                $productTypeId = $detail->product_type_id; // Directamente desde OrderDetail
                $categoryId = $detail->product->category_id ?? null; // Categoría del producto
                $category = Category::find($categoryId);
                //dump("Categoria: ". $category->name);
                //dump("ProductTypeId: ". $productTypeId);
                // Si el producto es una pizza clásica, especial o personalizada, se cuenta directamente
                if (in_array($categoryId, [1, 2, 8]) && $productTypeId) {
                    $this->sumarCantidad($semanas[$semanaKey], $productTypeId, $detail->quantity);
                }
                // Si el producto es un combo o promoción, se revisan sus opciones
                elseif (in_array($categoryId, [3, 7])) {
                    foreach ($detail->options as $option) {
                        $optionCategoryId = $option->product->category_id ?? null;
                        //$optionTypeId = $option->product_type_id; // Directamente desde OrderDetailOption

                        if (in_array($optionCategoryId, [1, 2, 8]) && $productTypeId) {
                            $this->sumarCantidad($semanas[$semanaKey], $productTypeId, $detail->quantity);
                        }
                    }
                } /*else {
                    dump("No pertenece a las categorias");
                }*/
            }
        }

        // Calcula los totales de cada tipo de pizza en todas las semanas
        $totalFamiliar = array_sum(array_column($semanas, 'cantFamiliar'));
        $totalGrande = array_sum(array_column($semanas, 'cantGrande'));
        $totalPersonal = array_sum(array_column($semanas, 'cantPersonal'));
        $numSemanas = count($semanas);

        // Calcula el promedio de pizzas vendidas por semana
        $resumen = [
            'totalFamiliar' => $totalFamiliar,
            'totalGrande' => $totalGrande,
            'totalPersonal' => $totalPersonal,
            'promedioFamiliar' => $numSemanas ? round($totalFamiliar / $numSemanas, 2) : 0,
            'promedioGrande' => $numSemanas ? round($totalGrande / $numSemanas, 2) : 0,
            'promedioPersonal' => $numSemanas ? round($totalPersonal / $numSemanas, 2) : 0,
        ];

        //dump(array_values($semanas));
        //dump($resumen);

        return response()->json(['semanas' => array_values($semanas), 'resumen' => $resumen]);
    }

    private function sumarCantidad(&$semana, $typeId, $cantidad)
    {
        $productType = ProductType::find($typeId);
        $type = Type::find($productType->type_id);
        //dump("Tipo: ".$type->name);
        //dump("Cantidad: ".$cantidad);
        if ( isset($productType) )
        {
            $type = $productType->type_id;
            //dump($typeId);
            switch ($type) {
                case 1:
                    $semana['cantFamiliar'] += $cantidad;
                    break;
                case 2:
                    $semana['cantGrande'] += $cantidad;
                    break;
                case 3:
                    $semana['cantPersonal'] += $cantidad;
                    break;
            }
        }

    }

    public function updateInvoiceData(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type_document' => 'required|in:boleta,factura',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($request->type_document === 'boleta') {
            $order->type_document = '03';
            $order->numero_documento_cliente = $request->dni;
            $order->email_cliente = $request->email;
        } elseif ($request->type_document === 'factura') {
            $order->type_document = '01';
            $order->numero_documento_cliente = $request->ruc;
            $order->nombre_cliente = $request->razon_social;
            $order->direccion_cliente = $request->direccion_fiscal;
            $order->email_cliente = $request->email;
        }

        $order->save();

        return response()->json(['message' => 'Datos actualizados correctamente.']);
    }
}
