<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Dedicated Webhook & Realtime Queue Server Command
 *
 * Automatically launched by `make dev` (`docker/run-dev.sh`) and `make run` (`docker/run-local.sh`)
 * alongside Laravel HTTP (`:8000`) and Bun Vite HMR (`:5173`).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 *
 * @co-collaborator msayib <https://github.com/msayib>
 */

namespace App\Console\Commands;

use App\Http\Controllers\WebhookController;
use App\Models\WebhookEndpoint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebhookServerCommand extends Command
{
    protected $signature = 'inventory:webhook-server
                            {--host=0.0.0.0 : Host interface for the Webhook Broker}
                            {--port=8085 : Port for the Webhook Broker HTTP receiver}
                            {--once : Bootstrap default endpoint, process one queue cycle, and exit}';

    protected $description = 'Menjalankan Webhook Broker Server (:8085) & Realtime Queue Worker untuk SIM-Inventaris Diskominfo Garut.';

    public function handle(): int
    {
        $host = (string) $this->option('host');
        $port = (int) $this->option('port');
        $once = (bool) $this->option('once');

        $this->bootstrapDefaultEndpoint($port);

        if ($once) {
            $this->components->info("Webhook Server ready on http://{$host}:{$port}/webhook (one-shot verification mode).");

            return self::SUCCESS;
        }

        $this->components->info("Starting SIM-Inventaris Webhook Broker on http://{$host}:{$port} + Realtime Queue Worker...");

        $brokerRouter = public_path('webhook-broker.php');
        $brokerProcess = null;

        if (is_file($brokerRouter) && function_exists('proc_open')) {
            $cmd = sprintf(
                '%s -S %s:%d %s',
                escapeshellarg(PHP_BINARY),
                escapeshellarg($host),
                $port,
                escapeshellarg($brokerRouter)
            );
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['file', storage_path('logs/webhook-server.log'), 'a'],
                2 => ['file', storage_path('logs/webhook-server.log'), 'a'],
            ];
            $brokerProcess = @proc_open($cmd, $descriptors, $pipes, base_path());
        }

        try {
            return Artisan::call('queue:work', [
                '--queue' => 'webhooks,default',
                '--sleep' => 1,
                '--tries' => 3,
                '--timeout' => 30,
            ], $this->getOutput());
        } finally {
            if (is_resource($brokerProcess)) {
                @proc_terminate($brokerProcess);
                @proc_close($brokerProcess);
            }
        }
    }

    protected function bootstrapDefaultEndpoint(int $port): void
    {
        try {
            if (! Schema::hasTable('webhook_endpoints')) {
                return;
            }

            if (WebhookEndpoint::count() === 0) {
                WebhookEndpoint::create([
                    'name' => 'Diskominfo Realtime Webhook Broker (Local)',
                    'target_url' => "http://127.0.0.1:{$port}/webhook",
                    'secret' => 'whsec_'.Str::random(32),
                    'events' => array_keys(WebhookController::AVAILABLE_EVENTS),
                    'is_active' => true,
                ]);
            }
        } catch (\Throwable) {
            // Non-blocking if database is still migrating
        }
    }
}
