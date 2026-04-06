<?php

namespace App\Http\Requests;

use App\Models\ContributionCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && in_array($this->user()->role, ['admin', 'treasurer'], true)
            && $this->user()->is_active;
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

            if (strcasecmp($category->name, 'Other') === 0 && ! $this->filled('other_description')) {
                $validator->errors()->add(
                    'other_description',
                    'Please provide an additional description when the Other category is selected.'
                );
            }
        });
    }
}
