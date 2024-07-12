<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class DictionaryImportProgressedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $userUuid;
    public $importedRecords;

    public function __construct($userUuid, $importedRecords)
    {
        $this->importedRecords = $importedRecords;
        $this->userUuid = $userUuid;
    }

    public function broadcastOn() {
        return new PrivateChannel('dictionary-import-progress.' . $this->userUuid);
    }
}
