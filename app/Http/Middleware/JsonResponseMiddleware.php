<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class JsonResponseMiddleware
{
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $response = $next($request);

        if (!$request->is('api/*') || !$response instanceof JsonResponse) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $originalData = $response->getData(true);
        $standardizedData = $this->standardizeResponse($originalData, $statusCode);

        return new JsonResponse($standardizedData, $statusCode, $response->headers->all());
    }

    private function standardizeResponse(array $data, int $statusCode): array
    {
        $isSuccess = $statusCode >= 200 && $statusCode < 300;

        if ($isSuccess) {
            return $this->formatSuccessResponse($data, $statusCode);
        }

        return $this->formatErrorResponse($data, $statusCode);
    }

    private function formatSuccessResponse(array $data, int $statusCode): array
    {
        if (isset($data['success']) && isset($data['data'])) {
            return $data;
        }

        return [
            'success' => true,
            'message' => $this->getStatusMessage($statusCode),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function formatErrorResponse(array $data, int $statusCode): array
    {
        if (isset($data['success']) && $data['success'] === false) {
            return $data;
        }
        if (isset($data['errors'])) {
            return [
                'success' => false,
                'message' => $data['message'] ?? 'Validation failed',
                'errors' => $data['errors'],
                'timestamp' => now()->toISOString(),
            ];
        }

        return [
            'success' => false,
            'message' => $data['message'] ?? $this->getStatusMessage($statusCode),
            'details' => $data['details'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function getStatusMessage(int $statusCode): string
    {
        return match ($statusCode) {
            200 => 'Request successful',
            201 => 'Resource created successfully',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Resource not found',
            422 => 'Validation failed',
            500 => 'Internal server error',
            default => Response::$statusTexts[$statusCode] ?? 'Unknown status',
        };
    }
}
