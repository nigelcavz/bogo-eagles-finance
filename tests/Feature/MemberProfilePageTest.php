<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_officer_can_open_member_detail_page(): void
    {
        $president = User::factory()->role('president')->create();
        $memberUser = User::factory()->role('member')->create([
            'email' => 'member.profile@example.com',
        ]);
        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'first_name' => 'Ramon',
            'last_name' => 'Dela Cruz',
        ]);

        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 700.00,
            'payment_date' => '2026-04-07',
            'status' => 'active',
            'created_by' => $president->id,
        ]);

        ContributionCoverage::create([
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 4,
            'coverage_label' => 'Apr 2026',
        ]);

        $this->actingAs($president)
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('Member Detail')
            ->assertSee('Profile Overview')
            ->assertSee('Account Information')
            ->assertSee('Apr 2026')
            ->assertSee('member.profile@example.com')
            ->assertSee('₱700.00');
    }

    public function test_non_admin_user_can_view_their_own_self_service_page(): void
    {
        $memberUser = User::factory()->role('officer')->create();
        $otherMemberUser = User::factory()->role('member')->create();

        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'first_name' => 'Nina',
            'last_name' => 'Reyes',
        ]);

        $otherMember = Member::factory()->create([
            'user_id' => $otherMemberUser->id,
            'first_name' => 'Joel',
            'last_name' => 'Tan',
        ]);

        $this->actingAs($memberUser)
            ->get(route('members.self'))
            ->assertOk()
            ->assertSee('Profile')
            ->assertSee($member->full_name)
            ->assertDontSee($otherMember->full_name);

        $this->actingAs($memberUser)
            ->get(route('members.show', $otherMember))
            ->assertForbidden();
    }

    public function test_member_profile_shows_november_and_december_as_discounted_for_january_full_year_dues(): void
    {
        $treasurer = User::factory()->role('treasurer')->create();
        $memberUser = User::factory()->role('member')->create();
        $member = Member::factory()->create([
            'user_id' => $memberUser->id,
            'first_name' => 'Test',
            'last_name' => 'Member',
        ]);

        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
            'default_amount' => 700,
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 7000.00,
            'payment_date' => '2026-01-20',
            'coverage_type' => 'monthly',
            'status' => 'active',
            'created_by' => $treasurer->id,
        ]);

        foreach (range(1, 12) as $month) {
            ContributionCoverage::create([
                'contribution_id' => $contribution->id,
                'member_id' => $member->id,
                'coverage_year' => 2026,
                'coverage_month' => $month,
                'coverage_label' => now()->setMonth($month)->startOfMonth()->format('M') . ' 2026',
            ]);
        }

        $this->actingAs($treasurer)
            ->get(route('members.show', $member))
            ->assertOk()
            ->assertSee('Nov 2026')
            ->assertSee('Dec 2026')
            ->assertSee('Discounted')
            ->assertSee('January full-year 2-month discount')
            ->assertDontSee('583.33');
    }

    public function test_secretary_can_open_member_directory_pages_but_officer_cannot(): void
    {
        $secretary = User::factory()->role('secretary')->create();
        $officer = User::factory()->role('officer')->create();
        $member = Member::factory()->create();

        $this->actingAs($secretary)
            ->get(route('members.show', $member))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('members.show', $member))
            ->assertForbidden();
    }

    public function test_member_self_service_page_handles_missing_linked_member_gracefully(): void
    {
        $memberUser = User::factory()->role('member')->create();

        $this->actingAs($memberUser)
            ->get(route('members.self'))
            ->assertOk()
            ->assertSee('No linked member profile');
    }

    public function test_admin_cannot_open_self_service_member_profile_page(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->get(route('members.self'))
            ->assertForbidden();
    }

    public function test_authorized_officers_can_update_member_active_status(): void
    {
        $president = User::factory()->role('president')->create();
        $vicePresident = User::factory()->role('vice_president')->create();
        $secretary = User::factory()->role('secretary')->create();
        $treasurer = User::factory()->role('treasurer')->create();

        foreach ([$president, $vicePresident, $secretary, $treasurer] as $actor) {
            $managedUser = User::factory()->role('member')->create([
                'is_active' => true,
            ]);

            $member = Member::factory()->create([
                'user_id' => $managedUser->id,
                'membership_status' => 'active',
            ]);

            $this->actingAs($actor)
                ->patch(route('members.update-status', $member), [
                    'membership_status' => 'inactive',
                ])
                ->assertRedirect(route('members.show', $member));

            $this->assertSame('inactive', $member->fresh()->membership_status);
            $this->assertFalse($managedUser->fresh()->is_active);
        }
    }
}
