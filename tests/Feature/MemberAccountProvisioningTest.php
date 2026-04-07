<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MemberAccountProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_member_with_linked_member_account(): void
    {
        $admin = User::factory()->role('admin')->create();

        $response = $this->actingAs($admin)->post(route('members.store'), [
            'member_code' => 'BEC-001',
            'email' => 'new.member@example.com',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'membership_status' => 'active',
        ]);

        $response->assertRedirect(route('members.index'));
        $response->assertSessionHas('new_member_account');

        $member = Member::firstOrFail();
        $user = User::where('email', 'new.member@example.com')->firstOrFail();

        $this->assertSame($user->id, $member->user_id);
        $this->assertSame('member', $user->role);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->password);
        $this->assertFalse(Hash::check('password', $user->password));

        $flash = session('new_member_account');
        $this->assertSame('new.member@example.com', $flash['email']);
        $this->assertTrue(Hash::check($flash['temporary_password'], $user->password));
    }

    public function test_secretary_can_access_member_creation_routes(): void
    {
        $secretary = User::factory()->role('secretary')->create();

        $this->actingAs($secretary)
            ->post(route('members.store'), [
                'email' => 'secretary-added@example.com',
                'first_name' => 'Leo',
                'last_name' => 'Rivera',
                'membership_status' => 'active',
            ])
            ->assertRedirect(route('members.index'));
    }

    public function test_member_creation_rolls_back_when_email_is_not_unique(): void
    {
        $admin = User::factory()->role('admin')->create();
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->from(route('members.create'))
            ->actingAs($admin)
            ->post(route('members.store'), [
                'email' => 'duplicate@example.com',
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'membership_status' => 'active',
            ]);

        $response->assertRedirect(route('members.create'));
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_temporary_password_is_required_to_be_changed_after_first_login(): void
    {
        $user = User::factory()->create([
            'email' => 'member.login@example.com',
            'password' => Hash::make('TempPass123'),
            'must_change_password' => true,
            'role' => 'member',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'member.login@example.com',
            'password' => 'TempPass123',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->actingAs($user)
            ->get(route('members.index'))
            ->assertRedirect(route('profile.edit'));

        $this->actingAs($user)
            ->put(route('password.update'), [
                'password' => 'NewSecurePass123!',
                'password_confirmation' => 'NewSecurePass123!',
            ])
            ->assertRedirect();

        $this->assertFalse($user->fresh()->must_change_password);
    }

    public function test_inactive_account_cannot_log_in_and_profile_delete_route_is_disabled(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive.member@example.com',
            'password' => Hash::make('TempPass123'),
            'is_active' => false,
            'role' => 'member',
        ]);

        $this->post(route('login'), [
            'email' => 'inactive.member@example.com',
            'password' => 'TempPass123',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertFalse(Route::has('profile.destroy'));
    }
}
