<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\Controller;
use Shopper\Models\Cart;
use Shopper\Models\Product;
use Shopper\Models\ProductVariant;

class CartController extends ApiController
{
    /**
     * Get current cart
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $cart = $this->getCurrentCart($request);

            if (! $cart) {
                return response()->json([
                    'data' => null,
                    'message' => 'Carrello vuoto',
                ]);
            }

            return response()->json([
                'data' => $cart->load(['lines.product', 'lines.productVariant', 'currency']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero del carrello',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array',
        ]);

        try {
            $cart = $this->getOrCreateCart($request);
            $product = Product::findOrFail($validated['product_id']);
            $variant = $validated['product_variant_id']
                ? ProductVariant::findOrFail($validated['product_variant_id'])
                : null;

            $line = $cart->addItem(
                $product,
                $validated['quantity'],
                $variant,
                $validated['options'] ?? []
            );

            return response()->json([
                'message' => 'Prodotto aggiunto al carrello',
                'data' => [
                    'cart' => $cart->fresh(['lines.product', 'lines.productVariant']),
                    'line' => $line,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiunta al carrello',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, string $lineId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        try {
            $cart = $this->getCurrentCart($request);

            if (! $cart) {
                return response()->json([
                    'message' => 'Carrello non trovato',
                ], 404);
            }

            $line = $cart->lines()->findOrFail($lineId);

            if ($validated['quantity'] == 0) {
                $cart->removeItem($line);
                $message = 'Prodotto rimosso dal carrello';
            } else {
                $unitPrice = $line->product_variant_id
                    ? $line->productVariant->price
                    : $line->product->price;

                $line->update([
                    'quantity' => $validated['quantity'],
                    'line_total' => $unitPrice * $validated['quantity'],
                ]);

                $cart->calculateTotals();
                $message = 'Quantità aggiornata';
            }

            return response()->json([
                'message' => $message,
                'data' => $cart->fresh(['lines.product', 'lines.productVariant']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento del carrello',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, string $lineId): JsonResponse
    {
        try {
            $cart = $this->getCurrentCart($request);

            if (! $cart) {
                return response()->json([
                    'message' => 'Carrello non trovato',
                ], 404);
            }

            $line = $cart->lines()->findOrFail($lineId);
            $cart->removeItem($line);

            return response()->json([
                'message' => 'Prodotto rimosso dal carrello',
                'data' => $cart->fresh(['lines.product', 'lines.productVariant']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la rimozione dal carrello',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $cart = $this->getCurrentCart($request);

            if (! $cart) {
                return response()->json([
                    'message' => 'Carrello già vuoto',
                ]);
            }

            $cart->lines()->delete();
            $cart->calculateTotals();

            return response()->json([
                'message' => 'Carrello svuotato',
                'data' => $cart->fresh(['lines']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante lo svuotamento del carrello',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply discount code
     */
    public function applyDiscount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        try {
            $cart = $this->getCurrentCart($request);

            if (! $cart) {
                return response()->json([
                    'message' => 'Carrello non trovato',
                ], 404);
            }

            // Here you would implement discount logic
            // For now, return a placeholder response

            return response()->json([
                'message' => 'Sconto applicato con successo',
                'data' => $cart->fresh(['lines.product', 'lines.productVariant']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'applicazione dello sconto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update shipping address
     */
    public function updateShippingAddress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            $cart = $this->getOrCreateCart($request);

            $cart->update([
                'shipping_address' => $validated,
            ]);

            return response()->json([
                'message' => 'Indirizzo di spedizione aggiornato',
                'data' => $cart->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento dell\'indirizzo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create cart for current session/customer
     */
    private function getCurrentCart(Request $request): ?Cart
    {
        if ($customerId = $request->user('customers')?->id) {
            return Cart::where('customer_id', $customerId)
                ->where('status', 'active')
                ->first();
        }

        $sessionId = $request->session()->getId();

        return Cart::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get or create cart for current session/customer
     */
    private function getOrCreateCart(Request $request): Cart
    {
        $cart = $this->getCurrentCart($request);

        if (! $cart) {
            $cart = Cart::create([
                'session_id' => $request->session()->getId(),
                'customer_id' => $request->user('customers')?->id,
                'currency_id' => 1, // Default currency - should be configurable
                'status' => 'active',
                'expires_at' => now()->addDays(30),
            ]);
        }

        return $cart;
    }
}
