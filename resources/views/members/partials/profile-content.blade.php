<div class="page-shell">
    <div class="page-content max-w-7xl">
        @if (session('success'))
            <div class="rounded-md bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-4">
            <div class="app-panel-muted p-5">
                <p class="text-sm font-medium text-slate-400">Posted Contributions</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-200">@money($activeContributionTotal)</p>
            </div>
            <div class="app-panel-muted p-5">
                <p class="text-sm font-medium text-slate-400">Contribution Records</p>
                <p class="mt-2 text-2xl font-semibold text-slate-100">{{ $contributionCount }}</p>
            </div>
            <div class="app-panel-muted p-5">
                <p class="text-sm font-medium text-slate-400">Posted Covered Months</p>
                <p class="mt-2 text-2xl font-semibold text-sky-200">{{ $activeCoverageCount }}</p>
            </div>
            <div class="app-panel-muted p-5">
                <p class="text-sm font-medium text-slate-400">Membership Status</p>
                <div class="mt-3">
                    <span @class([
                        'status-badge',
                        'status-active' => $member->membership_status === 'active',
                        'status-inactive' => $member->membership_status === 'inactive',
                        'status-suspended' => $member->membership_status === 'suspended',
                    ])>
                        {{ ucfirst($member->membership_status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="app-panel-muted p-6 lg:col-span-1">
                <h3 class="text-lg font-semibold text-slate-100">Profile Overview</h3>

                <div class="mt-5 space-y-6">
                    <div>
                        <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-400">Member Information</h4>
                        <dl class="mt-4 space-y-3 text-sm text-slate-300">
                            <div>
                                <dt class="font-medium text-slate-400">Member Code</dt>
                                <dd>{{ $member->member_code ?: '--' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Full Name</dt>
                                <dd>{{ $member->full_name }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Contact Number</dt>
                                <dd>{{ $member->contact_number ?: '--' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Address</dt>
                                <dd>{{ $member->address ?: '--' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Joined Date</dt>
                                <dd>{{ $member->joined_at?->format('M d, Y') ?: '--' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Birthdate</dt>
                                <dd>{{ $member->birthdate?->format('M d, Y') ?: '--' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-400">Notes</dt>
                                <dd>{{ $member->notes ?: '--' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="border-t border-slate-800 pt-6">
                        <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-400">Account Information</h4>
                        @if ($linkedUser)
                            <dl class="mt-4 space-y-3 text-sm text-slate-300">
                                <div>
                                    <dt class="font-medium text-slate-400">Name</dt>
                                    <dd>{{ $linkedUser->name }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-400">Email</dt>
                                    <dd>{{ $linkedUser->email }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-400">Role</dt>
                                    <dd>{{ \Illuminate\Support\Str::headline($linkedUser->role) }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-400">Account Status</dt>
                                    <dd>{{ $linkedUser->is_active ? 'Active' : 'Inactive' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-400">Must Change Password</dt>
                                    <dd>{{ $linkedUser->must_change_password ? 'Yes' : 'No' }}</dd>
                                </div>
                                <div>
                                    <dt class="font-medium text-slate-400">Email Verified</dt>
                                    <dd>{{ $linkedUser->email_verified_at?->format('M d, Y h:i A') ?: '--' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="mt-4 text-sm text-slate-400">No linked account record is attached to this member.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="app-panel lg:col-span-2">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Monthly Dues Coverage History</h3>
                        <p class="mt-1 text-sm text-slate-400">Coverage periods are listed from newest to oldest using the normalized monthly tracker records.</p>
                    </div>
                </div>
                <div class="panel-body">
                    @if ($coverageHistory->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Coverage</th>
                                        <th>Payment Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($coverageHistory as $coverage)
                                        <tr class="{{ $coverage->contribution?->status === 'voided' ? 'bg-slate-900/40 text-slate-400 hover:bg-slate-900/55' : '' }}">
                                            <td>{{ $coverage->coverage_label }}</td>
                                            <td>{{ $coverage->contribution?->payment_date?->format('M d, Y') ?: '--' }}</td>
                                            <td>{{ $coverage->contribution?->category?->name ?: '--' }}</td>
                                            <td class="{{ $coverage->contribution?->status === 'voided' ? 'text-slate-300' : 'font-semibold text-sky-200' }}">
                                                @if ($coverage->contribution)
                                                    @money($coverage->contribution->coverages_count > 0 ? ((float) $coverage->contribution->amount / $coverage->contribution->coverages_count) : $coverage->contribution->amount)
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $coverage->contribution?->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                    {{ ($coverage->contribution?->status ?? null) === 'active' ? 'Posted' : ucfirst($coverage->contribution?->status ?? 'unknown') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $coverageHistory->links() }}
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No monthly dues coverage history is recorded for this member yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="app-panel">
            <div class="panel-header">
                <div>
                    <h3 class="text-lg font-semibold text-slate-100">Contribution History</h3>
                    <p class="mt-1 text-sm text-slate-400">Contribution records are ordered from most recent to oldest.</p>
                </div>
            </div>
            <div class="panel-body">
                @if ($contributions->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Coverage</th>
                                    <th>Recorded By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contributions as $contribution)
                                    <tr class="{{ $contribution->status === 'voided' ? 'bg-slate-900/40 text-slate-400 hover:bg-slate-900/55' : '' }}">
                                        <td>{{ $contribution->payment_date->format('M d, Y') }}</td>
                                        <td>{{ $contribution->category->name }}</td>
                                        <td class="{{ $contribution->status === 'voided' ? 'text-slate-300' : 'font-semibold text-sky-200' }}">@money($contribution->amount)</td>
                                        <td>
                                            {{ $contribution->coverages->sortBy(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->map(fn ($coverage) => sprintf('%04d-%02d', $coverage->coverage_year, $coverage->coverage_month))->implode(', ') ?: '--' }}
                                        </td>
                                        <td>{{ $contribution->creator->name ?? '--' }}</td>
                                        <td>
                                            <span class="status-badge {{ $contribution->status === 'voided' ? 'border-red-500/30 bg-red-500/15 text-red-200' : 'status-active' }}">
                                                {{ $contribution->status === 'active' ? 'Posted' : ucfirst($contribution->status) }}
                                            </span>
                                            @if ($contribution->status === 'voided' && $contribution->void_reason)
                                                <p class="mt-2 text-xs text-red-200">
                                                    {{ $contribution->void_reason }}
                                                </p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $contributions->links() }}
                    </div>
                @else
                    <p class="text-sm text-slate-400">No contribution records are available for this member yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
