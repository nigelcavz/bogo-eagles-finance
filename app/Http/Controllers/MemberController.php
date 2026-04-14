<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContributionCoverage;
use App\Models\Member;
use App\Models\User;
use App\Support\MemberAccountStatusSynchronizer;
use App\Support\MemberClubPositionMapper;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(): View
    {
        $request = request();

        abort_unless($request->user()?->canViewMembers(), 403);

        $search = trim((string) $request->string('search'));

        $members = Member::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', '!=', User::ROLE_ADMIN);
                    });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nameQuery) use ($search) {
                    $nameQuery->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhereRaw("TRIM(CONCAT(first_name, ' ', last_name)) like ?", ['%' . $search . '%'])
                        ->orWhereRaw("TRIM(CONCAT(last_name, ' ', first_name)) like ?", ['%' . $search . '%']);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('members.index', compact('members', 'search'));
    }

    public function create(): View
    {
        abort_unless(request()->user()?->canManageMembers(), 403);

        return view('members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageMembers(), 403);

        $validated = $request->validate([
            'member_code' => ['nullable', 'string', 'max:50', 'unique:members,member_code'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'club_position' => ['nullable', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'membership_status' => ['required', 'in:active,inactive'],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $temporaryPassword = Str::password(random_int(10, 12), true, true, true, false);

        $member = DB::transaction(function () use ($validated, $temporaryPassword, $request) {
            $memberAttributes = collect($validated)
                ->except('email')
                ->merge([
                    'club_position' => MemberClubPositionMapper::forRole(User::ROLE_MEMBER),
                ])
                ->all();

            $user = User::create([
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => User::ROLE_MEMBER,
                'is_active' => MemberAccountStatusSynchronizer::userActiveForMembershipStatus($validated['membership_status']),
                'must_change_password' => true,
            ]);

            $member = Member::create([
                ...$memberAttributes,
                'user_id' => $user->id,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'member_account_created',
                'module' => 'members',
                'record_id' => $member->id,
                'description' => 'Member profile and linked member account created.',
                'old_values' => null,
                'new_values' => [
                    'member_id' => $member->id,
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            return $member;
        });

        return redirect()
            ->route('members.index')
            ->with('success', 'Member added successfully and linked login account created.')
            ->with('new_member_account', [
                'member_name' => $member->full_name,
                'email' => $validated['email'],
                'temporary_password' => $temporaryPassword,
            ]);
    }

    public function edit(Member $member): View
    {
        abort_unless(request()->user()?->canManageMembers(), 403);

        return view('members.edit', compact('member'));
    }

    public function show(Member $member): View
    {
        abort_unless(request()->user()?->canViewMembers(), 403);

        return view('members.show', $this->buildMemberProfileViewData(
            request: request(),
            member: $member,
            isSelfService: false,
        ));
    }

    public function self(Request $request): View
    {
        abort_unless($request->user()?->canViewOwnMemberProfile(), 403);

        $user = $request->user()->loadMissing('member');

        if (! $user->member) {
            return view('members.self', [
                'member' => null,
            ]);
        }

        return view('members.self', $this->buildMemberProfileViewData(
            request: $request,
            member: $user->member,
            isSelfService: true,
        ));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        abort_unless($request->user()?->canManageMembers(), 403);

        $validated = $request->validate([
            'member_code' => ['nullable', 'string', 'max:50', 'unique:members,member_code,' . $member->id],
            'club_position' => ['nullable', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'membership_status' => ['required', 'in:active,inactive'],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($member, $validated) {
            $linkedUser = $member->user()->first();

            if ($linkedUser && ! $linkedUser->isAdmin()) {
                $validated['club_position'] = MemberClubPositionMapper::forRole($linkedUser->role) ?? $validated['club_position'] ?? null;
                $linkedUser->forceFill([
                    'is_active' => MemberAccountStatusSynchronizer::userActiveForMembershipStatus($validated['membership_status']),
                ])->save();
            }

            $member->update($validated);
        });

        return redirect()
            ->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function updateStatus(Request $request, Member $member): RedirectResponse
    {
        abort_unless($request->user()?->canManageMemberStatus(), 403);

        $validated = $request->validate([
            'membership_status' => ['required', 'in:active,inactive'],
        ]);

        $newActiveState = $validated['membership_status'] === MemberAccountStatusSynchronizer::STATUS_ACTIVE;

        if ($member->membership_status === $validated['membership_status']
            && (! $member->user || (bool) $member->user->is_active === $newActiveState)) {
            return redirect()
                ->to($request->input('redirect_to', route('members.show', $member)))
                ->with('success', $newActiveState
                    ? 'Member is already active.'
                    : 'Member is already inactive.');
        }

        DB::transaction(function () use ($request, $member, $newActiveState) {
            $member->loadMissing('user');

            $oldValues = [
                'membership_status' => $member->membership_status,
                'user_is_active' => $member->user?->is_active,
            ];

            MemberAccountStatusSynchronizer::syncMember($member, $newActiveState);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $newActiveState ? 'member_reactivated' : 'member_deactivated',
                'module' => 'members',
                'record_id' => $member->id,
                'description' => $newActiveState
                    ? 'Member profile and linked account were reactivated.'
                    : 'Member profile and linked account were set to inactive.',
                'old_values' => $oldValues,
                'new_values' => [
                    'membership_status' => $member->fresh()->membership_status,
                    'user_is_active' => $member->fresh()->user?->is_active,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->to($request->input('redirect_to', route('members.show', $member)))
            ->with('success', $newActiveState
                ? 'Member reactivated successfully.'
                : 'Member set to inactive successfully.');
    }

    private function buildMemberProfileViewData(Request $request, Member $member, bool $isSelfService): array
    {
        $member->loadMissing('user');
        $coverageSearch = trim((string) $request->string('coverage_search'));
        $coverageMonth = trim((string) $request->string('coverage_month'));

        $contributions = $member->contributions()
            ->with(['category', 'creator', 'voider', 'coverages'])
            ->latest('payment_date')
            ->latest('id')
            ->paginate(10, ['*'], 'contributions_page')
            ->withQueryString();

        $coverageHistory = ContributionCoverage::query()
            ->with([
                'contribution' => function ($query) {
                    $query->with(['category', 'creator', 'voider'])
                        ->withCount('coverages');
                },
            ])
            ->where('member_id', $member->id)
            ->when($coverageSearch !== '', function (Builder $query) use ($coverageSearch) {
                $query->where(function (Builder $searchQuery) use ($coverageSearch) {
                    $searchQuery->where('coverage_label', 'like', '%' . $coverageSearch . '%')
                        ->orWhere('coverage_year', 'like', '%' . $coverageSearch . '%');
                });
            })
            ->when($coverageMonth !== '' && preg_match('/^\d{4}-\d{2}$/', $coverageMonth), function (Builder $query) use ($coverageMonth) {
                [$year, $month] = array_map('intval', explode('-', $coverageMonth));

                $query->where('coverage_year', $year)
                    ->where('coverage_month', $month);
            })
            ->orderByDesc('coverage_year')
            ->orderByDesc('coverage_month')
            ->paginate(10, ['*'], 'coverages_page')
            ->fragment('coverage-history')
            ->withQueryString();

        $activeContributionTotal = $member->contributions()
            ->where('status', 'active')
            ->sum('amount');

        $contributionCount = $member->contributions()->count();
        $activeCoverageCount = ContributionCoverage::query()
            ->where('member_id', $member->id)
            ->whereHas('contribution', function ($query) {
                $query->where('status', 'active');
            })
            ->count();

        return [
            'member' => $member,
            'contributions' => $contributions,
            'coverageHistory' => $coverageHistory,
            'activeContributionTotal' => $activeContributionTotal,
            'contributionCount' => $contributionCount,
            'activeCoverageCount' => $activeCoverageCount,
            'linkedUser' => $member->user,
            'isSelfService' => $isSelfService,
            'coverageSearch' => $coverageSearch,
            'coverageMonth' => $coverageMonth,
        ];
    }
}
