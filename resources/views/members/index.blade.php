<x-app-layout>
    @php($canManageMembers = auth()->user()?->canManageMembers() ?? false)
    @php($canManageMemberStatus = auth()->user()?->canManageMemberStatus() ?? false)

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Members
                </h2>
            </div>

            @if ($canManageMembers)
                <div class="sm:ml-auto">
                    <a
                        href="{{ route('members.create') }}"
                        class="btn-primary w-full justify-center sm:w-auto"
                    >
                        Add Member
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('new_member_account'))
                @php($account = session('new_member_account'))
                <div class="mb-4 rounded-md bg-yellow-100 p-4 text-yellow-900">
                    <p class="font-semibold">Temporary member account password</p>
                    <p class="mt-1 text-sm">
                        {{ $account['member_name'] }} can sign in with <span class="font-medium">{{ $account['email'] }}</span>.
                    </p>
                    <p class="mt-3">
                        Temporary password:
                        <span class="rounded-md border border-amber-700/40 bg-amber-950/40 px-2 py-1 font-mono text-sm text-amber-100">
                            {{ $account['temporary_password'] }}
                        </span>
                    </p>
                    <p class="mt-2 text-xs">
                        This password is shown only once. The member will be required to change it on first login.
                    </p>
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-header">
                    <div class="min-w-0">
                        <h3 class="section-heading">Member Directory</h3>
                        <p class="section-subheading">Manage membership records and access history quickly.</p>
                    </div>
                    <form
                        method="GET"
                        action="{{ route('members.index') }}"
                        class="w-full sm:ml-auto sm:w-auto"
                    >
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <label for="member-search" class="sr-only">Search members</label>
                            <div class="relative min-w-0 sm:w-80">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.63a.75.75 0 1 0 1.06-1.06l-2.629-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0A4 4 0 0 1 5 9Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input
                                    id="member-search"
                                    name="search"
                                    type="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Search first or last name"
                                    autocomplete="off"
                                    oninput="clearTimeout(this._memberSearchTimer); this._memberSearchTimer = setTimeout(() => this.form.requestSubmit(), 220)"
                                    class="block w-full rounded-xl border border-slate-800 bg-slate-950/70 py-2.5 pl-10 pr-4 text-sm text-slate-100 placeholder:text-slate-500 focus:border-sky-500/60 focus:outline-none focus:ring-2 focus:ring-sky-500/20"
                                >
                            </div>
                            @if (! empty($search))
                                <a
                                    href="{{ route('members.index') }}"
                                    class="btn-secondary justify-center whitespace-nowrap"
                                >
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="panel-body text-gray-900">
                    @if ($members->count())
                        <div class="mobile-simple-list">
                            @foreach ($members as $member)
                                <a href="{{ route('members.show', $member) }}" class="mobile-simple-list-item">
                                    {{ $member->full_name }}
                                </a>
                            @endforeach
                        </div>

                        <div class="desktop-table-wrap">
                            <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Member Code</th>
                                        <th>Club Position</th>
                                        <th>Full Name</th>
                                        <th>Contact Number</th>
                                        <th>Status</th>
                                        <th>Joined Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $member)
                                        <tr>
                                            <td class="border px-4 py-2">{{ $member->member_code ?? '--' }}</td>
                                            <td class="border px-4 py-2">{{ $member->club_position ?? 'Member' }}</td>
                                            <td>
                                                <a href="{{ route('members.show', $member) }}" class="font-semibold text-sky-200 transition duration-150 ease-in-out hover:text-sky-100">
                                                    {{ $member->full_name }}
                                                </a>
                                            </td>
                                            <td class="border px-4 py-2">{{ $member->contact_number ?? '--' }}</td>
                                            <td>
                                                <span @class([
                                                    'status-badge',
                                                    'status-active' => $member->membership_status === 'active',
                                                    'status-inactive' => $member->membership_status === 'inactive',
                                                    'status-suspended' => $member->membership_status === 'suspended',
                                                ])>
                                                    {{ ucfirst($member->membership_status) }}
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2">
                                                {{ $member->joined_at ? $member->joined_at->format('M d, Y') : '--' }}
                                            </td>
                                            <td class="text-slate-300">
                                                <div class="flex flex-wrap gap-2">
                                                    <a
                                                        href="{{ route('members.show', $member) }}"
                                                        class="btn-secondary-accent"
                                                    >
                                                        History
                                                    </a>
                                                    @if ($canManageMemberStatus)
                                                        <form method="POST" action="{{ route('members.update-status', $member) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                                            <input type="hidden" name="membership_status" value="{{ $member->membership_status === 'active' ? 'inactive' : 'active' }}">
                                                            <button
                                                                type="submit"
                                                                class="{{ $member->membership_status === 'active' ? 'inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-200 transition hover:bg-red-500/20' : 'btn-secondary' }}"
                                                            >
                                                                {{ $member->membership_status === 'active' ? 'Set Inactive' : 'Reactivate' }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                    @if ($canManageMembers)
                                                        <a
                                                            href="{{ route('members.edit', $member) }}"
                                                            class="btn-secondary"
                                                        >
                                                            Edit
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            {{ $members->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">
                            {{ ! empty($search) ? 'No members matched your search.' : 'No members found yet.' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
