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

                        <div>
                            <label for="event_id" class="block text-sm font-medium text-gray-700">Linked Event</label>
                            <select id="event_id" name="event_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">No linked event</option>
                                @foreach ($events as $event)
                                    <option value="{{ $event->id }}" @selected((int) old('event_id') === (int) $event->id)>
                                        {{ $event->event_date?->format('M d, Y') }} - {{ $event->title }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('event_id')" />
                        </div>

                        @if ($canManageCalendar)
                            <div class="rounded-2xl border border-slate-800/80 bg-slate-950/50 p-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-200">Create and Link New Event</h3>
                                        <p class="mt-1 text-sm text-slate-400">Use this when the announcement is specifically about an upcoming club event like a GMM.</p>
                                    </div>

                                    <label class="inline-flex items-center gap-3 rounded-lg border border-slate-700/80 bg-slate-900/80 px-4 py-3 text-sm text-slate-200">
                                        <input type="checkbox" name="create_event" value="1" class="rounded border-slate-600 bg-slate-900 text-sky-500 focus:ring-sky-400" @checked(old('create_event'))>
                                        Create a new event with this announcement
                                    </label>
                                </div>

                                <p class="mt-3 text-xs text-slate-500">If this is checked, leave the existing linked event selector empty and enter the new calendar event details here instead.</p>

                                <div class="mt-5 field-stack">
                                    <div>
                                        <label for="event_title" class="block text-sm font-medium text-gray-700">Event Title</label>
                                        <input id="event_title" name="event_title" type="text" value="{{ old('event_title', old('title')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <x-input-error class="mt-2" :messages="$errors->get('event_title')" />
                                    </div>

                                    <div>
                                        <label for="event_description" class="block text-sm font-medium text-gray-700">Event Description</label>
                                        <textarea id="event_description" name="event_description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('event_description') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('event_description')" />
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
                                            <input id="event_date" name="event_date" type="date" value="{{ old('event_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <x-input-error class="mt-2" :messages="$errors->get('event_date')" />
                                        </div>

                                        <div>
                                            <label for="event_start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                            <input id="event_start_time" name="event_start_time" type="time" value="{{ old('event_start_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <x-input-error class="mt-2" :messages="$errors->get('event_start_time')" />
                                        </div>

                                        <div>
                                            <label for="event_end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                            <input id="event_end_time" name="event_end_time" type="time" value="{{ old('event_end_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <x-input-error class="mt-2" :messages="$errors->get('event_end_time')" />
                                        </div>
                                    </div>

                                    <div>
                                        <label for="event_location" class="block text-sm font-medium text-gray-700">Location</label>
                                        <input id="event_location" name="event_location" type="text" value="{{ old('event_location') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <x-input-error class="mt-2" :messages="$errors->get('event_location')" />
                                    </div>
                                </div>
                            </div>
                        @endif

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
