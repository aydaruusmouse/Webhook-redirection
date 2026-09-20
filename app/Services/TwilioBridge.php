<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioBridge
{
    public function forward(array $payload): void
    {
        $url = (string) config('services.external_webhook.url');

        if ($url === '') {
            Log::error('EXTERNAL_WEBHOOK_URL is not set');

            return;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post($url, $payload);

        if (! $response->successful()) {
            Log::error('Webhook forward failed', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return;
        }

        Log::info('Webhook forwarded', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
}
