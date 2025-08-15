# Laravel Shopper - Control Panel Authentication

## Sistema di Autenticazione Control Panel

Il Control Panel di Laravel Shopper include un sistema completo di autenticazione simile a Statamic CMS con:

- ✅ **Login CP**: Interfaccia elegante per l'accesso al pannello di controllo
- ✅ **Forgot Password**: Sistema di recupero password via email
- ✅ **Reset Password**: Pagina per reimpostare la password
- ✅ **Session Shared**: Dati utente condivisi globalmente con Inertia.js
- ✅ **Rate Limiting**: Protezione contro attacchi brute force
- ✅ **Middleware**: Controllo accessi per il CP
- ✅ **Traduzioni**: Supporto completo IT/EN

## Installazione e Configurazione

### 1. Pubblicare i File CP

```bash
php artisan shopper:install --oauth
# oppure
php artisan vendor:publish --provider="LaravelShopper\ShopperServiceProvider" --tag="shopper-components"
```

### 2. Configurazione Ambiente

Aggiungi al file `.env`:

```env
# Control Panel Settings
SHOPPER_CP_NAME="Il Mio E-commerce"
SHOPPER_CP_PREFIX=cp
SHOPPER_CP_LOGO=/images/logo.png
SHOPPER_CP_LOGO_DARK=/images/logo-dark.png
SHOPPER_CP_FAVICON=/images/favicon.ico
```

### 3. Configurare Middleware

Il sistema usa automaticamente questi middleware:
- `web`: Sessioni e CSRF
- `shopper.inertia`: Condivisione dati globali 
- `cp`: Controllo accessi CP

### 4. Permessi Utente

Gli utenti possono accedere al CP se:
- Hanno il permesso `access-cp`
- Hanno il ruolo `admin` o `super-admin`  
- Hanno il campo `can_access_cp = true`

## Rotte Disponibili

### Rotte Pubbliche (Guest)
- `GET /cp/login` - Pagina di login
- `POST /cp/login` - Elaborazione login
- `GET /cp/forgot-password` - Pagina recupero password
- `POST /cp/forgot-password` - Invio email reset
- `GET /cp/reset-password/{token}` - Pagina reset password
- `POST /cp/reset-password` - Elaborazione reset

### Rotte Protette (Autenticati)
- `POST /cp/logout` - Logout
- `GET /cp/dashboard` - Dashboard principale
- `GET /cp/` - Redirect to dashboard

## Componenti Vue.js

### 1. Pagina Login CP

```vue
<!-- resources/js/Pages/Cp/Auth/Login.vue -->
<template>
    <div class="min-h-screen flex">
        <!-- Left Panel con branding e features -->
        <div class="hidden lg:flex lg:flex-1 lg:bg-gradient-to-br lg:from-indigo-600 lg:to-purple-700">
            <!-- Logo e welcome message -->
        </div>
        
        <!-- Right Panel con form login -->
        <div class="flex-1 flex flex-col justify-center">
            <!-- Login form responsive -->
        </div>
    </div>
</template>
```

### 2. Pagina Forgot Password

```vue
<!-- resources/js/Pages/Cp/Auth/ForgotPassword.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="mx-auto w-full max-w-sm">
            <!-- Form per richiesta reset password -->
        </div>
    </div>
</template>
```

### 3. Pagina Reset Password

```vue
<!-- resources/js/Pages/Cp/Auth/ResetPassword.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center">
        <div class="mx-auto w-full max-w-sm">
            <!-- Form per impostare nuova password -->
        </div>
    </div>
</template>
```

## Dati Shared Inertia

I seguenti dati sono disponibili globalmente in tutte le pagine Inertia:

```javascript
// Accesso in qualsiasi componente Vue
const { props } = usePage()

// Dati utente autenticato
props.auth.user // { id, name, email, avatar_url, roles, permissions, can_access_cp }

// Flash messages
props.flash.success
props.flash.error
props.flash.warning
props.flash.info
props.flash.status

// Configurazione app
props.app.name
props.app.url
props.app.debug

// Configurazione CP
props.cp.name
props.cp.url
props.cp.branding.logo
props.cp.branding.logo_dark
props.cp.branding.favicon

// Localizzazione
props.locale // 'it' o 'en'
props.locales // ['it', 'en']
```

## Controllers

### AuthenticatedSessionController

```php
// Gestisce login/logout
public function create(): Response // Mostra form login
public function store(Request $request): RedirectResponse // Elabora login
public function destroy(Request $request): RedirectResponse // Logout
```

### PasswordResetLinkController

```php
// Gestisce forgot password
public function create(): Response // Mostra form
public function store(Request $request): RedirectResponse // Invia email
```

### NewPasswordController

```php
// Gestisce reset password
public function create(Request $request): Response // Mostra form reset
public function store(Request $request): RedirectResponse // Salva nuova password
```

### DashboardController

```php
// Dashboard principale
public function index(Request $request): Response // Dashboard con stats
```

## Middleware

### ControlPanelMiddleware

Controlla l'accesso al CP verificando:
1. Utente autenticato
2. Permessi CP (`access-cp`, ruoli admin)
3. Campo `can_access_cp` se presente

### HandleInertiaRequests  

Condivide globalmente:
- Dati utente autenticato
- Flash messages
- Configurazioni app/CP
- Localizzazione

## Traduzioni

### File IT (`lang/it/auth.php`)

```php
'failed' => 'Le credenziali fornite non corrispondono ai nostri record.',
'cp_access_denied' => 'Non hai i permessi per accedere al Pannello di Controllo.',
'password_reset_sent' => 'Ti abbiamo inviato il link per il reset della password via email!',
// ... altre traduzioni
```

### File EN (`lang/en/auth.php`)

```php
'failed' => 'These credentials do not match our records.',
'cp_access_denied' => 'You do not have permission to access the Control Panel.',
'password_reset_sent' => 'We have emailed your password reset link!',
// ... altre traduzioni
```

## Personalizzazione

### Cambiare Tema/Stile

Modifica i file Vue in `resources/js/Pages/Cp/Auth/` per personalizzare:
- Colori (gradient, pulsanti)
- Logo e branding
- Layout e componenti
- Animazioni

### Aggiungere Campi Login

Estendi il controller e il form per aggiungere:
- Two-factor authentication
- Captcha
- Login con username
- Social login integration

### Controllo Accessi Custom

Modifica `ControlPanelMiddleware` per implementare logiche custom:
- IP whitelist
- Orari di accesso
- Controlli aggiuntivi

## Sicurezza

### Rate Limiting

- Login: 5 tentativi per email/IP
- Reset password: 3 tentativi per email/IP
- Throttle automatico con backoff

### CSRF Protection

- Tutti i form sono protetti da CSRF
- Token automatico in Inertia

### Session Security

- Rigenerazione session dopo login
- Invalidazione completa su logout
- Remember token gestito automaticamente

## Esempio Utilizzo

```php
// In un controller
class MyController extends Controller
{
    public function index(Request $request)
    {
        // Controlla se utente può accedere CP
        if (!$request->user()->can('access-cp')) {
            abort(403);
        }
        
        return Inertia::render('MyPage', [
            // I dati auth sono già condivisi globalmente
            'my_data' => $data,
        ]);
    }
}
```

```vue
<!-- In un componente Vue -->
<template>
    <div>
        <h1>Benvenuto {{ $page.props.auth.user.name }}</h1>
        <img :src="$page.props.cp.branding.logo" :alt="$page.props.cp.name">
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'

const page = usePage()
// Accesso diretto a tutti i dati shared
</script>
```

Il sistema è **completo e pronto per l'uso**, fornendo un'esperienza di login elegante e sicura simile a Statamic CMS! 🎉
