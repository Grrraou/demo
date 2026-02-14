<?php

namespace App\Models;

use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TeamMember extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'team_members';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_team_member');
    }

    public function ownedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(OwnedCompany::class, 'owned_company_team_member');
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_team_member')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'team_member_id');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->roles()->whereHas('permissions', fn ($q) => $q->where('slug', $slug))->exists();
    }

    public function canEditCustomers(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.customers');
    }

    public function canCreateArticles(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('create.articles');
    }

    public function canEditArticles(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.articles');
    }

    public function canViewInventory(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.inventory');
    }

    public function canEditInventory(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.inventory');
    }

    // Sales permissions
    public function canViewSales(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.sales');
    }

    public function canEditSales(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.sales');
    }

    // Customer permissions
    public function canViewCustomers(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.customers');
    }

    // Leads permissions
    public function canViewLeads(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.leads');
    }

    public function canEditLeads(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.leads');
    }

    // Calendar permissions
    public function canViewCalendar(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.calendar');
    }

    public function canEditCalendar(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.calendar');
    }

    // Accounting permissions
    public function canViewAccounting(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('view.accounting');
    }

    public function canEditAccounting(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('edit.accounting');
    }

    public function canClosePeriods(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('close.periods');
    }

    public function canAdminAccounting(): bool
    {
        return $this->roles()->where('slug', 'admin')->exists()
            || $this->hasPermission('admin.accounting');
    }
}
