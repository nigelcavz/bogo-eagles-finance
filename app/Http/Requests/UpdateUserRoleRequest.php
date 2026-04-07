<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin' && $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:admin,member,officer,president,treasurer'],
        ];
    }
}
