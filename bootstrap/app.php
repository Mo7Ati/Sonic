<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            \App\Http\Middleware\checkIncomingLocaleHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Instantiate the ExceptionHandler
        $exceptionHandler = new \App\Exceptions\ExceptionHandler();

        $exceptions->render(function (Throwable $e) use ($exceptionHandler) {

            Log::error('API Exception', [
                'message' => $e->getMessage(),
            ]);

            // Handle API or JSON-based responses
            if (request()->is('api/*'))
                return $exceptionHandler->handleApiException($e);
            // Handle Web-based (non-API) responses
        });
    })->create();
