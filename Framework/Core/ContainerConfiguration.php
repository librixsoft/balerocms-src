<?php

namespace Framework\Core;

use Framework\Core\View;

class ContainerConfiguration
{
    public static function register(Container $container): void
    {
        $container->singleton(View::class, new View(LOCAL_DIR . '/views'));
    }
}
