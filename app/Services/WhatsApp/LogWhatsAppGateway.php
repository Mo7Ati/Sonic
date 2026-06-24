<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Log;

class LogWhatsAppGateway implements WhatsAppGateway
{
    public function sendMessage(string $phoneE164, string $text): void
    {
        Log::info('WhatsApp message (log driver)', [
            'phone' => $phoneE164,
            'message' => $text,
        ]);
    }
}
