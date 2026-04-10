<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    //
    $exceptions->render(function (Throwable $e, $request) {

        if ($request->is('api/*')) {

            // DomainException = validación de negocio fallida = 422
            if ($e instanceof \DomainException) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            // Si la excepción ya tiene un response (ValidationException)
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                return $e->getResponse();
            }

            // Si no, devolver un JSON genérico
            return response()->json([
                'message' => $e->getMessage(),
            ], method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500);
        }
    });

    })->create();
