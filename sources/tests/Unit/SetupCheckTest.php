<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Halaman "instalasi belum selesai" (index.php) dan pesan artisan saat vendor/ belum ada.
 * Memakai PHPUnit murni karena pemeriksaan berjalan sebelum Laravel dimuat.
 */
class SetupCheckTest extends TestCase
{
    private string $root;

    /** @var array<string, mixed> */
    private array $saved = [];

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../../bootstrap/setup-check.php';

        $this->root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tsu-setup-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/sources/bootstrap', 0777, true);
        copy(__DIR__ . '/../../../index.php', $this->root . '/index.php');
        copy(__DIR__ . '/../../bootstrap/setup-check.php', $this->root . '/sources/bootstrap/setup-check.php');
        copy(__DIR__ . '/../../artisan', $this->root . '/sources/artisan');

        // Jangan terpengaruh APP_ENV/APP_KEY dari phpunit.xml atau environment pengembang
        foreach (['APP_KEY', 'APP_ENV'] as $key) {
            $this->saved[$key] = [getenv($key), $_SERVER[$key] ?? null, $_ENV[$key] ?? null];
            putenv($key);
            unset($_SERVER[$key], $_ENV[$key]);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);

        foreach ($this->saved as $key => [$env, $server, $environment]) {
            $env === false ? putenv($key) : putenv("{$key}={$env}");
            if ($server !== null) {
                $_SERVER[$key] = $server;
            }
            if ($environment !== null) {
                $_ENV[$key] = $environment;
            }
        }

        parent::tearDown();
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($path);
    }

    private function sources(): string
    {
        return $this->root . '/sources';
    }

    private function makeVendor(): void
    {
        mkdir($this->sources() . '/vendor', 0777, true);
        file_put_contents($this->sources() . '/vendor/autoload.php', '<?php');
    }

    public function test_reports_every_missing_step_in_order(): void
    {
        $this->assertSame(['vendor', 'env'], tsu_setup_problems($this->sources()));

        $this->makeVendor();
        $this->assertSame(['env'], tsu_setup_problems($this->sources()));

        file_put_contents($this->sources() . '/.env', "APP_NAME=Uji\nAPP_KEY=\n");
        $this->assertSame(['key'], tsu_setup_problems($this->sources()));

        file_put_contents($this->sources() . '/.env', "APP_NAME=Uji\nAPP_KEY=base64:" . base64_encode(random_bytes(32)) . "\n");
        $this->assertSame([], tsu_setup_problems($this->sources()));
    }

    public function test_quoted_and_commented_keys_are_handled(): void
    {
        $this->makeVendor();

        file_put_contents($this->sources() . '/.env', 'APP_KEY="base64:' . base64_encode(random_bytes(32)) . "\"\n");
        $this->assertSame([], tsu_setup_problems($this->sources()));

        file_put_contents($this->sources() . '/.env', "# APP_KEY=rahasia-lama\nAPP_KEY= # kosong\n");
        $this->assertSame(['key'], tsu_setup_problems($this->sources()));
    }

    public function test_placeholder_or_malformed_keys_are_not_accepted(): void
    {
        $this->makeVendor();

        // Teks contoh dari .env.example
        copy(__DIR__ . '/../../.env.example', $this->sources() . '/.env');
        $this->assertSame(['key'], tsu_setup_problems($this->sources()), 'Teks contoh APP_KEY harus dianggap belum diisi');

        $cases = [
            'base64:' . base64_encode(random_bytes(32)) => [],
            'base64:' . base64_encode(random_bytes(16)) => [],
            '"base64:' . base64_encode(random_bytes(32)) . '" # kunci' => [],
            str_repeat('a', 32) => [],
            'base64:' . base64_encode(random_bytes(20)) => ['key'],
            'base64:bukan-base64!!' => ['key'],
            'rahasia' => ['key'],
        ];
        foreach ($cases as $value => $expected) {
            file_put_contents($this->sources() . '/.env', "APP_ENV=local\nAPP_KEY={$value}\n");
            $this->assertSame($expected, tsu_setup_problems($this->sources()), "APP_KEY={$value}");
        }

        putenv('APP_KEY=bukan-kunci');
        $this->assertSame(['key'], tsu_setup_problems($this->sources()), 'Kunci dari environment server juga divalidasi');
    }

    public function test_environment_key_or_cached_config_skip_the_env_file_check(): void
    {
        $this->makeVendor();

        putenv('APP_KEY=base64:' . base64_encode(random_bytes(32)));
        $this->assertSame([], tsu_setup_problems($this->sources()), 'Server dengan APP_KEY dari environment tidak butuh .env');
        putenv('APP_KEY');

        mkdir($this->sources() . '/bootstrap/cache', 0777, true);
        file_put_contents($this->sources() . '/bootstrap/cache/config.php', '<?php return [];');
        $this->assertSame([], tsu_setup_problems($this->sources()), 'Konfigurasi ter-cache melewati pemeriksaan .env');
    }

    public function test_environment_is_read_from_env_file_or_server(): void
    {
        $this->assertSame('local', tsu_setup_environment($this->sources()));

        file_put_contents($this->sources() . '/.env', "APP_ENV=production\n");
        $this->assertSame('production', tsu_setup_environment($this->sources()));

        putenv('APP_ENV=staging');
        $this->assertSame('staging', tsu_setup_environment($this->sources()));
    }

    public function test_page_lists_the_steps_without_leaking_server_paths(): void
    {
        ob_start();
        tsu_render_setup_page(['vendor', 'env'], 'local');
        $html = ob_get_clean();

        $this->assertStringContainsString('Instalasi belum selesai', $html);
        $this->assertStringContainsString('composer install', $html);
        $this->assertStringContainsString('cp .env.example .env', $html);
        // Langkah database ditangani halaman error database tsu-core (muncul setelah langkah ini selesai)
        $this->assertStringNotContainsString('migrate', $html);
        $this->assertStringContainsString('halaman langkah menyiapkan database', $html);
        $this->assertSame(2, substr_count($html, 'badge todo'));
        $this->assertStringContainsString('Menunggu langkah sebelumnya', $html, 'APP_KEY tidak boleh ditandai Sudah selama .env belum ada');
        $this->assertStringNotContainsString('badge ok', $html);
        $this->assertStringNotContainsString($this->root, $html);
        $this->assertStringNotContainsString(str_replace('/', '\\', $this->root), $html);
    }

    public function test_page_embeds_the_tsu_favicon_because_core_assets_are_not_installed_yet(): void
    {
        foreach (['local', 'production'] as $environment) {
            ob_start();
            tsu_render_setup_page(['vendor'], $environment);
            $html = ob_get_clean();

            $this->assertMatchesRegularExpression('~<link rel="icon" type="image/png" href="data:image/png;base64,[A-Za-z0-9+/=]{500,}">~', $html);
        }
    }

    public function test_production_shows_only_a_short_message(): void
    {
        ob_start();
        tsu_render_setup_page(['vendor'], 'production');
        $html = ob_get_clean();

        $this->assertStringContainsString('Aplikasi sedang disiapkan', $html);
        $this->assertStringNotContainsString('composer install', $html);
        $this->assertStringNotContainsString('github.com', $html);
    }

    public function test_real_index_php_without_vendor_shows_the_setup_page(): void
    {
        $process = new Process([PHP_BINARY, $this->root . '/index.php'], $this->root);
        $process->run();

        $this->assertStringContainsString('Instalasi belum selesai', $process->getOutput());
        $this->assertStringNotContainsString('Fatal error', $process->getOutput() . $process->getErrorOutput());
    }

    public function test_real_artisan_without_vendor_explains_what_to_do(): void
    {
        $process = new Process([PHP_BINARY, 'artisan', 'list'], $this->sources());
        $process->run();

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('composer install', $process->getErrorOutput());
        $this->assertStringNotContainsString('Fatal error', $process->getOutput() . $process->getErrorOutput());
    }
}
