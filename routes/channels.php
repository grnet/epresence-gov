<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\PrivateChannel;
use App\Conference;

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

Broadcast::channel('conference-user-{user_id}', function ($user,$user_id) {

    return  $user->id == $user_id ? true : false;
});

Broadcast::channel('manage-conference-{conference_id}', function ($user,$conference_id) {

    $conference = Conference::findOrFail($conference_id);

    return  $user->hasAdminAccessToConference($conference);
});




