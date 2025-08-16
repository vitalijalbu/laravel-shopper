# Sistema Multilingue Laravel Shopper - Stile Statamic CMS

## Panoramica
Ho implementato un sistema multilingue completo per Laravel Shopper seguendo l'architettura di Statamic CMS, con supporto per italiano (IT) e inglese (EN). Il sistema include traduzioni shared per Inertia.js e un'esperienza utente fluida.

## Struttura File Implementati

### 1. File di Traduzione (lang/)
```
lang/
├── it/
│   ├── admin.php        # Traduzioni generali admin
│   ├── products.php     # Traduzioni specifiche prodotti
│   ├── categories.php   # Traduzioni categorie
│   ├── brands.php       # Traduzioni marchi
│   └── pages.php        # Traduzioni pagine
└── en/
    ├── admin.php        # English admin translations
    ├── products.php     # English product translations
    ├── categories.php   # English category translations
    ├── brands.php       # English brand translations
    └── pages.php        # English page translations
```

### 2. Middleware e Providers
- `app/Http/Middleware/LocaleMiddleware.php` - Gestione automatica lingua
- `app/Providers/InertiaServiceProvider.php` - Condivisione traduzioni con Inertia

### 3. Controller e API
- `src/Http/Controllers/Cp/LocaleController.php` - Endpoint per gestione lingue
- Aggiornato `ProductsController.php` con traduzioni

### 4. Frontend JavaScript
- `resources/js/Utils/translator.js` - Helper traduzioni stile Statamic
- `resources/js/stores/locale.js` - Store Pinia per gestione stato
- `resources/js/components/LocaleSelector.vue` - Selettore lingua UI

### 5. Configurazione
- `config/app.php` - Configurazione lingue disponibili

## Caratteristiche Principali

### 1. Sistema di Traduzione Avanzato
```php
// In Controller
__('products.title')
__('admin.actions.save')
__('products.messages.created')

// Con parametri
__('products.messages.bulk_deleted', ['count' => $count])
```

### 2. Integrazione Frontend
```javascript
// In Vue Component
const { t, tc } = useTranslation()

// Uso base
t('products.title')

// Con pluralizzazione
tc('admin.pagination.selected_items', count, { count })

// Con parametri
t('products.messages.bulk_deleted', { count: 5 })
```

### 3. Condivisione Dati Inertia
Le traduzioni sono automaticamente condivise con tutti i componenti Vue attraverso Inertia:
```javascript
// Disponibile in tutti i componenti
$page.props.translations
$page.props.locale
```

### 4. Gestione Intelligente Lingua
- Rilevamento automatico da sessione, preferenze utente, browser
- Persistenza in localStorage
- Aggiornamento preferenze utente autenticato

## Utilizzo Pratico

### 1. Nei Controller Laravel
```php
public function index()
{
    $page = Page::make(__('products.title'))
        ->breadcrumb(__('admin.navigation.home'), '/cp')
        ->primaryAction(__('products.create'), '/cp/products/create');
        
    return response()->json([
        'message' => __('products.messages.created')
    ]);
}
```

### 2. Nei Componenti Vue
```vue
<template>
  <div>
    <h1>{{ t('products.title') }}</h1>
    <button>{{ t('admin.actions.save') }}</button>
    <span>{{ tc('products.count', productCount, { count: productCount }) }}</span>
  </div>
</template>

<script>
import { useTranslation } from '@/stores/locale'

export default {
  setup() {
    const { t, tc, setLocale } = useTranslation()
    
    return { t, tc, setLocale }
  }
}
</script>
```

### 3. Selettore Lingua
```vue
<template>
  <LocaleSelector />
</template>
```

## Contenuto Traduzioni

### Traduzioni Admin (admin.php)
- **navigation**: Menu, breadcrumb, sezioni
- **actions**: Azioni comuni (save, edit, delete, etc.)
- **status**: Stati (active, published, draft, etc.)
- **time**: Gestione tempo e date
- **messages**: Messaggi sistema e validazione
- **pagination**: Paginazione tabelle
- **filters**: Filtri e ricerca
- **dashboard**: Dashboard e statistiche

### Traduzioni Prodotti (products.php)
- **fields**: Tutti i campi del prodotto
- **tabs**: Tab del form prodotto
- **stock_status**: Stati magazzino
- **messages**: Messaggi specifici prodotti
- **bulk_actions**: Azioni di gruppo
- **filters**: Filtri specifici prodotti
- **variants**: Gestione varianti
- **import/export**: Funzionalità import/export

### Traduzioni Categorie/Marchi/Pagine
- Traduzioni specifiche per ogni modulo
- Campi, messaggi, azioni specializzate

## Funzionalità Avanzate

### 1. Formatteri Localizzati
```javascript
// Valuta
formatCurrency(19.99, 'EUR', 'it-IT') // €19,99

// Date
formatDate(new Date(), {}, 'it-IT') // 14 agosto 2025

// Tempo relativo
formatRelativeTime(date, 'it-IT') // 2 ore fa
```

### 2. Plugin Vue Globale
```javascript
// Disponibile in tutti i componenti
this.t('key')
this.$tc('key', count)
this.$te('key') // verifica esistenza
```

### 3. Gestione Errori e Fallback
- Fallback automatico a chiave se traduzione mancante
- Gestione errori di caricamento traduzioni
- Supporto traduzioni dinamiche

## API Endpoints
- `GET /cp/translations/{locale}` - Carica traduzioni
- `PATCH /cp/user/locale` - Aggiorna preferenza utente
- `POST /cp/locale` - Cambia lingua sessione

## Vantaggi del Sistema

1. **Stile Statamic**: Architettura ispirata a Statamic CMS
2. **Performance**: Traduzioni cached e lazy loading
3. **Esperienza Utente**: Cambio lingua fluido senza reload
4. **Scalabilità**: Facile aggiunta nuove lingue
5. **Manutenibilità**: Organizzazione chiara file traduzioni
6. **Integrazione**: Perfetta integrazione Laravel + Vue + Inertia

## Prossimi Passi

1. Aggiungere altre lingue (fr, de, es)
2. Implementare traduzioni database per contenuti dinamici
3. Aggiungere interfaccia admin per gestione traduzioni
4. Cache avanzata traduzioni
5. Traduzioni automatiche AI per contenuti

Il sistema è ora completamente funzionale e pronto per essere utilizzato in produzione con supporto completo IT/EN.
