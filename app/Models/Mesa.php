<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = [
        'sala_id',
        'nombre',
        'estado',
        'descripcion',
    ];

    // Relaciones
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }
}
