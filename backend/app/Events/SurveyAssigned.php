<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;

class SurveyAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public array $payload;

    public function __construct(int $userId, array $payload)
    {
        $this->userId = $userId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'survey.assigned';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
