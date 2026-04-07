<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canViewFinance(), 403);

        $sort = $this->normalizeSort($request->string('sort', 'date_posted_desc')->toString());
        $status = $request->string('status', 'all')->toString();
        $search = trim($request->string('q')->toString());

        $expensesQuery = Expense::query()
            ->with(['category', 'creator', 'updater', 'voider'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery->where('description', 'like', '%' . $search . '%')
                        ->orWhere('payee_name', 'like', '%' . $search . '%')
                        ->orWhere('reference_number', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('expense_category_id'), function (Builder $query) use ($request) {
                $query->where('expense_category_id', $request->integer('expense_category_id'));
            })
            ->when($request->filled('date_from'), function (Builder $query) use ($request) {
                $query->whereDate('expense_date', '>=', $request->string('date_from')->toString());
            })
            ->when($request->filled('date_to'), function (Builder $query) use ($request) {
                $query->whereDate('expense_date', '<=', $request->string('date_to')->toString());
            })
            ->when($status !== 'all', function (Builder $query) use ($status) {
                $query->where('status', $status);
            });

        $activeTotal = (clone $expensesQuery)
            ->where('status', 'active')
            ->sum('amount');

        $voidedTotal = (clone $expensesQuery)
            ->where('status', 'voided')
            ->sum('amount');

        $this->applySort($expensesQuery, $sort);

        $expenses = $expensesQuery
            ->paginate(15)
            ->withQueryString();

        $categories = ExpenseCategory::query()
            ->orderBy('name')
            ->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'sort' => $sort,
            'activeTotal' => $activeTotal,
            'voidedTotal' => $voidedTotal,
        ]);
    }

    public function create(): View
    {
        abort_unless(request()->user()?->canManageFinance(), 403);

        return view('expenses.create', [
            'categories' => ExpenseCategory::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validated();

        $expense = DB::transaction(function () use ($validated, $request) {
            $expense = Expense::create([
                'expense_category_id' => $validated['expense_category_id'],
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'payee_name' => trim($validated['payee_name']),
                'description' => trim($validated['description']),
                'reference_number' => filled($validated['reference_number'] ?? null) ? trim($validated['reference_number']) : null,
                'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            $this->logActivity(
                $request,
                'expense_created',
                $expense->id,
                'Expense recorded.',
                null,
                $expense->only([
                    'expense_category_id',
                    'amount',
                    'expense_date',
                    'payee_name',
                    'description',
                    'reference_number',
                    'notes',
                    'status',
                ])
            );

            return $expense;
        });

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense): View|RedirectResponse
    {
        return redirect()
            ->route('expenses.index')
            ->with('error', 'Financial expense records are immutable. Please void the record and add a corrected expense instead.');
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        return redirect()
            ->route('expenses.index')
            ->with('error', 'Financial expense records are immutable. Please void the record and add a corrected expense instead.');
    }

    public function void(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($expense->status === 'voided') {
            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense was already voided.');
        }

        DB::transaction(function () use ($expense, $validated, $request) {
            $before = $expense->only([
                'expense_category_id',
                'amount',
                'expense_date',
                'payee_name',
                'description',
                'reference_number',
                'notes',
                'status',
                'void_reason',
                'voided_at',
                'voided_by',
            ]);

            $expense->update([
                'status' => 'voided',
                'void_reason' => trim($validated['void_reason']),
                'voided_at' => now(),
                'voided_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $this->logActivity(
                $request,
                'expense_voided',
                $expense->id,
                'Expense voided. Reason: ' . trim($validated['void_reason']),
                $before,
                $expense->fresh()->only([
                    'expense_category_id',
                    'amount',
                    'expense_date',
                    'payee_name',
                    'description',
                    'reference_number',
                    'notes',
                    'status',
                    'void_reason',
                    'voided_at',
                    'voided_by',
                ])
            );
        });

        return redirect()
            ->to($request->input('redirect_to', route('expenses.index')))
            ->with('success', 'Expense voided successfully.');
    }

    private function applySort(Builder $query, string $sort): void
    {
        if (str_starts_with($sort, 'category_')) {
            $query->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
                ->select('expenses.*');

            if ($sort === 'category_desc') {
                $query->orderByDesc('expense_categories.name')
                    ->orderByDesc('expenses.expense_date')
                    ->orderByDesc('expenses.id');

                return;
            }

            $query->orderBy('expense_categories.name')
                ->orderByDesc('expenses.expense_date')
                ->orderByDesc('expenses.id');

            return;
        }

        match ($sort) {
            'expense_date_asc' => $query->orderBy('expense_date')->orderBy('id'),
            'expense_date_desc' => $query->orderByDesc('expense_date')->orderByDesc('id'),
            'amount_asc' => $query->orderBy('amount')->orderBy('expense_date'),
            'amount_desc' => $query->orderByDesc('amount')->orderByDesc('expense_date'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    private function normalizeSort(string $sort): string
    {
        return in_array($sort, [
            'date_posted_desc',
            'expense_date_desc',
            'expense_date_asc',
            'amount_asc',
            'amount_desc',
            'category_asc',
            'category_desc',
        ], true) ? $sort : 'date_posted_desc';
    }

    private function logActivity(
        Request $request,
        string $action,
        int $recordId,
        string $description,
        ?array $oldValues,
        ?array $newValues
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'module' => 'expenses',
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
