<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Config;

use Framework\Core\Container;
use Framework\Core\Redirect;

use Framework\Core\ErrorConsole;

/**
 * Clase Context
 *
 * Proporciona un acceso global al contenedor de dependencias cuando la inyección
 * directa no es posible, como en métodos estáticos o clases no gestionadas por el contenedor.
 *
 * También permite registrar servicios como singletons para uso global, como helpers o utilidades.
 */
class Context
{
    /**
     * Contenedor de dependencias principal de la aplicación.
     *
     * @var Container
     */
    protected static Container $container;

    /**
     * Lista de servicios que se registrarán automáticamente como singleton.
     *
     * Formato: ['alias' => Clase::class]
     * Estos servicios estarán disponibles globalmente mediante Context::get().
     *
     * @var array<string, class-string>
     */
    protected static array $services = [
        // Como en PHP no hay clases estaticas, instanciar clases con solo metodos estaticos, services o clases
        // que se obtengan de manera global sin inyectar
        // sino deseas instanciar la clase colocala en Static para que el contenedor omita la instancia simulando una clase estatica
        // Aqui se define alguna clase que no se inyecte en ningun lado pero que necesite ser instancia para estar disponible
        // Solo si una clase no tiene dependencias debe crearse en FRamework/Static
        //'redirect' => Redirect::class,
        //'errorConsole' => ErrorConsole::class,
    ];

    /**
     * Inicializa el Context con el contenedor y registra los servicios definidos como singletons.
     *
     * Debe llamarse durante el bootstrapping (por ejemplo, en Boot.php)
     *
     * @param Container $container Contenedor de la aplicación
     */
    public static function init(Container $container): void
    {
        self::$container = $container;

        foreach (self::$services as $alias => $class) {
            // Instancia el servicio y lo registra como singleton
            $instance = $container->resolveInstance($class);
            $container->registerSingletonInstance($class, $instance);
        }
    }

    /**
     * Obtiene un servicio desde el contenedor por su alias o por su clase.
     *
     * Ejemplos:
     * - Context::get('redirect')       ← por alias
     * - Context::get(Redirect::class)  ← por clase
     *
     * Si se pasa un alias, se resuelve con la clase correspondiente en $services.
     * Si se pasa directamente una clase, se resuelve desde el contenedor.
     *
     * @param string $aliasOrClass Alias registrado o nombre completo de la clase
     * @return object Instancia del servicio solicitado
     */
    public static function get(string $aliasOrClass): object
    {
        $class = self::$services[$aliasOrClass] ?? $aliasOrClass;
        return self::$container->resolveInstance($class);
    }
}
