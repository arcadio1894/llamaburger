<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'salas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // app/Models/Sala.php
    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }
}
