<?php

namespace App\Events;

use App\Models\Comanda;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComandaCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comanda;
    public $kanban_id;

    public function __construct(Comanda $comanda)
    {
        // Traemos mesa y mozo para la tarjeta
        $this->comanda  = $comanda->load(['atencion.mesa', 'atencion.mozo']);
        $this->kanban_id = 'comanda_' . $comanda->id;  // <-- clave única en el kanban
    }

    public function broadcastOn()
    {
        // Canal separado del de orders (delivery), así no mezclamos cosas
        return new Channel('kitchenTickets');
    }

    public function broadcastAs()
    {
        return 'comanda.created';
    }

    public function broadcastWith()
    {
        return [
            'ticket' => [
                'id'          => $this->kanban_id,             // ej. "comanda_57"
                'comanda_id'  => $this->comanda->id,
                'numero'      => $this->comanda->numero,
                'atencion_id' => $this->comanda->atencion_id,
                'mesa'        => optional($this->comanda->atencion->mesa)->nombre,
                'mozo'        => optional($this->comanda->atencion->mozo)->nombre,
                'status'      => 'created',                    // columna inicial: Recibido
                'total'       => (float) $this->comanda->total,
                'items'       => $this->comanda->items()->count(),
            ],
        ];
    }
}
