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

class OrderStatusUpdatedDistributor implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $payload;

    public function __construct(Order $order)
    {
        $this->payload = [
            'order' => ['id' => $order->id],
            'status_name' => $order->status_name,
            'active_step' => $order->active_step,
            'distributor_id' => $order->distributor_id,
        ];
    }

    public function broadcastOn()
    {
        $channels = [];
        if (!empty($this->payload['distributor_id'])) {
            $channels[] = new Channel('orders.distributor.' . $this->payload['distributor_id']);
        }
        $channels[] = new Channel('orders.admin');
        return $channels;
    }

    public function broadcastAs()
    {
        return 'OrderStatusUpdatedDistributor';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}