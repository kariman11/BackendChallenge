<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotencyKey
{
    /**
     * How long we keep the final response cached (in seconds).
     * Default: 24 hours.
     */
    protected int $responseTtl = 10;

    /**
     * How long a processing lock lasts (seconds).
     */
    protected int $lockTtl = 30;

    /**
     * How long we poll waiting for a response while another worker processes the same key (seconds).
     */
    protected int $pollTimeout = 10;

    /**
     * Poll interval (seconds) when waiting for response.
     */
    protected float $pollInterval = 0.5;

    public function handle(Request $request, Closure $next)
    {
        // Only apply to POST (you can adapt if you want other verbs)
        if (! $request->isMethod('post')) {
            return $next($request);
        }

        $ikey = $request->header('Idempotency-Key') ?? $request->header('Idempotency_Key') ?? null;

        if (! $ikey || ! is_string($ikey) || trim($ikey) === '') {
            return response()->json([
                'status' => false,
                'message' => 'Missing required header: Idempotency-Key'
            ], Response::HTTP_BAD_REQUEST);
        }

        // canonicalize key
        $ikey = 'idem:' . sha1($ikey);

        $responseCacheKey = $ikey . ':response';
        $lockKey = $ikey . ':lock';

        // If we have a stored final response, return it (avoid re-processing)
        $stored = Cache::store('redis')->get($responseCacheKey);
        if ($stored !== null) {
            return $this->rebuildResponse($stored);
        }

        // Try to acquire a lock (only the first request should succeed)
        $acquired = Cache::store('redis')->add($lockKey, true, $this->lockTtl); // add = setnx
        if ($acquired) {
            // We are the owner — process the request and then capture the response
            $response = $next($request);

            // Build cache payload
            $payload = [
                'status' => $response->getStatusCode(),
                'headers' => $this->filterHeadersForStorage($response->headers->allPreserveCase()),
                'body' => $response->getContent(),
            ];

            // Store the payload for future identical requests
            Cache::store('redis')->put($responseCacheKey, $payload, $this->responseTtl);

            // Release the lock (delete key). Add small safety: don't care about return.
            Cache::store('redis')->forget($lockKey);

            return $response;
        }

        // Lock not acquired -> another worker is processing same idempotency key.
        // Poll for the final response until timeout.
        $waited = 0;
        while ($waited < $this->pollTimeout) {
            usleep((int)($this->pollInterval * 1_000_000)); // microseconds
            $waited += $this->pollInterval;

            $stored = Cache::store('redis')->get($responseCacheKey);
            if ($stored !== null) {
                return $this->rebuildResponse($stored);
            }
        }

        // If we reach here, the other worker didn't produce a cached response in time.
        return response()->json([
            'status' => false,
            'message' => 'Request is already being processed. Try again later.'
        ], Response::HTTP_CONFLICT);
    }

    protected function rebuildResponse(array $stored)
    {
        $status = $stored['status'] ?? 200;
        $body = $stored['body'] ?? '';
        $headers = $stored['headers'] ?? [];

        $response = response($body, $status);

        // restore some headers if present (content-type mostly)
        foreach ($headers as $name => $values) {
            // headers stored as arrays; set first value to response
            if (is_array($values) && count($values) > 0) {
                $response->header($name, $values[0]);
            }
        }

        return $response;
    }

    /**
     * Remove or normalize headers we don't want to store/restore.
     */
    protected function filterHeadersForStorage(array $allHeaders): array
    {
        // Remove transient headers that are container-specific
        unset($allHeaders['Set-Cookie'], $allHeaders['set-cookie']);

        // Keep content-type, cache-control, etc.
        return $allHeaders;
    }
}
