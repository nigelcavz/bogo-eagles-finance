<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $requireCurrentPassword = ! $request->user()->must_change_password;

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => [$requireCurrentPassword ? 'required' : 'nullable', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return back()
            ->with('status', 'password-updated')
            ->with('password_change_completed', 'Password updated successfully.');
    }
}
