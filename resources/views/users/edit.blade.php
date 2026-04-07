<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Edit User Role</h2>
                <p class="section-subheading">{{ $managedUser->name }} • {{ $managedUser->email }}</p>
            </div>

            <a href="{{ route('users.index') }}" class="btn-secondary">
                Back to User Roles
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-4xl">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md bg-red-100 p-4 text-red-800">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="app-panel-muted p-6">
                    <h3 class="text-lg font-semibold text-slate-100">Account Overview</h3>
                    <dl class="mt-4 space-y-3 text-sm text-slate-300">
                        <div>
                            <dt class="font-medium text-slate-400">Current Role</dt>
                            <dd>{{ \Illuminate\Support\Str::headline($managedUser->role) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-400">Linked Member</dt>
                            <dd>{{ $managedUser->member?->full_name ?? '--' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-400">Current Club Position</dt>
                            <dd>{{ $managedUser->member?->club_position ?? '--' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="app-panel lg:col-span-2">
                    <div class="panel-body">
                        <form method="POST" action="{{ route('users.update-role', $managedUser) }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="field-stack">
                                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                <select
                                    id="role"
                                    name="role"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    required
                                >
                                    @foreach ($availableRoles as $role)
                                        <option value="{{ $role }}" @selected(old('role', $managedUser->role) === $role)>
                                            {{ \Illuminate\Support\Str::headline($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-400">
                                    For linked non-admin users, saving a new role will automatically sync the member directory club position.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                                <x-primary-button>Save Role</x-primary-button>
                                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
