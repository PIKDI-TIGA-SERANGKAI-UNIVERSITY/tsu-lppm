<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus user di proyek satelit = mengeluarkan akun login saja ("kick").
 * Data mahasiswa (seperti data dosen/tendik) tetap ada: user_id menjadi NULL dan akan ditautkan
 * kembali lewat NIM saat yang bersangkutan login SSO lagi.
 *
 * Mengganti foreign key data_mahasiswas.user_id dari ON DELETE CASCADE menjadi ON DELETE SET NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->changeUserForeignKey('set null');
    }

    public function down(): void
    {
        $this->changeUserForeignKey('cascade');
    }

    private function changeUserForeignKey(string $onDelete): void
    {
        $table = config('app.table.data_mahasiswas');
        $users = config('app.table.users');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'user_id')) {
            return;
        }

        $current = collect(Schema::getForeignKeys($table))->first(fn ($fk) => $fk['columns'] === ['user_id']);

        if ($current && strtolower((string) $current['on_delete']) === $onDelete) {
            return; // sudah sesuai
        }

        Schema::table($table, static function (Blueprint $blueprint) use ($current, $users, $onDelete) {
            if ($current) {
                // MySQL/PostgreSQL: nama asli FK; SQLite hanya mendukung drop berdasarkan kolom
                $blueprint->dropForeign(Schema::getConnection()->getDriverName() === 'sqlite' ? ['user_id'] : $current['name']);
            }

            $foreign = $blueprint->foreign('user_id')->references('id')->on($users);
            $onDelete === 'set null' ? $foreign->nullOnDelete() : $foreign->cascadeOnDelete();
        });
    }
};
