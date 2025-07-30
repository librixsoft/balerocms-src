<?php

namespace Framework\Core;

use Framework\Http\RequestHelper;
use Framework\Core\View;
use Framework\Config\Context;


use Throwable;
use Exception;

class Boot
{
    /**
     * Contenedor de dependencias estático para toda la aplicación.
     */
    public static Container $container;

    /**
     * Inicializa el framework:
     * - Registra el autoload de clases.
     * - Registra el manejador de errores personalizado.
     * - Inicializa configuraciones globales.
     * - Instancia el contenedor de dependencias.
     */
    public function __construct()
    {
        spl_autoload_register([$this, "autoloadClass"]);

        ErrorConsole::register();

        // Primero: instancia el contenedor
        self::$container = new Container();

        // Este Context ahora registra tod internamente
        Context::init(self::$container);

    }

    /**
     * Autocarga una clase PHP dado su namespace.
     * Busca el archivo PHP correspondiente dentro de los directorios base definidos.
     *
     * @param string $class Nombre completo del namespace + clase.
     * @return void
     */
    public function autoloadClass(string $class): void
    {
        $baseDirs = [
            LOCAL_DIR . '/',
        ];

        $relativeClass = ltrim($class, '\\');
        $relativePath = str_replace('\\', '/', $relativeClass) . '.php';

        // Corrección en caso de doble prefijo 'Modules/Modules/'
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

        $message = "No se pudo cargar la clase <code>$class</code><br>Ruta esperada: <code>$relativePath</code>";
        ErrorConsole::handleException(new \Exception($message));
    }

    /**
     * Intenta cargar y ejecutar un controlador, manejando errores en caso de fallo.
     * Usualmente se llama para iniciar el flujo de ejecución de una petición HTTP.
     *
     * @param string $controllerClass Nombre completo del controlador.
     * @param array $args Argumentos opcionales para la creación del controlador.
     * @return void
     */
    public static function loadController(string $controllerClass, array $args = []): void
    {
        try {
            self::instantiateClass($controllerClass, $args);
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error cargando controlador '$controllerClass': " . $e->getMessage(), 0, $e)
            );
            exit;
        }
    }

    /**
     * Instancia cualquier clase pasando argumentos opcionales y
     * si la instancia tiene método init(), detecta automáticamente
     * sus dependencias y las inyecta desde el contenedor.
     *
     * Esto permite inyección automática para inicialización posterior
     * sin tener que hacerlo manualmente en cada controlador o clase.
     *
     * @param string $class Nombre completo de la clase a instanciar.
     * @param array $args Argumentos opcionales para el constructor.
     * @return object Instancia creada y lista para usar.
     *
     * @throws \RuntimeException Si no se pueden resolver las dependencias de init().
     */
    public static function instantiateClass(string $class, array $args = []): object
    {
        // Crear la instancia con el contenedor y argumentos para el constructor
        $instance = self::$container->resolveInstance($class, $args);

        // Obtener el método init mediante reflexión
        $method = new \ReflectionMethod($instance, 'init');

        // Obtener los parámetros del método init
        $parameters = $method->getParameters();

        $dependencies = [];

        // Por cada parámetro, obtener el tipo y crear su instancia
        foreach ($parameters as $param) {
            $dependencyClass = $param->getType()->getName();
            $dependencies[] = self::$container->resolveInstance($dependencyClass);
        }

        // Invocar el método init con las dependencias creadas
        $method->invokeArgs($instance, $dependencies);

        return $instance;
    }


}
