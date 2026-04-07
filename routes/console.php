<?php

use App\Models\User;
use App\Support\MemberClubPositionMapper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('members:sync-club-positions', function () {
    $updatedCount = 0;

    User::query()
        ->with('member')
        ->whereIn('role', User::clubRoles())
        ->chunkById(100, function ($users) use (&$updatedCount) {
            foreach ($users as $user) {
                $member = $user->member;

                if (! $member) {
                    continue;
                }

                $clubPosition = MemberClubPositionMapper::forRole($user->role);

                if (! $clubPosition || $member->club_position === $clubPosition) {
                    continue;
                }

                $member->forceFill([
                    'club_position' => $clubPosition,
                ])->saveQuietly();

                $updatedCount++;
            }
        });

    $this->info("Synchronized {$updatedCount} member club position(s).");
})->purpose('Sync linked member club_position values with current non-admin user roles.');
