<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_user_role_management_pages(): void
    {
        $admin = User::factory()->role('admin')->create();
        $managedUser = User::factory()->role('member')->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('User Role Management')
            ->assertSee($managedUser->email);

        $this->actingAs($admin)
            ->get(route('users.edit', $managedUser))
            ->assertOk()
            ->assertSee('Edit User Role')
            ->assertSee($managedUser->email);
    }

    public function test_non_admin_cannot_access_user_role_management(): void
    {
        $treasurer = User::factory()->role('treasurer')->create();
        $managedUser = User::factory()->role('member')->create();

        $this->actingAs($treasurer)
            ->get(route('users.index'))
            ->assertForbidden();

        $this->actingAs($treasurer)
            ->put(route('users.update-role', $managedUser), [
                'role' => 'officer',
            ])
            ->assertForbidden();

        $this->actingAs($treasurer)
            ->patch(route('users.update-status', $managedUser), [
                'is_active' => false,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_role_and_linked_member_club_position_syncs(): void
    {
        $admin = User::factory()->role('admin')->create();
        $managedUser = User::factory()->role('member')->create();
        $member = Member::factory()->create([
            'user_id' => $managedUser->id,
            'club_position' => 'Member',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('users.update-role', $managedUser), [
                'role' => 'treasurer',
            ]);

        $response->assertRedirect(route('users.edit', $managedUser));

        $this->assertSame('treasurer', $managedUser->fresh()->role);
        $this->assertSame('Treasurer', $member->fresh()->club_position);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user_role_updated',
            'module' => 'users',
            'record_id' => $managedUser->id,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_user_account_with_linked_member_sync(): void
    {
        $admin = User::factory()->role('admin')->create();
        $managedUser = User::factory()->role('member')->create([
            'is_active' => true,
        ]);
        $member = Member::factory()->create([
            'user_id' => $managedUser->id,
            'membership_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->patch(route('users.update-status', $managedUser), [
                'is_active' => false,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertFalse($managedUser->fresh()->is_active);
        $this->assertSame('inactive', $member->fresh()->membership_status);

        $this->actingAs($admin)
            ->patch(route('users.update-status', $managedUser), [
                'is_active' => true,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue($managedUser->fresh()->is_active);
        $this->assertSame('active', $member->fresh()->membership_status);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'users',
            'record_id' => $managedUser->id,
            'action' => 'user_account_deactivated',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'module' => 'users',
            'record_id' => $managedUser->id,
            'action' => 'user_account_reactivated',
        ]);
    }
}
