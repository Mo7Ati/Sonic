<?php

namespace App\Contracts;

interface WhatsAppGateway
{
    public function sendMessage(string $phoneE164, string $text): void;
}
