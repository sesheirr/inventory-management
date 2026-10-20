<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserLoggedInNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnboardingPwaCropLoginNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cropped_avatar_takes_precedence_over_uncropped_file_input_when_saving_profile(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Petugas Inventaris',
            'email' => 'petugas@garutkab.go.id',
        ]);

        // Uncropped original file (100x200 png)
        $uncroppedFile = UploadedFile::fake()->image('original-uncropped.png', 100, 200);

        // 1x1 cropped JPEG base64 payload (> 64 bytes so storeCroppedAvatarDataUrl accepts it)
        $croppedImageFile = UploadedFile::fake()->image('cropped-1x1.jpg', 80, 80);
        $croppedBinary = file_get_contents($croppedImageFile->getRealPath());
        $croppedDataUri = 'data:image/jpeg;base64,'.base64_encode($croppedBinary);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Petugas Inventaris Updated',
            'email' => 'petugas@garutkab.go.id',
            'avatar' => $uncroppedFile,
            'cropped_avatar' => $croppedDataUri,
        ]);

        $response->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertStringEndsWith('.jpg', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertSame($croppedBinary, Storage::disk('public')->get($user->avatar));
    }

    public function test_other_users_receive_notification_when_a_user_logs_in(): void
    {
        Notification::fake();

        $actor = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@garutkab.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $otherUser1 = User::factory()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@garutkab.go.id',
            'role' => 'superadmin',
        ]);

        $otherUser2 = User::factory()->create([
            'name' => 'Andi Wijaya',
            'email' => 'andi@garutkab.go.id',
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => 'budi@garutkab.go.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        Notification::assertSentTo($otherUser1, UserLoggedInNotice::class);
        Notification::assertSentTo($otherUser2, UserLoggedInNotice::class);
        Notification::assertNotSentTo($actor, UserLoggedInNotice::class);
    }

    public function test_lan_https_info_and_ca_certificate_endpoints_return_valid_responses(): void
    {
        $response = $this->getJson('/api/lan-https-info');

        $response->assertOk()
            ->assertJsonStructure([
                'ok',
                'https_url',
                'ca_cert_url',
                'https_port',
                'lan_ip',
            ]);

        if (file_exists(storage_path('certs/lan-ca.crt')) || file_exists(public_path('lan-ca.crt'))) {
            $certResponse = $this->get('/lan-ca.crt');
            $certResponse->assertOk();
            $this->assertStringContainsString('application/x-x509-ca-cert', (string) $certResponse->headers->get('Content-Type'));
        }
    }
}
