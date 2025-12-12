<?php

declare(strict_types=1);

namespace Cartino\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'collection',
        'slug',
        'title',
        'data',
        'status',
        'published_at',
        'author_id',
        'locale',
        'parent_id',
        'order',
    ];

    protected $casts = [
        'data' => 'array',
        'published_at' => 'datetime',
        'order' => 'integer',
    ];

    /**
     * Get the author of this entry
     */
    public function author()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'author_id');
    }

    /**
     * Get the parent entry
     */
    public function parent()
    {
        return $this->belongsTo(Entry::class, 'parent_id');
    }

    /**
     * Get child entries
     */
    public function children()
    {
        return $this->hasMany(Entry::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get a specific data value from the entry
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Set a specific data value in the entry
     */
    public function set(string $key, mixed $value): self
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;

        return $this;
    }

    /**
     * Scope to filter by collection
     */
    public function scopeInCollection($query, string $collection)
    {
        return $query->where('collection', $collection);
    }

    /**
     * Scope to get published entries
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope to get draft entries
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get scheduled entries
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('published_at', '>', now());
    }

    /**
     * Scope to filter by locale
     */
    public function scopeInLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Check if entry is published
     */
    public function isPublished(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Get the full URL for the entry
     */
    public function url(): string
    {
        return "/{$this->collection}/{$this->slug}";
    }
}
