<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * Pengganti halaman login ketika pikdi/tsu-foundation tidak terpasang.
 * Template tidak menyediakan login sendiri: halaman ini menjelaskan cara memasang autentikasi.
 */
class LoginGuideController extends Controller
{
    public function __invoke()
    {
        return view('auth.login-guide', ['title' => 'Login Belum Tersedia']);
    }
}
