<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestObservabilityMiddleware
{
    public const CACHE_KEY = 'inventory_observability_entries';

    public const MAX_ENTRIES = 80;

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $queries = [];
        $totalSqlMs = 0.0;
        $slowQueriesCount = 0;

        DB::listen(function ($query) use (&$queries, &$totalSqlMs, &$slowQueriesCount): void {
            $timeMs = round((float) ($query->time ?? 0.0), 2);
            $totalSqlMs += $timeMs;
            if ($timeMs >= 50.0) {
                $slowQueriesCount++;
            }

            if (count($queries) < 25) {
                $queries[] = [
                    'sql' => mb_substr((string) $query->sql, 0, 280),
                    'time_ms' => $timeMs,
                    'connection' => $query->connectionName ?? config('database.default'),
                ];
            }
        });

        /** @var Response $response */
        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $totalSqlMs = round($totalSqlMs, 2);
        $memoryMb = round(memory_get_peak_usage(true) / 1048576, 2);
        $queryCount = count($queries);

        // Attach standard W3C Server-Timing & observability headers
        $response->headers->set(
            'Server-Timing',
            sprintf('app;dur=%s;desc="Laravel Total", db;dur=%s;desc="SQL (%d queries)"', $durationMs, $totalSqlMs, $queryCount)
        );
        $response->headers->set('X-Observability-Duration-Ms', (string) $durationMs);
        $response->headers->set('X-Observability-Queries', (string) $queryCount);

        // Skip recording noisy background polling unless slow or error
        $path = '/'.ltrim($request->path(), '/');
        $isPollingNoise = in_array($path, ['/api/live-state', '/up'], true) && $durationMs < 250 && $response->getStatusCode() < 400;

        if (! $isPollingNoise) {
            try {
                $entries = Cache::get(self::CACHE_KEY, []);
                if (! is_array($entries)) {
                    $entries = [];
                }

                $user = $request->user();
                array_unshift($entries, [
                    'id' => uniqid('obs_', true),
                    'timestamp' => now()->format('Y-m-d H:i:s'),
                    'method' => $request->method(),
                    'path' => $path,
                    'route' => optional($request->route())->getName() ?? '-',
                    'status' => $response->getStatusCode(),
                    'duration_ms' => $durationMs,
                    'query_count' => $queryCount,
                    'sql_ms' => $totalSqlMs,
                    'slow_queries' => $slowQueriesCount,
                    'memory_mb' => $memoryMb,
                    'user_name' => $user ? $user->name : 'Guest',
                    'user_role' => $user ? $user->role : 'guest',
                    'ip' => $request->ip(),
                    'queries' => array_slice($queries, 0, 12),
                ]);

                if (count($entries) > self::MAX_ENTRIES) {
                    $entries = array_slice($entries, 0, self::MAX_ENTRIES);
                }

                Cache::put(self::CACHE_KEY, $entries, now()->addHours(12));
            } catch (Throwable) {
                // Never break application flow if cache storage is unavailable
            }
        }

        return $response;
    }
}
