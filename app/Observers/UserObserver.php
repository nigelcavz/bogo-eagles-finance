<?php

namespace App\Observers;

use App\Models\User;
use App\Support\MemberClubPositionMapper;

class UserObserver
{
    public function saved(User $user): void
    {
        if (! $user->wasChanged('role')) {
            return;
        }

        $clubPosition = MemberClubPositionMapper::forRole($user->role);

        if (! $clubPosition) {
            return;
        }

        $member = $user->member()->first();

        if (! $member || $member->club_position === $clubPosition) {
            return;
        }

        $member->forceFill([
            'club_position' => $clubPosition,
        ])->saveQuietly();
    }
}
