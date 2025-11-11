<?php

namespace App\Events;

use App\Models\Comanda;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComandaReadyForPickup implements ShouldBroadcast
{
    use SerializesModels;

    public $comandaId;
    public $numero;
    public $mesa;        // si aplica
    public $atencionId;
    public $mozoId;

    public function __construct(Comanda $comanda, $mozoId)
    {
        $this->comandaId  = $comanda->id;
        $this->numero     = $comanda->numero;
        $this->mesa       = optional($comanda->atencion)->mesa->nombre;
        $this->atencionId = $comanda->atencion_id;
        $this->mozoId     = $mozoId;
    }

    // Canal público segmentado por mozo
    public function broadcastOn()
    {
        return new Channel('mozos.'.$this->mozoId);
    }

    public function broadcastAs()
    {
        return 'ComandaReadyForPickup';
    }

    // Payload explícito (opcional pero claro)
    public function broadcastWith()
    {
        return [
            'comandaId'  => $this->comandaId,
            'numero'     => $this->numero,
            'mesa'       => $this->mesa,
            'atencionId' => $this->atencionId,
            'mozoId'     => $this->mozoId,
        ];
    }
}