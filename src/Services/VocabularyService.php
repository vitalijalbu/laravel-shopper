<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Models\Vocabulary;
use Illuminate\Support\Collection;

/**
 * VocabularyService
 *
 * Service for managing vocabularies and providing data for Inertia/Vue components.
 */
class VocabularyService
{
    /**
     * Get vocabularies for all common groups (for Inertia global props).
     *
     * @param string|null $locale
     * @return array<string, array>
     */
    public function getCommonVocabularies(?string $locale = null): array
    {
        return Vocabulary::getMultipleGroups([
            'order_status',
            'payment_status',
            'fulfillment_status',
            'shipping_status',
            'return_status',
            'product_type',
            'stock_status',
        ], $locale);
    }

    /**
     * Get vocabularies for specific groups.
     *
     * @param array $groups
     * @param string|null $locale
     * @return array<string, array>
     */
    public function getVocabularies(array $groups, ?string $locale = null): array
    {
        return Vocabulary::getMultipleGroups($groups, $locale);
    }

    /**
     * Get a single vocabulary group.
     *
     * @param string $group
     * @param string|null $locale
     * @return array
     */
    public function getGroup(string $group, ?string $locale = null): array
    {
        return Vocabulary::getSelectOptions($group, $locale);
    }

    /**
     * Get simple options (value => label) for a group.
     *
     * @param string $group
     * @param string|null $locale
     * @return array<string, string>
     */
    public function getOptions(string $group, ?string $locale = null): array
    {
        return Vocabulary::getOptions($group, $locale);
    }

    /**
     * Find a vocabulary by group and code.
     */
    public function find(string $group, string $code): ?Vocabulary
    {
        return Vocabulary::findByGroupAndCode($group, $code);
    }

    /**
     * Get label for a specific vocabulary code.
     */
    public function getLabel(string $group, string $code, ?string $locale = null): ?string
    {
        $vocabulary = $this->find($group, $code);

        return $vocabulary?->getLabel($locale);
    }

    /**
     * Validate if a transition is allowed.
     */
    public function canTransition(string $group, string $fromCode, string $toCode): bool
    {
        $from = $this->find($group, $fromCode);

        if (! $from) {
            return false;
        }

        return $from->canTransitionTo($toCode);
    }

    /**
     * Get all available groups.
     */
    public function getAllGroups(): Collection
    {
        return Vocabulary::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');
    }

    /**
     * Create or update a vocabulary.
     */
    public function createOrUpdate(string $group, string $code, array $data): Vocabulary
    {
        return Vocabulary::createOrUpdate($group, $code, $data);
    }

    /**
     * Bulk create or update vocabularies.
     */
    public function bulkCreateOrUpdate(string $group, array $vocabularies): void
    {
        foreach ($vocabularies as $vocabulary) {
            $this->createOrUpdate($group, $vocabulary['code'], $vocabulary);
        }
    }
}
