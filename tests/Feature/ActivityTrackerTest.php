<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActivityTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_tracker_route_is_registered_and_change_summary_is_readable(): void
    {
        $actor = User::factory()->role('treasurer')->create();

        $activityLog = ActivityLog::create([
            'user_id' => $actor->id,
            'action' => 'user_role_updated',
            'module' => 'users',
            'record_id' => $actor->id,
            'description' => 'User role updated through admin user management.',
            'old_values' => ['role' => 'member'],
            'new_values' => ['role' => 'treasurer'],
            'created_at' => now(),
        ]);

        $this->assertTrue(Route::has('activity-logs.index'));
        $this->assertSame('Users', $activityLog->formattedModule());
        $this->assertSame('User Role Updated', $activityLog->formattedAction());
        $this->assertSame([
            [
                'field' => 'Role',
                'from' => 'member',
                'to' => 'treasurer',
            ],
        ], $activityLog->changeSummary());
    }

    public function test_non_admin_cannot_access_activity_tracker_page(): void
    {
        $treasurer = User::factory()->role('treasurer')->create();

        $this->actingAs($treasurer)
            ->get(route('activity-logs.index'))
            ->assertForbidden();
    }
}
