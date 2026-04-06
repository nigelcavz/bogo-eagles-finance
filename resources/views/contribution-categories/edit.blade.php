<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Contribution Category
                </h2>
                <p class="text-sm text-gray-600">
                    Update settings without deleting category history.
                </p>
            </div>

            <a
                href="{{ route('contribution-categories.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Back to Categories
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('contribution-categories.update', $contributionCategory) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $contributionCategory->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>

                    <div>
                        <label for="default_amount" class="block text-sm font-medium text-gray-700">Default Amount</label>
                        <input id="default_amount" name="default_amount" type="number" step="0.01" min="0.01" value="{{ old('default_amount', $contributionCategory->default_amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description', $contributionCategory->description) }}</textarea>
                    </div>

                    <div>
                        <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="is_active" name="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="1" @selected((string) old('is_active', (int) $contributionCategory->is_active) === '1')>Active</option>
                            <option value="0" @selected((string) old('is_active', (int) $contributionCategory->is_active) === '0')>Inactive</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-3 border-t pt-4">
                        <x-primary-button>Update Category</x-primary-button>
                        <a
                            href="{{ route('contribution-categories.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
