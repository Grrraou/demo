<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    // Lead statuses for the Kanban board
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_PROPOSAL = 'proposal';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_NEW => ['label' => 'New', 'color' => 'gray'],
        self::STATUS_CONTACTED => ['label' => 'Contacted', 'color' => 'blue'],
        self::STATUS_QUALIFIED => ['label' => 'Qualified', 'color' => 'indigo'],
        self::STATUS_PROPOSAL => ['label' => 'Proposal', 'color' => 'purple'],
        self::STATUS_NEGOTIATION => ['label' => 'Negotiation', 'color' => 'amber'],
        self::STATUS_WON => ['label' => 'Won', 'color' => 'green'],
        self::STATUS_LOST => ['label' => 'Lost', 'color' => 'red'],
    ];

    protected $fillable = [
        'owned_company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'status',
        'position',
        'value',
        'notes',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'position' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
        'position' => 0,
    ];

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

    public static function getActiveStatuses(): array
    {
        // Return statuses excluding won/lost for the main kanban view
        return array_filter(self::STATUSES, fn($key) => !in_array($key, [self::STATUS_WON, self::STATUS_LOST]), ARRAY_FILTER_USE_KEY);
    }
}
