<?php

use App\Enums\PanelsEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Build the single, canonical API response envelope used across the whole application.
 *
 * Every API response (success, manual error, or rendered exception) shares this exact
 * set of keys so clients can rely on one consistent shape.
 *
 * @param  array<string, array<int, string>>|null  $errors  Validation field errors.
 * @param  array<string, mixed>|null  $debug  Exception debug info (local environments only).
 */
function apiEnvelope(
    bool $success,
    string $message,
    int $status,
    mixed $data = null,
    ?array $errors = null,
    mixed $extra = null,
    ?array $debug = null,
): JsonResponse {
    $payload = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'errors' => $errors,
        'error_code' => $success ? null : $status,
        'extra' => $extra,
    ];

    if ($debug !== null && app()->environment('local', 'development')) {
        $payload['debug'] = $debug;
    }

    return response()->json($payload, $status);
}

function successResponse(mixed $data = null, string $message = 'Success', int $status = 200, mixed $extra = null): JsonResponse
{
    return apiEnvelope(
        success: true,
        message: $message,
        status: $status,
        data: $data,
        extra: $extra,
    );
}

/**
 * @param  array<string, array<int, string>>|null  $errors  Validation field errors.
 */
function errorResponse(string $message = 'Error', int $status = 400, ?array $errors = null): JsonResponse
{
    return apiEnvelope(
        success: false,
        message: $message,
        status: $status,
        errors: $errors,
    );
}

function locale()
{
    return app()->getLocale();
}

function getByLocale($array)
{
    return Arr::get($array, locale(), $array['en']);
}
function normalizePhone(string $phone): array
{
    $localNumber = ltrim($phone, '0');

    return [
        '+970' . $localNumber,
        '+972' . $localNumber,
    ];
}
