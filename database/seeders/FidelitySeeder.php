<?php

declare(strict_types=1);

namespace Database\Seeders;

use Cartino\Models\Customer;
use Cartino\Models\FidelityCard;
use Cartino\Models\FidelityTransaction;
use Illuminate\Database\Seeder;

class FidelitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea alcune fidelity cards di esempio
        Customer::factory(50)->create()->each(function ($customer) {
            $card = FidelityCard::factory()->create([
                'customer_id' => $customer->id,
            ]);

            // Crea alcune transazioni per ogni card
            FidelityTransaction::factory(rand(5, 20))->create([
                'fidelity_card_id' => $card->id,
            ]);

            // Ricalcola i totali della card basandosi sulle transazioni
            $this->recalculateCardTotals($card);
        });

        // Crea alcune carte con molti punti
        Customer::factory(5)->create()->each(function ($customer) {
            $card = FidelityCard::factory()->withHighPoints()->create([
                'customer_id' => $customer->id,
            ]);

            // Crea molte transazioni per queste carte VIP
            FidelityTransaction::factory(rand(30, 50))->create([
                'fidelity_card_id' => $card->id,
            ]);

            $this->recalculateCardTotals($card);
        });

        // Crea alcune carte nuove senza transazioni
        Customer::factory(10)->create()->each(function ($customer) {
            FidelityCard::factory()->newCard()->create([
                'customer_id' => $customer->id,
            ]);
        });
    }

    /**
     * Ricalcola i totali di una fidelity card basandosi sulle sue transazioni
     */
    private function recalculateCardTotals(FidelityCard $card): void
    {
        $earnedPoints = $card->transactions()->where('type', 'earned')->sum('points');
        $redeemedPoints = abs($card->transactions()->where('type', 'redeemed')->sum('points'));
        $expiredPoints = abs($card->transactions()->where('type', 'expired')->sum('points'));
        $adjustments = $card->transactions()->where('type', 'adjusted')->sum('points');

        $totalEarned = $earnedPoints + max(0, $adjustments);
        $totalRedeemed = $redeemedPoints + abs(min(0, $adjustments));
        $availablePoints = $totalEarned - $totalRedeemed - $expiredPoints;

        $card->update([
            'total_points' => $totalEarned,
            'available_points' => max(0, $availablePoints),
            'total_earned' => $totalEarned,
            'total_redeemed' => $totalRedeemed,
            'last_activity_at' => $card->transactions()->latest()->first()?->created_at,
        ]);
    }
}
