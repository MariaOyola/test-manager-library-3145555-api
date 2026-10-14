<?php

namespace Library\Adapters\Inbound\Http;

use Illuminate\Http\JsonResponse;

final class ErrorResponses
{
    public static function make(string $code, string $message, int $status, array $details = []): JsonResponse
    {
        return response()->json(['code' => $code, 'message' => $message, 'details' => $details], $status);
    }
}
