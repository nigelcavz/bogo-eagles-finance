<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:' . implode(',', User::assignableRoles())],
        ];
    }
}
