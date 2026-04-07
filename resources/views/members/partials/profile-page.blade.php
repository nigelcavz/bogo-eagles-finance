@php
    $canManageFinance = auth()->user()?->canManageFinance() ?? false;
    $canManageMembers = auth()->user()?->canManageMembers() ?? false;
    $leadershipPositions = ['president', 'vice president', 'secretary', 'treasurer', 'officer'];
    $clubPosition = $member->club_position ?: 'Member';
    $clubPositionKey = \Illuminate\Support\Str::of($clubPosition)->lower()->trim()->toString();
    $clubPositionBadgeClasses = in_array($clubPositionKey, $leadershipPositions, true)
        ? 'border-amber-500/30 bg-amber-500/15 text-amber-200'
        : 'border-slate-500/30 bg-slate-500/15 text-slate-200';
@endphp

<x-slot name="header">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="section-heading">{{ $isSelfService ? 'Profile' : 'Member Detail' }}</h2>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <p class="text-2xl font-bold tracking-tight text-slate-100 sm:text-3xl">
                    {{ $member->full_name }}
                </p>
                <span class="status-badge {{ $clubPositionBadgeClasses }}">
                    {{ $clubPosition }}
                </span>
            </div>
            @if ($member->member_code)
                <p class="section-subheading mt-2">
                    {{ $member->member_code }}
                </p>
            @elseif ($isSelfService)
                <p class="section-subheading mt-2">
                    View your member profile, contribution history, and monthly dues coverage records.
                </p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($isSelfService)
                <a href="{{ route('profile.edit') }}" class="btn-secondary">
                    Account Settings
                </a>
            @else
                @if ($canManageFinance)
                    <a
                        href="{{ route('contributions.create', ['member_id' => $member->id]) }}"
                        class="btn-primary"
                    >
                        Record Contribution
                    </a>
                @endif

                @if ($canManageMembers)
                    <a
                        href="{{ route('members.edit', $member) }}"
                        class="btn-secondary"
                    >
                        Edit Member
                    </a>
                @endif
            @endif
        </div>
    </div>
</x-slot>

@include('members.partials.profile-content')
