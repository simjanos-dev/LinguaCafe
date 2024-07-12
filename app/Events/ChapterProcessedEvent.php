<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChapterProcessedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $userUuid;
    public $chapters;

    public function __construct($userUuid, $chapters)
    {
        $this->chapters = json_encode($chapters);
        $this->userUuid = $userUuid;
    }

    public function broadcastOn() {
        return new PrivateChannel('chapter-processed.' . $this->userUuid);
    }
}
