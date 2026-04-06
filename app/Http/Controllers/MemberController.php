<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Member::orderBy('last_name')
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

        Member::create($validated);

        return redirect()
            ->route('members.index')
            ->with('success', 'Member added successfully.');
    }

    public function edit(Member $member): View
    {
        return view('members.edit', compact('member'));
    }

    public function show(Member $member): View
    {
        $contributions = $member->contributions()
            ->with(['category', 'creator', 'coverages'])
            ->latest('payment_date')
            ->latest('id')
            ->paginate(10);

        $activeContributionTotal = $member->contributions()
            ->where('status', 'active')
            ->sum('amount');

        return view('members.show', compact('member', 'contributions', 'activeContributionTotal'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'member_code' => ['nullable', 'string', 'max:50', 'unique:members,member_code,' . $member->id],
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
}
