<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'atencion_id','invoice_id','metodo','monto','moneda',
        'monto_recibido','vuelto','referencia','estado','paid_at','user_id','extra'
    ];
    protected $casts = [
        'paid_at'=>'datetime',
        'extra'=>'array',
    ];

    public function atencion(){
        return $this->belongsTo(Atencion::class);
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
