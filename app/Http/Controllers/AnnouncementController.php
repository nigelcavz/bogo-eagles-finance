<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        $announcements = Announcement::query()
            ->with(['creator', 'updater', 'event'])
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        return view('announcements.create', [
            'events' => $this->eventOptions(),
            'canManageCalendar' => $request->user()?->canManageCalendar() ?? false,
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        $validated = $request->validated();
        $shouldCreateEvent = (bool) ($validated['create_event'] ?? false);

        if ($shouldCreateEvent) {
            abort_unless($request->user()?->canManageCalendar(), 403);
        }

        DB::transaction(function () use ($request, $validated) {
            if ((bool) ($validated['create_event'] ?? false)) {
                $event = $this->createLinkedEvent($request, $validated);
                $validated['event_id'] = $event->id;
            }

            $announcement = Announcement::create($this->announcementPayload($validated, $request->user()->id));

            $this->logAnnouncementActivity(
                request: $request,
                action: 'announcement_created',
                description: 'Announcement created.',
                recordId: $announcement->id,
                oldValues: null,
                newValues: $this->auditableValues($announcement),
            );
        });

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        return view('announcements.edit', [
            'announcement' => $announcement,
            'events' => $this->eventOptions(),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated, $announcement) {
            $oldValues = $this->auditableValues($announcement);

            $announcement->update($this->announcementPayload(
                validated: $validated,
                actorId: $request->user()->id,
                currentAnnouncement: $announcement,
            ));

            $this->logAnnouncementActivity(
                request: $request,
                action: 'announcement_updated',
                description: 'Announcement updated.',
                recordId: $announcement->id,
                oldValues: $oldValues,
                newValues: $this->auditableValues($announcement->fresh()),
            );
        });

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($request->user()?->canManageAnnouncements(), 403);

        DB::transaction(function () use ($request, $announcement) {
            $oldValues = $this->auditableValues($announcement);
            $recordId = $announcement->id;

            $announcement->delete();

            $this->logAnnouncementActivity(
                request: $request,
                action: 'announcement_deleted',
                description: 'Announcement deleted.',
                recordId: $recordId,
                oldValues: $oldValues,
                newValues: null,
            );
        });

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    private function announcementPayload(array $validated, int $actorId, ?Announcement $currentAnnouncement = null): array
    {
        $isPublished = (bool) ($validated['is_published'] ?? false);

        return [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'event_id' => $validated['event_id'] ?? null,
            'visibility' => $validated['visibility'],
            'is_published' => $isPublished,
            'published_at' => $isPublished
                ? ($currentAnnouncement?->published_at ?? now())
                : null,
            'created_by' => $currentAnnouncement?->created_by ?? $actorId,
            'updated_by' => $currentAnnouncement ? $actorId : null,
        ];
    }

    private function auditableValues(Announcement $announcement): array
    {
        return [
            'title' => $announcement->title,
            'body' => $announcement->body,
            'event_id' => $announcement->event_id,
            'visibility' => $announcement->visibility,
            'is_published' => (bool) $announcement->is_published,
            'published_at' => optional($announcement->published_at)?->toDateTimeString(),
        ];
    }

    private function createLinkedEvent(Request $request, array $validated): Event
    {
        $event = Event::create([
            'title' => $validated['event_title'],
            'description' => $validated['event_description'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['event_start_time'] ?? null,
            'end_time' => $validated['event_end_time'] ?? null,
            'location' => $validated['event_location'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'event_created',
            'module' => 'events',
            'record_id' => $event->id,
            'description' => 'Calendar event created from announcement flow.',
            'old_values' => null,
            'new_values' => [
                'title' => $event->title,
                'event_date' => $event->event_date?->toDateString(),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'location' => $event->location,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return $event;
    }

    private function eventOptions()
    {
        return Event::query()
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->orderBy('title')
            ->get();
    }

    private function logAnnouncementActivity(
        Request $request,
        string $action,
        string $description,
        int $recordId,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'module' => 'announcements',
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
