# ♿ Guida all'Accessibilità - Cartino

Implementazione completa degli standard WCAG 2.1 Level AA per garantire che la piattaforma sia accessibile a tutti gli utenti.

---

## 📋 Indice

1. [Composables Vue (Inertia)](#composables-vue-inertia)
2. [Componenti Vue Accessibility](#componenti-vue-accessibility)
3. [Componenti Blade Accessibility](#componenti-blade-accessibility)
4. [Best Practices](#best-practices)
5. [Testing Accessibilità](#testing-accessibilità)
6. [Checklist WCAG 2.1](#checklist-wcag-21)

---

## 🎯 Composables Vue (Inertia)

### 1. useFocusTrap

Gestisce il focus trap in modali/dialogs per garantire che gli utenti con keyboard possano navigare correttamente.

```vue
<script setup>
import { ref, watch } from 'vue'
import { useFocusTrap } from '@/composables/use-accessibility'

const isOpen = ref(false)
const dialogRef = ref(null)
const { activate, deactivate } = useFocusTrap()

watch(isOpen, (open) => {
  if (open) {
    activate(dialogRef.value)
  } else {
    deactivate()
  }
})
</script>

<template>
  <div v-if="isOpen" ref="dialogRef" role="dialog" aria-modal="true">
    <h2>Modal Title</h2>
    <button @click="isOpen = false">Close</button>
  </div>
</template>
```

**Features:**
- ✅ Trapping del focus dentro il dialogo
- ✅ Navigazione con Tab/Shift+Tab
- ✅ Ripristino focus dopo chiusura
- ✅ Auto-focus sul primo elemento focusable

### 2. useScreenReaderAnnounce

Annuncia messaggi agli screen reader senza mostrare UI visibile.

```vue
<script setup>
import { useScreenReaderAnnounce } from '@/composables/use-accessibility'

const { announce } = useScreenReaderAnnounce()

const saveProduct = async () => {
  await save()
  announce('Product saved successfully', 'polite')
}

const deleteProduct = async () => {
  await remove()
  announce('Product deleted', 'assertive') // Priorità alta
}
</script>
```

**Parametri:**
- `message` - Testo da annunciare
- `priority` - `'polite'` (default) o `'assertive'`

### 3. useKeyboardNavigation

Gestisce la navigazione con tastiera in liste (es. dropdown, menu).

```vue
<script setup>
import { ref } from 'vue'
import { useKeyboardNavigation } from '@/composables/use-accessibility'

const items = ref([
  { id: 1, label: 'Option 1' },
  { id: 2, label: 'Option 2' },
  { id: 3, label: 'Option 3' },
])

const { currentIndex, handleKeyDown } = useKeyboardNavigation(items, {
  loop: true,
  orientation: 'vertical', // o 'horizontal'
})

const onKeyDown = (e) => {
  if (handleKeyDown(e)) {
    // Navigation key was pressed
    focusItem(currentIndex.value)
  }
}
</script>

<template>
  <ul role="listbox" @keydown="onKeyDown">
    <li
      v-for="(item, index) in items"
      :key="item.id"
      role="option"
      :aria-selected="index === currentIndex"
    >
      {{ item.label }}
    </li>
  </ul>
</template>
```

**Tasti supportati:**
- `ArrowDown` / `ArrowRight` - Prossimo elemento
- `ArrowUp` / `ArrowLeft` - Elemento precedente
- `Home` - Primo elemento
- `End` - Ultimo elemento

### 4. useId

Genera ID unici per collegare label con input.

```vue
<script setup>
import { useId } from '@/composables/use-accessibility'

const emailId = useId('email')
const passwordId = useId('password')
</script>

<template>
  <div>
    <label :for="emailId">Email</label>
    <input :id="emailId" type="email" />
  </div>

  <div>
    <label :for="passwordId">Password</label>
    <input :id="passwordId" type="password" />
  </div>
</template>
```

---

## 🔧 Componenti Vue Accessibility

### SkipLink.vue

Permette agli utenti con keyboard di saltare direttamente al contenuto principale.

```vue
<template>
  <SkipLink
    href="#main-content"
    text="Skip to main content"
  />
</template>
```

**Posizionamento:**
Sempre come primo elemento nel layout:

```vue
<!-- layouts/app.vue -->
<template>
  <div>
    <SkipLink />
    <header>...</header>
    <main id="main-content">...</main>
  </div>
</template>
```

### ScreenReaderOnly.vue

Mostra contenuto solo agli screen reader (visualmente nascosto).

```vue
<template>
  <button>
    <IconTrash />
    <ScreenReaderOnly>Delete Product</ScreenReaderOnly>
  </button>

  <ScreenReaderOnly as="h1">
    Current page: Dashboard
  </ScreenReaderOnly>
</template>
```

**Props:**
- `as` - Tag HTML da usare (default: `'span'`)

### LiveRegion.vue

Annuncia cambiamenti dinamici agli screen reader.

```vue
<script setup>
import { ref } from 'vue'

const message = ref('')

const showSuccess = () => {
  message.value = 'Product added to cart'
  setTimeout(() => {
    message.value = ''
  }, 3000)
}
</script>

<template>
  <div>
    <button @click="showSuccess">Add to Cart</button>

    <LiveRegion
      :message="message"
      priority="polite"
    />
  </div>
</template>
```

**Props:**
- `message` - Messaggio da annunciare
- `priority` - `'polite'` o `'assertive'`
- `atomic` - Se true, annuncia l'intero contenuto

---

## 🎨 Componenti Blade Accessibility

### skip-link.blade.php

```blade
<x-accessibility.skip-link
    href="#main-content"
    text="Skip to main content"
/>

<!-- Con più skip link -->
<x-accessibility.skip-link href="#main-content" text="Skip to content" />
<x-accessibility.skip-link href="#main-nav" text="Skip to navigation" />
<x-accessibility.skip-link href="#footer" text="Skip to footer" />
```

### sr-only.blade.php

```blade
<!-- Icona con testo nascosto -->
<button>
    <svg>...</svg>
    <x-accessibility.sr-only>Delete Item</x-accessibility.sr-only>
</button>

<!-- Heading nascosto per screen reader -->
<x-accessibility.sr-only as="h2">
    Shopping Cart Items
</x-accessibility.sr-only>

<!-- Descrizione aggiuntiva -->
<a href="/product/laptop">
    Laptop Pro 15"
    <x-accessibility.sr-only>
        - View details and purchase
    </x-accessibility.sr-only>
</a>
```

### live-region.blade.php

```blade
<div x-data="{ message: '' }">
    <button @click="message = 'Item added to cart'">
        Add to Cart
    </button>

    <x-accessibility.live-region priority="polite">
        <span x-text="message"></span>
    </x-accessibility.live-region>
</div>
```

---

## ✅ Best Practices

### 1. Struttura Semantica HTML

```html
✅ CORRECT
<header>
    <nav aria-label="Main navigation">
        <ul>
            <li><a href="/">Home</a></li>
        </ul>
    </nav>
</header>

<main id="main-content">
    <article>
        <h1>Page Title</h1>
        <section>
            <h2>Section Title</h2>
        </section>
    </article>
</main>

<footer>
    <nav aria-label="Footer navigation">...</nav>
</footer>

❌ INCORRECT
<div class="header">
    <div class="nav">...</div>
</div>

<div class="main">
    <div class="title">Page Title</div>
</div>
```

### 2. Heading Hierarchy

```html
✅ CORRECT
<h1>Main Page Title</h1>
<h2>Section 1</h2>
<h3>Subsection 1.1</h3>
<h3>Subsection 1.2</h3>
<h2>Section 2</h2>

❌ INCORRECT
<h1>Main Page Title</h1>
<h3>Section 1</h3>  <!-- Skipped h2 -->
<h2>Section 2</h2>
```

### 3. Form Labels

```html
✅ CORRECT
<label for="email">Email Address</label>
<input id="email" type="email" name="email" required aria-required="true">

<label for="terms">
    <input id="terms" type="checkbox" name="terms" required>
    I accept the terms and conditions
</label>

❌ INCORRECT
<input type="email" placeholder="Email">  <!-- No label -->
<span>Email</span>
<input type="email">  <!-- Label not connected -->
```

### 4. Link vs Button

```html
✅ CORRECT
<a href="/products">View Products</a>  <!-- Navigation -->
<button type="button" @click="openModal">Open Modal</button>  <!-- Action -->

❌ INCORRECT
<span @click="goToProducts">View Products</span>  <!-- Not accessible -->
<a href="#" @click="openModal">Open Modal</a>  <!-- Wrong element -->
```

### 5. Images

```html
✅ CORRECT
<img src="product.jpg" alt="MacBook Pro 15 inch laptop">
<img src="decorative-border.png" alt="" role="presentation">

❌ INCORRECT
<img src="product.jpg">  <!-- Missing alt -->
<img src="product.jpg" alt="image">  <!-- Generic alt -->
```

### 6. Icon Buttons

```html
✅ CORRECT
<button type="button" aria-label="Delete item">
    <svg aria-hidden="true">...</svg>
</button>

<button type="button">
    <svg aria-hidden="true">...</svg>
    <span class="sr-only">Delete item</span>
</button>

❌ INCORRECT
<button type="button">
    <svg>...</svg>  <!-- No label -->
</button>
```

### 7. Modal/Dialog

```html
✅ CORRECT
<div
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
    aria-describedby="modal-description"
>
    <h2 id="modal-title">Confirm Deletion</h2>
    <p id="modal-description">Are you sure you want to delete this item?</p>
    <button>Cancel</button>
    <button>Delete</button>
</div>

❌ INCORRECT
<div class="modal">
    <div class="title">Confirm Deletion</div>
    <div>Are you sure?</div>
</div>
```

### 8. Loading States

```html
✅ CORRECT
<button disabled aria-busy="true">
    <span class="spinner" aria-hidden="true"></span>
    <span>Loading...</span>
</button>

<div role="status" aria-live="polite">
    Loading products, please wait...
</div>

❌ INCORRECT
<button disabled>
    <span class="spinner"></span>
</button>
```

### 9. Form Errors

```html
✅ CORRECT
<label for="email">Email</label>
<input
    id="email"
    type="email"
    aria-invalid="true"
    aria-describedby="email-error"
>
<span id="email-error" role="alert">
    Please enter a valid email address
</span>

❌ INCORRECT
<input type="email" class="error">
<span class="error-message">Invalid email</span>
```

### 10. Tables

```html
✅ CORRECT
<table>
    <caption>Product Inventory</caption>
    <thead>
        <tr>
            <th scope="col">Product</th>
            <th scope="col">Stock</th>
            <th scope="col">Price</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th scope="row">Laptop</th>
            <td>45</td>
            <td>$999</td>
        </tr>
    </tbody>
</table>

❌ INCORRECT
<div class="table">
    <div class="row">
        <div>Product</div>
        <div>Stock</div>
    </div>
</div>
```

---

## 🧪 Testing Accessibilità

### 1. Keyboard Navigation

Testa senza mouse:
- [ ] Tab - Naviga tra elementi focusabili
- [ ] Shift + Tab - Naviga indietro
- [ ] Enter - Attiva link/button
- [ ] Space - Attiva button, toggle checkbox
- [ ] Escape - Chiude modal/dropdown
- [ ] Arrow keys - Naviga in liste/menu

### 2. Screen Reader Testing

**NVDA (Windows - Free):**
```bash
# Download: https://www.nvaccess.org/download/
```

**VoiceOver (Mac - Built-in):**
```
Cmd + F5 - Toggle VoiceOver
Ctrl + Option + Arrow Keys - Navigate
```

**Testa:**
- [ ] Skip links funzionano
- [ ] Heading structure è corretta
- [ ] Form labels sono letti correttamente
- [ ] Button purpose è chiaro
- [ ] Error messages sono annunciati
- [ ] Loading states sono annunciati

### 3. Automated Testing

```bash
# Installa axe-core
npm install --save-dev @axe-core/playwright

# Installa pa11y
npm install --save-dev pa11y
```

**Test con Playwright:**

```javascript
// tests/accessibility.spec.js
import { test, expect } from '@playwright/test'
import AxeBuilder from '@axe-core/playwright'

test('homepage should not have accessibility violations', async ({ page }) => {
  await page.goto('/')

  const accessibilityScanResults = await new AxeBuilder({ page }).analyze()

  expect(accessibilityScanResults.violations).toEqual([])
})
```

### 4. Browser DevTools

**Chrome Lighthouse:**
1. F12 → Lighthouse tab
2. Select "Accessibility"
3. Generate report

**Firefox Accessibility Inspector:**
1. F12 → Accessibility tab
2. Check for issues

---

## ✅ Checklist WCAG 2.1 Level AA

### Perceivable

- [ ] **1.1.1** Tutte le immagini hanno alt text appropriato
- [ ] **1.3.1** Struttura HTML semantica (header, nav, main, etc.)
- [ ] **1.3.2** Sequenza di lettura logica
- [ ] **1.4.3** Contrasto minimo 4.5:1 per testo normale
- [ ] **1.4.4** Testo può essere ingrandito fino a 200%
- [ ] **1.4.10** Responsive design (no scroll orizzontale)
- [ ] **1.4.11** Contrasto 3:1 per componenti UI

### Operable

- [ ] **2.1.1** Tutte le funzionalità accessibili da tastiera
- [ ] **2.1.2** No keyboard trap
- [ ] **2.1.4** Shortcuts da tastiera con modificatori
- [ ] **2.4.1** Skip link presente
- [ ] **2.4.2** Page title descrittivo
- [ ] **2.4.3** Focus order logico
- [ ] **2.4.4** Link purpose chiaro dal testo
- [ ] **2.4.5** Multiple ways per navigare
- [ ] **2.4.6** Headings e labels descrittivi
- [ ] **2.4.7** Focus indicator visibile

### Understandable

- [ ] **3.1.1** Language of page dichiarato
- [ ] **3.2.1** On focus non causa cambiamenti inaspettati
- [ ] **3.2.2** On input non causa cambiamenti inaspettati
- [ ] **3.2.3** Navigazione consistente
- [ ] **3.2.4** Componenti identificati consistentemente
- [ ] **3.3.1** Error messages chiari
- [ ] **3.3.2** Labels o istruzioni per input
- [ ] **3.3.3** Error suggestions quando possibile
- [ ] **3.3.4** Prevenzione errori per azioni importanti

### Robust

- [ ] **4.1.2** Nome, ruolo e valore per componenti UI
- [ ] **4.1.3** Status messages annunciati agli screen reader

---

## 🎯 Quick Start

### Inertia/Vue Setup

```vue
<!-- layouts/app.vue -->
<script setup>
import SkipLink from '@/components/accessibility/SkipLink.vue'
</script>

<template>
  <div>
    <SkipLink />

    <header>
      <nav aria-label="Main navigation">
        <!-- Navigation -->
      </nav>
    </header>

    <main id="main-content" tabindex="-1">
      <slot />
    </main>

    <footer>
      <!-- Footer -->
    </footer>

    <!-- Global live region -->
    <div id="sr-live-region" role="status" aria-live="polite" aria-atomic="true" class="sr-only"></div>
  </div>
</template>
```

### Blade Setup

```blade
<!-- resources/views/themes/default/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Cartino')</title>
</head>
<body>
    <x-accessibility.skip-link />

    <header>
        <nav aria-label="Main navigation">
            <!-- Navigation -->
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <footer>
        <!-- Footer -->
    </footer>

    <!-- Global live region -->
    <x-accessibility.live-region id="global-announcer" />
</body>
</html>
```

---

## 📚 Risorse

- **WCAG 2.1 Guidelines**: https://www.w3.org/WAI/WCAG21/quickref/
- **WAI-ARIA Authoring Practices**: https://www.w3.org/WAI/ARIA/apg/
- **MDN Accessibility**: https://developer.mozilla.org/en-US/docs/Web/Accessibility
- **WebAIM**: https://webaim.org/
- **A11Y Project**: https://www.a11yproject.com/

---

**✅ L'accessibilità è ora implementata in tutta l'applicazione!**
