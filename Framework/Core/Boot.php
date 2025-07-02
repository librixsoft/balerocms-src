<?php

namespace Framework\Core;

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

    public function autoload($class)
    {
        $baseDirs = [
            LOCAL_DIR . '/',
        ];

        $relativeClass = ltrim($class, '\\');
        $relativePath = str_replace('\\', '/', $relativeClass) . '.php';

        // Corrección de doble Modules/
        if (str_starts_with($relativePath, 'Modules/Modules/')) {
            $relativePath = substr($relativePath, strlen('Modules/'));
        }

        foreach ($baseDirs as $baseDir) {
            $file = $baseDir . $relativePath;

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // Si no se encontró la clase, lanzar excepción y mostrar en consola
        $message = "No se pudo cargar la clase <code>$class</code><br>Ruta esperada: <code>$relativePath</code>";
        ErrorConsole::handleException(new \Exception($message));
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
