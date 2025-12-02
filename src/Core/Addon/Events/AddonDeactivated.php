<?php

declare(strict_types=1);

namespace Shopper\Core\Addon\Events;

use Shopper\Core\Addon\AddonInterface;

class PluginDeactivated
{
    public function __construct(public AddonInterface $plugin)
    {
    }
}
