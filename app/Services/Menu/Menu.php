<?php

namespace App\Services\Menu;

/**
 * @see https://m.dotdev.co/design-pattern-service-layer-with-laravel-5-740ff0a7b65f
 * @see https://blackdeerdev.com/laravel-services-pattern/
 */
final class Menu implements MenuInterface
{
    /**
     * Create new class instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function gerar()
    {
        return [];
    }
}
