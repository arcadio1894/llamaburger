<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mozo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','activo'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function atenciones() {
        return $this->hasMany(Atencion::class);
    }
}
