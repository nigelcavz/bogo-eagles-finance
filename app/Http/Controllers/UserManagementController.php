<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $search = trim((string) request('search'));

        $users = User::query()
            ->with('member')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function edit(User $user): View
    {
        $user->loadMissing('member');

        return view('users.edit', [
            'managedUser' => $user,
            'availableRoles' => ['admin', 'member', 'officer', 'president', 'treasurer'],
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $oldRole = $user->role;

        if ($oldRole === $validated['role']) {
            return redirect()
                ->route('users.edit', $user)
                ->with('success', 'User role is already set to that value.');
        }

        DB::transaction(function () use ($request, $user, $validated, $oldRole) {
            $user->update([
                'role' => $validated['role'],
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user_role_updated',
                'module' => 'users',
                'record_id' => $user->id,
                'description' => 'User role updated through admin user management.',
                'old_values' => [
                    'role' => $oldRole,
                ],
                'new_values' => [
                    'role' => $validated['role'],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('users.edit', $user)
            ->with('success', 'User role updated successfully.');
    }
}
