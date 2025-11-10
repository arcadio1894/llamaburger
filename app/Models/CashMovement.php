<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'order_id',
        'invoice_id',   // NUEVO
        'user_id',      // NUEVO
        'type',
        'amount',
        'description',
        'subtype',
        'regularize'
    ];

    protected $dates = ['created_at', 'updated_at'];

    // (Opcional) casts recomendados
    protected $casts = [
        'amount'     => 'decimal:2',
        'regularize' => 'boolean',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function order()
    {
        return$this->belongsTo(Order::class);
    }

    public function invoice() // NUEVO
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user() // NUEVO
    {
        return $this->belongsTo(User::class);
    }
}
