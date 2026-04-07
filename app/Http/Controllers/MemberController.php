<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContributionCoverage;
use App\Models\Member;
use App\Models\User;
use App\Support\MemberClubPositionMapper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Member::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('role', '!=', 'admin');
                    });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        return view('members.create');
    }

    public function store(Request $request): RedirectResponse
    {
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
            'membership_status' => ['required', 'in:active,inactive,suspended'],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $temporaryPassword = Str::password(random_int(10, 12), true, true, true, false);

        $member = DB::transaction(function () use ($validated, $temporaryPassword, $request) {
            $memberAttributes = collect($validated)
                ->except('email')
                ->merge([
                    'club_position' => MemberClubPositionMapper::forRole('member'),
                ])
                ->all();

            $user = User::create([
                'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($temporaryPassword),
                'role' => 'member',
                'is_active' => true,
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
        return view('members.edit', compact('member'));
    }

    public function show(Member $member): View
    {
        return view('members.show', $this->buildMemberProfileViewData(
            request: request(),
            member: $member,
            isSelfService: false,
        ));
    }

    public function self(Request $request): View
    {
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
            'membership_status' => ['required', 'in:active,inactive,suspended'],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $member->update($validated);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    private function buildMemberProfileViewData(Request $request, Member $member, bool $isSelfService): array
    {
        $member->loadMissing('user');

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
            ->orderByDesc('coverage_year')
            ->orderByDesc('coverage_month')
            ->paginate(12, ['*'], 'coverages_page')
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
        ];
    }
}
