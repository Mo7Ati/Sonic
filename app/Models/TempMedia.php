<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TempMedia extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'session_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Clean up expired temporary media
     */
    public static function cleanupExpired()
    {
        static::where('expires_at', '<', now())
            ->get()
            ->each(function ($tempMedia) {
                $tempMedia->clearMediaCollection();
                $tempMedia->delete();
            });
    }
}
