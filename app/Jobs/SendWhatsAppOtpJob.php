<?php

namespace App\Jobs;

use App\Contracts\WhatsAppGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsAppOtpJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly array $phone_with_both_country_codes,
        public readonly string $message,
    ) {}

    public function handle(WhatsAppGateway $gateway): void
    {
        foreach ($this->phone_with_both_country_codes as $phone) {
            $gateway->sendMessage($phone, $this->message);
        }
    }
}
