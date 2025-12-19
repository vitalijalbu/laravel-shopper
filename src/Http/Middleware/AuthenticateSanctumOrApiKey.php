<?php

declare(strict_types=1);

namespace Cartino\Http\Middleware;

use Cartino\Auth\ApiKeyUser;
use Cartino\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware che permette autenticazione flessibile via Sanctum OR API Key
 * Questo middleware controlla prima se c'è un utente autenticato via Sanctum,
 * altrimenti tenta di validare una API key.
 */
class AuthenticateSanctumOrApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Controlla se c'è un utente autenticato via Sanctum
        if ($request->user()) {
            return $next($request);
        }

        // 2. Tenta autenticazione via API Key
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $apiKey) {
            return response()->json([
                'message' => 'Autenticazione richiesta. Usa Sanctum token o API key (header X-API-Key o query parameter api_key)',
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
                'message' => 'Accesso negato per questo endpoint con l\'API key fornita',
            ], 403);
        }

        // Aggiorna last_used_at
        $key->markAsUsed();

        // Imposta l'API key nella request per uso successivo
        $request->merge(['_api_key' => $key]);

        // Imposta un attributo per sapere che siamo autenticati via API key
        $request->attributes->set('authenticated_via', 'api_key');

        // Autentica un utente virtuale basato sulla API key, così le policy CRUD funzionano
        Auth::setUser(new ApiKeyUser($key));

        return $next($request);
    }
}
