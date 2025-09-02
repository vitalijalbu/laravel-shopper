# 🔐 Advanced Permission System

Sistema di gestione permessi avanzato per Laravel Shopper con interfaccia moderna e flessibile.

## 📁 **Struttura File**

```
src/Http/Controllers/Api/
├── PermissionController.php          # Gestione permessi strutturati
├── RoleController.php                # Gestione ruoli avanzata
└── PermissionBuilderController.php   # Builder interface per matrice permessi

src/Http/Requests/Api/
├── PermissionRequest.php             # Validazione richieste permessi
└── RoleRequest.php                   # Validazione richieste ruoli
```

## 🚀 **Features**

### **1. Permission Structure**
- **Organizzazione per aree**: Content, Commerce, Users, System
- **Permessi granulari**: view, create, edit, delete, publish, configure
- **Auto-generazione**: Creazione automatica permessi per nuove risorse

### **2. Role Management**
- **CRUD completo** con validazione avanzata
- **Clonazione ruoli** per template rapidi
- **Assegnazione utenti** bulk
- **Statistiche** utilizzo ruoli

### **3. Permission Builder**
- **Matrix Interface** - Vista tabellare ruoli vs permessi
- **Templates** predefiniti (Content Manager, Shop Manager, etc.)
- **Bulk Operations** - Aggiornamenti massivi
- **Export/Import** configurazioni

## 🎯 **API Endpoints**

### **Permission Management**
```http
GET    /admin/permissions                      # Struttura permessi
GET    /admin/permissions/roles/{id}/permissions  # Permessi ruolo
PUT    /admin/permissions/roles/{id}/permissions  # Aggiorna permessi
POST   /admin/permissions/generate             # Genera permessi
POST   /admin/permissions/super-role           # Crea Super User
GET    /admin/permissions/tree                 # Albero permessi
```

### **Role Management**  
```http
GET    /admin/roles                           # Lista ruoli
POST   /admin/roles                           # Crea ruolo
GET    /admin/roles/{id}                      # Dettagli ruolo
PUT    /admin/roles/{id}                      # Aggiorna ruolo
DELETE /admin/roles/{id}                      # Elimina ruolo
POST   /admin/roles/{id}/assign-users         # Assegna utenti
POST   /admin/roles/{id}/remove-users         # Rimuovi utenti  
POST   /admin/roles/{id}/clone                # Clona ruolo
GET    /admin/roles-statistics                # Statistiche
```

### **Permission Builder**
```http
GET    /admin/permission-builder              # Builder interface
PUT    /admin/permission-builder/matrix       # Aggiorna matrice
POST   /admin/permission-builder/apply-template  # Applica template
POST   /admin/permission-builder/generate-resource  # Genera permessi risorsa
GET    /admin/permission-builder/export       # Esporta config
POST   /admin/permission-builder/import       # Importa config
```

## 💻 **Usage Examples**

### **Frontend Integration**
```javascript
// Carica builder interface
const { structure, roles, matrix } = await fetch('/admin/permission-builder')
  .then(res => res.json());

// Applica template
await fetch('/admin/permission-builder/apply-template', {
  method: 'POST',
  body: JSON.stringify({
    role_id: 1,
    template: 'content_manager'
  })
});

// Aggiorna matrice
await fetch('/admin/permission-builder/matrix', {
  method: 'PUT', 
  body: JSON.stringify({
    matrix: [{
      role_id: 1,
      permissions: ['view content', 'create content']
    }]
  })
});
```

### **Laravel Usage**
```php
// Check permissions in controllers
$this->middleware('permission:view content')->only(['index']);
$this->middleware('permission:create content')->only(['store']);

// In views
@can('edit content')
    <button>Edit</button>
@endcan

// Generate permissions for new resource
POST /admin/permission-builder/generate-resource
{
  "resource": "products",
  "actions": ["view", "create", "edit", "delete"]
}
```

## 🎨 **Permission Templates**

- **Content Manager**: Gestisce contenuti e pubblicazioni
- **Shop Manager**: Gestisce prodotti, ordini e inventario  
- **Customer Service**: Supporto clienti e gestione ordini
- **Read Only**: Solo visualizzazione
- **Super Admin**: Accesso completo

## 🔧 **Setup**

1. **Genera permessi base**:
```bash
POST /admin/permissions/generate
```

2. **Crea Super User**:
```bash
POST /admin/permissions/super-role
```

3. **Applica template**:
```bash
POST /admin/permission-builder/apply-template
```

---

Sistema **production-ready** con interfaccia moderna e gestione avanzata dei permessi! 🚀
