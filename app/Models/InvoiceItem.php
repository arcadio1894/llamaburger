<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id','product_id','descripcion','cantidad','unidad',
        'valor_unitario','precio_unitario','subtotal','igv','total','afectacion','extra'
    ];
    protected $casts = ['extra'=>'array'];

    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }
}
