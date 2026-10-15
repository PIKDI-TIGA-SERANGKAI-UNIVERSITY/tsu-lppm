<?php

namespace App\Support\Profiles;

use App\Models\DataMahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Pikdi\TsuFoundation\Profiles\ProfileProvider;

/**
 * Profil mahasiswa milik aplikasi (tabel data_mahasiswas), didaftarkan ke tsu-foundation
 * di AppServiceProvider. Dipakai saat login (profil aktif di session) dan sinkronisasi SSO.
 */
class MahasiswaProfileProvider extends ProfileProvider
{
    protected ?bool $available = null;

    public function roles(): array
    {
        return ['mahasiswa'];
    }

    public function available(): bool
    {
        return $this->available ??= Schema::hasTable((new DataMahasiswa())->getTable());
    }

    public function find(Model $user): ?Model
    {
        return DataMahasiswa::query()->where('user_id', $user->getKey())->first();
    }

    public function identity(Model $profile): ?string
    {
        return $profile->nim ?: null;
    }

    /**
     * MODE CLAIM PROFILE: tautkan/isi profil mahasiswa dari data SSO tanpa menimpa data yang sudah ada.
     */
    public function syncFromSso(Model $user, array $data): bool
    {
        $nim = $data['nim'] ?? $data['username'] ?? null;

        if (empty($nim)) {
            return false;
        }

        // Kolom profil => key data SSO (diisi hanya bila kosong / saat profil baru)
        $fields = [
            'nik_ktp'        => 'nik_ktp',
            'tempat_lahir'   => 'tempat_lahir',
            'tgl_lahir'      => 'tgl_lahir',
            'jenis_kelamin'  => 'jk',
            'agama'          => 'agama',
            'no_hp'          => 'no_hp',
            'email_pribadi'  => 'email_pribadi',
            'alamat_lengkap' => 'alamat',
            'nama_ayah'      => 'nama_ayah',
            'nama_ibu'       => 'nama_ibu',
            'no_hp_ortu'     => 'no_hp_ortu',
        ];

        $existing = DataMahasiswa::query()->where('nim', $nim)->first();

        if ($existing) {
            // Tautkan user_id ke akun yang login
            $existing->user_id = $user->getKey();

            if (empty($existing->nama) && ! empty($data['name'])) {
                $existing->nama = $data['name'];
            }
            foreach ($fields as $column => $key) {
                if (empty($existing->{$column}) && ! empty($data[$key])) {
                    $existing->{$column} = $data[$key];
                }
            }

            if ($existing->isDirty()) {
                $existing->save();
            }

            return true;
        }

        $newData = ['nim' => $nim, 'user_id' => $user->getKey(), 'nama' => $data['name'] ?? null];
        foreach ($fields as $column => $key) {
            if (! empty($data[$key])) {
                $newData[$column] = $data[$key];
            }
        }

        DataMahasiswa::query()->create($newData);

        return true;
    }
}
