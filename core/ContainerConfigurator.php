<?php

class ContainerConfiguration
{
    public static function register(Container $container): void
    {

        /**
         * Solamente se registran constructores personalizados en singleton
         *  todos los demas son automaticos
         */
        $container->singleton(View::class, new View(LOCAL_DIR . '/views'));

    }
}
