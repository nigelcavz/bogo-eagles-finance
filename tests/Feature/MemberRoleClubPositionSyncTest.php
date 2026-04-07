<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberRoleClubPositionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_linked_member_club_position_syncs_when_user_role_changes(): void
    {
        $user = User::factory()->role('member')->create();
        $member = Member::factory()->create([
            'user_id' => $user->id,
            'club_position' => 'Member',
        ]);

        $user->update(['role' => 'treasurer']);

        $this->assertSame('Treasurer', $member->fresh()->club_position);

        $user->update(['role' => 'president']);

        $this->assertSame('President', $member->fresh()->club_position);

        $user->update(['role' => 'vice_president']);

        $this->assertSame('Vice President', $member->fresh()->club_position);

        $user->update(['role' => 'secretary']);

        $this->assertSame('Secretary', $member->fresh()->club_position);
    }

    public function test_admin_role_does_not_overwrite_existing_member_club_position(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create([
            'user_id' => $user->id,
            'club_position' => 'Treasurer',
        ]);

        $user->update(['role' => 'admin']);

        $this->assertSame('Treasurer', $member->fresh()->club_position);
    }

    public function test_sync_command_repairs_existing_non_admin_role_mismatches(): void
    {
        $officer = User::factory()->role('officer')->create();
        $officerMember = Member::factory()->create([
            'user_id' => $officer->id,
            'club_position' => 'Member',
        ]);

        $secretary = User::factory()->role('secretary')->create();
        $secretaryMember = Member::factory()->create([
            'user_id' => $secretary->id,
            'club_position' => 'Member',
        ]);

        $admin = User::factory()->role('admin')->create();
        $adminMember = Member::factory()->create([
            'user_id' => $admin->id,
            'club_position' => 'Member',
        ]);

        $this->artisan('members:sync-club-positions')
            ->expectsOutput('Synchronized 2 member club position(s).')
            ->assertExitCode(0);

        $this->assertSame('Officer', $officerMember->fresh()->club_position);
        $this->assertSame('Secretary', $secretaryMember->fresh()->club_position);
        $this->assertSame('Member', $adminMember->fresh()->club_position);
    }
}
