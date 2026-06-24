<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppGateway;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NabdaOtpGateway implements WhatsAppGateway
{
    public function sendMessage(string $phoneE164, string $text): void
    {
        $response = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->withHeaders(['Authorization' => $this->resolveAuthorizationHeader()])
            ->post('/api/v1/messages/send', [
                'phone' => $phoneE164,
                'message' => $text,
            ]);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Failed to send WhatsApp message via Nabda OTP: ' . $exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * Nabda accepts two auth styles:
     * - Instance API key (sk_…): raw Authorization header, no Bearer prefix.
     * - Instance access token (from select-instance): Bearer token.
     */
    private function resolveAuthorizationHeader(): string
    {
        $apiKey = config('whatsapp.nabda.api_key');
        $instanceToken = config('whatsapp.nabda.instance_token');

        if (filled($apiKey)) {
            return $apiKey;
        }

        if (filled($instanceToken)) {
            return str_starts_with($instanceToken, 'Bearer ')
                ? $instanceToken
                : 'Bearer ' . $instanceToken;
        }

        throw new RuntimeException('Configure NABDA_API_KEY in .env (the sk_… key from your Nabda dashboard).');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('whatsapp.nabda.base_url'), '/');
    }
}
