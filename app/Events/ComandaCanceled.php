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

class ComandaCanceled implements ShouldBroadcast
{
    use SerializesModels;

    public $comandaId;
    public $atencionId;
    public $estado;

    public function __construct(Comanda $comanda)
    {
        $this->comandaId  = $comanda->id;
        $this->atencionId = $comanda->atencion_id;
        $this->estado     = $comanda->estado; // "cancelada"
    }

    public function broadcastOn()
    {
        // Canal público por comanda; la vista show escucha solo su propia comanda
        return new Channel('comandas.'.$this->comandaId);
    }

    public function broadcastAs()
    {
        return 'ComandaCanceled';
    }

    public function broadcastWith()
    {
        return [
            'comandaId'  => $this->comandaId,
            'atencionId' => $this->atencionId,
            'estado'     => $this->estado,
        ];
    }
}