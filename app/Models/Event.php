<?php

namespace App\Models;

use App\Models\Chat\Conversation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Event extends Model
{
    public const TYPE_USER = 'user';
    public const TYPE_AUTO = 'auto';
    public const TYPE_DAYS_OFF = 'days_off';

    public const STATUS_INVITED = 'invited';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    public const COLORS = [
        'blue' => '#3B82F6',
        'green' => '#22C55E',
        'red' => '#EF4444',
        'yellow' => '#EAB308',
        'purple' => '#A855F7',
        'pink' => '#EC4899',
        'indigo' => '#6366F1',
        'teal' => '#14B8A6',
        'orange' => '#F97316',
        'gray' => '#6B7280',
    ];

    protected $fillable = [
        'owned_company_id',
        'title',
        'description',
        'type',
        'start_at',
        'end_at',
        'all_day',
        'color',
        'conversation_id',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'all_day' => 'boolean',
    ];

    protected $attributes = [
        'type' => self::TYPE_USER,
        'all_day' => false,
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'created_by');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'event_team_member')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function invitedParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', self::STATUS_INVITED);
    }

    public function acceptedParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', self::STATUS_ACCEPTED);
    }

    public function declinedParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', self::STATUS_DECLINED);
    }

    public function isCreator(int $teamMemberId): bool
    {
        return $this->created_by === $teamMemberId;
    }

    public function isParticipant(int $teamMemberId): bool
    {
        return $this->participants()->where('team_member_id', $teamMemberId)->exists();
    }

    public function canView(int $teamMemberId): bool
    {
        return $this->isCreator($teamMemberId) || $this->isParticipant($teamMemberId);
    }

    public function canEdit(int $teamMemberId): bool
    {
        return $this->isCreator($teamMemberId);
    }

    public function invite(array $teamMemberIds): void
    {
        $data = [];
        foreach ($teamMemberIds as $id) {
            if ($id != $this->created_by && !$this->isParticipant($id)) {
                $data[$id] = ['status' => self::STATUS_INVITED];
            }
        }
        if (!empty($data)) {
            $this->participants()->attach($data);
        }
    }

    public function respond(int $teamMemberId, string $status): void
    {
        if (in_array($status, [self::STATUS_ACCEPTED, self::STATUS_DECLINED])) {
            $this->participants()->updateExistingPivot($teamMemberId, ['status' => $status]);
        }
    }

    public function getColorHexAttribute(): string
    {
        return self::COLORS[$this->color] ?? self::COLORS['blue'];
    }

    public function createLinkedConversation(): ?Conversation
    {
        if ($this->conversation_id) {
            return $this->conversation;
        }

        $participantIds = $this->participants()->pluck('team_members.id')->toArray();
        $participantIds[] = $this->created_by;
        $participantIds = array_unique($participantIds);

        $conversation = Conversation::create([
            'name' => $this->title,
            'type' => 'group',
            'meeting_token' => Str::random(20),
            'created_by' => $this->created_by,
        ]);

        $conversation->participants()->attach($participantIds);

        $this->update(['conversation_id' => $conversation->id]);

        return $conversation;
    }
}
