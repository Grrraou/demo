<?php

namespace App\Models\Chat;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    // Message types
    public const TYPE_MESSAGE = 'message';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_USER_JOINED = 'user_joined';
    public const TYPE_USER_LEFT = 'user_left';
    public const TYPE_RENAMED = 'renamed';

    protected $fillable = [
        'conversation_id',
        'team_member_id',
        'type',
        'body',
        'meeting_id',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'team_member_id' => 'integer',
        'meeting_id' => 'integer',
    ];

    protected $attributes = [
        'type' => self::TYPE_MESSAGE,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function isMeetingMessage(): bool
    {
        return $this->meeting_id !== null;
    }

    public function isSystemMessage(): bool
    {
        return $this->type !== self::TYPE_MESSAGE;
    }

    public static function createSystemMessage(int $conversationId, string $type, string $body, ?int $actorId = null): self
    {
        return static::create([
            'conversation_id' => $conversationId,
            'team_member_id' => $actorId,
            'type' => $type,
            'body' => $body,
        ]);
    }
}
