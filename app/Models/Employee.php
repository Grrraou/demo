<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'employees';

    protected static function newFactory(): \Database\Factories\EmployeeFactory
    {
        return \Database\Factories\EmployeeFactory::new();
    }

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
        return $this->belongsToMany(Role::class, 'role_employee');
    }

    public function ownedCompanies(): BelongsToMany
    {
        return $this->belongsToMany(OwnedCompany::class, 'owned_company_employee');
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
}
