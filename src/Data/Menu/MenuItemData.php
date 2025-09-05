<?php

namespace Shopper\Data\Menu;

use Spatie\LaravelData\Data;

class MenuItemData extends Data
{
    public function __construct(
        public ?int $id,
        public int $menu_id,
        public ?int $parent_id,
        public string $title,
        public ?string $url,
        public string $type,
        public ?string $reference_type,
        public ?int $reference_id,
        public array $data,
        public string $status,
        public bool $opens_in_new_window,
        public ?string $css_class,
        public int $sort_order,
        public int $depth,
        public array $children = [],
    ) {}

    public static function fromModel(\Shopper\Models\MenuItem $item): self
    {
        return new self(
            id: $item->id,
            menu_id: $item->menu_id,
            parent_id: $item->parent_id,
            title: $item->title,
            url: $item->getComputedUrl(),
            type: $item->type,
            reference_type: $item->reference_type,
            reference_id: $item->reference_id,
            data: $item->data ?? [],
            status: $item->status ?? 'active',
            opens_in_new_window: $item->opens_in_new_window,
            css_class: $item->css_class,
            sort_order: $item->sort_order,
            depth: $item->depth,
            children: $item->children->map(fn ($child) => self::fromModel($child))->toArray(),
        );
    }
}
