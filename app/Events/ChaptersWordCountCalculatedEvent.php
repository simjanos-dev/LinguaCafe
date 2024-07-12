<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ChaptersWordCountCalculatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    
    private $userUuid;
    public $wordCounts;

    public function __construct($userUuid, $wordCounts)
    {
        $this->wordCounts = $wordCounts;
        $this->userUuid = $userUuid;
    }

    public function broadcastOn() {
        return new PrivateChannel('chapters-word-count-calculated.' . $this->userUuid);
    }
}
