<?php

namespace App\Models;

use Carbon\Carbon;
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
        'sent_to_kitchen_at',
        'started_cooking_at',
        'estimated_minutes',
        'estimated_ready_at',
        'ready_at',
        'delivered_at',
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

    public function getFormattedSendToKitchenAttribute()
    {
        if ($this->sent_to_kitchen_at != null)
        {
            return Carbon::parse($this->sent_to_kitchen_at)->isoFormat('DD/MM/YYYY [a las] h:mm A');
        } else {
            return "Sin Fecha";
        }

    }

    public function getFormattedStartedCookingAttribute()
    {
        if ($this->started_cooking_at != null)
        {
            return Carbon::parse($this->started_cooking_at)->isoFormat('DD/MM/YYYY [a las] h:mm A');
        } else {
            return "Sin Fecha";
        }

    }

    public function getFormattedEstimatedReadyAttribute()
    {
        if ($this->estimated_ready_at != null)
        {
            return Carbon::parse($this->estimated_ready_at)->isoFormat('DD/MM/YYYY [a las] h:mm A');
        } else {
            return "Sin Fecha";
        }

    }
}
