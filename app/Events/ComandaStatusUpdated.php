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

class ComandaStatusUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $comanda;

    public function __construct(Comanda $comanda)
    {
        // enviamos los datos actualizados de la comanda
        $this->comanda = $comanda->fresh();
    }

    public function broadcastOn()
    {
        // usa el mismo canal que tu evento .comanda.created
        return new Channel('kitchenTickets');
    }

    public function broadcastAs()
    {
        // el nombre del evento que el frontend escuchará
        return 'comanda.updated';
    }

    public function broadcastWith()
    {
        // los datos que el frontend necesita para repintar la tarjeta
        return [
            'data' => [
                'id' => $this->comanda->id,
                'numero' => $this->comanda->numero,
                'estado' => $this->mapComandaToKanban($this->comanda->estado),
                'mesa'   => optional($this->comanda->atencion->mesa)->nombre,
                'mozo'   => optional($this->comanda->atencion->mozo)->nombre,
                'total' => $this->comanda->total,
                'estimated_minutes' => $this->comanda->estimated_minutes,
                'estimated_ready_at' => optional($this->comanda->estimated_ready_at)->toDateTimeString(),
            ]
        ];
    }

    private function mapComandaToKanban($status)
    {
        // Normalizamos el valor recibido
        $s = trim(strtolower($status));

        switch ($s) {
            case 'enviada':
                return 'created';      // 📥 Recibido en cocina
            case 'cocinando':
                return 'processing';   // 🔥 En preparación
            case 'lista':
                return 'shipped';      // 🍽️ Listo para entregar
            case 'servida':
                return 'completed';    // ✅ Entregado al cliente (fuera del kanban)
            case 'cancelada':
                return 'cancelled';    // ❌ Pedido cancelado (fuera del kanban)
            default:
                return 'created';      // Valor por defecto
        }
    }
}