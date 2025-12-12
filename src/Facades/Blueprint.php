<?php

declare(strict_types=1);

namespace Cartino\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cartino\Blueprints\Blueprint|null find(string $handle)
 * @method static \Illuminate\Support\Category all()
 * @method static \Cartino\Blueprints\Blueprint make(string $handle)
 * @method static void save(\Cartino\Blueprints\Blueprint $blueprint)
 *
 * @see \Cartino\Blueprints\BlueprintRepository
 */
class Blueprint extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cartino.blueprints';
    }
}
