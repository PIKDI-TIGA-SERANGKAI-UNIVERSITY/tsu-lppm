<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * Bypass akses penuh untuk role pikdi, super admin, dan super admin {modul}
     * (Gate::before) sudah didaftarkan oleh pikdi/tsu-foundation.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
