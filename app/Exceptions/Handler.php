<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use GuzzleHttp\Exception\RequestException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody(), true);

                return response()->json([
                    'error' => $body['error'] ?? 'DeepSeek API Error',
                    'details' => $body['message'] ?? $e->getMessage(),
                ], $statusCode);
            }

            return response()->json([
                'error' => 'DeepSeek API Connection Error',
                'details' => $e->getMessage(),
            ], 500);
        });
    }
}
