<?php

namespace App\Jobs\Chat;

use App\Events\Chat\MessageSent;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Notifications\Chat\NewMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $senderId,
        public string $body
    ) {}

    public function handle(): void
    {
        // Create the message
        $message = Message::create([
            'conversation_id' => $this->conversationId,
            'team_member_id' => $this->senderId,
            'body' => $this->body,
        ]);

        // Load relationships for broadcasting
        $message->load('sender');

        // Broadcast the message to all participants
        broadcast(new MessageSent($message))->toOthers();

        // Send database notifications to other participants
        $conversation = Conversation::find($this->conversationId);
        
        $conversation->otherParticipants($this->senderId)
            ->get()
            ->each(function ($participant) use ($message) {
                $participant->notify(new NewMessageNotification($message));
            });
    }
}
