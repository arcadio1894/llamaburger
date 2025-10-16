<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_CREATED = 'created';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';

    protected $appends = ['data_payment','status_name', 'active_step', 'formatted_date', 'formatted_created_date', 'amount_pay', 'total_amount_print', 'order_phone', 'order_user'];

    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'billing_address_id',
        'total_amount',
        'status',
        'payment_method_id',
        'payment_amount',
        'payment_code',
        'amount_shipping',
        'shipping_district_id',
        'observations',
        'state_annulled',
        'estimated_time',
        'distributor_id',
        'date_processing',
        'shop_id',
        'flames',

        // Campos adicionales para facturación
        'serie',              // Serie del documento (ejemplo: F001, B001)
        'numero',             // Número correlativo del documento
        'type_document',      // Tipo de documento (01 para factura, 03 para boleta)
        'sunat_ticket',       // Ticket de respuesta de SUNAT
        'sunat_status',       // Estado del comprobante en SUNAT (ejemplo: Enviado, Rechazado)
        'sunat_message',      // Mensaje o error recibido de SUNAT
        'xml_path',           // Ruta del archivo XML generado
        'cdr_path',           // Ruta del archivo CDR generado
        'fecha_emision',       // Fecha de emisión del documento

        'nombre_cliente',
        'tipo_documento_cliente', // '1' = DNI, '6' = RUC
        'numero_documento_cliente',
        'direccion_cliente',
        'email_cliente'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashMovements()
    {
        return $this->hasMany(CashMovement::class, 'order_id');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function shipping_address()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billing_address()
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getOrderPhoneAttribute()
    {
        if ($this->shipping_address)
        {
            return $this->shipping_address->phone;
        }
        return "N/N";
    }

    public function getOrderUserAttribute()
    {
        if ($this->shipping_address)
        {
            return $this->shipping_address->first_name . " " .$this->shipping_address->last_name;
        }
        return "N/N";
    }

    // Accesor para obtener el pago o codigo yape
    public function getDataPaymentAttribute()
    {
        switch ($this->payment_method->code) {
            case 'pos':
                return "";
            case 'efectivo':
                return "S/. ".number_format($this->payment_amount, 2);
            case 'yape_plin':
                return $this->payment_code;
            default:
                return 0;
        }
    }

    // Accesor para obtener el estado en español
    public function getStatusNameAttribute()
    {
        $statusNames = [
            'created' => 'RECIBIDO',
            'processing' => 'COCINANDO',
            'shipped' => 'EN TRAYECTO',
            'completed' => 'ENTREGADO',
        ];

        if ( $this->state_annulled == 1 ) {
            return 'RECHAZADO';
        } else {
            return array_key_exists($this->status, $statusNames)
                ? $statusNames[$this->status]
                : 'DESCONOCIDO';
        }
    }

    // Método para obtener el número del paso activo según el estado
    public function getActiveStepAttribute()
    {
        switch ($this->status) {
            case 'created':
                return 1;
            case 'processing':
                return 2;
            case 'shipped':
                return 3;
            case 'completed':
                return 4;
            default:
                return 0;
        }
    }

    public function getFormattedDateAttribute()
    {
        return Carbon::parse($this->created_at)->addMinutes(40)->isoFormat('DD/MM/YYYY [a las] h:mm A');
    }

    public function getFormattedCreatedDateAttribute()
    {
        return Carbon::parse($this->created_at)->isoFormat('DD/MM/YYYY [a las] h:mm A');
    }

    public function getAmountPayAttribute()
    {
        // Obtener el descuento aplicado, si existe
        $userCoupon = UserCoupon::where('order_id', $this->id)->first();

        // Verificar si hay un descuento aplicado
        if ($userCoupon) {
            // Si existe un descuento, restar el discount_amount del total
            return number_format($this->total_amount - $userCoupon->discount_amount + $this->amount_shipping, 2, '.', '');
        }

        // Si no hay descuento, devolver el total sin cambios
        return number_format($this->total_amount + $this->amount_shipping, 2, '.', '');

    }

    public function getDataTotalsAttribute()
    {
        $order = Order::find($this->id);

        $userCoupon = UserCoupon::where('order_id', $order->id)->first();
        $discount = $userCoupon ? round($userCoupon->discount_amount, 2) : 0;

        $amount_shipping = round($order->amount_shipping, 2);

        $items = $order->details->map(function ($item) {
            $valor_unitario = round($item->price / 1.18, 6);
            $subtotal = $valor_unitario * $item->quantity; // 20.25
            $igv = ($item->price * $item->quantity) - $subtotal; // 23.90 - 20.25 = 3.65

            return [
                "unidad_de_medida" => "NIU",
                "codigo" => $item->product_id,
                "descripcion" => $item->product->full_name,
                "cantidad" => $item->quantity,
                "valor_unitario" => round($valor_unitario, 6),
                "precio_unitario" => round($item->price, 6),
                "subtotal" => round($subtotal, 6), // 34.66
                "tipo_de_igv" => "1", // gravado
                "igv" => round($igv, 2), // 6.24
                "total" => round($item->price*$item->quantity, 6) // 40.90
            ];
        })->toArray();

        if ($amount_shipping > 0) {
            $valor_unitario_envio = round($amount_shipping / 1.18, 6);
            $igv_envio = round($amount_shipping - $valor_unitario_envio, 6);

            $items[] = [
                "unidad_de_medida" => "NIU",
                "codigo" => "ENVIO",
                "descripcion" => "Costo de envío",
                "cantidad" => 1,
                "valor_unitario" => $valor_unitario_envio,
                "precio_unitario" => $amount_shipping,
                "subtotal" => $valor_unitario_envio,
                "tipo_de_igv" => "1",
                "igv" => $igv_envio,
                "total" => $amount_shipping
            ];
        }

        $total_gravada = array_sum(array_column($items, 'subtotal'));

        return [
                "total_gravada" => number_format($total_gravada - $discount, 2, '.', ''),
                "total_igv" => number_format(($total_gravada - $discount) * 0.18, 2, '.', ''),
                "total" => number_format(($total_gravada - $discount) * 1.18, 2, '.', ''),
                "total_a_pagar" => number_format(($total_gravada - $discount) * 1.18, 2, '.', ''),
            ] + ($discount > 0 ? [
                "descuento_global" => number_format($discount, 2, '.', ''),
                "total_descuento" => number_format($discount, 2, '.', '')
            ] : []) + [
                "items" => $items,
            ];

    }

    public function getTotalAmountPrintAttribute()
    {
        return number_format($this->total_amount + $this->amount_shipping, 2, '.', '');

    }

    public function getDateEstimatedFormatAttribute()
    {
        if (!$this->date_processing || !$this->estimated_time) {
            return null;
        }

        $deliveryDate = Carbon::parse($this->date_processing)->addMinutes($this->estimated_time);

        return $deliveryDate->format('d/m/Y \a \l\a\s g:i a');

    }

}
