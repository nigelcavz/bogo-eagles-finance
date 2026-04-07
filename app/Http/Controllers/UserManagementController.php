<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\MemberAccountStatusSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'availableRoles' => User::assignableRoles(),
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

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canManageUsers(), 403);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->isAdmin()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Admin accounts cannot be deactivated from this screen.');
        }

        $newActiveState = (bool) $validated['is_active'];

        if ((bool) $user->is_active === $newActiveState) {
            return redirect()
                ->route('users.index')
                ->with('success', $newActiveState
                    ? 'Account is already active.'
                    : 'Account is already inactive.');
        }

        DB::transaction(function () use ($request, $user, $newActiveState) {
            $user->loadMissing('member');

            $oldValues = [
                'user_is_active' => (bool) $user->is_active,
                'member_membership_status' => $user->member?->membership_status,
            ];

            MemberAccountStatusSynchronizer::syncUser($user, $newActiveState);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $newActiveState ? 'user_account_reactivated' : 'user_account_deactivated',
                'module' => 'users',
                'record_id' => $user->id,
                'description' => $newActiveState
                    ? 'User account and linked member profile were reactivated.'
                    : 'User account and linked member profile were deactivated.',
                'old_values' => $oldValues,
                'new_values' => [
                    'user_is_active' => (bool) $user->fresh()->is_active,
                    'member_membership_status' => $user->fresh()->member?->membership_status,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('users.index')
            ->with('success', $newActiveState
                ? 'Account reactivated successfully.'
                : 'Account deactivated successfully.');
    }
}
