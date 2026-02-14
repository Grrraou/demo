<?php

namespace App\Models\Chat;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'type',
        'entity_type',
        'entity_id',
        'meeting_token',
        'created_by',
        'archived_at',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'created_by' => 'integer',
        'archived_at' => 'datetime',
    ];

    // Conversation types
    public const TYPE_DIRECT = 'direct';
    public const TYPE_GROUP = 'group';
    public const TYPE_ENTITY = 'entity';

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            if (!$conversation->meeting_token) {
                $conversation->meeting_token = Str::random(32);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'created_by');
    }

    public function isCreator(int $teamMemberId): bool
    {
        return $this->created_by === $teamMemberId;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    public function unarchive(): void
    {
        $this->update(['archived_at' => null]);
    }

    public function getJitsiUrl(?string $displayName = null): string
    {
        $roomId = 'kaizen-' . $this->id . '-' . $this->meeting_token;
        $jitsiHost = config('services.jitsi.host', 'http://localhost:8443');
        
        $url = rtrim($jitsiHost, '/') . '/' . $roomId;
        
        // Add user display name if provided
        if ($displayName) {
            $encodedName = rawurlencode($displayName);
            $url .= "#userInfo.displayName=\"{$encodedName}\"";
        }
        
        return $url;
    }

    public function getDisplayName(int $excludeTeamMemberId): string
    {
        // If conversation has a name, use it
        if ($this->name) {
            return $this->name;
        }

        // Otherwise, use participant names (existing behavior)
        $otherParticipants = $this->participants->where('id', '!=', $excludeTeamMemberId);
        
        if ($this->type === self::TYPE_DIRECT) {
            return $otherParticipants->first()?->name ?? 'Unknown';
        }
        
        return $otherParticipants->pluck('name')->join(', ');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'conversation_team_member')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    public function hasParticipant(int $teamMemberId): bool
    {
        return $this->participants()->where('team_member_id', $teamMemberId)->exists();
    }

    public function otherParticipants(int $excludeTeamMemberId): BelongsToMany
    {
        return $this->participants()->where('team_member_id', '!=', $excludeTeamMemberId);
    }

    public function markAsRead(int $teamMemberId): void
    {
        $this->participants()->updateExistingPivot($teamMemberId, [
            'last_read_at' => now(),
        ]);
    }

    public function unreadCountFor(int $teamMemberId): int
    {
        $pivot = $this->participants()
            ->where('team_member_id', $teamMemberId)
            ->first()
            ?->pivot;

        if (!$pivot || !$pivot->last_read_at) {
            return $this->messages()->where('team_member_id', '!=', $teamMemberId)->count();
        }

        return $this->messages()
            ->where('team_member_id', '!=', $teamMemberId)
            ->where('created_at', '>', $pivot->last_read_at)
            ->count();
    }

    public static function findDirectBetween(int $teamMember1Id, int $teamMember2Id): ?self
    {
        return static::where('type', self::TYPE_DIRECT)
            ->whereHas('participants', fn ($q) => $q->where('team_member_id', $teamMember1Id))
            ->whereHas('participants', fn ($q) => $q->where('team_member_id', $teamMember2Id))
            ->first();
    }

    public static function findOrCreateDirect(int $teamMember1Id, int $teamMember2Id, ?string $name = null): self
    {
        $existing = static::findDirectBetween($teamMember1Id, $teamMember2Id);

        if ($existing) {
            // Update name if provided and conversation doesn't have one
            if ($name && !$existing->name) {
                $existing->update(['name' => $name]);
            }
            return $existing;
        }

        $conversation = static::create([
            'type' => self::TYPE_DIRECT,
            'name' => $name,
            'created_by' => $teamMember1Id, // First user is the creator
        ]);
        $conversation->participants()->attach([$teamMember1Id, $teamMember2Id]);

        return $conversation;
    }

    public static function createGroup(array $teamMemberIds, ?string $name = null, ?int $createdBy = null): self
    {
        $conversation = static::create([
            'type' => self::TYPE_GROUP,
            'name' => $name,
            'created_by' => $createdBy ?? $teamMemberIds[0] ?? null,
        ]);
        $conversation->participants()->attach($teamMemberIds);

        return $conversation;
    }

    public static function findOrCreateForEntity(string $entityType, int $entityId, array $teamMemberIds): self
    {
        $existing = static::where('type', self::TYPE_ENTITY)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();

        if ($existing) {
            // Sync participants for entity-bound conversations
            $existing->participants()->syncWithoutDetaching($teamMemberIds);
            return $existing;
        }

        $conversation = static::create([
            'type' => self::TYPE_ENTITY,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
        $conversation->participants()->attach($teamMemberIds);

        return $conversation;
    }

    public function addParticipants(array $teamMemberIds, ?int $invitedById = null): void
    {
        // Get names of new participants for logging
        $newParticipants = TeamMember::whereIn('id', $teamMemberIds)
            ->whereNotIn('id', $this->participants->pluck('id'))
            ->get();
        
        $this->participants()->syncWithoutDetaching($teamMemberIds);
        $this->touch();

        // Log system messages for each new participant
        $inviterName = $invitedById ? TeamMember::find($invitedById)?->name : null;
        
        foreach ($newParticipants as $participant) {
            $body = $inviterName 
                ? "{$inviterName} added {$participant->name} to the conversation"
                : "{$participant->name} joined the conversation";
            
            Message::createSystemMessage(
                $this->id,
                Message::TYPE_USER_JOINED,
                $body,
                $invitedById
            );
        }
    }

    public function removeParticipant(int $teamMemberId): bool
    {
        // Creator can't leave - they must archive instead
        if ($this->isCreator($teamMemberId)) {
            return false;
        }

        // Must be a participant to leave
        if (!$this->hasParticipant($teamMemberId)) {
            return false;
        }

        // Get name before removing for logging
        $participantName = TeamMember::find($teamMemberId)?->name ?? 'Someone';

        $this->participants()->detach($teamMemberId);
        $this->touch();
        
        // Log system message
        Message::createSystemMessage(
            $this->id,
            Message::TYPE_USER_LEFT,
            "{$participantName} left the conversation",
            $teamMemberId
        );
        
        return true;
    }

    public function rename(?string $newName, int $renamedById): void
    {
        $oldName = $this->name;
        $this->update(['name' => $newName]);

        $renamerName = TeamMember::find($renamedById)?->name ?? 'Someone';
        
        if ($newName && $oldName) {
            $body = "{$renamerName} renamed the conversation from \"{$oldName}\" to \"{$newName}\"";
        } elseif ($newName) {
            $body = "{$renamerName} named the conversation \"{$newName}\"";
        } else {
            $body = "{$renamerName} removed the conversation name";
        }

        Message::createSystemMessage(
            $this->id,
            Message::TYPE_RENAMED,
            $body,
            $renamedById
        );
    }

    public function canLeave(int $teamMemberId): bool
    {
        // Creator can't leave - they must archive instead
        if ($this->isCreator($teamMemberId)) {
            return false;
        }

        return $this->hasParticipant($teamMemberId);
    }

    public function canArchive(int $teamMemberId): bool
    {
        // Only creator can archive
        return $this->isCreator($teamMemberId);
    }
}
