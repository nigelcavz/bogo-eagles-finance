<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_treasurer_can_open_contribution_pages(): void
    {
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create();

        $admin = User::factory()->role('admin')->create();
        $treasurer = User::factory()->role('treasurer')->create();

        $this->actingAs($admin)
            ->get(route('contributions.index'))
            ->assertOk();

        $this->actingAs($treasurer)
            ->get(route('contributions.create', ['member_id' => $member->id]))
            ->assertOk()
            ->assertSee($category->name);
    }

    public function test_member_role_is_denied_contribution_pages(): void
    {
        $memberUser = User::factory()->role('member')->create();

        $this->actingAs($memberUser)
            ->get(route('contributions.index'))
            ->assertForbidden();
    }

    public function test_contribution_can_be_created_from_existing_category_dropdown(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Voluntary Contributions',
            'default_amount' => 1000,
        ]);

        $response = $this->actingAs($user)->post(route('contributions.store'), [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 2000.00,
            'payment_date' => '2026-04-05',
            'payment_type' => 'cash',
            'reference_number' => 'RCPT-1001',
            'notes' => 'Paid in person',
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'voluntary-contributions']));

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => '2000.00',
            'payment_type' => 'cash',
            'reference_number' => 'RCPT-1001',
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    public function test_other_category_requires_additional_description_and_stores_it_in_notes(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Other',
        ]);

        $response = $this->from(route('contributions.create'))
            ->actingAs($user)
            ->post(route('contributions.store'), [
                'member_id' => $member->id,
                'contribution_category_id' => $category->id,
                'amount' => 1000.00,
                'payment_date' => '2026-04-05',
            ]);

        $response->assertRedirect(route('contributions.create'));
        $response->assertSessionHasErrors('other_description');

        $this->assertDatabaseCount('contributions', 0);

        $response = $this->actingAs($user)->post(route('contributions.store'), [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 1000.00,
            'payment_date' => '2026-04-05',
            'other_description' => 'Special collection for a unique member pledge',
            'notes' => 'Collected during meeting',
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'other']));

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => '1000.00',
            'status' => 'active',
        ]);

        $this->assertStringContainsString(
            'Other category detail: Special collection for a unique member pledge',
            Contribution::firstOrFail()->notes
        );
    }

    public function test_voiding_marks_contribution_voided_without_deleting_it(): void
    {
        $creator = User::factory()->role('treasurer')->create();
        $voider = User::factory()->role('admin')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create();

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 1500.00,
            'payment_date' => '2026-04-03',
            'status' => 'active',
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($voider)->patch(route('contributions.void', $contribution), [
            'void_reason' => 'Entered twice',
        ]);

        $response->assertRedirect(route('contributions.index'));

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'status' => 'voided',
            'void_reason' => 'Entered twice',
            'voided_by' => $voider->id,
            'updated_by' => $voider->id,
        ]);
    }

    public function test_filters_and_member_history_show_expected_contributions(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $memberA = Member::factory()->create(['first_name' => 'Alice', 'last_name' => 'Rivera']);
        $memberB = Member::factory()->create(['first_name' => 'Ben', 'last_name' => 'Santos']);
        $category = ContributionCategory::factory()->create(['name' => 'Monthly Dues/Contributions']);

        $contributionA = Contribution::create([
            'member_id' => $memberA->id,
            'contribution_category_id' => $category->id,
            'amount' => 1200.00,
            'payment_date' => '2026-04-02',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $contributionB = Contribution::create([
            'member_id' => $memberB->id,
            'contribution_category_id' => $category->id,
            'amount' => 1200.00,
            'payment_date' => '2026-04-04',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('contributions.index', ['member_id' => $memberA->id]))
            ->assertOk()
            ->assertViewHas('contributions', function ($paginator) use ($memberA) {
                return $paginator->count() === 1
                    && $paginator->first()->member_id === $memberA->id;
            });

        $this->actingAs($user)
            ->get(route('members.show', $memberA))
            ->assertOk()
            ->assertSee('Member Contribution History')
            ->assertSee($category->name)
            ->assertDontSee($memberB->full_name);
    }

    public function test_inactive_member_cannot_be_used_for_contribution_entry(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create([
            'membership_status' => 'inactive',
        ]);
        $category = ContributionCategory::factory()->create();

        $response = $this->from(route('contributions.create'))
            ->actingAs($user)
            ->post(route('contributions.store'), [
                'member_id' => $member->id,
                'contribution_category_id' => $category->id,
                'amount' => 500.00,
                'payment_date' => '2026-04-05',
            ]);

        $response->assertRedirect(route('contributions.create'));
        $response->assertSessionHasErrors('member_id');

        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_monthly_dues_store_creates_coverage_rows_and_redirects_to_tracker(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
            'default_amount' => 700,
        ]);

        $response = $this->actingAs($user)->post(route('contributions.store'), [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 9999.00,
            'payment_date' => '2026-04-10',
            'coverage_year' => 2026,
            'coverage_months' => [1, 2, 3],
            'reference_number' => 'MTH-2026-001',
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'monthly-dues', 'year' => 2026]));

        $contribution = Contribution::firstOrFail();

        $this->assertSame('monthly', $contribution->coverage_type);
        $this->assertSame('2100.00', $contribution->amount);

        $this->assertDatabaseHas('contribution_coverages', [
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 1,
        ]);

        $this->assertDatabaseHas('contribution_coverages', [
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 3,
        ]);
    }

    public function test_duplicate_monthly_coverage_is_blocked_without_partial_records(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
        ]);

        $existingContribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 500.00,
            'payment_date' => '2026-01-10',
            'coverage_type' => 'monthly',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        ContributionCoverage::create([
            'contribution_id' => $existingContribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 2,
            'coverage_label' => 'Feb 2026',
        ]);

        $response = $this->from(route('contributions.create'))
            ->actingAs($user)
            ->post(route('contributions.store'), [
                'member_id' => $member->id,
                'contribution_category_id' => $category->id,
                'amount' => 1000.00,
                'payment_date' => '2026-02-10',
                'coverage_year' => 2026,
                'coverage_months' => [2, 3],
            ]);

        $response->assertRedirect(route('contributions.create'));
        $response->assertSessionHasErrors('coverage_months');

        $this->assertDatabaseCount('contributions', 1);
        $this->assertDatabaseCount('contribution_coverages', 1);
    }

    public function test_standard_contribution_can_be_updated_without_changing_member_or_category(): void
    {
        $user = User::factory()->role('admin')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Voluntary Contributions',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 400.00,
            'payment_date' => '2026-04-01',
            'reference_number' => 'OLD-REF',
            'notes' => 'Original notes',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('contributions.update', $contribution), [
            'amount' => 650.00,
            'payment_date' => '2026-04-15',
            'payment_type' => 'bank transfer',
            'reference_number' => 'NEW-REF',
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'voluntary-contributions']));

        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => '650.00',
            'payment_type' => 'bank transfer',
            'reference_number' => 'NEW-REF',
            'updated_by' => $user->id,
        ]);
    }

    public function test_monthly_dues_entries_with_coverages_must_be_corrected_by_void_and_reentry(): void
    {
        $user = User::factory()->role('admin')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 600.00,
            'payment_date' => '2026-01-05',
            'coverage_type' => 'monthly',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        ContributionCoverage::create([
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 1,
            'coverage_label' => 'Jan 2026',
        ]);

        $response = $this->actingAs($user)->get(route('contributions.edit', $contribution));

        $response->assertRedirect(route('contributions.types.show', ['type' => 'monthly-dues', 'year' => 2026]));
    }

    public function test_monthly_availability_endpoint_returns_already_covered_months(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
        ]);

        $contribution = Contribution::create([
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 1200.00,
            'payment_date' => '2026-02-01',
            'coverage_type' => 'monthly',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        ContributionCoverage::create([
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 1,
            'coverage_label' => 'Jan 2026',
        ]);

        ContributionCoverage::create([
            'contribution_id' => $contribution->id,
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'coverage_month' => 4,
            'coverage_label' => 'Apr 2026',
        ]);

        $response = $this->actingAs($user)->getJson(route('contributions.monthly-availability', [
            'member_id' => $member->id,
            'coverage_year' => 2026,
            'contribution_category_id' => $category->id,
        ]));

        $response->assertOk()->assertExactJson([
            'covered_months' => [1, 4],
        ]);
    }

    public function test_full_year_monthly_dues_paid_in_january_uses_discount_amount(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
            'default_amount' => 700,
        ]);

        $response = $this->actingAs($user)->post(route('contributions.store'), [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 8400.00,
            'payment_date' => '2026-01-20',
            'coverage_year' => 2026,
            'coverage_months' => range(1, 12),
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'monthly-dues', 'year' => 2026]));

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => '7000.00',
        ]);
    }

    public function test_full_year_monthly_dues_paid_after_january_uses_regular_total(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $member = Member::factory()->create();
        $category = ContributionCategory::factory()->create([
            'name' => 'Monthly Dues/Contributions',
            'default_amount' => 700,
        ]);

        $response = $this->actingAs($user)->post(route('contributions.store'), [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => 7000.00,
            'payment_date' => '2026-02-05',
            'coverage_year' => 2026,
            'coverage_months' => range(1, 12),
        ]);

        $response->assertRedirect(route('contributions.types.show', ['type' => 'monthly-dues', 'year' => 2026]));

        $this->assertDatabaseHas('contributions', [
            'member_id' => $member->id,
            'contribution_category_id' => $category->id,
            'amount' => '8400.00',
        ]);
    }
}
