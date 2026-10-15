<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginGuideController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Dimuat oleh RouteServiceProvider dalam grup middleware "web".
| Route modul berada di Modules/{Modul}/Routes/web.php.
|
*/

// Halaman depan (landing page)
Route::get('/', [HomeController::class, 'index'])->name('indexing')->middleware('guest');

// Buku Panduan Pengembang (/panduan) disediakan pikdi/tsu-core: route, tampilan, dan bab-bab milik paket.
// Bab milik starter kit ini didaftarkan di App\Providers\AppServiceProvider::registerDocs().

// Tanpa pikdi/tsu-foundation belum ada sistem login: tampilkan panduan memasang autentikasi.
// (Dengan tsu-foundation, route "login" disediakan oleh foundation: SSO, lokal, Emergency, Rescue.)
if (! class_exists(\Pikdi\TsuFoundation\TsuFoundationServiceProvider::class)) {
    Route::get('login', LoginGuideController::class)->name('login');
}
