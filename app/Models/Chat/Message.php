<?php

namespace App\Models\Chat;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'team_member_id',
        'body',
        'meeting_id',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'team_member_id' => 'integer',
        'meeting_id' => 'integer',
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
}
