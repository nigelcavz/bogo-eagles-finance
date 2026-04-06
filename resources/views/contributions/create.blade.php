<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Record Contribution
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Record a member contribution using the existing contribution categories. Corrections should be handled through traceable updates, not deletion.
                </p>
            </div>

            <a
                href="{{ route('contributions.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Back to Contributions
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-5xl">
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
                                    <label for="member_id" class="block text-sm font-medium text-gray-700">Member</label>
                                    <select id="member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="">Select member</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" @selected((string) old('member_id', request('member_id')) === (string) $member->id)>
                                                {{ $member->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field-stack">
                                    <label for="contribution_category_id" class="block text-sm font-medium text-gray-700">Contribution Category</label>
                                    <select id="contribution_category_id" name="contribution_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="">Select category</option>
                                        @foreach ($categories as $category)
                                            <option
                                                value="{{ $category->id }}"
                                                data-default-amount="{{ $category->default_amount }}"
                                                @selected((string) old('contribution_category_id') === (string) $category->id)
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

                                <div class="field-stack md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Optional supporting notes">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                                <x-primary-button>Save Contribution</x-primary-button>
                                <a
                                    href="{{ route('contributions.index') }}"
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelect = document.getElementById('contribution_category_id');
            const amountInput = document.getElementById('amount');
            const otherDescriptionWrapper = document.getElementById('other-description-wrapper');

            if (!amountInput || !categorySelect || !otherDescriptionWrapper) {
                return;
            }

            const toggleOtherDescription = () => {
                const selectedText = categorySelect.options[categorySelect.selectedIndex]?.text?.trim().toLowerCase();

                otherDescriptionWrapper.classList.toggle('hidden', selectedText !== 'other');
            };

            categorySelect?.addEventListener('change', () => {
                if (amountInput.value) {
                    return;
                }

                const defaultAmount = categorySelect.options[categorySelect.selectedIndex]?.dataset.defaultAmount;

                if (defaultAmount) {
                    amountInput.value = defaultAmount;
                }

                toggleOtherDescription();
            });

            toggleOtherDescription();
        });
    </script>
</x-app-layout>
