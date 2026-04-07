<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="section-heading">Announcements</h2>
                <p class="section-subheading">Create, publish, update, and remove club announcements that can feed dashboard alerts.</p>
            </div>

            <a href="{{ route('announcements.create') }}" class="btn-primary">
                Add Announcement
            </a>
        </div>
    </x-slot>

    <div class="page-shell">
        <div class="page-content max-w-7xl">
            @if (session('success'))
                <div class="rounded-md bg-green-100 p-4 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="app-panel">
                <div class="panel-header">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-100">Announcement Records</h3>
                        <p class="mt-1 text-sm text-slate-400">Published announcements can be used by the dashboard notification bar for general club updates.</p>
                    </div>
                </div>

                <div class="panel-body">
                    @if ($announcements->count())
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Visibility</th>
                                        <th>Published</th>
                                        <th>Created By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($announcements as $announcement)
                                        <tr>
                                            <td>
                                                <div class="font-medium text-slate-100">{{ $announcement->title }}</div>
                                                <div class="mt-1 max-w-xl text-xs text-slate-400">{{ $announcement->body }}</div>
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $announcement->is_published ? 'status-active' : 'status-inactive' }}">
                                                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td>{{ \Illuminate\Support\Str::headline($announcement->visibility) }}</td>
                                            <td>{{ $announcement->published_at?->format('M d, Y h:i A') ?? '--' }}</td>
                                            <td>{{ $announcement->creator?->name ?? '--' }}</td>
                                            <td>
                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ route('announcements.edit', $announcement) }}" class="btn-secondary-accent">
                                                        Edit
                                                    </a>

                                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center rounded-md border border-red-500/30 bg-red-500/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-red-200 transition hover:bg-red-500/20">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $announcements->links() }}
                        </div>
                    @else
                        <p class="text-sm text-slate-400">No announcements have been created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
