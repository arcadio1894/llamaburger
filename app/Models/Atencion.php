<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atencion extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'mesa_id',
        'mozo_id',
        'personas',
        'comentario',
        'estado',
        'opened_at',
        'closed_at'
    ];

    protected $table = "atenciones";

    public function mesa() {
        return $this->belongsTo(Mesa::class);
    }
    public function mozo() {
        return $this->belongsTo(Mozo::class);
    }
    public function comandas() {
        return $this->hasMany(Comanda::class);
    }
}
