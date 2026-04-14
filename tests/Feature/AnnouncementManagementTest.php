<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_role_can_create_announcement_and_it_is_logged(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TREASURER,
            'is_active' => true,
        ]);
        $event = Event::create([
            'title' => 'General Assembly',
            'description' => 'Scheduled club gathering.',
            'event_date' => now()->addWeek()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'location' => 'Clubhouse',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('announcements.store'), [
            'title' => 'General Assembly',
            'body' => 'Monthly club gathering this Saturday.',
            'event_id' => $event->id,
            'visibility' => 'all',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('announcements.index'));

        $announcement = Announcement::first();

        $this->assertNotNull($announcement);
        $this->assertSame('General Assembly', $announcement->title);
        $this->assertTrue((bool) $announcement->is_published);
        $this->assertSame($event->id, $announcement->event_id);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'announcement_created',
            'module' => 'announcements',
            'record_id' => $announcement->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_authorized_role_can_create_linked_event_from_announcement_flow(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PRESIDENT,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('announcements.store'), [
            'title' => 'General Membership Meeting',
            'body' => 'Please attend the upcoming GMM this weekend.',
            'create_event' => '1',
            'event_title' => 'General Membership Meeting',
            'event_description' => 'Monthly GMM for all active members.',
            'event_date' => now()->addWeek()->toDateString(),
            'event_start_time' => '18:30',
            'event_end_time' => '20:00',
            'event_location' => 'Clubhouse Hall',
            'visibility' => 'all',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('announcements.index'));

        $event = Event::first();
        $announcement = Announcement::first();

        $this->assertNotNull($event);
        $this->assertNotNull($announcement);
        $this->assertSame($event->id, $announcement->event_id);
        $this->assertSame('General Membership Meeting', $event->title);
        $this->assertSame($user->id, $event->created_by);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'event_created',
            'module' => 'events',
            'record_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'announcement_created',
            'module' => 'announcements',
            'record_id' => $announcement->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_member_cannot_create_announcement(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('announcements.store'), [
                'title' => 'Blocked',
                'body' => 'This should not be allowed.',
                'visibility' => 'all',
            ])
            ->assertForbidden();
    }

    public function test_create_event_option_cannot_be_combined_with_existing_linked_event(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SECRETARY,
            'is_active' => true,
        ]);

        $event = Event::create([
            'title' => 'Existing Event',
            'description' => 'Already on the calendar.',
            'event_date' => now()->addDays(5)->toDateString(),
            'start_time' => '19:00',
            'end_time' => '20:00',
            'location' => 'Clubhouse',
            'created_by' => $user->id,
        ]);

        $response = $this->from(route('announcements.create'))
            ->actingAs($user)
            ->post(route('announcements.store'), [
                'title' => 'Conflicting Event Setup',
                'body' => 'This should fail validation.',
                'create_event' => '1',
                'event_id' => $event->id,
                'event_title' => 'New Event',
                'event_date' => now()->addWeek()->toDateString(),
                'visibility' => 'all',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('event_id');

        $this->assertDatabaseCount('announcements', 0);
        $this->assertDatabaseCount('events', 1);
    }

    public function test_authorized_role_can_update_and_delete_announcement_with_audit_logs(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PRESIDENT,
            'is_active' => true,
        ]);

        $announcement = Announcement::create([
            'title' => 'Initial Title',
            'body' => 'Initial body.',
            'visibility' => 'all',
            'is_published' => false,
            'created_by' => $user->id,
        ]);
        $event = Event::create([
            'title' => 'Board Meeting',
            'description' => 'Planning session.',
            'event_date' => now()->addDays(10)->toDateString(),
            'start_time' => '19:00',
            'end_time' => '21:00',
            'location' => 'Clubhouse',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('announcements.update', $announcement), [
            'title' => 'Updated Title',
            'body' => 'Updated body.',
            'event_id' => $event->id,
            'visibility' => 'all',
            'is_published' => '1',
        ])->assertRedirect(route('announcements.index'));

        $announcement->refresh();

        $this->assertSame('Updated Title', $announcement->title);
        $this->assertTrue((bool) $announcement->is_published);
        $this->assertSame($event->id, $announcement->event_id);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'announcement_updated',
            'module' => 'announcements',
            'record_id' => $announcement->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('announcements.destroy', $announcement))
            ->assertRedirect(route('announcements.index'));

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'announcement_deleted',
            'module' => 'announcements',
            'record_id' => $announcement->id,
            'user_id' => $user->id,
        ]);
    }
}
