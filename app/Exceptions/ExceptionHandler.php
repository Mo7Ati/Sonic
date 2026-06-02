<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    /**
     * Handle API exceptions, rendering them through the canonical response envelope.
     */
    public function handleApiException(Throwable $e): JsonResponse
    {
        $debug = [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if ($e instanceof ValidationException) {
            return apiEnvelope(
                success: false,
                message: $e->getMessage(),
                status: 422,
                errors: $e->errors(),
                debug: $debug,
            );
        }

        if ($e instanceof AuthenticationException || $e->getMessage() === 'Route [login] not defined.') {
            return apiEnvelope(false, __('auth.unauthenticated'), 401, debug: $debug);
        }

        if ($e instanceof AuthorizationException) {
            return apiEnvelope(false, __('auth.unauthorized'), 403, debug: $debug);
        }

        if ($e instanceof NotFoundHttpException) {
            return apiEnvelope(false, __('errors.not_found'), 404, debug: $debug);
        }

        // Preserve the status code of any HTTP exception (e.g. abort(409, ...)).
        if ($e instanceof HttpExceptionInterface) {
            return apiEnvelope(false, $e->getMessage(), $e->getStatusCode(), debug: $debug);
        }

        // Never leak internal exception details to clients in production.
        $message = app()->environment('local', 'development')
            ? $e->getMessage()
            : __('errors.server');

        return apiEnvelope(false, $message, 500, debug: $debug);
    }
}
