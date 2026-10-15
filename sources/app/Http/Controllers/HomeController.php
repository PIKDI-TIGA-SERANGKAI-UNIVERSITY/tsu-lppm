<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * Halaman depan (landing page) aplikasi. Ubah sesuai kebutuhan proyek Anda.
 * FEATURE_DEMO_LANDING_PAGE=false menampilkan kanvas kosong (landing/blank).
 */
class HomeController extends Controller
{
    public function index()
    {
        $isDemoLanding = filter_var(config('tsu-core.features.demo_landing_page', true), FILTER_VALIDATE_BOOLEAN);

        $data = [
            'project_name'   => strtoupper((string) config('tsu-core.module.name')),
            'moduleFullName' => tsu_module_title(),
            'homebaseUrl'    => tsu_homebase_url(),
            'isDemoLanding'  => $isDemoLanding,
        ];

        return view($isDemoLanding ? 'landing.index' : 'landing.blank', $data);
    }
}
