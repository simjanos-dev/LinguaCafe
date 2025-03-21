<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChapterStateUpdatedEvent implements ShouldBroadcastNow
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
        return new PrivateChannel('chapter-status-update.' . $this->userUuid);
    }
}
