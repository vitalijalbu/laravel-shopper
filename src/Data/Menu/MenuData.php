<?php

namespace Shopper\Data\Menu;

use Spatie\LaravelData\Data;

class MenuData extends Data
{
    public function __construct(
        public ?int $id,
        public string $handle,
        public string $title,
        public ?string $description,
        public array $settings,
        public bool $is_active,
        public int $sort_order,
        public array $items = [],
    ) {}

    public static function fromModel(\Shopper\Models\Menu $menu): self
    {
        return new self(
            id: $menu->id,
            handle: $menu->handle,
            title: $menu->title,
            description: $menu->description,
            settings: $menu->settings ?? [],
            is_active: $menu->is_active,
            sort_order: $menu->sort_order,
            items: $menu->getTree(),
        );
    }
}
