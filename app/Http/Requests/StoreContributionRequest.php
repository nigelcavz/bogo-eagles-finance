<?php

namespace App\Http\Requests;

use App\Models\ContributionCategory;
use App\Models\ContributionCoverage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContributionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $categoryId = $this->input('contribution_category_id');

        if (! $categoryId) {
            return;
        }

        $category = ContributionCategory::query()->find($categoryId);

        if (! $category?->requiresMonthlyCoverage()) {
            return;
        }

        $months = collect($this->input('coverage_months', []))
            ->filter(fn ($month) => $month !== null && $month !== '')
            ->map(fn ($month) => (int) $month)
            ->unique()
            ->values();

        if ($months->isEmpty() || blank($this->input('payment_date'))) {
            return;
        }

        $this->merge([
            'amount' => $category->calculateMonthlyCoverageAmount(
                $months->count(),
                $this->input('payment_date')
            ),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->canManageFinance() ?? false;
    }

    public function rules(): array
    {
        return [
            'member_id' => [
                'required',
                'integer',
                Rule::exists('members', 'id')->where('membership_status', 'active'),
            ],
            'contribution_category_id' => [
                'required',
                'integer',
                Rule::exists('contribution_categories', 'id')->where('is_active', true),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'payment_date' => ['required', 'date'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'other_description' => ['nullable', 'string', 'max:1000'],
            'coverage_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'coverage_months' => ['nullable', 'array'],
            'coverage_months.*' => ['integer', 'between:1,12', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $categoryId = $this->integer('contribution_category_id');

            if (!$categoryId) {
                return;
            }

            $category = ContributionCategory::query()->find($categoryId);

            if (!$category) {
                return;
            }

            if ($category->requiresOtherDescription() && ! $this->filled('other_description')) {
                $validator->errors()->add(
                    'other_description',
                    'Please provide an additional description when the Other category is selected.'
                );
            }

            if (! $category->requiresMonthlyCoverage()) {
                return;
            }

            $year = $this->input('coverage_year');
            $months = collect($this->input('coverage_months', []))
                ->filter(fn ($month) => $month !== null && $month !== '')
                ->map(fn ($month) => (int) $month)
                ->values();

            if (blank($year)) {
                $validator->errors()->add(
                    'coverage_year',
                    'Please select the coverage year for Monthly Dues/Contributions.'
                );
            }

            if ($months->isEmpty()) {
                $validator->errors()->add(
                    'coverage_months',
                    'Please select at least one covered month for Monthly Dues/Contributions.'
                );

                return;
            }

            if ($validator->errors()->hasAny(['member_id', 'coverage_year', 'coverage_months'])) {
                return;
            }

            $duplicateMonths = ContributionCoverage::query()
                ->join('contributions', 'contributions.id', '=', 'contribution_coverages.contribution_id')
                ->where('contributions.status', 'active')
                ->where('contributions.contribution_category_id', $category->id)
                ->where('contribution_coverages.member_id', $this->integer('member_id'))
                ->where('contribution_coverages.coverage_year', (int) $year)
                ->whereIn('contribution_coverages.coverage_month', $months->all())
                ->pluck('contribution_coverages.coverage_month')
                ->unique()
                ->sort()
                ->values();

            if ($duplicateMonths->isNotEmpty()) {
                $validator->errors()->add(
                    'coverage_months',
                    'The selected member already has active monthly dues coverage for: ' .
                    $duplicateMonths->map(fn (int $month) => now()->setMonth($month)->startOfMonth()->format('M'))->implode(', ') .
                    '.'
                );
            }

            $expectedAmount = $category->calculateMonthlyCoverageAmount(
                $months->unique()->count(),
                $this->input('payment_date')
            );

            if ((string) $this->input('amount') !== $expectedAmount) {
                $this->merge([
                    'amount' => $expectedAmount,
                ]);
            }
        });
    }
}
