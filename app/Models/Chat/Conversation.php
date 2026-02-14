<?php

namespace App\Models\Chat;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Conversation extends Model
{
    protected $fillable = [
        'type',
        'entity_type',
        'entity_id',
    ];

    protected $casts = [
        'entity_id' => 'integer',
    ];

    // Conversation types
    public const TYPE_DIRECT = 'direct';
    public const TYPE_GROUP = 'group';
    public const TYPE_ENTITY = 'entity';

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

    public static function findOrCreateDirect(int $teamMember1Id, int $teamMember2Id): self
    {
        $existing = static::findDirectBetween($teamMember1Id, $teamMember2Id);

        if ($existing) {
            return $existing;
        }

        $conversation = static::create(['type' => self::TYPE_DIRECT]);
        $conversation->participants()->attach([$teamMember1Id, $teamMember2Id]);

        return $conversation;
    }

    public static function createGroup(array $teamMemberIds): self
    {
        $conversation = static::create(['type' => self::TYPE_GROUP]);
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
}
