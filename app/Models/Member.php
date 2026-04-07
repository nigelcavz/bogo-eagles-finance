<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_code',
        'club_position',
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'gender',
        'birthdate',
        'contact_number',
        'address',
        'membership_status',
        'joined_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'joined_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function contributionCoverages(): HasMany
    {
        return $this->hasMany(ContributionCoverage::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = [
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ];

        return trim(implode(' ', array_filter($parts)));
    }
}
