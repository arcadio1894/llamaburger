<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'comanda_id',
        'product_id',
        'nombre',
        'precio_unit',
        'cantidad',
        'estado',
        'opciones'
    ];

    protected $casts = ['opciones' => 'array'];
    protected $appends = ['total'];

    public function comanda()
    {
        return $this->belongsTo(Comanda::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAttribute()
    {
        // Suma segura (float) con 2 decimales
        return round(((float)$this->precio_unit) * ((int)$this->cantidad), 2);
    }

    public function liquidaciones()
    {
        return $this->hasMany(ComandaItemLiquidacion::class);
    }

    // Cantidad pagada acumulada
    public function getPagadoQtyAttribute()
    {
        return (int) $this->liquidaciones()->sum('qty');
    }

    // Restante = cantidad - pagado
    public function getRestanteAttribute()
    {
        return max(0, (int)$this->cantidad - $this->pagado_qty);
    }
}
