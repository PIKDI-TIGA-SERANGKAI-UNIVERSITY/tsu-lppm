<?php

namespace App\Models\Concerns;

/*
 * Kemampuan user dari pikdi/tsu-foundation (isAdmin(), profile_photo_url, kolom SSO tersembunyi)
 * HANYA bila paket tersebut terpasang. Template tetap berjalan bila foundation dilepas
 * (composer remove pikdi/tsu-foundation) tanpa perlu mengubah model User.
 */
if (trait_exists(\Pikdi\TsuFoundation\Models\Concerns\TsuUser::class)) {
    trait TsuFoundationUser
    {
        use \Pikdi\TsuFoundation\Models\Concerns\TsuUser;
    }
} else {
    trait TsuFoundationUser
    {
    }
}
