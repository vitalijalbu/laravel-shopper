# Laravel Shopper - OAuth Authentication Setup

## Sistema di Autenticazione OAuth

Il sistema Laravel Shopper include un sistema completo di autenticazione OAuth che supporta i seguenti provider:

- Google
- Facebook  
- Twitter
- GitHub
- LinkedIn
- Apple
- Discord
- Microsoft

## Installazione e Configurazione

### 1. Installazione del Package

```bash
composer require vitalijalbu/laravel-shopper
```

### 2. Pubblicazione delle Risorse

```bash
php artisan vendor:publish --provider="VitaliJalbu\Shopper\ShopperServiceProvider"
```

### 3. Esecuzione delle Migrazioni

```bash
php artisan migrate
```

### 4. Configurazione dei Provider OAuth

Aggiungi le credenziali OAuth nel file `.env`:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# Facebook OAuth  
FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret

# Twitter OAuth
TWITTER_CLIENT_ID=your_twitter_client_id
TWITTER_CLIENT_SECRET=your_twitter_client_secret

# GitHub OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret

# LinkedIn OAuth
LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret

# Apple OAuth
APPLE_CLIENT_ID=your_apple_client_id
APPLE_CLIENT_SECRET=your_apple_client_secret

# Discord OAuth
DISCORD_CLIENT_ID=your_discord_client_id
DISCORD_CLIENT_SECRET=your_discord_client_secret

# Microsoft OAuth
MICROSOFT_CLIENT_ID=your_microsoft_client_id
MICROSOFT_CLIENT_SECRET=your_microsoft_client_secret
```

### 5. Configurazione delle Rotte

Le rotte OAuth sono già configurate automaticamente:

**Rotte Web (tradizionali):**
- `GET /auth/social/{provider}/redirect` - Redirect OAuth
- `GET /auth/social/{provider}/callback` - Callback OAuth
- `POST /auth/social/{provider}/link` - Collega account (richiede autenticazione)
- `DELETE /auth/social/{provider}/unlink` - Scollega account (richiede autenticazione)

**Rotte API:**
- `GET /api/auth/social/providers` - Lista provider disponibili
- `GET /api/auth/social/{provider}/redirect` - URL redirect per SPA
- `POST /api/auth/social/{provider}/callback` - Callback per SPA (ritorna token)
- `GET /api/auth/social/connected` - Account collegati (richiede autenticazione)
- `POST /api/auth/social/{provider}/link` - Collega account API (richiede autenticazione)
- `DELETE /api/auth/social/{provider}/unlink` - Scollega account API (richiede autenticazione)

## Utilizzo nel Frontend

### Componente Vue.js

Il package include un componente Vue.js pronto per l'uso:

```vue
<template>
  <div>
    <SocialAuthComponent 
      :auth-mode="'login'"
      :show-divider="true"
      :show-connected-accounts="false"
      :api-mode="false"
      @success="handleSuccess"
      @error="handleError"
    />
  </div>
</template>

<script setup>
import SocialAuthComponent from '@/components/Auth/SocialAuthComponent.vue'

const handleSuccess = (data) => {
  console.log('Authentication successful:', data)
  // Redirect o aggiorna UI
}

const handleError = (error) => {
  console.error('Authentication failed:', error)
  // Mostra messaggio di errore
}
</script>
```

### Props del Componente

- `authMode`: `'login'` | `'register'` - Modalità di autenticazione
- `showDivider`: `boolean` - Mostra divisore "OR"
- `showConnectedAccounts`: `boolean` - Mostra account collegati per utenti autenticati
- `apiMode`: `boolean` - Usa API endpoints invece di rotte web
- `intendedUrl`: `string` - URL di destinazione dopo l'autenticazione

### Eventi

- `@success` - Autenticazione riuscita
- `@error` - Errore di autenticazione
- `@loading-change` - Cambio stato loading

## Configurazione Provider OAuth

### Google

1. Vai su [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuovo progetto o selezionane uno esistente
3. Abilita Google+ API
4. Crea credenziali OAuth 2.0
5. Aggiungi URL di callback: `https://tuodominio.com/auth/social/google/callback`

### Facebook

1. Vai su [Facebook Developers](https://developers.facebook.com/)
2. Crea una nuova app
3. Aggiungi prodotto Facebook Login
4. Configura Valid OAuth Redirect URIs: `https://tuodominio.com/auth/social/facebook/callback`

### Twitter

1. Vai su [Twitter Developer Portal](https://developer.twitter.com/)
2. Crea una nuova app
3. Configura Callback URL: `https://tuodominio.com/auth/social/twitter/callback`

### GitHub

1. Vai su GitHub Settings → Developer settings → OAuth Apps
2. Crea una nuova OAuth App
3. Configura Authorization callback URL: `https://tuodominio.com/auth/social/github/callback`

### LinkedIn

1. Vai su [LinkedIn Developer Portal](https://developer.linkedin.com/)
2. Crea una nuova app
3. Aggiungi Sign In with LinkedIn product
4. Configura Redirect URLs: `https://tuodominio.com/auth/social/linkedin/callback`

## Personalizzazione

### Modifica del Model User

Il package estende automaticamente il model User. Se vuoi personalizzare il comportamento:

```php
// In app/Models/User.php

use VitaliJalbu\Shopper\Traits\HasSocialAccounts;

class User extends Authenticatable
{
    use HasSocialAccounts;
    
    // Il tuo codice personalizzato
}
```

### Controller Personalizzati

Puoi estendere i controller per personalizzare il comportamento:

```php
use VitaliJalbu\Shopper\Http\Controllers\Auth\SocialAuthController;

class CustomSocialAuthController extends SocialAuthController
{
    protected function handleSuccessfulAuthentication($user, $provider)
    {
        // La tua logica personalizzata
        parent::handleSuccessfulAuthentication($user, $provider);
    }
}
```

## Sicurezza

### CSRF Protection

Le rotte web sono protette automaticamente da CSRF. Per le API, usa i token Sanctum.

### Rate Limiting

Considera l'aggiunta di rate limiting alle rotte OAuth:

```php
Route::middleware(['throttle:oauth'])->group(function () {
    // Rotte OAuth
});
```

### Validazione Email

Il sistema richiede sempre un indirizzo email valido per la registrazione. Se il provider non fornisce l'email, l'autenticazione fallisce.

## Troubleshooting

### Errori Comuni

1. **Provider not configured**: Verifica che le credenziali siano nel file `.env`
2. **Invalid redirect URI**: Controlla che l'URL di callback sia configurato correttamente nel provider
3. **Email required**: Il provider deve fornire un indirizzo email
4. **Account already exists**: L'email è già associata a un altro account

### Debug

Abilita il debug mode per vedere errori dettagliati:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Logs

I log OAuth sono salvati in `storage/logs/laravel.log`:

```php
Log::info("OAuth authentication successful for user {$user->id} via {$provider}");
Log::error("OAuth callback failed for {$provider}: " . $e->getMessage());
```

## Traduzioni

Il sistema supporta completamente italiano e inglese. Le traduzioni sono in:

- `lang/en/social.php`
- `lang/it/social.php`

### Aggiungere Nuove Lingue

1. Copia `lang/en/social.php` in `lang/{locale}/social.php`
2. Traduci le stringhe
3. Configura la lingua di default in `config/app.php`

## API Response Format

### Successo

```json
{
    "success": true,
    "message": "Authentication successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe", 
            "email": "john@example.com",
            "avatar_url": "https://...",
            "provider": "google",
            "email_verified": true,
            "connected_providers": ["google", "facebook"]
        },
        "token": {
            "access_token": "...",
            "token_type": "Bearer",
            "expires_at": "2024-01-01T00:00:00.000000Z"
        }
    }
}
```

### Errore

```json
{
    "success": false,
    "message": "Authentication failed",
    "error": "Provider not configured"
}
```

## Supporto

Per supporto e segnalazione bug, apri un issue su [GitHub](https://github.com/vitalijalbu/laravel-shopper).

## License

MIT License. Vedi [LICENSE](LICENSE) per dettagli.
