<?php

namespace App\Providers;

use App\Contracts\WhatsAppGateway;
use App\Services\WhatsApp\LogWhatsAppGateway;
use App\Services\WhatsApp\NabdaOtpGateway;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppGateway::class, function (): WhatsAppGateway {
            return match (config('whatsapp.driver')) {
                'nabda' => new NabdaOtpGateway,
                'log' => new LogWhatsAppGateway,
                default => throw new InvalidArgumentException(
                    'Unsupported WhatsApp driver: '.config('whatsapp.driver'),
                ),
            };
        });
    }
}
