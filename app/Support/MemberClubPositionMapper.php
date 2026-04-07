<?php

namespace App\Support;

class MemberClubPositionMapper
{
    public static function forRole(?string $role): ?string
    {
        return match ($role) {
            'member' => 'Member',
            'officer' => 'Officer',
            'president' => 'President',
            'treasurer' => 'Treasurer',
            default => null,
        };
    }
}
