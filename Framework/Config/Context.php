<?php

namespace Framework\Config;

use Framework\Core\ConfigSettings;
use Framework\Core\Container;

class Context
{
    protected static Container $container;

    // Aquí defines todos los servicios (solo una vez)
    protected static array $services = [
        'config' => ConfigSettings::class,
        // 'request' => \Framework\Http\RequestHelper::class,
        // 'logger' => \Framework\Logger\Logger::class,
    ];

    public static function init(Container $container): void
    {
        self::$container = $container;

        // Registro automático de los servicios como singleton
        foreach (self::$services as $alias => $class) {
            $container->registerSingletonInstance($class, new $class());
        }
    }

    public static function getContainer(): Container
    {
        return self::$container;
    }

    // Acceso por alias o FQCN
    public static function get(string $aliasOrClass): mixed
    {
        $class = self::$services[$aliasOrClass] ?? $aliasOrClass;
        return self::$container->resolveInstance($class);
    }
}
