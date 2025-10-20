<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComandaItemLiquidacion extends Model
{
    use HasFactory;
    protected $table = 'comanda_item_liquidaciones';

    protected $fillable = [
        'comanda_item_id',   // FK al item de comanda origen
        'invoice_id',        // FK a la cabecera del comprobante
        'invoice_item_id',   // FK al detalle del comprobante (opcional pero útil)
        'qty',               // cantidad liquidada (pagada) en este comprobante
        'monto',             // monto total asociado a esa qty (con IGV)
    ];

    // Relaciones
    public function comandaItem()
    {
        return $this->belongsTo(ComandaItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
