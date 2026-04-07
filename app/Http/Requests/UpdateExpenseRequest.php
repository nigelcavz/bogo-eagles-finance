<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageFinance() ?? false;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => [
                'required',
                'integer',
                Rule::exists('expense_categories', 'id')->where('is_active', true),
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'expense_date' => ['required', 'date'],
            'payee_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'edit_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
