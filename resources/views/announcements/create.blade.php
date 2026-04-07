<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Create Announcement</h2>
                <p class="section-subheading">Publish a clear club update that can also appear in the dashboard notification bar.</p>
            </div>

            <a href="{{ route('announcements.index') }}" class="btn-secondary">
                Back to Announcements
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-4xl">
            <div class="app-panel">
                <div class="panel-body">
                    <form method="POST" action="{{ route('announcements.store') }}" class="field-stack">
                        @csrf

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input id="title" name="title" type="text" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700">Announcement Details</label>
                            <textarea id="body" name="body" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('body') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('body')" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
                            <div>
                                <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                                <select id="visibility" name="visibility" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="all" @selected(old('visibility', 'all') === 'all')>All Users</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('visibility')" />
                            </div>

                            <label class="inline-flex items-center gap-3 rounded-lg border border-slate-700/80 bg-slate-900/80 px-4 py-3 text-sm text-slate-200">
                                <input type="checkbox" name="is_published" value="1" class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-400" @checked(old('is_published'))>
                                Publish immediately
                            </label>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="btn-primary">Save Announcement</button>
                            <a href="{{ route('announcements.index') }}" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
