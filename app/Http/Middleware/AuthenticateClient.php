<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClient
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->header('X-API-Key') ?: $request->bearerToken();

        if (! is_string($plain) || $plain === '') {
            return ApiResponse::error('API key requerida.', 401);
        }

        $client = Client::query()->where('api_key_hash', hash('sha256', $plain))->first();

        if (! $client) {
            return ApiResponse::error('API key inválida.', 401);
        }

        $request->attributes->set('client', $client);

        if (! $client->is_active) {
            return ApiResponse::error('El cliente está inactivo.', 403);
        }

        if ($client->rate_limit_per_minute) {
            $key = 'client-api:'.$client->id;

            if (RateLimiter::tooManyAttempts($key, $client->rate_limit_per_minute)) {
                return ApiResponse::error('Demasiadas solicitudes.', 429);
            }

            RateLimiter::hit($key, 60);
        }

        return $next($request);
    }
}
