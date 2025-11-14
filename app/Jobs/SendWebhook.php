<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;

class SendWebhook implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public $tries = 5;           // retries
    public $timeout = 10;        // seconds

    public function __construct(
        public string $url,
        public array $payload,
        public string $signature
    ) {}

    public function handle()
    {
        $response = Http::withHeaders([
            'X-Signature' => $this->signature,
        ])->post($this->url, $this->payload);

        if ($response->failed()) {
            Log::warning("Webhook failed: {$this->url} Status: {$response->status()}");
            $this->release(10); // retry after 10 seconds
        }
    }

    public function failed(\Throwable $e)
    {
        Log::error("Webhook permanently failed: {$this->url}");
        // It will go to failed_jobs table (DLQ)
    }
}
