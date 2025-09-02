<?php

namespace LaravelShopper\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelShopper\Models\Order;
use LaravelShopper\Models\Customer;
use LaravelShopper\Models\Product;

class OrderRepository extends BaseRepository
{
    protected array $with = ['customer', 'items', 'items.product'];

    protected string $cachePrefix = 'orders';

    protected function makeModel(): Model
    {
        return new Order();
    }

    /**
     * Get paginated orders with filters and search
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['customer', 'items.product']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Payment status filter
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Create a new order with items
     */
    public function createWithItems(array $orderData, array $items): Order
    {
        // Clear cache
        $this->clearCache();

        // Generate order number if not provided
        if (empty($orderData['order_number'])) {
            $orderData['order_number'] = $this->generateOrderNumber();
        }

        // Calculate totals
        $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
        $shippingAmount = $orderData['shipping_amount'] ?? 0;
        $orderData['total_amount'] = $subtotal + $shippingAmount;

        // Create order
        $order = $this->model->create($orderData);

        // Add order items
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return $order->load(['customer', 'items.product']);
    }

    /**
     * Update order with items
     */
    public function updateWithItems(int $id, array $orderData, array $items): Model
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);

        // Calculate totals
        $subtotal = collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price']);
        $shippingAmount = $orderData['shipping_amount'] ?? 0;
        $orderData['total_amount'] = $subtotal + $shippingAmount;

        // Update order
        $order->update($orderData);

        // Update order items - delete old ones and create new ones
        $order->items()->delete();
        
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return $order->load(['customer', 'items.product']);
    }

    /**
     * Update order
     */
    public function update(int $id, array $attributes): Model
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);
        $order->update($attributes);
        
        return $order;
    }

    /**
     * Delete order
     */
    public function delete(int $id): bool
    {
        // Clear cache
        $this->clearCache();

        $order = $this->model->find($id);
        
        // Delete order items first
        $order->items()->delete();
        
        return $order->delete();
    }

    /**
     * Get customers for order creation
     */
    public function getCustomersForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Customer::select('id', 'first_name', 'last_name', 'email')
                      ->where('is_active', true)
                      ->orderBy('first_name')
                      ->get();
    }

    /**
     * Get products for order creation
     */
    public function getProductsForSelect(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::select('id', 'name', 'price')
                     ->where('is_active', true)
                     ->orderBy('name')
                     ->get();
    }

    /**
     * Generate unique order number
     */
    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->model->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        \Illuminate\Support\Facades\Cache::tags($tags)->flush();
    }
}
