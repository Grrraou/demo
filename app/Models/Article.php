<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    public const IMAGE_DIR = 'blog-images';

    protected $fillable = [
        'owned_company_id',
        'author_id',
        'name',
        'slug',
        'keywords',
        'content',
        'image',
        'draft',
        'public',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'draft' => 'boolean',
            'public' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function ownedCompany(): BelongsTo
    {
        return $this->belongsTo(OwnedCompany::class, 'owned_company_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'author_id');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset(self::IMAGE_DIR . '/' . basename($this->image));
    }

    /** Article is considered published when not draft and published_at is set. */
    public function isPublished(): bool
    {
        return ! $this->draft && $this->published_at !== null;
    }

    /** Public URL for viewing the article: /blog/{company_slug}/{slug} */
    public function publicUrl(): string
    {
        $this->loadMissing('ownedCompany:id,slug');
        return url('/blog/' . $this->ownedCompany->slug . '/' . $this->slug);
    }
}
