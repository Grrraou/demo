<?php

namespace App\Models\Chat;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Meeting extends Model
{
    protected $table = 'chat_meetings';

    protected $fillable = [
        'conversation_id',
        'started_by',
        'room_name',
        'room_token',
        'ended_at',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'started_by');
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    public function getJitsiUrl(?string $displayName = null): string
    {
        // Use the room token for privacy - only those with the token can join
        $roomId = $this->room_name . '-' . $this->room_token;
        
        $url = "https://meet.jit.si/{$roomId}";
        
        // Add user info if display name is provided
        if ($displayName) {
            $encodedName = rawurlencode($displayName);
            $url .= "#userInfo.displayName=\"{$encodedName}\"";
        }
        
        return $url;
    }

    public function getEmbedConfig(): array
    {
        return [
            'roomName' => $this->room_name . '-' . $this->room_token,
            'width' => '100%',
            'height' => 500,
            'parentNode' => null,
            'configOverwrite' => [
                'startWithAudioMuted' => true,
                'startWithVideoMuted' => false,
                'prejoinPageEnabled' => true,
            ],
            'interfaceConfigOverwrite' => [
                'TOOLBAR_BUTTONS' => [
                    'microphone', 'camera', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'chat', 'settings',
                    'raisehand', 'videoquality', 'tileview',
                ],
                'SHOW_JITSI_WATERMARK' => false,
                'SHOW_WATERMARK_FOR_GUESTS' => false,
            ],
        ];
    }

    public static function createForConversation(Conversation $conversation, int $startedBy): self
    {
        // Generate a unique room name based on conversation
        $roomName = 'kaizen-conv-' . $conversation->id;
        
        // Generate a secure token to make the room private
        $roomToken = Str::random(32);

        return static::create([
            'conversation_id' => $conversation->id,
            'started_by' => $startedBy,
            'room_name' => $roomName,
            'room_token' => $roomToken,
        ]);
    }
}
