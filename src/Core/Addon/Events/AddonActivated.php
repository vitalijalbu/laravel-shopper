<?php

declare(strict_types=1);

namespace Shopper\Core\Addon\Events;

use Shopper\Core\Addon\AddonInterface;

class PluginActivated
{
    public function __construct(public AddonInterface $plugin)
    {
    }
}
