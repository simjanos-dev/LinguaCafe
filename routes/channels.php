<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('dictionary-import-progress.{userUuid}', function ($user, $userUuid) {
    return $user->uuid === $userUuid;
});

Broadcast::channel('chapters-word-count-calculated.{userUuid}', function ($user, $userUuid) {
    return $user->uuid === $userUuid;
});
