<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'atencion_id','customer_id','tipo','serie','numero',
        'cliente_nombre','cliente_doc_tipo','cliente_doc_num','cliente_direccion',
        'op_gravada','igv','op_exonerada','op_inafecta','descuento','total','moneda',
        'estado','issue_date','extra'
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'extra' => 'array',
    ];

    public function atencion() {
        return $this->belongsTo(Atencion::class);
    }

    public function customer(){
        return $this->belongsTo(Cliente::class, 'customer_id');
    }

    public function items(){
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }
}
