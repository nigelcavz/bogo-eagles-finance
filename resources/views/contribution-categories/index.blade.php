<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Contribution Categories
            </h2>
            <p class="text-sm text-gray-600">
                Set up the contribution types that finance staff can post against.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
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
                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900">Add Category</h3>

                    <form method="POST" action="{{ route('contribution-categories.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>

                        <div>
                            <label for="default_amount" class="block text-sm font-medium text-gray-700">Default Amount</label>
                            <input id="default_amount" name="default_amount" type="number" step="0.01" min="0.01" value="{{ old('default_amount') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="is_active" name="is_active" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="1" @selected(old('is_active', '1') === '1')>Active</option>
                                <option value="0" @selected(old('is_active') === '0')>Inactive</option>
                            </select>
                        </div>

                        <x-primary-button>Save Category</x-primary-button>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900">Existing Categories</h3>

                    @if ($categories->count())
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full border border-gray-200 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-4 py-2 text-left">Name</th>
                                        <th class="border px-4 py-2 text-left">Default Amount</th>
                                        <th class="border px-4 py-2 text-left">Status</th>
                                        <th class="border px-4 py-2 text-left">Used In Contributions</th>
                                        <th class="border px-4 py-2 text-left">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td class="border px-4 py-2">
                                                <div class="font-medium text-gray-900">{{ $category->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $category->description ?: 'No description' }}</div>
                                            </td>
                                            <td class="border px-4 py-2">
                                    {{ $category->default_amount ? \App\Support\Currency::format($category->default_amount) : '--' }}
                                            </td>
                                            <td class="border px-4 py-2">
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2">{{ $category->contributions_count }}</td>
                                            <td class="border px-4 py-2">
                                                <a
                                                    href="{{ route('contribution-categories.edit', $category) }}"
                                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50"
                                                >
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-600">No contribution categories created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
