<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContributionRequest;
use App\Models\Contribution;
use App\Models\ContributionCategory;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributionController extends Controller
{
    public function index(Request $request): View
    {
        $contributionsQuery = Contribution::query()
            ->with(['member', 'category', 'creator', 'voider', 'coverages'])
            ->when($request->filled('member_id'), function (Builder $query) use ($request) {
                $query->where('member_id', $request->integer('member_id'));
            })
            ->when($request->filled('contribution_category_id'), function (Builder $query) use ($request) {
                $query->where('contribution_category_id', $request->integer('contribution_category_id'));
            })
            ->when($request->filled('date_from'), function (Builder $query) use ($request) {
                $query->whereDate('payment_date', '>=', $request->string('date_from')->toString());
            })
            ->when($request->filled('date_to'), function (Builder $query) use ($request) {
                $query->whereDate('payment_date', '<=', $request->string('date_to')->toString());
            })
            ->when(
                $request->filled('status') && $request->string('status')->toString() !== 'all',
                function (Builder $query) use ($request) {
                    $query->where('status', $request->string('status')->toString());
                }
            );

        $activeTotal = (clone $contributionsQuery)
            ->where('status', 'active')
            ->sum('amount');

        $voidedTotal = (clone $contributionsQuery)
            ->where('status', 'voided')
            ->sum('amount');

        $contributions = $contributionsQuery
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $members = Member::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $categories = ContributionCategory::query()
            ->orderBy('name')
            ->get();

        return view('contributions.index', compact(
            'contributions',
            'members',
            'categories',
            'activeTotal',
            'voidedTotal'
        ));
    }

    public function create(): View
    {
        $members = Member::query()
            ->where('membership_status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $categories = ContributionCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('contributions.create', compact('members', 'categories'));
    }

    public function store(StoreContributionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $notes = collect([
            $validated['notes'] ?? null,
            filled($validated['other_description'] ?? null)
                ? 'Other category detail: ' . trim($validated['other_description'])
                : null,
        ])
            ->filter()
            ->implode("\n\n");

        Contribution::create([
            'member_id' => $validated['member_id'],
            'contribution_category_id' => $validated['contribution_category_id'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference_number' => $validated['reference_number'] ?? null,
            'notes' => $notes !== '' ? $notes : null,
            'payment_type' => $validated['payment_type'] ?? null,
            'coverage_type' => null,
            'status' => 'active',
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('contributions.create')
            ->with('success', 'Contribution recorded successfully.');
    }

    public function void(Request $request, Contribution $contribution): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string'],
        ]);

        if ($contribution->status === 'voided') {
            return redirect()
                ->route('contributions.index')
                ->with('success', 'Contribution was already voided.');
        }

        $contribution->update([
            'status' => 'voided',
            'void_reason' => $validated['void_reason'],
            'voided_at' => now(),
            'voided_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('contributions.index')
            ->with('success', 'Contribution voided successfully.');
    }
}
