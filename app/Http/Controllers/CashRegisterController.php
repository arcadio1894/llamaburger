<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\DataGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    public function indexCashRegister($type)
    {
        /*$cashRegister = CashRegister::where('type', $type)->latest()->first();*/
        $cashRegister = CashRegister::where('type', $type)
            ->orderByDesc('opening_time') // o 'id' si lo prefieres
            ->first();

        // TODO: Puede pasar 3 cosas: Que no exista, que exista abierta, que exista cerrada
        $balance_total = 0;
        $buttons = [];
        $state = '<div class="col-md-4 col-6">
            <div class="small-box bg-secondary">
              <div class="inner">
                <h4>Cerrada</h4>
              </div>
              <div class="icon">
                <i class="fas fa-lock" style="font-size: 40px"></i>
              </div>
              <a href="#" id="btn-openCashRegister" class="small-box-footer">Abrir Caja <i class="fas fa-door-open"></i></a>
            </div>
          </div>';

        if ( !$cashRegister )
        {
            // No existe caja
            CashRegisterLog::create([
                'cash_register_id' => null,
                'user_id' => auth()->id(),
                'action' => 'Ver estado de caja',
                'description' => "Caja de tipo '{$type}' no existe. Mostrando opción para abrir."
            ]);


            // TODO: No existe
            array_push($buttons, ['open']);

        } else {
            // TODO: Existe

            if ( $cashRegister->status == 1 ) // abierta
            {
                // Caja abierta
                CashRegisterLog::create([
                    'cash_register_id' => $cashRegister->id,
                    'user_id' => auth()->id(),
                    'action' => 'Ver estado de caja',
                    'description' => "Caja de tipo '{$type}' está ABIERTA. Mostrando opción para cerrar."
                ]);

                $balance_total = round($cashRegister->current_balance, 2);
                array_push($buttons, ['close']);
                $state = '<div class="col-md-4 col-6">
                            <div class="small-box bg-success">
                              <div class="inner">
                                <h4>Abierta</h4>
                              </div>
                              <div class="icon">
                                <i class="fas fa-lock-open" style="font-size: 40px"></i>
                              
                              </div>
                              <a href="#" id="btn-closeCashRegister" class="small-box-footer">Cerrar Caja <i class="fas fa-door-closed"></i></a>
                            </div>
                          </div>';
            } else {

                // Caja cerrada
                CashRegisterLog::create([
                    'cash_register_id' => $cashRegister->id,
                    'user_id' => auth()->id(),
                    'action' => 'Ver estado de caja',
                    'description' => "Caja de tipo '{$type}' está CERRADA. Mostrando opción para abrir."
                ]);

                // cerrada
                $balance_total = $cashRegister->closing_balance;
                array_push($buttons, ['open']);
                $state = '<div class="col-md-4 col-6">
                            <div class="small-box bg-danger">
                              <div class="inner">
                                <h4>Cerrada</h4>
                              </div>
                              <div class="icon">
                                <i class="fas fa-lock" style="font-size: 40px"></i>
                              </div>
                              <a href="#" id="btn-openCashRegister" class="small-box-footer">Abrir Caja <i class="fas fa-door-open"></i></a>
                            </div>
                          </div>';
            }

        }

        $active = '';
        if ( $type == 'efectivo' )
        {
            $active = 'Efectivo';
        } elseif ( $type == 'yape' ) {
            $active = 'Yape';
        } elseif ( $type == 'plin' ) {
            $active = 'Plin';
        } elseif ( $type == 'bancario' ) {
            $active = 'Bancario';
        }

        return view('cashRegister.index', compact( 'balance_total','buttons', 'active', 'state'));
    }

    public function openCashRegister( Request $request )
    {
        DB::beginTransaction();
        try {

            $type = $request->get('type');
            $balance_total = $request->get('balance_total');

            $existing = CashRegister::where('type', strtolower($type))
                ->whereNull('closing_time')
                ->exists();

            if ($existing) {
                // No abrir otra caja, ya hay una abierta
                return response()->json(['message' => "Hay una caja abierta"], 422);
            }

            $caja = CashRegister::create([
                'opening_balance' => $balance_total,
                'current_balance' => $balance_total,
                'opening_time' => Carbon::now('America/Lima'),
                'type' => strtolower($type),
                'status' => 1,
            ]);

            // ✅ Registrar en log
            CashRegisterLog::create([
                'cash_register_id' => $caja->id,
                'user_id' => auth()->id(),
                'action' => 'Apertura de caja',
                'description' => "Caja de tipo '{$type}' aperturada con S/ {$balance_total}."
            ]);

            $state = '<div class="col-md-4 col-6">
                            <div class="small-box bg-success">
                              <div class="inner">
                                <h4>Abierta</h4>
                              </div>
                              <div class="icon">
                                <i class="fas fa-lock-open" style="font-size: 40px"></i>
                              
                              </div>
                              <a href="#" id="btn-closeCashRegister" class="small-box-footer">Cerrar Caja <i class="fas fa-door-closed"></i></a>
                            </div>
                          </div>';

            DB::commit();

        } catch ( \Throwable $e ) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Caja aperturada con éxito.',
            'state' => $state,
            'balance_total' => $caja->current_balance
        ], 200);
    }

    public function closeCashRegister( Request $request )
    {
        DB::beginTransaction();
        try {

            $type = $request->get('type');
            $balance_total = $request->get('balance_total');

            // Verificar si la tienda está abierta
            $currentStatus = DataGeneral::getValue('status_store'); // 1: Abierta, 0: Cerrada
            if ($currentStatus == 1) {
                return response()->json(['message' => 'PRIMERO DEBES CERRAR LA TIENDA.'], 422);
            }


            $cashRegister = CashRegister::where('type', $type)
                ->orderByDesc('opening_time') // o 'id' si lo prefieres
                ->first();

            if ( !isset($cashRegister) )
            {
                // TODO: No existe
                return response()->json(['message' => "No existe una caja creada"], 422);
            } else {
                // TODO: Existe

                if ( $cashRegister->status == 0 )
                {
                    // cerrada
                    return response()->json(['message' => "Ya está cerrada la caja"], 422);
                }

            }

            $cashRegister->closing_balance = $balance_total;
            $cashRegister->current_balance = $balance_total;
            $cashRegister->closing_time = Carbon::now('America/Lima');
            $cashRegister->status = 0;
            $cashRegister->save();

            // ✅ Registrar en log
            CashRegisterLog::create([
                'cash_register_id' => $cashRegister->id,
                'user_id' => auth()->id(),
                'action' => 'Cierre de caja',
                'description' => "Caja de tipo '{$type}' cerrada con S/ {$balance_total}."
            ]);

            $state = '<div class="col-md-4 col-6">
                            <div class="small-box bg-danger">
                              <div class="inner">
                                <h4>Cerrada</h4>
                              </div>
                              <div class="icon">
                                <i class="fas fa-lock" style="font-size: 40px"></i>
                              </div>
                              <a href="#" id="btn-openCashRegister" class="small-box-footer">Abrir Caja <i class="fas fa-door-open"></i></a>
                            </div>
                          </div>';

            DB::commit();

        } catch ( \Throwable $e ) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Caja cerrada con éxito.',
            'state' => $state,
            'balance_total' => $cashRegister->current_balance
        ], 200);
    }

    public function incomeCashRegister( Request $request )
    {
        DB::beginTransaction();
        try {
            $type = $request->get('type');
            $balance_total = $request->get('balance_total');
            $amount = $request->get('amount');
            $description = $request->get('description');

            /*$cashRegister = CashRegister::where('type', $type)->latest()->first();*/
            $cashRegister = CashRegister::where('type', $type)
                ->orderByDesc('opening_time') // o 'id' si lo prefieres
                ->first();

            if ( !isset($cashRegister) )
            {
                // TODO: No existe
                return response()->json(['message' => "No se puede hacer un ingreso a una caja inexistente."], 422);
            } else {
                // TODO: Existe

                if ( $cashRegister->status == 1 )
                {
                    // abierta

                    // 1. Registrar el movimiento en la tabla `CashMovement`
                    CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'type' => 'income', // Tipo de movimiento: ingreso
                        'amount' => $amount,
                        'description' => $description,
                    ]);

                    // 2. Actualizar los datos de `CashRegister`
                    $cashRegister->current_balance += $amount; // Actualizamos el saldo actual
                    $cashRegister->total_incomes += $amount; // Actualizamos el total de ingresos

                    // Guardar los cambios en la caja
                    $cashRegister->save();

                    // ✅ Registrar en CashRegisterLog
                    CashRegisterLog::create([
                        'cash_register_id' => $cashRegister->id,
                        'user_id' => auth()->id(),
                        'action' => 'Ingreso',
                        'description' => "Ingreso de S/ {$amount}. {$description}"
                    ]);
                } else {
                    // cerrada
                    return response()->json(['message' => "No se puede hacer un ingreso a una caja cerrada."], 422);
                }

            }

            DB::commit();

            return response()->json([
                'message' => 'Ingreso registrado con éxito.',
                'balance_total' => round($cashRegister->current_balance, 2)
            ], 200);

        } catch ( \Throwable $e ) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }

    public function expenseCashRegister(Request $request)
    {
        DB::beginTransaction();
        try {
            $type = $request->get('type');
            $amount = $request->get('amount');
            $description = $request->get('description');

            // Buscar la caja abierta del usuario actual
            /*$cashRegister = CashRegister::where('type', $type)
                ->latest()
                ->first();*/
            $cashRegister = CashRegister::where('type', $type)
                ->orderByDesc('opening_time') // o 'id' si lo prefieres
                ->first();

            if (!isset($cashRegister)) {
                // Caja no encontrada
                return response()->json(['message' => "No se puede hacer un egreso de una caja inexistente."], 422);
            } else {
                if ($cashRegister->status == 1) {
                    // Caja abierta

                    // Validar que hay suficiente saldo en la caja
                    if ($cashRegister->current_balance < $amount) {
                        return response()->json(['message' => "No hay suficiente saldo en la caja para realizar este egreso."], 422);
                    }

                    // 1. Registrar el movimiento en la tabla `CashMovement`
                    CashMovement::create([
                        'cash_register_id' => $cashRegister->id,
                        'type' => 'expense', // Tipo de movimiento: egreso
                        'amount' => $amount,
                        'description' => $description,
                    ]);

                    // 2. Actualizar los datos de `CashRegister`
                    $cashRegister->current_balance -= $amount; // Restamos el monto del saldo actual
                    $cashRegister->total_expenses += $amount; // Actualizamos el total de egresos

                    // Guardar los cambios en la caja
                    $cashRegister->save();

                    // ✅ Registrar en log
                    CashRegisterLog::create([
                        'cash_register_id' => $cashRegister->id,
                        'user_id' => auth()->id(),
                        'action' => 'Egreso',
                        'description' => "Egreso de S/ {$amount}. {$description}"
                    ]);

                } else {
                    // Caja cerrada
                    return response()->json(['message' => "No se puede hacer un egreso de una caja cerrada."], 422);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Egreso registrado con éxito.',
                'balance_total' => round($cashRegister->current_balance, 2)
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function regularizeCashRegister( Request $request )
    {
        //dd($request);
        DB::beginTransaction();
        try {
            $type = strtolower($request->get('type'));
            $cash_movement_id = $request->get('cash_movement_id');
            $amount = round((float)$request->get('amount'), 2);

            /*$cashRegister = CashRegister::where('type', $type)->latest()->first();*/
            $cashRegister = CashRegister::where('type', $type)
                ->orderByDesc('opening_time') // o 'id' si lo prefieres
                ->first();

            if ( !isset($cashRegister) )
            {
                // TODO: No existe
                return response()->json(['message' => "No se puede hacer un ingreso a una caja inexistente."], 422);
            } else {
                // TODO: Existe

                if ( $cashRegister->status == 1 )
                {
                    // abierta

                    // 1. Registrar el movimiento en la tabla `CashMovement`
                    $cashMovement = CashMovement::find($cash_movement_id);
                    $cashMovement->amount = $amount;
                    $cashMovement->regularize = 1;
                    $cashMovement->save();

                    // 2. Actualizar los datos de `CashRegister`
                    $cashRegister->current_balance += $amount; // Actualizamos el saldo actual
                    $cashRegister->total_sales += $amount; // Actualizamos el total de ingresos

                    // Guardar los cambios en la caja
                    $cashRegister->save();

                    // ✅ Registrar en log
                    CashRegisterLog::create([
                        'cash_register_id' => $cashRegister->id,
                        'user_id' => auth()->id(),
                        'action' => 'Regularizacion',
                        'description' => "Regularizacion de S/ {$cashMovement->amount}."
                    ]);
                } else {
                    // cerrada
                    return response()->json(['message' => "No se puede hacer un ingreso a una caja cerrada."], 422);
                }

            }

            DB::commit();

            return response()->json([
                'message' => 'Regularización registrada con éxito.',
                'balance_total' => round($cashRegister->current_balance, 2)
            ], 200);

        } catch ( \Throwable $e ) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }

    }

    public function getDataMovements(Request $request, $pageNumber = 1)
    {
        $perPage = 50;

        $type = strtolower($request->input('type'));

        /*$cashRegister = CashRegister::where('type', $type)
            ->where('user_id', Auth::user()->id)->get();*/
        $cashRegisterIds = CashRegister::where('type', $type)
            ->pluck('id'); // Devuelve una colección de IDs de CashRegister

        $array = [];
        $pagination = [];

        if ( isset($cashRegisterIds) )
        {
            //$query = CashMovement::where('cash_register_id', $cashRegister->id)->orderBy('id', 'desc');
            $query = CashMovement::whereIn('cash_register_id', $cashRegisterIds)
                ->orderBy('id', 'desc'); // Asegúrate de que haya un campo de fecha para ordenar

            $totalFilteredRecords = $query->count();
            $totalPages = ceil($totalFilteredRecords / $perPage);

            $startRecord = ($pageNumber - 1) * $perPage + 1;
            $endRecord = min($totalFilteredRecords, $pageNumber * $perPage);

            $movements = $query->skip(($pageNumber - 1) * $perPage)
                ->take($perPage)
                ->get();

            foreach ( $movements as $movement )
            {
                if ( $movement->type == 'income')
                {
                    $tipo = 'Ingreso';
                } elseif ( $movement->type == 'sale' && $movement->subtype == 'pos' && $movement->regularize == 0 ) {
                    $tipo = 'Regularizar';
                } elseif ( $movement->type == 'sale' && $movement->subtype == 'pos' && $movement->regularize == 1 ) {
                    $tipo = 'Venta';
                } elseif ( $movement->type == 'expense' ) {
                    $tipo = 'Egreso';
                } else {
                    $tipo = 'Venta';
                }

                array_push($array, [
                    "id" => $movement->id,
                    "type" => $tipo,
                    "amount" => $movement->amount,
                    "order_id" => $movement->order_id,
                    "invoice_id" => $movement->invoice_id,
                    "origen" => ($movement->subtype == null) ? 'N/A': $movement->subtype,
                    "description" => $movement->description,
                    "date" => $movement->created_at->format('d/m/Y h:i A')
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
        }

        return ['data' => $array, 'pagination' => $pagination];
    }
}
