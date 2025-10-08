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

    public function comanda()
    {
        return $this->belongsTo(Comanda::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
