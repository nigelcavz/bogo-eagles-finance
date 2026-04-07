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

    public function test_member_self_service_page_handles_missing_linked_member_gracefully(): void
    {
        $memberUser = User::factory()->role('member')->create();

        $this->actingAs($memberUser)
            ->get(route('members.self'))
            ->assertOk()
            ->assertSee('No linked member profile');
    }
}
