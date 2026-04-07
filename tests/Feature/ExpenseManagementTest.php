<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_viewers_can_open_expense_index_but_only_managers_can_open_create_page(): void
    {
        $admin = User::factory()->role('admin')->create();
        $treasurer = User::factory()->role('treasurer')->create();
        $president = User::factory()->role('president')->create();
        $vicePresident = User::factory()->role('vice_president')->create();
        $secretary = User::factory()->role('secretary')->create();
        $officer = User::factory()->role('officer')->create();
        $category = ExpenseCategory::create([
            'name' => 'Seminars',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('expenses.index'))
            ->assertOk();

        $this->actingAs($treasurer)
            ->get(route('expenses.create'))
            ->assertOk()
            ->assertSee($category->name);

        foreach ([$president, $vicePresident, $secretary, $officer] as $viewer) {
            $this->actingAs($viewer)
                ->get(route('expenses.index'))
                ->assertOk();

            $this->actingAs($viewer)
                ->get(route('expenses.create'))
                ->assertForbidden();
        }
    }

    public function test_member_role_is_denied_expense_pages(): void
    {
        $memberUser = User::factory()->role('member')->create();

        $this->actingAs($memberUser)
            ->get(route('expenses.index'))
            ->assertForbidden();
    }

    public function test_expense_can_be_created_from_existing_category_dropdown(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $category = ExpenseCategory::create([
            'name' => 'Event Expenses',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'expense_category_id' => $category->id,
            'amount' => 3500.00,
            'expense_date' => '2026-04-07',
            'payee_name' => 'Bogo Catering Services',
            'description' => 'Food and venue supplies for chapter event',
            'reference_number' => 'EXP-2001',
            'notes' => 'Official receipt received',
        ]);

        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'expense_category_id' => $category->id,
            'amount' => '3500.00',
            'payee_name' => 'Bogo Catering Services',
            'reference_number' => 'EXP-2001',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'module' => 'expenses',
            'record_id' => $expense->id,
            'action' => 'expense_created',
        ]);
    }

    public function test_financial_expense_edit_routes_are_not_registered(): void
    {
        $this->assertFalse(Route::has('expenses.edit'));
        $this->assertFalse(Route::has('expenses.update'));
    }

    public function test_expenses_can_be_filtered_by_search_category_and_status(): void
    {
        $user = User::factory()->role('treasurer')->create();
        $eventCategory = ExpenseCategory::create([
            'name' => 'Event Expenses',
            'is_active' => true,
        ]);
        $seminarCategory = ExpenseCategory::create([
            'name' => 'Seminars',
            'is_active' => true,
        ]);

        Expense::create([
            'expense_category_id' => $eventCategory->id,
            'amount' => 2500.00,
            'expense_date' => '2026-04-05',
            'payee_name' => 'Hall Rental',
            'description' => 'Venue reservation for anniversary event',
            'reference_number' => 'EVT-101',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        Expense::create([
            'expense_category_id' => $seminarCategory->id,
            'amount' => 900.00,
            'expense_date' => '2026-04-03',
            'payee_name' => 'Speaker Honorarium',
            'description' => 'Leadership seminar session',
            'reference_number' => 'SEM-201',
            'status' => 'voided',
            'void_reason' => 'Wrong category',
            'voided_at' => now(),
            'voided_by' => $user->id,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('expenses.index', [
                'q' => 'venue',
                'expense_category_id' => $eventCategory->id,
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Venue reservation for anniversary event')
            ->assertDontSee('Leadership seminar session');
    }

    public function test_voiding_marks_expense_voided_without_deleting_it(): void
    {
        $creator = User::factory()->role('treasurer')->create();
        $voider = User::factory()->role('admin')->create();
        $category = ExpenseCategory::create([
            'name' => 'Other',
            'is_active' => true,
        ]);

        $expense = Expense::create([
            'expense_category_id' => $category->id,
            'amount' => 500.00,
            'expense_date' => '2026-04-04',
            'payee_name' => 'Office Supplies',
            'description' => 'Printer ink purchase',
            'status' => 'active',
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($voider)->patch(route('expenses.void', $expense), [
            'void_reason' => 'Entered twice',
        ]);

        $response->assertRedirect(route('expenses.index'));

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'voided',
            'void_reason' => 'Entered twice',
            'voided_by' => $voider->id,
            'updated_by' => $voider->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $voider->id,
            'module' => 'expenses',
            'record_id' => $expense->id,
            'action' => 'expense_voided',
        ]);
    }
}
