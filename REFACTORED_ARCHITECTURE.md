# 🔄 Shopper E-commerce Platform - Refactored Architecture

## 📋 Major Updates & Improvements

### ✅ **Enum-Based Architecture**
- **CartStatus**: `ACTIVE`, `ABANDONED`, `CONVERTED`, `EXPIRED`
- **AddressType**: `BILLING`, `SHIPPING`
- **WishlistStatus**: `ACTIVE`, `INACTIVE`, `ARCHIVED`
- **StockNotificationStatus**: `PENDING`, `SENDING`, `SENT`, `FAILED`, `CANCELLED`
- **MenuItemType**: `LINK`, `PAGE`, `CATEGORY`, `PRODUCT`, `CUSTOM`
- **OrderStatus**: `PENDING`, `CONFIRMED`, `PROCESSING`, `SHIPPED`, `DELIVERED`, `CANCELLED`, `REFUNDED`

### 🛒 **Unified Cart System (Replaced AbandonedCart)**
- **Single Cart Model**: Gestisce tutti gli stati del carrello
- **Smart Status Transitions**: Automatic abandonment detection
- **Recovery System**: Email-based cart recovery with tokens
- **Conversion Tracking**: Links to completed orders
- **Session & Customer Management**: Merge carts on login

### 🌍 **Multilingual Validation System**
- **Translation Keys**: All validation messages use `__()` function
- **Language Files**: English & Italian translations
- **Enum Labels**: Multilingual enum values
- **Extensible**: Easy to add more languages

### 🏗️ **Clean Architecture Patterns**

#### **Request Validation Layer**
```php
// Uso di Enum nelle regole di validazione
'status' => ['required', Rule::enum(CartStatus::class)],
'type' => ['required', Rule::enum(AddressType::class)],

// Messaggi tradotti
'status.enum' => __('shopper::validation.cart.status.enum'),
```

#### **Model Casts with Enums**
```php
protected $casts = [
    'status' => CartStatus::class,
    'type' => AddressType::class,
    // ...
];
```

#### **Enum Methods**
```php
// Labels tradotti
public function label(): string {
    return __('shopper::cart.status.' . $this->value);
}

// Colori per UI
public function color(): string {
    return match($this) {
        self::ACTIVE => 'green',
        self::ABANDONED => 'orange',
        // ...
    };
}
```

## 🚀 **New Features & Capabilities**

### **Cart Management**
- **Multi-State Tracking**: Active → Abandoned → Recovered/Converted
- **Smart Abandonment**: Time-based automatic detection
- **Recovery Campaigns**: Automated email sequences
- **Merge Functionality**: Session + Customer cart merging
- **Activity Tracking**: Last activity timestamps

### **Enhanced Validation**
- **Type Safety**: Enum-based validation rules
- **Multilingual**: Translation key-based messages
- **Comprehensive**: Complete field validation coverage
- **Consistent**: Standardized error messages

### **Improved Data Transfer**
- **CartData**: Complete cart information with relationships
- **Enum Integration**: Status enums in DTOs
- **Performance**: Optimized data loading
- **Type Hints**: Full TypeScript-ready structure

## 📊 **Database Schema Updates**

### **Carts Table (New)**
```sql
CREATE TABLE carts (
    id bigint PRIMARY KEY,
    session_id varchar(255) INDEX,
    customer_id bigint FOREIGN KEY,
    email varchar(255),
    status ENUM('active', 'abandoned', 'converted', 'expired'),
    items JSON,
    subtotal decimal(10,2),
    tax_amount decimal(10,2),
    shipping_amount decimal(10,2),
    discount_amount decimal(10,2),
    total_amount decimal(10,2),
    currency varchar(3),
    
    -- Abandonment tracking
    last_activity_at timestamp,
    abandoned_at timestamp,
    recovery_emails_sent int DEFAULT 0,
    last_recovery_email_sent_at timestamp,
    recovered boolean DEFAULT false,
    recovered_at timestamp,
    converted_order_id bigint FOREIGN KEY,
    
    -- Address data
    shipping_address JSON,
    billing_address JSON,
    metadata JSON,
    
    created_at timestamp,
    updated_at timestamp
);
```

### **Enhanced Indexes**
- `(status, last_activity_at)` - Abandonment queries
- `(status, created_at)` - Analytics queries  
- `(recovered, abandoned_at)` - Recovery tracking

## 🔧 **Updated Services & Repositories**

### **CartService**
- `createCart()`, `updateCart()`, `addItem()`, `removeItem()`
- `markAsAbandoned()`, `markAsRecovered()`, `markAsConverted()`
- `scheduleRecoveryEmail()`, `sendRecoveryEmail()`
- `getOrCreateForSession()`, `getOrCreateForCustomer()`
- `mergeCarts()`, `generateRecoveryLink()`

### **CartRepository**
- `getEligibleForRecovery()`, `autoMarkAbandoned()`
- `getRecoveryStatistics()`, `getTopAbandonedProducts()`
- `bulkSendRecoveryEmails()`, `cleanOldCarts()`
- `getBySession()`, `getByCustomer()`

## 📁 **File Structure Updates**

```
src/
├── Enums/                    # Nuovo: Enum definitions
│   ├── CartStatus.php
│   ├── AddressType.php
│   ├── WishlistStatus.php
│   ├── StockNotificationStatus.php
│   ├── MenuItemType.php
│   └── OrderStatus.php
├── Models/
│   ├── Cart.php             # Nuovo: Unified cart model
│   ├── CustomerAddress.php  # Updated: Enum casts
│   ├── Wishlist.php         # Updated: Enum casts
│   └── StockNotification.php # Updated: Enum casts
├── Http/Requests/
│   ├── Cart/                # Nuovo: Cart requests
│   │   ├── StoreCartRequest.php
│   │   └── UpdateCartRequest.php
│   ├── CustomerAddress/     # Updated: Enum validation
│   ├── Wishlist/           # Updated: Enum validation
│   └── Menu/               # Updated: Translated messages
├── Services/
│   └── CartService.php      # Nuovo: Complete cart management
├── Repositories/
│   └── CartRepository.php   # Nuovo: Advanced cart operations
├── Jobs/
│   └── SendCartRecoveryEmail.php # Nuovo: Recovery emails
└── Data/
    └── Cart/
        └── CartData.php     # Nuovo: Cart DTO
```

## 🌍 **Translation Files**

```
resources/lang/
├── en/
│   ├── validation.php       # Nuovo: English validation messages
│   ├── cart.php            # Enum labels
│   └── address.php         # Address types
└── it/
    ├── validation.php       # Nuovo: Italian validation messages
    ├── cart.php            # Enum labels (Italian)
    └── address.php         # Address types (Italian)
```

## 🎯 **Benefits of New Architecture**

### **Type Safety**
- Enum-based status management
- Strong typing throughout
- IDE autocomplete support
- Compile-time validation

### **Maintainability**
- Single source of truth for statuses
- Centralized validation messages
- Consistent error handling
- Easy to extend

### **Internationalization**
- Translation key-based messages
- Support for multiple languages
- Enum labels in different languages
- Extensible language system

### **Performance**
- Optimized database queries
- Proper indexing strategy
- Efficient cart operations
- Smart caching implementation

### **Business Logic**
- Clear state transitions
- Automated processes
- Recovery workflows
- Analytics & reporting

## ✅ **Migration Path**

1. **Database**: Replace `abandoned_carts` with unified `carts` table
2. **Models**: Update enum casts and relationships
3. **Validation**: Switch to translation keys
4. **Services**: Use new CartService instead of AbandonedCartService
5. **Frontend**: Update to use new enum values and translations

## 🚀 **Ready for Production**

La nuova architettura è:
- ✅ **Type-safe** con enum PHP 8.1+
- ✅ **Multilingual** con sistema di traduzioni
- ✅ **Scalable** con repository pattern ottimizzato
- ✅ **Maintainable** con separazione pulita delle responsabilità
- ✅ **Business-ready** con logiche avanzate di e-commerce

**Perfect for Shopify-like platform with enterprise-grade architecture! 🎉**
