<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Edit Expense</h2>
                <p class="section-subheading">
                    Update the expense record carefully and provide a required reason so the audit trail remains clear.
                </p>
            </div>

            <a href="{{ route('expenses.index') }}" class="btn-secondary">
                Back to Expenses
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-5xl">
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
                    <h3 class="text-lg font-semibold text-slate-100">Record Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm text-slate-300">
                        <div>
                            <dt class="font-medium text-slate-400">Status</dt>
                            <dd>{{ ucfirst($expense->status) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-400">Created By</dt>
                            <dd>{{ $expense->creator->name ?? 'Unknown user' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-400">Date Posted</dt>
                            <dd>{{ $expense->created_at?->format('M d, Y h:i A') ?? '--' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-400">Last Updated</dt>
                            <dd>{{ $expense->updated_at?->format('M d, Y h:i A') ?? '--' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="app-panel lg:col-span-2">
                    <div class="panel-header">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-100">Expense Details</h3>
                            <p class="mt-1 text-sm text-slate-400">Changes remain on the same record, and the reason for edit is written to the activity log.</p>
                        </div>
                    </div>

                    <div class="panel-body">
                        <form method="POST" action="{{ route('expenses.update', $expense) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-grid">
                                <div class="field-stack">
                                    <label for="expense_category_id" class="block text-sm font-medium text-gray-700">Expense Category</label>
                                    <select id="expense_category_id" name="expense_category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                        <option value="">Select a category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(old('expense_category_id', $expense->expense_category_id) == $category->id)>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field-stack">
                                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $expense->amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="expense_date" class="block text-sm font-medium text-gray-700">Expense Date</label>
                                    <input id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', $expense->expense_date?->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="payee_name" class="block text-sm font-medium text-gray-700">Payee / Vendor</label>
                                    <input id="payee_name" name="payee_name" type="text" value="{{ old('payee_name', $expense->payee_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Purpose / Description</label>
                                    <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('description', $expense->description) }}</textarea>
                                </div>

                                <div class="field-stack">
                                    <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference / Receipt Number</label>
                                    <input id="reference_number" name="reference_number" type="text" value="{{ old('reference_number', $expense->reference_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div class="field-stack md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $expense->notes) }}</textarea>
                                </div>

                                <div class="field-stack md:col-span-2">
                                    <label for="edit_reason" class="block text-sm font-medium text-gray-700">Reason for Edit</label>
                                    <textarea id="edit_reason" name="edit_reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('edit_reason') }}</textarea>
                                    <p class="text-xs text-slate-400">This reason is stored in the activity log for audit review.</p>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-800 pt-5">
                                <x-primary-button>Update Expense</x-primary-button>
                                <a href="{{ route('expenses.index') }}" class="btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
