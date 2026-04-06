<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Contribution
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Update safe payment details without changing the member, contribution type, or monthly coverage history.
                </p>
            </div>

            <a href="{{ route('contributions.types.show', ['type' => $type]) }}" class="btn-secondary">
                Back to {{ $contribution->category->name }}
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-4xl">
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
                <div class="app-panel-muted p-6 lg:col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900">Record Summary</h3>
                    <dl class="mt-4 space-y-3 text-sm text-gray-700">
                        <div>
                            <dt class="font-medium text-gray-500">Member</dt>
                            <dd>{{ $contribution->member->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Category</dt>
                            <dd>{{ $contribution->category->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Status</dt>
                            <dd>{{ ucfirst($contribution->status) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Posted</dt>
                            <dd>{{ $contribution->created_at?->format('M d, Y h:i A') ?? '--' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="app-panel lg:col-span-2">
                    <div class="panel-body">
                        <form method="POST" action="{{ route('contributions.update', $contribution) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-grid">
                                <div class="field-stack">
                                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input id="amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $contribution->amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="payment_date" class="block text-sm font-medium text-gray-700">Date Paid</label>
                                    <input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', $contribution->payment_date?->toDateString()) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>

                                <div class="field-stack">
                                    <label for="payment_type" class="block text-sm font-medium text-gray-700">Payment Type</label>
                                    <input id="payment_type" name="payment_type" type="text" value="{{ old('payment_type', $contribution->payment_type) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div class="field-stack">
                                    <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                                    <input id="reference_number" name="reference_number" type="text" value="{{ old('reference_number', $contribution->reference_number) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>

                                <div class="field-stack md:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea id="notes" name="notes" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $contribution->notes) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-800 pt-5">
                                <x-primary-button>Update Contribution</x-primary-button>
                                <a href="{{ route('contributions.types.show', ['type' => $type]) }}" class="btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
