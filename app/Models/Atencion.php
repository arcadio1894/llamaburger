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
        'closed_at',
        'tipo',
    ];

    protected $table = "atenciones";

    public function scopeMesas($q) {
        return $q->where('tipo','mesa');
    }

    public function scopeExternos($q) {
        return $q->where('tipo','externo');
    }

    public function mesa() {
        return $this->belongsTo(Mesa::class);
    }
    public function mozo() {
        return $this->belongsTo(Mozo::class);
    }
    public function comandas() {
        return $this->hasMany(Comanda::class);
    }
    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
    public function payments(){
        return $this->hasMany(Payment::class);
    }
}
