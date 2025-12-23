<?php

declare(strict_types=1);

namespace Cartino\Database\Seeders;

use Cartino\Models\Vocabulary;
use Illuminate\Database\Seeder;

class VocabularySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedOrderStatuses();
        $this->seedPaymentStatuses();
        $this->seedFulfillmentStatuses();
        $this->seedShippingStatuses();
        $this->seedReturnStatuses();
        $this->seedProductTypes();
        $this->seedStockStatuses();
    }

    /**
     * Seed order statuses.
     */
    protected function seedOrderStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => [
                    'color' => 'orange',
                    'is_final' => false,
                    'allowed_transitions' => ['confirmed', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'confirmed',
                'labels' => ['en' => 'Confirmed', 'it' => 'Confermato'],
                'sort_order' => 20,
                'meta' => [
                    'color' => 'blue',
                    'is_final' => false,
                    'allowed_transitions' => ['processing', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'processing',
                'labels' => ['en' => 'Processing', 'it' => 'In lavorazione'],
                'sort_order' => 30,
                'meta' => [
                    'color' => 'purple',
                    'is_final' => false,
                    'allowed_transitions' => ['shipped', 'cancelled'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'shipped',
                'labels' => ['en' => 'Shipped', 'it' => 'Spedito'],
                'sort_order' => 40,
                'meta' => [
                    'color' => 'indigo',
                    'is_final' => false,
                    'allowed_transitions' => ['delivered', 'returned'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'delivered',
                'labels' => ['en' => 'Delivered', 'it' => 'Consegnato'],
                'sort_order' => 50,
                'meta' => [
                    'color' => 'green',
                    'is_final' => true,
                    'allowed_transitions' => ['returned'],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'cancelled',
                'labels' => ['en' => 'Cancelled', 'it' => 'Annullato'],
                'sort_order' => 60,
                'meta' => [
                    'color' => 'red',
                    'is_final' => true,
                    'allowed_transitions' => [],
                ],
                'is_system' => true,
            ],
            [
                'code' => 'refunded',
                'labels' => ['en' => 'Refunded', 'it' => 'Rimborsato'],
                'sort_order' => 70,
                'meta' => [
                    'color' => 'gray',
                    'is_final' => true,
                    'allowed_transitions' => [],
                ],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('order_status', $status['code'], $status);
        }
    }

    /**
     * Seed payment statuses.
     */
    protected function seedPaymentStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'authorized',
                'labels' => ['en' => 'Authorized', 'it' => 'Autorizzato'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'paid',
                'labels' => ['en' => 'Paid', 'it' => 'Pagato'],
                'sort_order' => 30,
                'meta' => ['color' => 'green', 'is_final' => true],
                'is_system' => true,
            ],
            [
                'code' => 'partially_refunded',
                'labels' => ['en' => 'Partially Refunded', 'it' => 'Parzialmente rimborsato'],
                'sort_order' => 40,
                'meta' => ['color' => 'yellow', 'is_final' => false],
                'is_system' => true,
            ],
            [
                'code' => 'refunded',
                'labels' => ['en' => 'Refunded', 'it' => 'Rimborsato'],
                'sort_order' => 50,
                'meta' => ['color' => 'gray', 'is_final' => true],
                'is_system' => true,
            ],
            [
                'code' => 'failed',
                'labels' => ['en' => 'Failed', 'it' => 'Fallito'],
                'sort_order' => 60,
                'meta' => ['color' => 'red', 'is_final' => true],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('payment_status', $status['code'], $status);
        }
    }

    /**
     * Seed fulfillment statuses.
     */
    protected function seedFulfillmentStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'fulfilled',
                'labels' => ['en' => 'Fulfilled', 'it' => 'Evaso'],
                'sort_order' => 20,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
            [
                'code' => 'partially_fulfilled',
                'labels' => ['en' => 'Partially Fulfilled', 'it' => 'Parzialmente evaso'],
                'sort_order' => 30,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('fulfillment_status', $status['code'], $status);
        }
    }

    /**
     * Seed shipping statuses.
     */
    protected function seedShippingStatuses(): void
    {
        $statuses = [
            [
                'code' => 'pending',
                'labels' => ['en' => 'Pending', 'it' => 'In attesa'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'processing',
                'labels' => ['en' => 'Processing', 'it' => 'In preparazione'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
            [
                'code' => 'shipped',
                'labels' => ['en' => 'Shipped', 'it' => 'Spedito'],
                'sort_order' => 30,
                'meta' => ['color' => 'indigo'],
                'is_system' => true,
            ],
            [
                'code' => 'in_transit',
                'labels' => ['en' => 'In Transit', 'it' => 'In transito'],
                'sort_order' => 40,
                'meta' => ['color' => 'purple'],
                'is_system' => true,
            ],
            [
                'code' => 'delivered',
                'labels' => ['en' => 'Delivered', 'it' => 'Consegnato'],
                'sort_order' => 50,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('shipping_status', $status['code'], $status);
        }
    }

    /**
     * Seed return statuses.
     */
    protected function seedReturnStatuses(): void
    {
        $statuses = [
            [
                'code' => 'requested',
                'labels' => ['en' => 'Requested', 'it' => 'Richiesto'],
                'sort_order' => 10,
                'meta' => ['color' => 'orange'],
                'is_system' => true,
            ],
            [
                'code' => 'approved',
                'labels' => ['en' => 'Approved', 'it' => 'Approvato'],
                'sort_order' => 20,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
            [
                'code' => 'rejected',
                'labels' => ['en' => 'Rejected', 'it' => 'Rifiutato'],
                'sort_order' => 30,
                'meta' => ['color' => 'red'],
                'is_system' => true,
            ],
            [
                'code' => 'in_transit',
                'labels' => ['en' => 'In Transit', 'it' => 'In transito'],
                'sort_order' => 40,
                'meta' => ['color' => 'purple'],
                'is_system' => true,
            ],
            [
                'code' => 'received',
                'labels' => ['en' => 'Received', 'it' => 'Ricevuto'],
                'sort_order' => 50,
                'meta' => ['color' => 'indigo'],
                'is_system' => true,
            ],
            [
                'code' => 'completed',
                'labels' => ['en' => 'Completed', 'it' => 'Completato'],
                'sort_order' => 60,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('return_status', $status['code'], $status);
        }
    }

    /**
     * Seed product types.
     */
    protected function seedProductTypes(): void
    {
        $types = [
            [
                'code' => 'physical',
                'labels' => ['en' => 'Physical Product', 'it' => 'Prodotto fisico'],
                'sort_order' => 10,
                'meta' => ['requires_shipping' => true, 'has_inventory' => true],
                'is_system' => true,
            ],
            [
                'code' => 'digital',
                'labels' => ['en' => 'Digital Product', 'it' => 'Prodotto digitale'],
                'sort_order' => 20,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false],
                'is_system' => true,
            ],
            [
                'code' => 'service',
                'labels' => ['en' => 'Service', 'it' => 'Servizio'],
                'sort_order' => 30,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false],
                'is_system' => true,
            ],
            [
                'code' => 'subscription',
                'labels' => ['en' => 'Subscription', 'it' => 'Abbonamento'],
                'sort_order' => 40,
                'meta' => ['requires_shipping' => false, 'has_inventory' => false, 'is_recurring' => true],
                'is_system' => true,
            ],
        ];

        foreach ($types as $type) {
            Vocabulary::createOrUpdate('product_type', $type['code'], $type);
        }
    }

    /**
     * Seed stock statuses.
     */
    protected function seedStockStatuses(): void
    {
        $statuses = [
            [
                'code' => 'in_stock',
                'labels' => ['en' => 'In Stock', 'it' => 'Disponibile'],
                'sort_order' => 10,
                'meta' => ['color' => 'green'],
                'is_system' => true,
            ],
            [
                'code' => 'low_stock',
                'labels' => ['en' => 'Low Stock', 'it' => 'Scorte basse'],
                'sort_order' => 20,
                'meta' => ['color' => 'yellow'],
                'is_system' => true,
            ],
            [
                'code' => 'out_of_stock',
                'labels' => ['en' => 'Out of Stock', 'it' => 'Esaurito'],
                'sort_order' => 30,
                'meta' => ['color' => 'red'],
                'is_system' => true,
            ],
            [
                'code' => 'preorder',
                'labels' => ['en' => 'Pre-order', 'it' => 'Pre-ordine'],
                'sort_order' => 40,
                'meta' => ['color' => 'blue'],
                'is_system' => true,
            ],
        ];

        foreach ($statuses as $status) {
            Vocabulary::createOrUpdate('stock_status', $status['code'], $status);
        }
    }
}
