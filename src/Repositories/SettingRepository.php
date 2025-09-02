<?php

namespace LaravelShopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use LaravelShopper\Models\Setting;

class SettingRepository extends BaseRepository
{
    protected string $cachePrefix = 'settings';

    protected function makeModel(): Model
    {
        return new Setting();
    }

    /**
     * Get setting value by key
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = $this->getCacheKey('value', $key);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($key, $default) {
            $setting = $this->model->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value
     */
    public function set(string $key, $value): Model
    {
        $setting = $this->model->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        $this->clearCache();

        return $setting;
    }

    /**
     * Get multiple settings by keys
     */
    public function getMultiple(array $keys): array
    {
        $cacheKey = $this->getCacheKey('multiple', md5(serialize($keys)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($keys) {
            $settings = $this->model->whereIn('key', $keys)->get();
            $result = [];

            foreach ($keys as $key) {
                $setting = $settings->firstWhere('key', $key);
                $result[$key] = $setting ? $setting->value : null;
            }

            return $result;
        });
    }

    /**
     * Set multiple settings
     */
    public function setMultiple(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->model->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->clearCache();
    }

    /**
     * Get all settings grouped by category
     */
    public function getAllGrouped(): array
    {
        $cacheKey = $this->getCacheKey('all_grouped', '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $settings = $this->model->all();
            $grouped = [];

            foreach ($settings as $setting) {
                $parts = explode('.', $setting->key);
                $category = $parts[0] ?? 'general';
                
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                
                $grouped[$category][$setting->key] = $setting->value;
            }

            return $grouped;
        });
    }

    /**
     * Get settings by category prefix
     */
    public function getByCategory(string $category): Collection
    {
        $cacheKey = $this->getCacheKey('category', $category);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($category) {
            return $this->model->where('key', 'like', $category . '.%')->get();
        });
    }

    /**
     * Delete setting by key
     */
    public function deleteByKey(string $key): bool
    {
        $result = $this->model->where('key', $key)->delete();
        $this->clearCache();
        return $result > 0;
    }

    /**
     * Get general settings
     */
    public function getGeneralSettings(): array
    {
        return $this->getMultiple([
            'general.store_name',
            'general.store_description',
            'general.store_email',
            'general.store_phone',
            'general.store_address',
            'general.store_city',
            'general.store_state',
            'general.store_country',
            'general.store_postal_code',
            'general.timezone',
            'general.currency',
            'general.weight_unit',
            'general.dimension_unit',
        ]);
    }

    /**
     * Update general settings
     */
    public function updateGeneralSettings(array $settings): void
    {
        $filteredSettings = [];
        foreach ($settings as $key => $value) {
            if (strpos($key, 'general.') === 0) {
                $filteredSettings[$key] = $value;
            }
        }

        $this->setMultiple($filteredSettings);
    }

    /**
     * Get checkout settings
     */
    public function getCheckoutSettings(): array
    {
        return $this->getMultiple([
            'checkout.guest_checkout_enabled',
            'checkout.account_creation_required',
            'checkout.terms_acceptance_required',
            'checkout.newsletter_signup_enabled',
            'checkout.order_notes_enabled',
            'checkout.phone_required',
            'checkout.company_field_enabled',
        ]);
    }

    /**
     * Get email settings
     */
    public function getEmailSettings(): array
    {
        return $this->getMultiple([
            'email.order_confirmation_enabled',
            'email.order_status_updates_enabled',
            'email.shipping_confirmation_enabled',
            'email.customer_welcome_enabled',
            'email.low_stock_notifications_enabled',
            'email.admin_notification_email',
        ]);
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        Cache::tags($tags)->flush();
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $method, mixed $identifier): string
    {
        return $this->cachePrefix . '_' . $method . ($identifier ? '_' . $identifier : '');
    }
}
