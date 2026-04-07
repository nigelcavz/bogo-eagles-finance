<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EventCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('calendar.index'));
        $this->assertTrue(Route::has('calendar.create'));
        $this->assertTrue(Route::has('calendar.store'));
    }

    public function test_authorized_role_can_create_event(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SECRETARY,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'title' => 'Board Meeting',
                'description' => 'Monthly planning session.',
                'event_date' => now()->addWeek()->toDateString(),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'location' => 'Clubhouse',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'title' => 'Board Meeting',
            'created_by' => $user->id,
            'location' => 'Clubhouse',
        ]);
    }

    public function test_member_cannot_create_event(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('calendar.store'), [
                'title' => 'Blocked Event',
                'event_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_created_event_keeps_expected_calendar_fields(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_OFFICER,
            'is_active' => true,
        ]);

        $event = Event::create([
            'title' => 'Outreach Drive',
            'description' => 'Community activity.',
            'event_date' => now()->addDays(3)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'location' => 'Town Plaza',
            'created_by' => $user->id,
        ]);

        $this->assertSame('Outreach Drive', $event->title);
        $this->assertSame('Town Plaza', $event->location);
        $this->assertSame($user->id, $event->created_by);
    }
}
