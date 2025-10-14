<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Comanda extends Model
{
    use HasFactory;

    protected $fillable = [
        'atencion_id',
        'numero',
        'estado',
        'subtotal',
        'descuento',
        'igv',
        'total',
        'sent_to_kitchen_at'
    ];

    public function atencion()
    {
        return $this->belongsTo(Atencion::class);
    }

    public function items()
    {
        return $this->hasMany(ComandaItem::class);
    }

    public function recalcTotals()
    {
        $sub = $this->items()->sum(DB::raw('cantidad * precio_unit'));
        $this->subtotal = $sub;
        $this->descuento = $this->descuento ?? 0;
        $this->igv = round($sub * 0.18, 2);
        $this->total = round($sub - $this->descuento + $this->igv, 2);
        $this->save();
    }
}
