<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCreateFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_room_create_form_does_not_show_location_field(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/rooms/create');

        $response->assertOk();
        $response->assertDontSee('Lokasi');
        $response->assertDontSee('name="location"');
    }
}
