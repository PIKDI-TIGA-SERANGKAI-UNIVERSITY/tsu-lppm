<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Pikdi\TsuCore\Traits\TsuExceptionHandlerTrait;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Halaman error standar TSU (database down, 403 RBAC, 404, 419, HTTP error, 500 produksi)
     * disediakan oleh pikdi/tsu-core agar pembaruan tersalurkan lewat composer update.
     */
    use TsuExceptionHandlerTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    // Handle Unauthenticated (User Logout Sendiri / Sesi Mati)
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Render an exception into an HTTP response.
     * Tanpa penanganan khusus TSU (mis. APP_DEBUG=true pada error kode), Laravel menampilkan Ignition.
     */
    public function render($request, Throwable $e): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response|null
    {
        return $this->renderTsuException($request, $e) ?? parent::render($request, $e);
    }
}
