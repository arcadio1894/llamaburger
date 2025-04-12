<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegisterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'action',
        'description',
        'user_id',
    ];

    // Relación con la caja
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
