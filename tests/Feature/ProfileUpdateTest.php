<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@example.com',
<<<<<<< HEAD
            'birth_date' => '2024-10-15',
=======
>>>>>>> 9045b62d7ecd2508d9f5d26bb1eb1bc61acbb644
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
<<<<<<< HEAD
        $this->assertSame('2024-10-15', $user->birth_date->format('Y-m-d'));
    }

    public function test_user_can_upload_avatar_profile(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotEmpty($user->avatar);
        $this->assertTrue(Storage::disk('public')->exists($user->avatar));
=======
>>>>>>> 9045b62d7ecd2508d9f5d26bb1eb1bc61acbb644
    }
}
