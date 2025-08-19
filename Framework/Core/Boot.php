<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Http\RequestHelper;
use Framework\Core\View;
use Framework\Config\Context;

use Framework\Static\Constant;
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

        // Primero: instancia el contenedor
        self::$container = new Container();

        ErrorConsole::register();

        // Global services
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
            Constant::LOCAL_BASEPATH . '/',
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
     * LLama el metodo initControllerAndInject() de la clase padre
     * Ejecuta la DI adicionalmente al constructor con el metodo initControllerAndInject()
     * @param string $controllerClass
     */
    public static function loadController(string $controllerClass): void
    {
        try {
            // Instancia el controlador con DI en el constructor
            $instance = self::instantiateClass($controllerClass);

            // Evitar que tener que llamar __parent:: dentro de los modulos/controllers
            // Si el controlador tiene método initControllerAndInject -> inyectar dependencias y ejecutar
            if (method_exists($instance, 'initControllerAndInject')) {
                $method = new \ReflectionMethod($instance, 'initControllerAndInject');
                $parameters = $method->getParameters();
                $dependencies = [];

                foreach ($parameters as $param) {
                    $type = $param->getType();

                    if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                        throw new \RuntimeException(
                            "No se puede resolver el parámetro \${$param->getName()} en {$controllerClass}::initControllerAndInject()"
                        );
                    }

                    // Reutiliza la DI del contenedor
                    $dependencies[] = self::instantiateClass($type->getName());
                }

                $method->invokeArgs($instance, $dependencies);
            }

        } catch (\Throwable $e) {
            ErrorConsole::handleException(
                new \Exception("Error cargando controlador '$controllerClass': " . $e->getMessage(), 0, $e)
            );
            exit;
        }
    }

    /**
     * Instancia cualquier clase pasando argumentos opcionales.
     * No realiza lógica extra ni inyecciones automáticas.
     *
     * @param array $args Argumentos opcionales para el constructor.
     * @return object Instancia creada.
     */
    public static function instantiateClass(string $class): object
    {
        return self::$container->resolveInstance($class);
    }

}
