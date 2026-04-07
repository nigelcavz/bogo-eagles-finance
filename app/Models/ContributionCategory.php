<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function monthlyBaseAmount(): float
    {
        return (float) ($this->default_amount ?? 0);
    }

    public function calculateMonthlyCoverageAmount(int $monthCount, mixed $paymentDate = null): string
    {
        $monthCount = max(0, $monthCount);
        $baseAmount = $this->monthlyBaseAmount();

        if ($monthCount === 12 && $paymentDate !== null && Carbon::parse($paymentDate)->month === 1) {
            return number_format($baseAmount * 10, 2, '.', '');
        }

        return number_format($baseAmount * $monthCount, 2, '.', '');
    }
}
