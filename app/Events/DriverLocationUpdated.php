<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orderId;
    public $latitude;
    public $longitude;
    public $speed;
    public $heading;
    public $batteryLevel;
    public $updatedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(
        $orderId,
        $latitude,
        $longitude,
        $speed = null,
        $heading = null,
        $batteryLevel = null
    ) {
        $this->orderId = $orderId;
        $this->latitude = (float) $latitude;
        $this->longitude = (float) $longitude;
        $this->speed = $speed !== null ? (float) $speed : null;
        $this->heading = $heading !== null ? (float) $heading : null;
        $this->batteryLevel = $batteryLevel !== null ? (int) $batteryLevel : null;
        $this->updatedAt = now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.tracking.' . $this->orderId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'DriverLocationUpdated';
    }
}
