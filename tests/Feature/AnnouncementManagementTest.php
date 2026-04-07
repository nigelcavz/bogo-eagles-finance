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
