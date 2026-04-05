<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionCoverage extends Model
{
    use HasFactory;

    protected $fillable = [
        'contribution_id',
        'member_id',
        'coverage_year',
        'coverage_month',
        'coverage_label',
    ];

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}