<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Public, Auth & Dashboard Inertia Tests
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V2PublicAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_v2_public_auth_and_dashboard_inertia_pages_render_and_carry_attribution(): void
    {
        $requiredPages = [
            'resources/js/pages/Welcome.tsx',
            'resources/js/pages/Auth/Login.tsx',
            'resources/js/pages/Auth/Register.tsx',
            'resources/js/pages/Auth/ForgotPassword.tsx',
            'resources/js/pages/Dashboard/Index.tsx',
        ];

        foreach ($requiredPages as $pagePath) {
            $full = base_path($pagePath);
            $this->assertFileExists($full, "Missing v2 page: {$pagePath}");
            $this->assertStringContainsString('fwzann-abd', (string) file_get_contents($full));
        }

        // Guest routes with ?v2=1 render Inertia root with component name
        $this->get('/?v2=1')
            ->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Welcome', false);

        $this->get('/login?v2=1')
            ->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Auth\\/Login', false);

        $this->get('/register?v2=1')
            ->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Auth\\/Register', false);

        $this->get('/forgot-password?v2=1')
            ->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Auth\\/ForgotPassword', false);

        // Authenticated dashboard with ?v2=1 renders Dashboard/Index Inertia payload
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)
            ->get('/dashboard?v2=1')
            ->assertOk()
            ->assertSee('data-page', false)
            ->assertSee('Dashboard\\/Index', false);
    }
}
