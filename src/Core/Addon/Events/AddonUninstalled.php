<?php

declare(strict_types=1);

namespace Shopper\Core\Addon\Events;

use Shopper\Core\Addon\AddonInterface;

class PluginUninstalled
{
    public function __construct(public AddonInterface $plugin)
    {
    }
}
