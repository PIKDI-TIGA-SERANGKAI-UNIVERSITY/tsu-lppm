<?php

namespace Tests\Feature;

use App\Console\Commands\ServeCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * `php artisan serve` harus memakai perintah milik template (folder kerja = akar repo) dan router
 * sources/server.php yang menirukan aturan keamanan .htaccess akar repo.
 */
class ArtisanServeTest extends TestCase
{
    private ?Process $server = null;

    private string $baseUrl = '';

    protected function tearDown(): void
    {
        if ($this->server && $this->server->isRunning()) {
            $this->server->stop(1);
        }

        $this->app?->usePublicPath(base_path('public'));

        parent::tearDown();
    }

    public function test_serve_command_is_replaced_by_the_template_command(): void
    {
        $this->assertInstanceOf(ServeCommand::class, Artisan::all()['serve']);
    }

    public function test_document_root_is_the_repository_root(): void
    {
        $root = ServeCommand::documentRoot();

        $this->assertFileExists($root . DIRECTORY_SEPARATOR . 'index.php');
        $this->assertDirectoryExists($root . DIRECTORY_SEPARATOR . 'public');
        $this->assertFileExists($root . DIRECTORY_SEPARATOR . '.htaccess');
    }

    public function test_router_mirrors_the_htaccess_security_rules(): void
    {
        $this->startServer();

        // Aset statis di public/ dilayani
        $this->assertSame(200, $this->httpStatus('/public/robots.txt'));

        // Ditolak seperti .htaccess akar repo
        foreach (['/sources/.env', '/.git/config', '/.env', '/sources/composer.json', '/README.md', '/sources/artisan', '/public/', '/sources/'] as $blocked) {
            $this->assertSame(403, $this->httpStatus($blocked), "{$blocked} harus ditolak");
        }

        // Skrip PHP di public/ tidak boleh dieksekusi
        $probe = ServeCommand::documentRoot() . '/public/zz-uji-router.php';
        file_put_contents($probe, '<?php echo "dieksekusi";');
        try {
            $this->assertSame(403, $this->httpStatus('/public/zz-uji-router.php'));
        } finally {
            @unlink($probe);
        }

        // Selain itu diteruskan ke Laravel (bukan ditolak router)
        $this->assertNotSame(403, $this->httpStatus('/rute-yang-tidak-ada-sama-sekali'));
        $this->assertNotSame(403, $this->httpStatus('/index.php'));
    }

    public function test_htaccess_still_contains_the_rules_the_router_mirrors(): void
    {
        $htaccess = file_get_contents(ServeCommand::documentRoot() . '/.htaccess');

        // Bila salah satu aturan ini berubah, sesuaikan sources/server.php lalu perbarui tes ini
        $this->assertStringContainsString('RewriteRule (^|/)\.(?!well-known/) - [F,L]', $htaccess);
        $this->assertStringContainsString('RewriteRule ^(?!public/|index\.php$). - [F,L]', $htaccess);
        $this->assertStringContainsString('RewriteRule ^public/.*\.(php\d?|phtml|phar)$ - [F,L,NC]', $htaccess);
    }

    public function test_serve_command_moves_the_working_directory_to_the_repository_root(): void
    {
        $command = new class extends ServeCommand {
            public ?string $workingDirectory = null;

            protected function startProcess($hasEnvironment)
            {
                $this->workingDirectory = public_path();

                return new class {
                    public function isRunning(): bool
                    {
                        return false;
                    }

                    public function getExitCode(): int
                    {
                        return 0;
                    }
                };
            }
        };
        // Didaftarkan ke Artisan agar opsi global (--env) tersedia seperti saat `php artisan serve`
        $this->app[\Illuminate\Contracts\Console\Kernel::class]->registerCommand($command);

        $error = null;
        try {
            Artisan::call('serve', ['--no-reload' => true]);
        } catch (\Throwable $e) {
            $error = get_class($e) . ': ' . $e->getMessage();
        }

        $this->assertSame(ServeCommand::documentRoot(), $command->workingDirectory, (string) $error);
    }

    private function startServer(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($socket === false) {
            $this->markTestSkipped('Tidak dapat membuka port lokal: ' . $errstr);
        }
        $port = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        $root = ServeCommand::documentRoot();
        $this->server = new Process([PHP_BINARY, '-S', "127.0.0.1:{$port}", '-t', $root, base_path('server.php')], $root);
        $this->server->start();
        $this->baseUrl = "http://127.0.0.1:{$port}";

        for ($i = 0; $i < 50; $i++) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);
            if ($connection) {
                fclose($connection);

                return;
            }
            usleep(100000);
        }

        $this->markTestSkipped('Server bawaan PHP tidak dapat dijalankan: ' . $this->server->getErrorOutput());
    }

    private function httpStatus(string $path): int
    {
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 15, 'follow_location' => 0]]);
        @file_get_contents($this->baseUrl . $path, false, $context);

        return (int) (isset($http_response_header[0]) && preg_match('~\s(\d{3})\s~', $http_response_header[0] . ' ', $m) ? $m[1] : 0);
    }
}
