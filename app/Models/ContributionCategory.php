<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ContributionCategory extends Model
{
    use HasFactory;

    public const MONTHLY_DUES_NAME = 'Monthly Dues/Contributions';
    public const OTHER_NAME = 'Other';

    protected $fillable = [
        'name',
        'description',
        'default_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function requiresMonthlyCoverage(): bool
    {
        return $this->name === self::MONTHLY_DUES_NAME;
    }

    public function requiresOtherDescription(): bool
    {
        return $this->name === self::OTHER_NAME;
    }
}
