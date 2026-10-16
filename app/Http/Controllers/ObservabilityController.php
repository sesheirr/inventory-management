<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Admin Observability & Live 1000ms Heartbeat Controller
 *
 * Provides 1000ms auto-updating telemetry metrics, SQL query trace inspection,
 * filtering, and pagination for HTTP & SQL traces.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 *
 * @co-collaborator msayib <https://github.com/msayib>
 */

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
        $payload = $this->collectTelemetryPayload($request);

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'ok' => true,
                'refresh_interval_ms' => 1000,
                'heartbeat_interval_ms' => 1000,
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
                'pagination' => $payload['pagination'],
                'filters' => $payload['filters'],
                'tableStats' => $payload['tableStats'],
                'runtimeInfo' => $payload['runtimeInfo'],
                'telescopeEnabled' => true,
            ]);
        }

        return view('observability.index', $payload);
    }

    /**
     * 1000ms Automatic Heartbeat Telemetry Endpoint (`GET /observability/metrics`).
     */
    public function metrics(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'refresh_interval_ms' => 1000,
            'heartbeat_interval_ms' => 1000,
            ...$this->collectTelemetryPayload($request),
        ]);
    }

    public function clear(): RedirectResponse
    {
        Cache::forget(RequestObservabilityMiddleware::CACHE_KEY);

        ActivityLog::record(
            'deleted',
            'Membersihkan rekaman trace observability & telemetri HTTP/SQL',
            'Observability'
        );

        return redirect()
            ->route('observability.index')
            ->with('success', 'Rekaman telemetri & observability berhasil dibersihkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function collectTelemetryPayload(?Request $request = null): array
    {
        $allEntries = Cache::get(RequestObservabilityMiddleware::CACHE_KEY, []);
        if (! is_array($allEntries)) {
            $allEntries = [];
        }

        // Exclude the background 1s metrics heartbeat itself so the trace table stays clean
        $allEntries = array_values(array_filter($allEntries, function ($entry) {
            $path = (string) ($entry['path'] ?? '');

            return ! str_contains($path, 'observability/metrics') && ! str_contains($path, 'live/pulse');
        }));

        $totalRequests = count($allEntries);
        $durations = array_map(fn ($e) => (float) ($e['duration_ms'] ?? 0), $allEntries);
        $queryCounts = array_map(fn ($e) => (int) ($e['query_count'] ?? 0), $allEntries);
        $slowQueriesTotal = array_sum(array_map(fn ($e) => (int) ($e['slow_queries'] ?? 0), $allEntries));
        $memories = array_map(fn ($e) => (float) ($e['memory_mb'] ?? 0), $allEntries);

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

        // Apply optional search, method, and status filters before pagination
        $search = trim((string) ($request?->input('search', '') ?? ''));
        $methodFilter = strtoupper(trim((string) ($request?->input('method', '') ?? '')));
        $statusFilter = trim((string) ($request?->input('status', '') ?? ''));

        $filteredEntries = array_values(array_filter($allEntries, function ($e) use ($search, $methodFilter, $statusFilter) {
            if ($search !== '') {
                $haystack = strtolower(($e['path'] ?? '').' '.($e['method'] ?? '').' '.($e['user_name'] ?? ''));
                if (! str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }
            if ($methodFilter !== '' && $methodFilter !== 'ALL') {
                if (strtoupper((string) ($e['method'] ?? 'GET')) !== $methodFilter) {
                    return false;
                }
            }
            if ($statusFilter !== '' && $statusFilter !== 'all') {
                $st = (int) ($e['status'] ?? 200);
                if ($statusFilter === '2xx' && ($st < 200 || $st >= 300)) {
                    return false;
                }
                if ($statusFilter === '3xx' && ($st < 300 || $st >= 400)) {
                    return false;
                }
                if ($statusFilter === '4xx' && ($st < 400 || $st >= 500)) {
                    return false;
                }
                if ($statusFilter === '5xx' && $st < 500) {
                    return false;
                }
            }

            return true;
        }));

        $perPage = (int) ($request?->input('per_page', 10) ?? 10);
        if (! in_array($perPage, [5, 10, 20, 30, 50], true)) {
            $perPage = 10;
        }

        $filteredTotal = count($filteredEntries);
        $lastPage = max(1, (int) ceil($filteredTotal / $perPage));
        $currentPage = max(1, min($lastPage, (int) ($request?->input('page', 1) ?? 1)));
        $offset = ($currentPage - 1) * $perPage;
        $paginatedEntries = array_slice($filteredEntries, $offset, $perPage);

        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $filteredTotal,
            'last_page' => $lastPage,
            'from' => $filteredTotal > 0 ? $offset + 1 : 0,
            'to' => min($filteredTotal, $offset + count($paginatedEntries)),
        ];

        $filters = [
            'search' => $search,
            'method' => $methodFilter,
            'status' => $statusFilter,
            'per_page' => $perPage,
            'page' => $currentPage,
        ];

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
            'entries' => $paginatedEntries,
            'pagination' => $pagination,
            'filters' => $filters,
            'summary' => $summary,
            'tableStats' => $tableStats,
            'runtimeInfo' => $runtimeInfo,
            'max_entries' => RequestObservabilityMiddleware::MAX_ENTRIES,
        ];
    }
}
