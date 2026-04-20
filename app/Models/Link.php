<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Link extends Model
{
    protected $fillable = [
        'original_url',
        'slug',
        'qr_code_svg',
        'total_clicks',
    ];

    protected $appends = ['full_short_url'];

    protected $casts = [
        'total_clicks' => 'integer',
    ];

    public function getFullShortUrlAttribute(): string
    {
        return config('app.url') . '/' . $this->slug;
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }
}