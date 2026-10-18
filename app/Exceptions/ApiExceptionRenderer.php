<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Normalizes every API exception into the { success, data, message } envelope
 * used by all /api/* endpoints, so the frontend can feed SweetAlert2 directly.
 */
class ApiExceptionRenderer
{
    public static function render(Throwable $e, Request $request): JsonResponse
    {
        [$status, $message, $errors] = self::resolve($e);

        $payload = [
            'success' => false,
            'data' => null,
            'message' => $message,
        ];

        if ($errors) {
            $payload['errors'] = $errors;
        }

        if (App::hasDebugModeEnabled() && $status === 500) {
            $payload['exception'] = [
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return response()->json($payload, $status);
    }

    /**
     * @return array{0: int, 1: string, 2: array|null}
     */
    private static function resolve(Throwable $e): array
    {
        return match (true) {
            $e instanceof ValidationException => [422, 'Data yang dikirim tidak valid.', $e->errors()],
            $e instanceof InsufficientStockException => [422, $e->getMessage(), null],
            $e instanceof AuthenticationException => [401, 'Anda harus login terlebih dahulu.', null],
            $e instanceof AuthorizationException => [403, 'Anda tidak memiliki akses untuk aksi ini.', null],
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => [404, 'Data tidak ditemukan.', null],
            $e instanceof HttpExceptionInterface => [$e->getStatusCode(), $e->getMessage() ?: 'Terjadi kesalahan.', null],
            default => [500, App::hasDebugModeEnabled() ? $e->getMessage() : 'Terjadi kesalahan pada server.', null],
        };
    }
}
