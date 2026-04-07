<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContributionRequest;
use App\Http\Requests\UpdateContributionRequest;
use App\Models\Contribution;
use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContributionController extends Controller
{
    private const TYPE_MAP = [
        'monthly-dues' => ContributionCategory::MONTHLY_DUES_NAME,
        'alalayan-ng-agila' => 'Alalayan ng Agila',
        'voluntary-contributions' => 'Voluntary Contributions',
        'other' => ContributionCategory::OTHER_NAME,
    ];

    private const MONTH_LABELS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()?->canViewFinance(), 403);

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
            ->active()
            ->orderBy('name')
            ->get();

        $typePages = $this->buildTypePages();

        return view('contributions.index', compact(
            'contributions',
            'members',
            'categories',
            'activeTotal',
            'voidedTotal',
            'typePages'
        ));
    }

    public function showType(Request $request, string $type): View
    {
        abort_unless($request->user()?->canViewFinance(), 403);

        $category = $this->resolveCategoryFromType($type);

        if ($category->requiresMonthlyCoverage()) {
            return $this->showMonthlyTracker($request, $category, $type);
        }

        return $this->showStandardType($request, $category, $type);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $members = Member::query()
            ->where('membership_status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $categories = ContributionCategory::query()
            ->active()
            ->orderBy('name')
            ->get();

        $selectedCategoryId = (int) old('contribution_category_id', (int) request('contribution_category_id'));
        $selectedMemberId = (int) old('member_id', (int) request('member_id'));
        $selectedCoverageYear = (int) old('coverage_year', now()->year);
        $backContext = $this->resolveCreateBackContext($request);

        return view('contributions.create', [
            'members' => $members,
            'categories' => $categories,
            'monthOptions' => self::MONTH_LABELS,
            'selectedCategoryId' => $selectedCategoryId,
            'selectedMemberId' => $selectedMemberId,
            'selectedCoverageYear' => $selectedCoverageYear,
            'backUrl' => $backContext['url'],
            'backLabel' => $backContext['label'],
            'availabilityUrl' => route('contributions.monthly-availability'),
        ]);
    }

    public function monthlyAvailability(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validate([
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'coverage_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'contribution_category_id' => ['required', 'integer', 'exists:contribution_categories,id'],
        ]);

        $category = ContributionCategory::query()->findOrFail($validated['contribution_category_id']);

        if (! $category->requiresMonthlyCoverage()) {
            return response()->json([
                'covered_months' => [],
            ]);
        }

        $coveredMonths = $this->coveredMonthsForMemberYear(
            (int) $validated['member_id'],
            (int) $validated['coverage_year'],
            $category
        );

        return response()->json([
            'covered_months' => $coveredMonths,
        ]);
    }

    public function store(StoreContributionRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validated();
        $category = ContributionCategory::query()->findOrFail($validated['contribution_category_id']);

        $notes = $this->buildContributionNotes(
            $validated['notes'] ?? null,
            $validated['other_description'] ?? null,
            $category
        );

        $contribution = DB::transaction(function () use ($validated, $request, $category, $notes) {
            $contribution = Contribution::create([
                'member_id' => $validated['member_id'],
                'contribution_category_id' => $validated['contribution_category_id'],
                'amount' => $this->resolveContributionAmount($validated, $category),
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $notes,
                'payment_type' => $validated['payment_type'] ?? null,
                'coverage_type' => $category->requiresMonthlyCoverage() ? 'monthly' : null,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            if ($category->requiresMonthlyCoverage()) {
                $year = (int) $validated['coverage_year'];
                $months = collect($validated['coverage_months'] ?? [])
                    ->map(fn ($month) => (int) $month)
                    ->unique()
                    ->sort()
                    ->values();

                foreach ($months as $month) {
                    $contribution->coverages()->create([
                        'member_id' => $validated['member_id'],
                        'coverage_year' => $year,
                        'coverage_month' => $month,
                        'coverage_label' => self::MONTH_LABELS[$month] . ' ' . $year,
                    ]);
                }
            }

            return $contribution;
        });

        $redirectParameters = ['type' => $this->typeForCategory($category)];

        if ($category->requiresMonthlyCoverage()) {
            $redirectParameters['year'] = (int) $validated['coverage_year'];
        }

        return redirect()
            ->route('contributions.types.show', $redirectParameters)
            ->with('success', $category->requiresMonthlyCoverage()
                ? 'Monthly dues contribution recorded and coverage periods saved successfully.'
                : 'Contribution recorded successfully.');
    }

    public function edit(Contribution $contribution): View|RedirectResponse
    {
        return redirect()
            ->route('contributions.types.show', ['type' => $this->typeForCategory($contribution->loadMissing('category')->category)])
            ->with('error', 'Financial contribution records are immutable. Please void the record and enter a corrected contribution instead.');
    }

    public function update(UpdateContributionRequest $request, Contribution $contribution): RedirectResponse
    {
        return redirect()
            ->route('contributions.types.show', ['type' => $this->typeForCategory($contribution->loadMissing('category')->category)])
            ->with('error', 'Financial contribution records are immutable. Please void the record and enter a corrected contribution instead.');
    }

    public function void(Request $request, Contribution $contribution): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

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

        $redirectTarget = $request->input('redirect_to');
        $previousUrl = url()->previous();
        $previousPath = $previousUrl ? parse_url($previousUrl, PHP_URL_PATH) : null;

        if (! filled($redirectTarget)) {
            $redirectTarget = $previousUrl
                && $previousUrl !== url()->current()
                && filled($previousPath)
                && $previousPath !== '/'
                ? $previousUrl
                : route('contributions.index');
        }

        return redirect()
            ->to($redirectTarget)
            ->with('success', 'Contribution voided successfully.');
    }

    private function showStandardType(Request $request, ContributionCategory $category, string $type): View
    {
        $sort = $this->normalizeStandardSort($request->string('sort', 'date_posted_desc')->toString());
        $status = $request->string('status', 'all')->toString();

        $contributions = Contribution::query()
            ->with(['member', 'creator', 'voider', 'coverages'])
            ->where('contribution_category_id', $category->id)
            ->when($status !== 'all', function (Builder $query) use ($status) {
                $query->where('status', $status);
            });

        $this->applyStandardSort($contributions, $sort);

        $contributions = $contributions
            ->paginate(15)
            ->withQueryString();

        return view('contributions.type-list', [
            'category' => $category,
            'type' => $type,
            'sort' => $sort,
            'status' => $status,
            'contributions' => $contributions,
        ]);
    }

    private function showMonthlyTracker(Request $request, ContributionCategory $category, string $type): View
    {
        $year = $request->integer('year', now()->year);
        $year = max(2000, min(2100, $year));

        $members = Member::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $coverages = ContributionCoverage::query()
            ->with(['contribution', 'member'])
            ->where('coverage_year', $year)
            ->whereHas('contribution', function (Builder $query) use ($category) {
                $query->where('contribution_category_id', $category->id)
                    ->where('status', 'active');
            })
            ->get();

        $coveragesByMember = $coverages->groupBy('member_id');

        $trackerRows = $members->map(function (Member $member) use ($coveragesByMember) {
            $memberCoverages = $coveragesByMember->get($member->id, collect());
            $coverageCounts = $memberCoverages
                ->groupBy('coverage_month')
                ->map(fn (Collection $items) => $items->count());

            $coveredMonthCount = $coverageCounts->filter(fn (int $count) => $count > 0)->count();
            $unpaidMonthCount = 12 - $coveredMonthCount;
            $contributionTotal = $memberCoverages
                ->groupBy('contribution_id')
                ->sum(fn (Collection $items) => (float) $items->first()->contribution->amount);

            return [
                'member' => $member,
                'months' => collect(self::MONTH_LABELS)->mapWithKeys(function (string $label, int $month) use ($coverageCounts) {
                    $count = (int) ($coverageCounts->get($month) ?? 0);

                    return [$month => [
                        'label' => $label,
                        'covered' => $count > 0,
                        'duplicate' => $count > 1,
                        'count' => $count,
                    ]];
                }),
                'paid_month_count' => $coveredMonthCount,
                'unpaid_month_count' => $unpaidMonthCount,
                'total_paid' => $contributionTotal,
                'status' => $coveredMonthCount === 12 ? 'fully_paid' : ($coveredMonthCount === 0 ? 'unpaid' : 'partial'),
                'has_duplicates' => $coverageCounts->contains(fn (int $count) => $count > 1),
            ];
        });

        $availableYears = ContributionCoverage::query()
            ->select('coverage_year')
            ->distinct()
            ->orderBy('coverage_year')
            ->pluck('coverage_year')
            ->push(now()->year)
            ->push($year)
            ->unique()
            ->sort()
            ->values();

        $duplicateMemberCount = $trackerRows->filter(fn (array $row) => $row['has_duplicates'])->count();

        return view('contributions.monthly-tracker', [
            'category' => $category,
            'type' => $type,
            'year' => $year,
            'availableYears' => $availableYears,
            'trackerRows' => $trackerRows,
            'monthLabels' => self::MONTH_LABELS,
            'fullyPaidCount' => $trackerRows->where('status', 'fully_paid')->count(),
            'partialCount' => $trackerRows->where('status', 'partial')->count(),
            'unpaidCount' => $trackerRows->where('status', 'unpaid')->count(),
            'duplicateMemberCount' => $duplicateMemberCount,
        ]);
    }

    private function applyStandardSort(Builder $query, string $sort): void
    {
        if (Str::startsWith($sort, 'member_')) {
            $query->join('members', 'members.id', '=', 'contributions.member_id')
                ->select('contributions.*');

            if ($sort === 'member_desc') {
                $query->orderByDesc('members.last_name')
                    ->orderByDesc('members.first_name');

                return;
            }

            $query->orderBy('members.last_name')
                ->orderBy('members.first_name');

            return;
        }

        match ($sort) {
            'date_paid_asc' => $query->orderBy('payment_date')->orderBy('id'),
            'date_paid_desc' => $query->orderByDesc('payment_date')->orderByDesc('id'),
            'amount_asc' => $query->orderBy('amount')->orderBy('payment_date'),
            'amount_desc' => $query->orderByDesc('amount')->orderByDesc('payment_date'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    private function normalizeStandardSort(string $sort): string
    {
        return in_array($sort, [
            'date_posted_desc',
            'date_paid_desc',
            'date_paid_asc',
            'member_asc',
            'member_desc',
            'amount_asc',
            'amount_desc',
        ], true) ? $sort : 'date_posted_desc';
    }

    private function resolveCategoryFromType(string $type): ContributionCategory
    {
        $categoryName = self::TYPE_MAP[$type] ?? null;

        if ($categoryName !== null) {
            return ContributionCategory::query()
                ->where('name', $categoryName)
                ->firstOrFail();
        }

        $category = ContributionCategory::query()
            ->get()
            ->first(fn (ContributionCategory $category) => Str::slug(str_replace('/', ' ', $category->name)) === $type);

        abort_if($category === null, 404);

        return $category;
    }

    private function typeForCategory(ContributionCategory $category): string
    {
        $type = array_search($category->name, self::TYPE_MAP, true);

        return $type !== false ? $type : Str::slug(str_replace('/', ' ', $category->name));
    }

    private function buildTypePages(): Collection
    {
        $categories = ContributionCategory::query()
            ->active()
            ->whereIn('name', array_values(self::TYPE_MAP))
            ->get()
            ->keyBy('name');

        return collect(self::TYPE_MAP)
            ->map(function (string $categoryName, string $type) use ($categories) {
                $category = $categories->get($categoryName);

                if (! $category) {
                    return null;
                }

                $activeQuery = Contribution::query()
                    ->where('contribution_category_id', $category->id)
                    ->where('status', 'active');

                return [
                    'type' => $type,
                    'category' => $category,
                    'route' => route('contributions.types.show', [
                        'type' => $type,
                        ...($category->requiresMonthlyCoverage() ? ['year' => now()->year] : []),
                    ]),
                    'active_count' => (clone $activeQuery)->count(),
                    'active_total' => (clone $activeQuery)->sum('amount'),
                ];
            })
            ->filter()
            ->values();
    }

    private function buildContributionNotes(?string $notes, ?string $otherDescription, ContributionCategory $category): ?string
    {
        $compiled = collect([
            filled($notes) ? trim($notes) : null,
            $category->requiresOtherDescription() && filled($otherDescription)
                ? 'Other category detail: ' . trim($otherDescription)
                : null,
        ])->filter()->implode("\n\n");

        return $compiled !== '' ? $compiled : null;
    }

    private function coveredMonthsForMemberYear(int $memberId, int $year, ContributionCategory $category): array
    {
        return ContributionCoverage::query()
            ->join('contributions', 'contributions.id', '=', 'contribution_coverages.contribution_id')
            ->where('contributions.status', 'active')
            ->where('contributions.contribution_category_id', $category->id)
            ->where('contribution_coverages.member_id', $memberId)
            ->where('contribution_coverages.coverage_year', $year)
            ->pluck('contribution_coverages.coverage_month')
            ->unique()
            ->sort()
            ->map(fn ($month) => (int) $month)
            ->values()
            ->all();
    }

    private function resolveCreateBackContext(Request $request): array
    {
        $back = $request->string('back')->toString();
        $type = $request->string('type')->toString();
        $year = $request->integer('year');

        if ($back === 'type' && array_key_exists($type, self::TYPE_MAP)) {
            $parameters = ['type' => $type];

            if ($type === 'monthly-dues' && $year > 0) {
                $parameters['year'] = $year;
            }

            return [
                'url' => route('contributions.types.show', $parameters),
                'label' => 'Back to ' . self::TYPE_MAP[$type],
            ];
        }

        return [
            'url' => route('contributions.index'),
            'label' => 'Back to Contributions',
        ];
    }

    private function resolveContributionAmount(array $validated, ContributionCategory $category): string|float|int
    {
        if (! $category->requiresMonthlyCoverage()) {
            return $validated['amount'];
        }

        $monthCount = collect($validated['coverage_months'] ?? [])
            ->map(fn ($month) => (int) $month)
            ->unique()
            ->count();

        return $category->calculateMonthlyCoverageAmount($monthCount, $validated['payment_date'] ?? null);
    }
}
