<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'agent_id',
        'comanda_id',
        'printer_name',
        'content',
        'status'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function comanda()
    {
        return $this->belongsTo(Comanda::class);
    }

    protected $casts = ['content' => 'array'];
}
