<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreatedDistributor implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $payload;

    public function __construct(Order $order)
    {
        // Prepara solo lo necesario para pintar la tarjeta
        $this->payload = [
            'order' => [
                'id' => $order->id,
                'status_name' => $order->status_name,
                'active_step' => $order->active_step,
                'date_estimated_format' => $order->date_estimated_format,
                'data_totals' => [
                    'total_a_pagar' => data_get($order->data_totals, 'total_a_pagar', '0.00'),
                ],
                'created_at' => optional($order->created_at)->format('Y-m-d H:i:s'),
                'shipping_address' => $order->relationLoaded('shipping_address') || $order->shipping_address
                    ? [
                        'address'   => optional($order->shipping_address)->address,
                        'latitude'  => optional($order->shipping_address)->latitude,
                        'longitude' => optional($order->shipping_address)->longitude,
                    ]
                    : null,
                'distributor_id' => $order->distributor_id,
            ],
        ];
    }

    public function broadcastOn()
    {
        $channels = [];
        if (!empty($this->payload['order']['distributor_id'])) {
            $channels[] = new Channel('orders.distributor.' . $this->payload['order']['distributor_id']);
        }
        // Canal para admins (todas)
        $channels[] = new Channel('orders.admin');

        return $channels;
    }

    public function broadcastAs()
    {
        return 'OrderCreatedDistributor';
    }

    // Opcional: para controlar el payload exacto
    public function broadcastWith()
    {
        return $this->payload;
    }
}