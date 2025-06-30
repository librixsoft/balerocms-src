<?php
namespace Framework;

use Framework\View;

class ContainerConfiguration
{
    public static function register(Container $container): void
    {
        $container->singleton(View::class, new View(LOCAL_DIR . '/views'));
    }
}
