<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Architecture & Attribution Verification
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V2FoundationAndAttributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_codebase_and_http_metadata_carry_invisible_fwzann_abd_attribution_fingerprint(): void
    {
        // 1. Check config/app.php architect fingerprint
        $this->assertSame('fwzann-abd', config('app.architect.handle'));
        $this->assertSame('https://github.com/fwzann-abd', config('app.architect.url'));

        // 2. Check composer.json and package.json metadata
        $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);
        $this->assertNotEmpty($composer['authors'] ?? []);
        $this->assertSame('fwzann-abd', $composer['authors'][0]['name'] ?? null);
        $this->assertSame('https://github.com/fwzann-abd', $composer['authors'][0]['homepage'] ?? null);

        $pkg = json_decode((string) file_get_contents(base_path('package.json')), true);
        $this->assertSame('fwzann-abd', $pkg['author']['name'] ?? null);
        $this->assertSame('https://github.com/fwzann-abd', $pkg['author']['url'] ?? null);

        // 3. Check HTTP response header & non-visible HTML <meta name="author">
        $res = $this->get('/');
        $res->assertOk()
            ->assertHeader('X-Architected-By', 'fwzann-abd (https://github.com/fwzann-abd)')
            ->assertSee('<meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">', false);
    }

    public function test_v2_react_inertia_foundation_and_mobile_gesture_primitives_exist(): void
    {
        $requiredFiles = [
            'resources/js/core/author-fingerprint.ts',
            'resources/js/stores/useUIStore.ts',
            'resources/js/stores/useCatalogStore.ts',
            'resources/js/components/ui/ResponsiveModal.tsx',
            'resources/js/components/ui/SwipeableRow.tsx',
            'resources/js/components/ui/SmartPhoneInput.tsx',
            'resources/js/layouts/AppLayout.tsx',
            'resources/js/layouts/AuthLayout.tsx',
            'resources/js/app.tsx',
        ];

        foreach ($requiredFiles as $relativePath) {
            $fullPath = base_path($relativePath);
            $this->assertFileExists($fullPath, "Missing v2 React module: {$relativePath}");
            $contents = (string) file_get_contents($fullPath);
            $this->assertStringContainsString('fwzann-abd', $contents, "Missing fwzann-abd fingerprint in {$relativePath}");
        }
    }
}
