<?php

namespace Framework;

use Framework\ContainerConfiguration;
use Framework\ErrorConsole;
use Framework\Container;
use Throwable;
use Exception;

class Boot
{
    public static Container $container;

    public function __construct()
    {
        // Registrar autoload para cargar clases según namespace dentro de /core/
        spl_autoload_register([$this, "autoload"]);

        // Registrar manejador de errores personalizado
        ErrorConsole::register();

        // Crear instancia del contenedor de dependencias
        self::$container = new Container();

        // Registrar servicios y singletons en el contenedor
        ContainerConfiguration::register(self::$container);
    }

    /**
     * Autoload PSR-4 manual: convierte el namespace a ruta dentro de /core/
     *
     * @param string $class Nombre completo de la clase con namespace
     */
    public function autoload($class)
    {
        $baseDir = LOCAL_DIR . '/core/';
        $relativeClass = ltrim($class, '\\');
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }

    /**
     * Resolver una clase con manejo seguro de excepciones
     *
     * @param string $class Nombre completo con namespace
     * @param array $args Argumentos para el constructor (opcional)
     */
    public static function safeResolve(string $class, array $args = []): void
    {
        try {
            self::resolve($class, $args);
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error resolving '$class': " . $e->getMessage(), 0, $e)
            );
            exit;
        }
    }

    /**
     * Resolver una clase a través del contenedor de dependencias
     *
     * @param string $class Nombre completo con namespace
     * @param array $args Argumentos para el constructor (opcional)
     * @return object Instancia creada
     */
    public static function resolve(string $class, array $args = []): object
    {
        return self::$container->resolve($class, $args);
    }
}
