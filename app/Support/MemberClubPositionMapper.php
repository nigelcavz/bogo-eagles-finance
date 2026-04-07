<?php

namespace App\Support;

use App\Models\User;

class MemberClubPositionMapper
{
    public static function forRole(?string $role): ?string
    {
        return match ($role) {
            User::ROLE_PRESIDENT => 'President',
            User::ROLE_VICE_PRESIDENT => 'Vice President',
            User::ROLE_SECRETARY => 'Secretary',
            User::ROLE_TREASURER => 'Treasurer',
            User::ROLE_OFFICER => 'Officer',
            User::ROLE_MEMBER => 'Member',
            default => null,
        };
    }
}
