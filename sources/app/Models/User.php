<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TsuFoundationUser;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // TsuFoundationUser: memakai trait TsuUser milik pikdi/tsu-foundation (isAdmin(), profile_photo_url,
    // kolom SSO tersembunyi dari serialisasi) bila paket itu terpasang; aman bila foundation dilepas.
    use HasRoles, Notifiable, HasUuids, TsuFoundationUser;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'sso_id',
        'username',
        'name',
        'email',
        'avatar_url',
        'unit',
        'isactive',
        'sso_access_token',
        'sso_refresh_token',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sso_access_token',
        'sso_refresh_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'isactive' => 'boolean',
    ];

    public function getTable()
    {
        return config('app.table.users');
    }

    // Relasi ke Profil Mahasiswa
    public function mahasiswa()
    {
        return $this->hasOne(DataMahasiswa::class, 'user_id');
    }

    // Relasi ke Profil Dosen/Tendik
    public function dosenTendik()
    {
        $model = 'Pikdi\\TsuKaryawan\\Models\\DataDosenTendik';

        // Tanpa package pikdi/tsu-karyawan (atau tabelnya belum dimigrasi): relasi kosong agar $user->dosenTendik bernilai null
        if (! class_exists($model) || ! \Illuminate\Support\Facades\Schema::hasTable((new $model())->getTable())) {
            return $this->hasOne(static::class, 'id')->whereRaw('1 = 0');
        }

        return $this->hasOne($model, 'user_id');
    }

    /**
     * Cek apakah User ini Mahasiswa
     * Cara pakai: if ($user->isMahasiswa()) { ... }
     */
    public function isMahasiswa()
    {
        return $this->hasRole('mahasiswa');
    }

    /**
     * Cek apakah User ini Dosen atau Tendik
     */
    public function isDosenTendik()
    {
        return $this->hasRole(['dosen', 'tendik', 'admin_prodi', 'dekan']);
    }

    /**
     * Magic Accessor: Ambil data profil aktif secara otomatis
     * Cara pakai: $user->profil->nim atau $user->profil->nama_lengkap
     */
    public function getProfilAttribute()
    {
        if ($this->isMahasiswa()) {
            return $this->mahasiswa;
        }

        if ($this->isDosenTendik()) {
            return $this->dosenTendik;
        }
        return null;
    }

    /*
     * Hapus user = mengeluarkan akun login saja ("kick"). Profil mahasiswa & dosen/tendik TIDAK dihapus:
     * foreign key user_id bernilai NULL (nullOnDelete) dan profil ditautkan kembali lewat NIM/NIK
     * saat yang bersangkutan login SSO lagi.
     */
}
