<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    protected $fillable = ['nombre', 'tipo_doc', 'num_doc'];

    public function getDisplayNameAttribute()
    {
        $doc = $this->num_doc ? " ({$this->num_doc})" : '';
        return "{$this->nombre}{$doc}";
    }
}
