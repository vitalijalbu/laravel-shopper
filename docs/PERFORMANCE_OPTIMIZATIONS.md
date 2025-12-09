## Ecommerce Performance Plan (500k products, multi-store/channels)

### Key Findings (code today)
- `Product` uses `HasOptimizedFilters` with default eager `brand` + `productType`, but listings (`ProductCollection`) also touch `category`, `brand`, `variants_count`; ensure list queries pre-load `category`, `brand`, `variants` counts to avoid N+1.
- `BaseRepository` caches using a single stored query builder and no tag invalidation (only prefix). Long-lived instances may keep state; reset builder before each query and prefer cache tags.
- `Cart` keeps `items` JSON while `CartLine` exists; risk of divergence and large payloads. Prefer normalized `cart_lines` and drop JSON once migrated.
- `OrderRepository` loads `items.product` but `Order` model uses `lines`; align naming to avoid double queries and ensure eager relations are consistent.
- Filtering/search relies on DB `LIKE`; at 500k SKUs this will be slow without full-text/search engine.

### High-Impact Optimizations
- **ORM loading**: On all product lists (`paginateFilter`, CP grids, GraphQL), always `with(['brand:id,name,slug', 'productType:id,name', 'category:id,name', 'media'])` and `withCount(['variants'])`. Keep projection lean via `select` for list views (id, site_id, name, slug, price, status, published_at, brand_id, product_type_id, category_id, average_rating, review_count).
- **Query patterns**: Replace `->get()->flatMap()` in `CartRepository::getTopAbandonedProducts` with aggregated SQL to avoid loading cart payloads. For exports/sitemaps use `chunkById`/`cursor`.
- **Search**: Offload product search and faceting to Elastic/Meilisearch/OpenSearch. Sync via queue; store denormalized searchable doc (site_id, channel, availability, price range, facets, variant options) to avoid heavy DB `LIKE` scans.
- **Caching**: Cache rendered product detail responses (short TTL) and catalog/navigation trees (long TTL). Use Redis tags (site/channel) so invalidations are scoped. Consider HTTP cache (CDN) for public product and media URLs with ETag/Last-Modified.
- **Price/inventory**: Keep prices as `INT` cents; avoid `decimal` when summing large sets. Inventory updates must be atomic (`update ... where quantity >= ?`). For flash sales, use a reservation table with TTL.
- **Write/read split**: Use read replicas for catalog reads; keep writes on primary. Route heavy GET to replicas with a safe lag guard on cart/checkout paths.
- **Background jobs**: Move slow tasks (recommendations, email, search reindex, media conversions) off request path. Use queue batch + rate limiting.

### Database Schema / Index Plan
Assuming MySQL/InnoDB.

**Products** (`products`)
- Ensure int/bigint unsigned PKs; prices as `int` cents; `average_rating` as `decimal(3,2)`.
- Indexes:
  - unique `(site_id, slug)`; unique `(site_id, sku)`.
  - index `(site_id, status, published_at)` for storefront listings.
  - index `(site_id, brand_id)`; `(site_id, product_type_id)`; `(status, published_at)`.
  - partial/filtered if supported: `(status='published', published_at)`.
- Optional: partition by `site_id` (HASH) when store count is high to keep secondary indexes smaller.

**Product Variants** (`product_variants`)
- unique `(product_id, sku)`; index `(product_id, is_active)`; index `(product_id, price)` if variant price filtering exists.
- Store inventory in `product_variant_inventories` with `(variant_id)` PK + `(location_id, variant_id)` unique for multi-location.

**Catalogs / Channels**
- `catalog_product`: composite indexes `(catalog_id, product_id)` and `(product_id, catalog_id)`; add `position` index if used for ordering.
- `catalog_product_variant`: indexes on `(catalog_id, product_variant_id)`; `(product_variant_id, catalog_id)`.
- `site_catalog`: index `(site_id, is_active)`; `(site_id, is_default)`.

**Categories/Collections**
- For tree structures, ensure `(parent_id)` and `(lft, rgt)` indexes if using nested sets. Add `(site_id, slug)` unique.

**Orders**
- unique `order_number`; indexes `(customer_id, created_at)`, `(status, created_at)`, `(payment_status, fulfillment_status)`, `(site_id, channel_id, created_at)` if multi-channel.
- `order_lines`: indexes `(order_id)`, `(product_id)`, `(product_variant_id)`.

**Carts**
- Prefer `cart_lines` over JSON. Add `(cart_id)` and `(product_id, cart_id)` indexes. On `carts`, index `(status, updated_at)` for abandonment scans.

**Reviews/Favorites**
- `product_reviews`: index `(product_id, is_approved)`, `(product_id, created_at)`.
- Polymorphic favorites: composite index `(favoriteable_type, favoriteable_id, customer_id)`.

### Query + Code Changes (actionable)
- Add a reusable `Product::scopeListDefaults` that applies lean selects, eager loads, counts, and `published` scope; use it in API/GraphQL/CP listings to standardize and prevent N+1.
- Update `ProductController@index/search/featured` to call `with(...)` + `withCount` and select minimal columns before `paginateFilter`.
- In `ProductResource`/`ProductCollection`, rely on preloaded relations; add guards to avoid calling `$product->category` when not loaded.
- Refactor `CartRepository::getTopAbandonedProducts` to a single SQL aggregation:
  - `select product_id, sum(quantity) qty, sum(total) total from cart_lines join carts on ... where carts.status='abandoned' group by product_id order by qty desc limit 10`.
- Align `OrderRepository` to use `lines` relation consistently; ensure `with(['customer','lines.product'])` and adjust property names.
- Replace `BaseRepository` shared `$query` with per-call builder or call `resetQuery()` at start of each public method to avoid stale where clauses and to make caching consistent.
- Add DB-level safeguards for inventory decrement: `update product_variants set stock = stock - ? where id = ? and stock >= ?` and check affected rows.

### Data Retention / Archiving
- Move old orders/events to cold tables or partitions by year; keep hot partition small. Use summary tables for dashboards (daily sales, top products) updated via jobs.
- TTL log tables (webhooks, job logs) via scheduled deletes or MySQL events.

### Storage & Memory
- Prefer `json` for flexible fields but keep hot filters in scalar columns (status, price, weight, dimensions ranges). Avoid large blobs in frequently queried tables; move media/meta to side tables.
- Compress large text (descriptions) in search index only; keep DB lean with mediumtext but avoid selecting it in list queries.

### Caching/Infra Checklist
- Redis: cache product detail, catalog trees, filters, and settings keyed by `site:{id}:...`; enable tag-based invalidation.
- CDN: cache images/media aggressively; set `Cache-Control` for product pages where acceptable with soft TTL + stale-while-revalidate.
- Horizon/Queue: isolate high-priority queues (checkout, inventory) from low-priority (emails, exports).

### Migration Sketches (snippets)
```php
// products table indexes
Schema::table('products', function (Blueprint $table) {
    $table->unique(['site_id', 'slug']);
    $table->unique(['site_id', 'sku']);
    $table->index(['site_id', 'status', 'published_at']);
    $table->index(['site_id', 'brand_id']);
    $table->index(['site_id', 'product_type_id']);
    $table->index(['status', 'published_at']);
});

// catalog pivot
Schema::table('catalog_product', function (Blueprint $table) {
    $table->index(['catalog_id', 'product_id']);
    $table->index(['product_id', 'catalog_id']);
});

// variants
Schema::table('product_variants', function (Blueprint $table) {
    $table->unique(['product_id', 'sku']);
    $table->index(['product_id', 'is_active']);
});

// orders
Schema::table('orders', function (Blueprint $table) {
    $table->unique('order_number');
    $table->index(['customer_id', 'created_at']);
    $table->index(['status', 'created_at']);
    $table->index(['payment_status', 'fulfillment_status']);
});

// carts
Schema::table('carts', function (Blueprint $table) {
    $table->index(['status', 'updated_at']);
});

Schema::table('cart_lines', function (Blueprint $table) {
    $table->index('cart_id');
    $table->index(['product_id', 'cart_id']);
});
```

### Rollout Order (suggested)
1) Add indexes above; deploy with minimal lock (pt-online-schema-change / gh-ost if needed).
2) Standardize product listing scope + eager loads; fix repositories to reset builders.
3) Switch cart to normalized `cart_lines`, deprecate JSON `items` writes, then drop column in later migration.
4) Introduce inventory-safe decrement and reservations.
5) Add Redis tag caching + CDN headers; configure read replicas for catalog reads.
6) Integrate search engine and backfill index; route search/filters there.
7) Partition/archive old orders/events and build summary tables.

### KPIs to Track
- P95/P99 for product list and detail, checkout create, add-to-cart.
- DB metrics: buffer pool hit, slow query count, replica lag, deadlocks.
- Cache: hit ratio per keyspace, tag flush frequency.
- Index health: handler_read_next, full table scans, rows_examined per query.
