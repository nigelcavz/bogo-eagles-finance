<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Announcement;
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

        $response = $this->actingAs($user)->post(route('announcements.store'), [
            'title' => 'General Assembly',
            'body' => 'Monthly club gathering this Saturday.',
            'visibility' => 'all',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('announcements.index'));

        $announcement = Announcement::first();

        $this->assertNotNull($announcement);
        $this->assertSame('General Assembly', $announcement->title);
        $this->assertTrue((bool) $announcement->is_published);

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

        $this->actingAs($user)->put(route('announcements.update', $announcement), [
            'title' => 'Updated Title',
            'body' => 'Updated body.',
            'visibility' => 'all',
            'is_published' => '1',
        ])->assertRedirect(route('announcements.index'));

        $announcement->refresh();

        $this->assertSame('Updated Title', $announcement->title);
        $this->assertTrue((bool) $announcement->is_published);

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
