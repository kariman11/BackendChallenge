<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public static function send($orgId, $eventName, $payload)
    {
        $webhook = \DB::table('webhooks')->where('organization_id', $orgId)->first();

        if (! $webhook) {
            Log::warning("No webhook configured for org {$orgId}");
            return;
        }

        // JSON payload
        $data = [
            'event' => $eventName,
            'timestamp' => now()->toISOString(),
            'data' => $payload,
        ];

        // HMAC signature
        $signature = hash_hmac('sha256', json_encode($data), $webhook->secret);

        // Dispatch queued job
        \App\Jobs\SendWebhook::dispatch($webhook->url, $data, $signature);
    }
}
