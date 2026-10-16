<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RequestObservabilityMiddleware;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ObservabilityController extends Controller
{
    public function index(Request $request)
    {
        $payload = $this->collectTelemetryPayload();

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'ok' => true,
                'refresh_interval_ms' => 1000,
                ...$payload,
            ]);
        }

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Admin/Observability/Index', [
                'summary' => [
                    'avg_duration_ms' => $payload['summary']['avg_duration_ms'],
                    'p95_duration_ms' => $payload['summary']['p95_duration_ms'],
                    'avg_queries' => $payload['summary']['avg_queries'],
                    'slow_queries_total' => $payload['summary']['slow_queries_total'],
                    'peak_memory_mb' => $payload['summary']['peak_memory_mb'],
                    'total_requests' => $payload['summary']['total_requests'],
                    'recent_requests' => $payload['entries'],
                ],
                'tableStats' => $payload['tableStats'],
                'runtimeInfo' => $payload['runtimeInfo'],
                'telescopeEnabled' => true,
            ]);
        }

        return view('observability.index', $payload);
    }

    /**
     * 1-Second Realtime Telemetry Stream Endpoint (`GET /observability/metrics`).
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'refresh_interval_ms' => 1000,
            ...$this->collectTelemetryPayload(),
        ]);
    }

    public function clear(): RedirectResponse
    {
        Cache::forget(RequestObservabilityMiddleware::CACHE_KEY);

        return redirect()
            ->route('observability.index')
            ->with('success', 'Rekaman telemetri & observability berhasil dibersihkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function collectTelemetryPayload(): array
    {
        $entries = Cache::get(RequestObservabilityMiddleware::CACHE_KEY, []);
        if (! is_array($entries)) {
            $entries = [];
        }

        $totalRequests = count($entries);
        $durations = array_map(fn ($e) => (float) ($e['duration_ms'] ?? 0), $entries);
        $queryCounts = array_map(fn ($e) => (int) ($e['query_count'] ?? 0), $entries);
        $slowQueriesTotal = array_sum(array_map(fn ($e) => (int) ($e['slow_queries'] ?? 0), $entries));
        $memories = array_map(fn ($e) => (float) ($e['memory_mb'] ?? 0), $entries);

        $avgDurationMs = $totalRequests > 0 ? round(array_sum($durations) / $totalRequests, 2) : 0.0;
        $avgQueries = $totalRequests > 0 ? round(array_sum($queryCounts) / $totalRequests, 1) : 0.0;
        $peakMemoryMb = $totalRequests > 0 ? round(max($memories), 2) : round(memory_get_peak_usage(true) / 1048576, 2);

        $p95DurationMs = 0.0;
        if ($totalRequests > 0) {
            $sorted = $durations;
            sort($sorted);
            $idx = (int) min($totalRequests - 1, floor($totalRequests * 0.95));
            $p95DurationMs = round($sorted[$idx], 2);
        }

        $dbPingMs = 0.0;
        $dbConnected = false;
        try {
            $t0 = microtime(true);
            DB::select('SELECT 1');
            $dbPingMs = round((microtime(true) - $t0) * 1000, 2);
            $dbConnected = true;
        } catch (Throwable) {
            $dbConnected = false;
        }

        $tableStats = [
            'users' => User::count(),
            'products' => Product::count(),
            'categories' => Category::count(),
            'rooms' => Room::count(),
            'mutations' => Mutation::count(),
            'activity_logs' => ActivityLog::count(),
        ];

        $runtimeInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'db_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'db_connected' => $dbConnected,
            'db_ping_ms' => $dbPingMs,
            'server_time' => now()->format('H:i:s'),
        ];

        $summary = [
            'total_requests' => $totalRequests,
            'avg_duration_ms' => $avgDurationMs,
            'p95_duration_ms' => $p95DurationMs,
            'avg_queries' => $avgQueries,
            'slow_queries_total' => $slowQueriesTotal,
            'peak_memory_mb' => $peakMemoryMb,
        ];

        return [
            'entries' => $entries,
            'summary' => $summary,
            'tableStats' => $tableStats,
            'runtimeInfo' => $runtimeInfo,
            'max_entries' => RequestObservabilityMiddleware::MAX_ENTRIES,
        ];
    }
}
