<?php

use App\Http\Middleware\AuthenticateJWT;
use App\Http\Middleware\AuthenticateSiswa;
use App\Http\Middleware\CheckFaseAktif;
use App\Http\Middleware\InjectSiswaAuthContext;
use App\Http\Middleware\RequireExamRole;
use App\Http\Middleware\VerifyJwtToken;
use App\Http\Middleware\VerifyServiceKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.siswa' => AuthenticateSiswa::class,
            'auth.jwt' => AuthenticateJWT::class,
            'jwt.verify' => VerifyJwtToken::class,
            'exam.role' => RequireExamRole::class,
            'service.auth' => VerifyServiceKey::class,
            'service.key' => VerifyServiceKey::class,
            'fase.aktif' => CheckFaseAktif::class,
            'siswa.context' => InjectSiswaAuthContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                    ], 422);
                }
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource tidak ditemukan',
                    ], 404);
                }
                if ($e instanceof HttpExceptionInterface) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Error',
                    ], $e->getStatusCode());
                }

                // Jangan bocorkan detail internal (nama koneksi, SQL, DSN)
                // ke klien saat APP_DEBUG tidak aktif.
                $debug = (bool) config('app.debug');

                return response()->json([
                    'success' => false,
                    'message' => $debug
                        ? ($e->getMessage() ?: 'Server Error')
                        : 'Internal Server Error',
                ], 500);
            }
        });
    })->create();
