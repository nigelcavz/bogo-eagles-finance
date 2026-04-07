<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Add Event</h2>
                <p class="section-subheading">Schedule a club event for the shared calendar and future dashboard reminders.</p>
            </div>

            <a href="{{ route('calendar.index') }}" class="btn-secondary">
                Back to Calendar
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-4xl">
            <div class="app-panel">
                <div class="panel-body">
                    <form method="POST" action="{{ route('calendar.store') }}" class="field-stack">
                        @csrf

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                            <input id="title" name="title" type="text" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
                                <input id="event_date" name="event_date" type="date" value="{{ old('event_date', $selectedDate) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <x-input-error class="mt-2" :messages="$errors->get('event_date')" />
                            </div>

                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input id="start_time" name="start_time" type="time" value="{{ old('start_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                            </div>

                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input id="end_time" name="end_time" type="time" value="{{ old('end_time') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
                            </div>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input id="location" name="location" type="text" value="{{ old('location') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="btn-primary">Save Event</button>
                            <a href="{{ route('calendar.index') }}" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
