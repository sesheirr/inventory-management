<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class SwitchDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:use 
                            {target : Target database configuration (local or live)}
                            {--test : Test connection only without writing to .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch active database in .env between local and live MySQL targets';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $target = strtolower($this->argument('target'));

        if (! in_array($target, ['local', 'live'])) {
            $this->error("Target tidak valid: '{$target}'. Gunakan 'local' atau 'live'.");

            return self::FAILURE;
        }

        $basePath = base_path();
        $envPath = base_path('.env');
        $targetEnvPath = base_path(".env.db.{$target}");
        $targetExamplePath = base_path(".env.db.{$target}.example");

        if (! file_exists($envPath)) {
            $this->error("File .env tidak ditemukan di {$envPath}.");

            return self::FAILURE;
        }

        if (! file_exists($targetEnvPath)) {
            if (file_exists($targetExamplePath)) {
                $this->warn("File {$targetEnvPath} belum ada. Membuat dari template {$targetExamplePath}...");
                copy($targetExamplePath, $targetEnvPath);
            } else {
                $this->error("File konfigurasi database target '{$targetEnvPath}' tidak ditemukan.");

                return self::FAILURE;
            }
        }

        // Parse target database settings
        $targetContent = file_get_contents($targetEnvPath);
        $targetVars = $this->parseEnvLines($targetContent);

        $requiredKeys = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($requiredKeys as $key) {
            if (! isset($targetVars[$key])) {
                $this->error("File {$targetEnvPath} tidak memiliki key '{$key}'.");

                return self::FAILURE;
            }
        }

        // Test target connection
        $connectionOk = $this->testDatabaseConnection(
            $targetVars['DB_CONNECTION'] ?? 'mysql',
            $targetVars['DB_HOST'] ?? '127.0.0.1',
            (int) ($targetVars['DB_PORT'] ?? 3306),
            $targetVars['DB_DATABASE'] ?? '',
            $targetVars['DB_USERNAME'] ?? 'root',
            $targetVars['DB_PASSWORD'] ?? ''
        );

        if ($this->option('test')) {
            return $connectionOk ? self::SUCCESS : self::FAILURE;
        }

        // Apply changes to .env
        $this->updateEnvFile($envPath, $targetVars);

        $this->info('---------------------------------------------------------');
        $this->info(' Berhasil beralih ke Database Target: [ '.strtoupper($target).' ]');
        $this->line('  - Host     : '.($targetVars['DB_HOST'] ?? '-'));
        $this->line('  - Port     : '.($targetVars['DB_PORT'] ?? '-'));
        $this->line('  - Database : '.($targetVars['DB_DATABASE'] ?? '-'));
        $this->line('  - User     : '.($targetVars['DB_USERNAME'] ?? '-'));
        $this->line('  - Status   : '.($connectionOk ? '<fg=green>TERHUBUNG (OK)</>' : '<fg=yellow>TIDAK TERHUBUNG (Periksa host/kredensial)</>'));
        $this->info('---------------------------------------------------------');

        // Clear config cache
        $this->callSilent('config:clear');

        return self::SUCCESS;
    }

    /**
     * Parse key-value pairs from env file content.
     */
    protected function parseEnvLines(string $content): array
    {
        $vars = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                // Strip surrounding quotes
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                $vars[$key] = $val;
            }
        }

        return $vars;
    }

    /**
     * Update target DB variables in the main .env file safely.
     */
    protected function updateEnvFile(string $envPath, array $newVars): void
    {
        $content = file_get_contents($envPath);
        $dbKeys = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];

        foreach ($dbKeys as $key) {
            if (! array_key_exists($key, $newVars)) {
                continue;
            }

            $val = $newVars[$key];
            // Format value with quotes if it contains spaces or special characters
            if (preg_match('/\s/', $val)) {
                $val = "\"{$val}\"";
            }

            // Match active non-commented key
            $pattern = "/^({$key}=).*$/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$val}", $content);
            } else {
                $content .= "\n{$key}={$val}";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Test raw database connection with timeout.
     */
    protected function testDatabaseConnection(string $driver, string $host, int $port, string $database, string $username, string $password): bool
    {
        $this->line("Menguji koneksi ke {$driver}://{$username}@{$host}:{$port}/{$database}...");

        if ($driver === 'sqlite') {
            $this->error('Target database adalah SQLite. Target yang diperbolehkan adalah MySQL (local/live).');

            return false;
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4, // 4 seconds timeout
            ];

            $pdo = new PDO($dsn, $username, $password, $options);
            $stmt = $pdo->query('SELECT VERSION()');
            $version = $stmt ? $stmt->fetchColumn() : 'unknown';

            $this->info("Koneksi berhasil! Versi MySQL Server: {$version}");

            return true;
        } catch (\Throwable $e) {
            $this->warn('Gagal terhubung ke database: '.$e->getMessage());

            return false;
        }
    }
}
