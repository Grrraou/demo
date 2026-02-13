<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OwnedCompany extends Model
{
    /** Logos live under public/company-logos/ so they can be committed. */
    public const LOGO_DIR = 'company-logos';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'logo',
    ];

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset(self::LOGO_DIR . '/' . basename($this->logo));
    }

    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(TeamMember::class, 'owned_company_team_member');
    }

    public function customerCompanies(): BelongsToMany
    {
        return $this->belongsToMany(CustomerCompany::class, 'customer_company_owned_company');
    }
}
