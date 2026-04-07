<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Support\MemberClubPositionMapper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => trim($request->string('first_name')->toString() . ' ' . $request->string('last_name')->toString()),
                'email' => $request->email,
                'role' => User::ROLE_MEMBER,
                'password' => Hash::make($request->password),
            ]);

            Member::create([
                'user_id' => $user->id,
                'club_position' => MemberClubPositionMapper::forRole($user->role),
                'first_name' => $request->string('first_name')->toString(),
                'last_name' => $request->string('last_name')->toString(),
                'membership_status' => 'active',
                'joined_at' => now()->toDateString(),
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
