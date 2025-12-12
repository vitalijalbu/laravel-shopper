<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip se è già autenticato via Sanctum
        if ($request->user()) {
            return $next($request);
        }

        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'API key mancante. Usa header X-API-Key o query parameter api_key',
            ], 401);
        }

        // Hash della key fornita
        $hashedKey = ApiKey::hash($apiKey);

        // Cerca la key nel database
        $key = ApiKey::where('key', $hashedKey)->active()->first();

        if (! $key || ! $key->isValid()) {
            return response()->json([
                'message' => 'API key non valida o scaduta',
            ], 401);
        }

        // Verifica accesso all'endpoint
        if (! $key->canAccessEndpoint($request->method(), $request->path())) {
            return response()->json([
                'message' => 'Accesso negato per questo endpoint',
            ], 403);
        }

        // Aggiorna last_used_at
        $key->markAsUsed();

        // Imposta l'API key nella request per uso successivo
        $request->merge(['_api_key' => $key]);

        return $next($request);
    }
}
