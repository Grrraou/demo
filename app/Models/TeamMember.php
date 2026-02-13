<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
