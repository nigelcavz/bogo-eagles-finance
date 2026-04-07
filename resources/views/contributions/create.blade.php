<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Record Contribution
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Record a member contribution using the existing contribution categories. Monthly dues entries can cover one or more months using the normalized coverage tracker.
                </p>
            </div>

            <a
                href="{{ $backUrl }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                {{ $backLabel }}
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-5xl">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    <p class="font-semibold">Please fix the following errors:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-body text-gray-900">
                    @if ($categories->isEmpty())
                        <div class="rounded-md bg-yellow-100 p-4 text-yellow-900">
                            <p class="font-semibold">Contribution categories are required before recording payments.</p>
                            <a href="{{ route('contribution-categories.index') }}" class="mt-2 inline-block text-sm font-medium text-indigo-700 hover:text-indigo-800">
                                Open contribution category setup
                            </a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('contributions.store') }}">
                            @csrf

                            <div class="form-grid">
                                <div class="field-stack">
                                    <label for="member_search" class="block text-sm font-medium text-gray-700">Member</label>
                                    @php
                                        $selectedMember = $members->firstWhere('id', $selectedMemberId);
                                    @endphp
                                    <div class="relative">
                                        <input
                                            id="member_search"
                                            type="text"
                                            value="{{ old('member_search', $selectedMember ? ($selectedMember->full_name . ($selectedMember->member_code ? ' (' . $selectedMember->member_code . ')' : '')) : '') }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                            placeholder="Type a member name"
                                            autocomplete="off"
                                            required
                                        >
                                        <input id="member_id" name="member_id" type="hidden" value="{{ old('member_id', request('member_id')) }}" required>

                                        <div
                                            id="member-results-panel"
                                            class="absolute left-0 right-0 z-[70] mt-2 hidden overflow-hidden rounded-2xl border border-slate-700/80 bg-slate-900/95 shadow-2xl shadow-slate-950/50 ring-1 ring-slate-800/80 backdrop-blur"
                                        >
                                            <div id="member-results-list" class="max-h-72 overflow-y-auto py-2"></div>
                                            <div id="member-results-empty" class="hidden px-4 py-4 text-sm text-slate-400">
                                                No matching members found.
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Start typing to find a registered active member, then select the matching result.
                                    </p>
                                </div>

                                <div class="field-stack">
                                    <label for="contribution_category_id" class="block text-sm font-medium text-gray-700">Contribution Category</label>
                                    <select id="contribution_category_id" name="contribution_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option
                                                value="{{ $category->id }}"
                                                data-default-amount="{{ $category->default_amount }}"
                                                @selected((string) old('contribution_category_id', request('contribution_category_id')) === (string) $category->id)
                                            >
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field-stack">
                                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="payment_date" class="block text-sm font-medium text-gray-700">Contribution Date</label>
                                    <input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', now()->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="payment_type" class="block text-sm font-medium text-gray-700">Payment Type</label>
                                    <input id="payment_type" name="payment_type" type="text" value="{{ old('payment_type') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Cash, bank transfer, etc.">
                                </div>

                                <div class="field-stack">
                                    <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <input id="reference_number" name="reference_number" type="text" value="{{ old('reference_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Receipt or transfer reference">
                                </div>

                                <div id="other-description-wrapper" class="field-stack md:col-span-2 {{ old('other_description') ? '' : 'hidden' }}">
                                    <label for="other_description" class="block text-sm font-medium text-gray-700">
                                        Additional Description for Other Category
                                    </label>
                                    <textarea
                                        id="other_description"
                                        name="other_description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        placeholder="Describe the contribution when Other is selected"
                                    >{{ old('other_description') }}</textarea>
                                    <p class="text-xs text-gray-500">
                                        This will be stored with the contribution notes until a dedicated field is added later.
                                    </p>
                                </div>

                                <div id="monthly-coverage-wrapper" class="field-stack md:col-span-2 {{ old('coverage_year') || old('coverage_months') || ((int) $selectedCategoryId && optional($categories->firstWhere('id', $selectedCategoryId))->requiresMonthlyCoverage()) ? '' : 'hidden' }}">
                                    <div class="rounded-2xl border border-slate-800/80 bg-slate-950/40 p-5">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <label for="coverage_year" class="block text-sm font-medium text-gray-700">Monthly Coverage Year</label>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Monthly Dues/Contributions must record the exact covered months. Duplicate active month coverage is blocked.
                                                </p>
                                            </div>

                                            <div class="w-full sm:w-44">
                                                <input
                                                    id="coverage_year"
                                                    name="coverage_year"
                                                    type="number"
                                                    min="2000"
                                                    max="2100"
                                                    value="{{ $selectedCoverageYear }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                >
                                            </div>
                                        </div>

                                        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                                            @foreach ($monthOptions as $monthValue => $monthLabel)
                                                <label
                                                    class="coverage-month-option flex items-center gap-3 rounded-xl border border-slate-800/70 bg-slate-900/70 px-3 py-3 text-sm text-slate-200 transition duration-150 ease-in-out hover:border-slate-700 hover:bg-slate-900"
                                                    data-month-option="{{ $monthValue }}"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        name="coverage_months[]"
                                                        value="{{ $monthValue }}"
                                                        class="coverage-month-checkbox rounded border-slate-600 text-sky-400 shadow-sm focus:ring-sky-500"
                                                        data-month="{{ $monthValue }}"
                                                        @checked(in_array($monthValue, array_map('intval', old('coverage_months', [])), true))
                                                    >
                                                    <span class="font-medium">{{ $monthLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="field-stack md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Optional supporting notes">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                                <x-primary-button>Save Contribution</x-primary-button>
                                <a
                                    href="{{ $backUrl }}"
                                    class="btn-secondary"
                                >
                                    Cancel
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $memberSearchOptions = $members->map(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->full_name,
                'member_code' => $member->member_code,
                'label' => $member->full_name . ($member->member_code ? ' (' . $member->member_code . ')' : ''),
            ];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelect = document.getElementById('contribution_category_id');
            const amountInput = document.getElementById('amount');
            const memberSearchInput = document.getElementById('member_search');
            const memberIdInput = document.getElementById('member_id');
            const memberResultsPanel = document.getElementById('member-results-panel');
            const memberResultsList = document.getElementById('member-results-list');
            const memberResultsEmpty = document.getElementById('member-results-empty');
            const otherDescriptionWrapper = document.getElementById('other-description-wrapper');
            const monthlyCoverageWrapper = document.getElementById('monthly-coverage-wrapper');
            const coverageYearInput = document.getElementById('coverage_year');
            const paymentDateInput = document.getElementById('payment_date');
            const monthCheckboxes = Array.from(document.querySelectorAll('.coverage-month-checkbox'));
            const availabilityUrl = @json($availabilityUrl);
            const members = {{ \Illuminate\Support\Js::from($memberSearchOptions) }};
            let monthlyAvailabilityRequest = 0;

            if (!amountInput || !categorySelect || !otherDescriptionWrapper || !monthlyCoverageWrapper || !memberSearchInput || !memberIdInput || !coverageYearInput || !paymentDateInput || !memberResultsPanel || !memberResultsList || !memberResultsEmpty) {
                return;
            }

            const getSelectedCategoryOption = () => categorySelect.options[categorySelect.selectedIndex] ?? null;

            const getSelectedCategoryName = () => getSelectedCategoryOption()?.text?.trim().toLowerCase() ?? '';

            const isMonthlyDuesCategory = () => getSelectedCategoryName() === 'monthly dues/contributions';

            const getDefaultAmount = () => {
                const rawAmount = getSelectedCategoryOption()?.dataset.defaultAmount;
                const parsed = Number.parseFloat(rawAmount ?? '');

                return Number.isFinite(parsed) ? parsed : 0;
            };

            const getSelectedMonthlyCoverageCount = () => monthCheckboxes
                .filter((checkbox) => checkbox.checked && !checkbox.disabled)
                .length;

            const paymentDateMonth = () => {
                if (!paymentDateInput.value || !paymentDateInput.value.includes('-')) {
                    return null;
                }

                return Number.parseInt(paymentDateInput.value.split('-')[1], 10) || null;
            };

            const formatAmount = (value) => Number.isFinite(value) ? value.toFixed(2) : '';

            const calculateMonthlyDueAmount = () => {
                const baseAmount = getDefaultAmount();

                if (!baseAmount) {
                    return '';
                }

                const selectedMonthCount = getSelectedMonthlyCoverageCount();
                const effectiveMonthCount = selectedMonthCount > 0 ? selectedMonthCount : 1;

                if (selectedMonthCount === 12 && paymentDateMonth() === 1) {
                    return formatAmount(baseAmount * 10);
                }

                return formatAmount(baseAmount * effectiveMonthCount);
            };

            const syncAmountInput = () => {
                if (isMonthlyDuesCategory()) {
                    const monthlyAmount = calculateMonthlyDueAmount();

                    if (monthlyAmount !== '') {
                        amountInput.value = monthlyAmount;
                    }

                    return;
                }

                if (!amountInput.value) {
                    const defaultAmount = getDefaultAmount();
                    amountInput.value = defaultAmount ? formatAmount(defaultAmount) : '';
                }
            };

            const hideMemberResults = () => {
                memberResultsPanel.classList.add('hidden');
            };

            const showMemberResults = () => {
                memberResultsPanel.classList.remove('hidden');
            };

            const findExactMemberMatch = () => {
                const searchValue = memberSearchInput.value.trim().toLowerCase();
                return members.find((member) => member.label.toLowerCase() === searchValue);
            };

            const syncMemberSelection = () => {
                const match = findExactMemberMatch();
                memberIdInput.value = match?.id ?? '';
            };

            const renderMemberResults = () => {
                const searchValue = memberSearchInput.value.trim().toLowerCase();

                if (searchValue === '') {
                    hideMemberResults();
                    memberResultsEmpty.classList.add('hidden');
                    memberResultsList.innerHTML = '';
                    return;
                }

                const startsWithMatches = members.filter((member) =>
                    member.label.toLowerCase().startsWith(searchValue)
                    || member.name.toLowerCase().startsWith(searchValue)
                    || (member.member_code || '').toLowerCase().startsWith(searchValue)
                );

                const containsMatches = members.filter((member) =>
                    !startsWithMatches.includes(member) && (
                        member.label.toLowerCase().includes(searchValue)
                        || member.name.toLowerCase().includes(searchValue)
                        || (member.member_code || '').toLowerCase().includes(searchValue)
                    )
                );

                const filteredMembers = [...startsWithMatches, ...containsMatches].slice(0, 12);

                memberResultsList.innerHTML = '';

                if (filteredMembers.length === 0) {
                    memberResultsEmpty.classList.remove('hidden');
                    showMemberResults();
                    return;
                }

                memberResultsEmpty.classList.add('hidden');

                filteredMembers.forEach((member) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'flex w-full items-start justify-between gap-4 px-4 py-3 text-left transition duration-150 ease-in-out hover:bg-slate-800/90 focus:bg-slate-800/90 focus:outline-none';
                    button.dataset.memberId = member.id;
                    button.dataset.memberLabel = member.label;
                    button.innerHTML = `
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-slate-100">${member.name}</div>
                            <div class="mt-1 text-xs text-slate-400">${member.member_code ? `Code: ${member.member_code}` : 'Registered member'}</div>
                        </div>
                        <div class="shrink-0 rounded-full border border-slate-700 bg-slate-950/70 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-300">
                            Select
                        </div>
                    `;

                    button.addEventListener('click', () => {
                        memberSearchInput.value = member.label;
                        memberIdInput.value = String(member.id);
                        hideMemberResults();
                        refreshMonthlyAvailability();
                    });

                    memberResultsList.appendChild(button);
                });

                showMemberResults();
            };

            const toggleCategorySections = () => {
                const selectedText = getSelectedCategoryName();

                otherDescriptionWrapper.classList.toggle('hidden', selectedText !== 'other');
                monthlyCoverageWrapper.classList.toggle('hidden', selectedText !== 'monthly dues/contributions');
            };

            const applyCoveredMonths = (coveredMonths) => {
                const covered = new Set((coveredMonths || []).map((month) => Number(month)));

                monthCheckboxes.forEach((checkbox) => {
                    const month = Number(checkbox.dataset.month);
                    const wrapper = checkbox.closest('[data-month-option]');
                    const isCovered = covered.has(month);

                    checkbox.disabled = isCovered;
                    checkbox.checked = isCovered;

                    wrapper?.classList.toggle('opacity-60', isCovered);
                    wrapper?.classList.toggle('cursor-not-allowed', isCovered);
                    wrapper?.classList.toggle('border-emerald-500/30', isCovered);
                    wrapper?.classList.toggle('bg-emerald-500/10', isCovered);
                    wrapper?.classList.toggle('text-emerald-200', isCovered);
                });

                syncAmountInput();
            };

            const refreshMonthlyAvailability = async () => {
                const requestId = ++monthlyAvailabilityRequest;
                const selectedText = getSelectedCategoryName();

                applyCoveredMonths([]);

                if (selectedText !== 'monthly dues/contributions') {
                    return;
                }

                if (!memberIdInput.value || !coverageYearInput.value || !categorySelect.value) {
                    return;
                }

                const params = new URLSearchParams({
                    member_id: memberIdInput.value,
                    coverage_year: coverageYearInput.value,
                    contribution_category_id: categorySelect.value,
                });

                try {
                    const response = await fetch(`${availabilityUrl}?${params.toString()}`, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load monthly coverage availability.');
                    }

                    const payload = await response.json();
                    if (requestId !== monthlyAvailabilityRequest) {
                        return;
                    }
                    applyCoveredMonths(payload.covered_months ?? []);
                } catch (error) {
                    if (requestId === monthlyAvailabilityRequest) {
                        applyCoveredMonths([]);
                    }
                }
            };

            categorySelect?.addEventListener('change', () => {
                toggleCategorySections();
                syncAmountInput();
                refreshMonthlyAvailability();
            });

            memberSearchInput.addEventListener('input', () => {
                syncMemberSelection();
                renderMemberResults();
                refreshMonthlyAvailability();
            });

            memberSearchInput.addEventListener('focus', () => {
                if (memberSearchInput.value.trim() !== '') {
                    renderMemberResults();
                }
            });

            memberSearchInput.addEventListener('blur', () => {
                window.setTimeout(() => {
                    syncMemberSelection();
                    hideMemberResults();
                }, 120);
            });

            coverageYearInput.addEventListener('change', refreshMonthlyAvailability);
            coverageYearInput.addEventListener('input', refreshMonthlyAvailability);
            paymentDateInput.addEventListener('change', syncAmountInput);
            paymentDateInput.addEventListener('input', syncAmountInput);
            monthCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', syncAmountInput);
            });

            syncMemberSelection();
            toggleCategorySections();
            syncAmountInput();
            refreshMonthlyAvailability();
        });
    </script>
</x-app-layout>
