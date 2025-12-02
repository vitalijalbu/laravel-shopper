<?php

declare(strict_types=1);

namespace Shopper\Core\Addon\Events;

use Shopper\Core\Addon\AddonInterface;

class PluginUpdated
{
    public function __construct(
        public AddonInterface $plugin,
        public string $fromVersion,
        public string $toVersion
    ) {}
}
