<?php

use App\Enums\PanelsEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

function getPanel()
{
    $path = request()->path();

    foreach (PanelsEnum::cases() as $panel) {
        if (Str::startsWith($path, $panel->value)) {
            return $panel->value;
        }
    }
    return null;
}
function isAdminPanel(): bool
{
    return request()->is([PanelsEnum::ADMIN->value, PanelsEnum::ADMIN->value . '/*']);
}

function successResponse($data, $message = 'Success', $status = 200, $extra = null)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'extra' => $extra,
    ], $status);
}

function errorResponse($message = 'Error', $status = 400)
{
    return response()->json([
        'success' => false,
        'message' => $message,
    ], $status);
}

function locale()
{
    return app()->getLocale();
}

function getByLocale($array)
{
    return Arr::get($array, locale(), $array['en']);
}
function syncMedia($request, $model, $collection)
{
    $temp_ids = $request->input('temp_ids', null);

    if ($temp_ids) {

        $media_ids = is_array($temp_ids) ? $temp_ids : explode(',', $temp_ids);

        Media::query()
            ->whereIn('id', $media_ids)
            ->get()
            ->each(function ($media) use ($model, $collection) {
                $media->move($model, $collection);
                $media->delete();
            });
    }
}
