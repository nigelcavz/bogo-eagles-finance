<?php

namespace App\Support;

use App\Models\Member;
use App\Models\User;

class MemberAccountStatusSynchronizer
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public static function membershipStatusFor(bool $active): string
    {
        return $active ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
    }

    public static function userActiveForMembershipStatus(string $membershipStatus): bool
    {
        return $membershipStatus === self::STATUS_ACTIVE;
    }

    public static function syncMember(Member $member, bool $active): void
    {
        $member->forceFill([
            'membership_status' => self::membershipStatusFor($active),
        ])->save();

        if ($member->user) {
            $member->user->forceFill([
                'is_active' => $active,
            ])->save();
        }
    }

    public static function syncUser(User $user, bool $active): void
    {
        $user->forceFill([
            'is_active' => $active,
        ])->save();

        if ($user->member) {
            $user->member->forceFill([
                'membership_status' => self::membershipStatusFor($active),
            ])->save();
        }
    }
}
