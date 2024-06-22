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

    public $importedRecords;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($importedRecords)
    {
        $this->importedRecords = $importedRecords;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        $userId = Auth::user()->id;

        return new PrivateChannel('dictionary-import-progress.' . $userId);
    }
}
