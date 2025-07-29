<?php

namespace Framework\Core;

use Framework\Http\RequestHelper;
use Framework\Core\View;

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

        ConfigSettings::init();
        Redirect::init();

        self::$container = new Container();
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
        // Usamos el nuevo método resolveInstance para crear la instancia con dependencias
        $instance = self::$container->resolveInstance($class, $args);

        // Lee los params de el metodo init() y los inyecta automaticamente
        if (method_exists($instance, 'init')) {
            $method = new \ReflectionMethod($instance, 'init');
            $parameters = $method->getParameters();

            $dependencies = [];

            foreach ($parameters as $param) {
                $paramType = $param->getType();

                if (
                    $paramType instanceof \ReflectionNamedType &&
                    !$paramType->isBuiltin()
                ) {
                    $dependencyClass = $paramType->getName();
                    $dependencies[] = self::$container->resolveInstance($dependencyClass);
                } else {
                    throw new \RuntimeException("No se puede resolver el parámetro '{$param->getName()}' en {$class}::init()");
                }
            }

            $method->invokeArgs($instance, $dependencies);
        }

        return $instance;
    }
}
