<?php

/**
 * Pemeriksaan instalasi tsu-project-template, dijalankan SEBELUM Laravel dimuat.
 *
 * Tanpa dependensi apa pun (tidak memakai vendor/), karena justru dipakai saat vendor/ belum ada.
 * Bila langkah instalasi wajib belum lengkap, index.php menampilkan halaman panduan singkat
 * (HTTP 503) alih-alih error PHP mentah yang juga membuka path server.
 *
 * Yang diperiksa (berurutan sesuai langkah instalasi):
 *   vendor : sources/vendor/autoload.php ada            -> composer install
 *   env    : sources/.env ada (atau APP_KEY dari environment server)
 *   key    : APP_KEY terisi kunci yang sah (16 atau 32 byte, mentah atau base64:)  -> php artisan key:generate
 *            Teks contoh di .env.example ("HAPUS_KALIMAT_INI_...") dianggap belum diisi.
 *
 * Konfigurasi yang sudah di-cache (php artisan config:cache, lazim di server) melewati pemeriksaan .env.
 * Pemeriksaan ini murah: beberapa file_exists dan, hanya bila perlu, membaca .env.
 */

if (! function_exists('tsu_setup_env_value')) {
    /**
     * Nilai satu baris .env tanpa tanda kutip dan tanpa komentar di belakangnya.
     */
    function tsu_setup_env_value(string $raw): string
    {
        $raw = trim($raw);

        if ($raw !== '' && ($raw[0] === '"' || $raw[0] === "'")) {
            $end = strpos($raw, $raw[0], 1);

            return $end === false ? substr($raw, 1) : substr($raw, 1, $end - 1);
        }

        return trim((string) preg_replace('/\s+#.*$/', '', $raw));
    }
}

if (! function_exists('tsu_setup_valid_key')) {
    /**
     * Kunci enkripsi yang sah untuk Laravel: 16 atau 32 byte (AES-128 / AES-256), mentah atau "base64:...".
     * Teks contoh atau nilai acak lain akan memicu "Unsupported cipher or incorrect key length".
     */
    function tsu_setup_valid_key(string $key): bool
    {
        $key = trim($key);

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return $decoded !== false && in_array(strlen($decoded), [16, 32], true);
        }

        return in_array(strlen($key), [16, 32], true);
    }
}

if (! function_exists('tsu_setup_problems')) {
    /**
     * @return array<int, string> daftar masalah: vendor, env, key
     */
    function tsu_setup_problems(string $sources): array
    {
        $problems = [];

        if (! is_file($sources . '/vendor/autoload.php')) {
            $problems[] = 'vendor';
        }

        if (is_file($sources . '/bootstrap/cache/config.php')) {
            return $problems;
        }

        $environmentKey = getenv('APP_KEY') ?: ($_SERVER['APP_KEY'] ?? ($_ENV['APP_KEY'] ?? ''));
        if (is_string($environmentKey) && trim($environmentKey) !== '') {
            if (! tsu_setup_valid_key($environmentKey)) {
                $problems[] = 'key';
            }

            return $problems;
        }

        $envFile = $sources . '/.env';
        if (! is_file($envFile)) {
            $problems[] = 'env';

            return $problems;
        }

        $key = preg_match('/^\s*APP_KEY\s*=(.*)$/m', (string) file_get_contents($envFile), $m) ? tsu_setup_env_value($m[1]) : '';
        if (! tsu_setup_valid_key($key)) {
            $problems[] = 'key';
        }

        return $problems;
    }
}

if (! function_exists('tsu_setup_environment')) {
    /**
     * APP_ENV tanpa Laravel: dari environment server, lalu dari .env. Default "local".
     */
    function tsu_setup_environment(string $sources): string
    {
        $env = getenv('APP_ENV') ?: ($_SERVER['APP_ENV'] ?? ($_ENV['APP_ENV'] ?? ''));

        if ((! is_string($env) || $env === '') && is_file($sources . '/.env')
            && preg_match('/^\s*APP_ENV\s*=\s*["\']?([A-Za-z0-9_-]+)/m', (string) file_get_contents($sources . '/.env'), $m)) {
            $env = $m[1];
        }

        return is_string($env) && $env !== '' ? strtolower($env) : 'local';
    }
}

if (! function_exists('tsu_render_setup_page')) {
    /**
     * Halaman "instalasi belum selesai". Di production hanya pesan singkat tanpa perintah teknis.
     *
     * @param  array<int, string>  $problems
     */
    function tsu_render_setup_page(array $problems, string $environment = 'local'): void
    {
        if (! headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store');
            header('Retry-After: 300');
        }

        $isProduction = $environment === 'production';
        $e = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $steps = [
            'vendor' => [
                'title' => 'Pasang dependensi (Composer)',
                'desc'  => 'Folder <code>sources/vendor</code> belum ada. Jalankan di folder <code>sources</code>:',
                'cmd'   => 'composer install',
                'hint'  => 'Paket TSU berada di repo GitHub privat PIKDI. Bila gagal karena akses, minta akses baca ke PIKDI lalu uji dengan <code>git ls-remote https://github.com/PIKDI-TIGA-SERANGKAI-UNIVERSITY/tsu-core.git</code>. Di Windows jalankan juga <code>git config --global core.longpaths true</code>.',
            ],
            'env' => [
                'title' => 'Buat berkas konfigurasi .env',
                'desc'  => 'Berkas <code>sources/.env</code> belum ada. Salin dari contoh, lalu isi koneksi database:',
                'cmd'   => 'cp .env.example .env',
                'hint'  => 'Di Command Prompt Windows: <code>copy .env.example .env</code>.',
            ],
            'key' => [
                'title' => 'Buat kunci aplikasi (APP_KEY)',
                'desc'  => '<code>APP_KEY</code> di <code>.env</code> masih kosong atau berisi teks contoh. Jalankan di folder <code>sources</code>:',
                'cmd'   => 'php artisan key:generate',
                'hint'  => '',
            ],
        ];

        $order = ['vendor', 'env', 'key'];
        $firstProblem = null;
        foreach ($order as $key) {
            if (in_array($key, $problems, true)) {
                $firstProblem = $key;
                break;
            }
        }

        echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<meta name="robots" content="noindex">'
            // Logo TSU tertanam: halaman ini tampil sebelum vendor/ dan aset core tersedia
            . '<link rel="icon" type="image/png" href="data:image/png;base64,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">'
            . '<title>' . ($isProduction ? 'Aplikasi Sedang Disiapkan' : 'Instalasi Belum Selesai') . '</title>'
            . '<style>'
            . ':root{--bg:#f4f7f8;--card:#ffffff;--text:#1e293b;--muted:#64748b;--primary:#094b54;--accent:#f8c12a;--ok:#15803d;--bad:#b91c1c;--code:#0f172a;--codetext:#e2e8f0;--border:#e2e8f0}'
            . '@media (prefers-color-scheme:dark){:root{--bg:#0b1220;--card:#111a2e;--text:#e2e8f0;--muted:#94a3b8;--primary:#38bdf8;--ok:#4ade80;--bad:#f87171;--code:#020617;--border:#1e293b}}'
            . '*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:15px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;padding:32px 16px}'
            . '.card{max-width:760px;margin:0 auto;background:var(--card);border:1px solid var(--border);border-top:4px solid var(--accent);border-radius:14px;padding:28px}'
            . '.brand{font-weight:700;color:var(--primary);letter-spacing:.04em;font-size:13px;text-transform:uppercase}'
            . 'h1{margin:6px 0 8px;font-size:24px}p{margin:0 0 12px}.muted{color:var(--muted)}'
            . 'ol{padding:0;margin:20px 0 0;list-style:none;counter-reset:s}li.step{border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:12px}'
            . 'li.step h2{font-size:16px;margin:0 0 6px;display:flex;gap:8px;align-items:center}'
            . '.badge{font-size:12px;font-weight:700;border-radius:999px;padding:2px 10px}.ok{color:var(--ok);border:1px solid var(--ok)}.todo{color:var(--bad);border:1px solid var(--bad)}.wait{color:var(--muted);border:1px solid var(--muted)}'
            . 'li.done,li.pending{opacity:.7}li.current{border-color:var(--primary);box-shadow:0 0 0 2px color-mix(in srgb,var(--primary) 25%,transparent)}'
            . 'pre{background:var(--code);color:var(--codetext);border-radius:8px;padding:10px 12px;margin:8px 0 0;overflow-x:auto;position:relative;font:13px/1.5 ui-monospace,Consolas,monospace}'
            . 'code{font-family:ui-monospace,Consolas,monospace;font-size:13px;background:color-mix(in srgb,var(--muted) 15%,transparent);padding:1px 5px;border-radius:4px}pre code{background:none;padding:0}'
            . 'button.copy{position:absolute;top:6px;right:6px;font-size:12px;border:0;border-radius:6px;padding:3px 8px;cursor:pointer;background:#334155;color:#e2e8f0}'
            . '.hint{font-size:13px;color:var(--muted);margin-top:8px}.next{margin-top:18px;padding-top:14px;border-top:1px solid var(--border)}'
            . '</style></head><body><div class="card"><div class="brand">Tiga Serangkai University &middot; PIKDI</div>';

        if ($isProduction) {
            echo '<h1>Aplikasi sedang disiapkan</h1>'
                . '<p class="muted">Aplikasi ini belum siap digunakan. Silakan coba beberapa saat lagi atau hubungi administrator.</p>'
                . '</div></body></html>';

            return;
        }

        echo '<h1>Instalasi belum selesai</h1>'
            . '<p class="muted">Aplikasi belum bisa dijalankan karena beberapa langkah instalasi belum dilakukan. Ikuti langkah yang bertanda <strong>Perlu dilakukan</strong> secara berurutan, lalu muat ulang halaman ini.</p>'
            . '<ol>';

        foreach ($order as $key) {
            $step = $steps[$key];
            $todo = in_array($key, $problems, true);
            // APP_KEY baru bisa diperiksa setelah .env ada: jangan ditandai "Sudah" bila .env belum ada
            $pending = $key === 'key' && in_array('env', $problems, true);

            if ($todo) {
                $class = $key === $firstProblem ? 'step current' : 'step';
                $badge = '<span class="badge todo">Perlu dilakukan</span>';
            } elseif ($pending) {
                $class = 'step pending';
                $badge = '<span class="badge wait">Menunggu langkah sebelumnya</span>';
            } else {
                $class = 'step done';
                $badge = '<span class="badge ok">Sudah</span>';
            }

            echo '<li class="' . $class . '"><h2>' . $badge . $e($step['title']) . '</h2>';
            if ($todo) {
                echo '<p>' . $step['desc'] . '</p><pre><code>' . $e($step['cmd']) . '</code><button class="copy" type="button">Salin</button></pre>';
                if ($step['hint'] !== '') {
                    echo '<div class="hint">' . $step['hint'] . '</div>';
                }
            }
            echo '</li>';
        }

        echo '</ol><div class="next"><strong>Setelah itu</strong>, muat ulang halaman ini. Bila database belum siap, aplikasi akan menampilkan halaman langkah menyiapkan database (membuat database dan menjalankan migrasi).'
            . '<p class="hint">Panduan lengkap ada di <code>README.md</code> dan berkas <code>sources/docs/panduan/mulai.md</code>. Setelah aplikasi berjalan, buka halaman <code>/panduan</code>.</p></div>'
            . '</div><script>document.querySelectorAll("button.copy").forEach(function(b){b.addEventListener("click",function(){var t=b.parentNode.querySelector("code").innerText;'
            . 'if(navigator.clipboard){navigator.clipboard.writeText(t).then(function(){b.textContent="Tersalin";setTimeout(function(){b.textContent="Salin"},1500)})}})});</script>'
            . '</body></html>';
    }
}
