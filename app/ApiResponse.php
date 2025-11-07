<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function ok(
        mixed $data = null,
        string $message = 'OK',
        ?array $meta = null,
        ?array $links = null
    ): JsonResponse {
        return $this->wrap(true, $message, $data, null, $meta, $links, 200);
    }

    public function created(
        mixed $data = null,
        string $message = 'Created',
        ?array $meta = null
    ): JsonResponse {
        return $this->wrap(true, $message, $data, null, $meta, null, 201);
    }

    public function fail(
        string $message,
        ?array $errors = null,
        int $status = 400
    ): JsonResponse {
        return $this->wrap(false, $message, null, $errors, null, null, $status);
    }

    protected function wrap(
        bool $success,
        string $message,
        mixed $data,
        ?array $errors,
        ?array $meta,
        ?array $links,
        int $status
    ): JsonResponse {
        return response()->json([
            'success'    => $success,
            'message'    => $message,
            'data'       => $data,
            'errors'     => $errors,
            'meta'       => $meta,
            'links'      => $links,
            'request_id' => request()->header('X-Request-ID')
                ?? (string) request()->attributes->get('request_id', ''),
            'timestamp'  => now()->toISOString(),
        ], $status);
    }
}
