<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">User Role Management</h2>
                <p class="section-subheading">Review user accounts and update permission roles from one admin-only page.</p>
            </div>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Accounts</h3>
                        <p class="mt-1 text-sm text-slate-400">Changing a non-admin user's role also updates the linked member club position automatically.</p>
                    </div>

                    <form method="GET" action="{{ route('users.index') }}" class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <label for="search" class="sr-only">Search users by name</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Search by name"
                            class="block w-full rounded-md border-gray-300 shadow-sm sm:w-72"
                        >
                        <div class="flex gap-2">
                            <x-primary-button>Search</x-primary-button>
                            @if ($search !== '')
                                <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="panel-body">
                    @if ($users->count())
                        <div class="mobile-record-list">
                            @foreach ($users as $managedUser)
                                <article class="mobile-record-card">
                                    <div class="mobile-record-header">
                                        <div class="min-w-0">
                                            <h4 class="mobile-record-title">{{ $managedUser->name }}</h4>
                                            <p class="mt-1 break-all text-sm text-slate-400">{{ $managedUser->email }}</p>
                                        </div>

                                        <span class="status-badge {{ $managedUser->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>

                                    <div class="mobile-kv">
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Role</div>
                                            <div class="mobile-kv-value">{{ \Illuminate\Support\Str::headline($managedUser->role) }}</div>
                                        </div>
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Linked Member</div>
                                            <div class="mobile-kv-value">
                                                @if ($managedUser->member)
                                                    {{ $managedUser->member->full_name }}
                                                @else
                                                    --
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mobile-kv-item">
                                            <div class="mobile-kv-label">Club Position</div>
                                            <div class="mobile-kv-value">{{ $managedUser->member?->club_position ?? '--' }}</div>
                                        </div>
                                    </div>

                                    <div class="mobile-action-row">
                                        <a href="{{ route('users.edit', $managedUser) }}" class="btn-secondary-accent">
                                            Edit Role
                                        </a>

                                        @if (! $managedUser->isAdmin())
                                            <form method="POST" action="{{ route('users.update-status', $managedUser) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}">
                                                <button
                                                    type="submit"
                                                    class="{{ $managedUser->is_active ? 'inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-red-200 transition hover:bg-red-500/20' : 'btn-secondary' }}"
                                                >
                                                    {{ $managedUser->is_active ? 'Deactivate' : 'Reactivate' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-slate-400">
                                                Admin account
                                            </span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="desktop-table-wrap">
                            <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Account Status</th>
                                        <th>Linked Member</th>
                                        <th>Club Position</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $managedUser)
                                        <tr>
                                            <td>
                                                <div class="font-medium text-slate-100">{{ $managedUser->name }}</div>
                                            </td>
                                            <td>{{ $managedUser->email }}</td>
                                            <td>
                                                <span class="status-badge {{ $managedUser->role === 'admin' ? 'border-violet-500/30 bg-violet-500/15 text-violet-200' : 'border-sky-500/30 bg-sky-500/15 text-sky-200' }}">
                                                    {{ \Illuminate\Support\Str::headline($managedUser->role) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $managedUser->is_active ? 'status-active' : 'status-inactive' }}">
                                                    {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($managedUser->member)
                                                    <a href="{{ route('members.show', $managedUser->member) }}" class="font-medium text-sky-300 transition hover:text-sky-200">
                                                        {{ $managedUser->member->full_name }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">--</span>
                                                @endif
                                            </td>
                                            <td>{{ $managedUser->member?->club_position ?? '--' }}</td>
                                            <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ route('users.edit', $managedUser) }}" class="btn-secondary-accent">
                                                        Edit Role
                                                    </a>

                                                    @if (! $managedUser->isAdmin())
                                                        <form method="POST" action="{{ route('users.update-status', $managedUser) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 0 : 1 }}">
                                                            <button
                                                                type="submit"
                                                                class="{{ $managedUser->is_active ? 'inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-200 transition hover:bg-red-500/20' : 'btn-secondary' }}"
                                                            >
                                                                {{ $managedUser->is_active ? 'Deactivate' : 'Reactivate' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-xs text-slate-500">Admin account</span>
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
                            {{ $users->links() }}
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No user accounts are available yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
