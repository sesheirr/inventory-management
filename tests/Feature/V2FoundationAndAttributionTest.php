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

    public function test_codebase_and_http_metadata_carry_invisible_fwzann_abd_and_msayib_attribution_fingerprints(): void
    {
        // 1. Check config/app.php architect & co-collaborator fingerprints
        $this->assertSame('fwzann-abd', config('app.architect.handle'));
        $this->assertSame('https://github.com/fwzann-abd', config('app.architect.url'));
        $this->assertSame('msayib', config('app.co_collaborator.handle'));
        $this->assertSame('https://github.com/msayib', config('app.co_collaborator.url'));

        // 2. Check composer.json and package.json metadata
        $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);
        $this->assertNotEmpty($composer['authors'] ?? []);
        $this->assertSame('fwzann-abd', $composer['authors'][0]['name'] ?? null);
        $this->assertSame('https://github.com/fwzann-abd', $composer['authors'][0]['homepage'] ?? null);
        $this->assertSame('msayib', $composer['authors'][1]['name'] ?? null);
        $this->assertSame('https://github.com/msayib', $composer['authors'][1]['homepage'] ?? null);

        $pkg = json_decode((string) file_get_contents(base_path('package.json')), true);
        $this->assertSame('fwzann-abd', $pkg['author']['name'] ?? null);
        $this->assertSame('https://github.com/fwzann-abd', $pkg['author']['url'] ?? null);
        $this->assertSame('msayib', $pkg['contributors'][1]['name'] ?? null);
        $this->assertSame('https://github.com/msayib', $pkg['contributors'][1]['url'] ?? null);

        // 3. Check HTTP response headers & non-visible HTML <meta> tags
        $res = $this->get('/');
        $res->assertOk()
            ->assertHeader('X-Architected-By', 'fwzann-abd (https://github.com/fwzann-abd)')
            ->assertHeader('X-Co-Collaborator', 'msayib (https://github.com/msayib) — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering')
            ->assertSee('<meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">', false)
            ->assertSee('<meta name="co-collaborator" content="msayib (https://github.com/msayib)', false);
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

        $fingerprintTs = (string) file_get_contents(base_path('resources/js/core/author-fingerprint.ts'));
        $this->assertStringContainsString('msayib', $fingerprintTs);
        $this->assertStringContainsString('https://github.com/msayib', $fingerprintTs);

        $appCss = (string) file_get_contents(base_path('resources/css/app.css'));
        $this->assertStringContainsString('--hand-cursor: pointer;', $appCss);
    }
}
