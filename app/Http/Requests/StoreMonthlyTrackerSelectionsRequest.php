<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMonthlyTrackerSelectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageFinance() ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required', 'date'],
            'coverage_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'selections' => ['required', 'array', 'min:1'],
            'selections.*.member_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('members', 'id')->where('membership_status', 'active'),
            ],
            'selections.*.months' => ['required', 'array', 'min:1'],
            'selections.*.months.*' => ['integer', 'between:1,12'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('selections', []) as $index => $selection) {
                $months = collect($selection['months'] ?? [])
                    ->map(fn ($month) => (int) $month)
                    ->filter(fn ($month) => $month >= 1 && $month <= 12)
                    ->values();

                if ($months->count() !== $months->unique()->count()) {
                    $validator->errors()->add(
                        "selections.$index.months",
                        'Each selected month may only be chosen once per member.'
                    );
                }
            }
        });
    }
}
