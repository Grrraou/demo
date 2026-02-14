<?php

use App\Models\Chat\Conversation;
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

// Private channel for conversations
// Only participants of the conversation can subscribe
Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    
    if (!$conversation) {
        return false;
    }

    return $conversation->hasParticipant($user->id);
});

// Optional: User-specific channel for notifications
Broadcast::channel('App.Models.TeamMember.{id}', function ($user, int $id) {
    return $user->id === $id;
});
