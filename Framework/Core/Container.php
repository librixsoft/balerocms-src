<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Closure;
use ReflectionClass;

class Container
{
    /**
     * Array que asocia identificadores de clase o interfaz a closures que crean las instancias.
     *
     * @var array<string, Closure>
     */
    private array $bindings = [];

    /**
     * Registra una instancia singleton para un identificador dado.
     *
     * Cada vez que se solicite este identificador, se devolverá siempre la misma instancia.
     *
     * @param string $id Nombre de clase o interfaz.
     * @param object $instance Instancia única a devolver.
     * @return void
     */
    public function registerSingletonInstance(string $id, object $instance): void
    {
        $this->bindings[$id] = fn() => $instance;
    }

    /**
     * Registra un "binding" personalizado para un identificador.
     *
     * Un binding es una función que genera la instancia asociada a un identificador,
     * permitiendo controlar la creación de objetos (ejemplo: pasaje de parámetros, implementación concreta, factories).
     *
     * @param string $id Identificador (clase o interfaz).
     * @param Closure $factoryFunc Función que crea y retorna la instancia.
     * @return void
     */
    public function registerBindingFactory(string $id, Closure $factoryFunc): void
    {
        $this->bindings[$id] = $factoryFunc;
    }

    /**
     * Resuelve y devuelve una instancia de la clase o interfaz indicada.
     *
     * Primero busca si hay un binding o singleton registrado.
     * Si no, intenta instanciar automáticamente analizando el constructor y sus dependencias.
     *
     * Lanza excepción si no puede instanciar o resolver dependencias no tipadas o escalares.
     *
     * @param string $id Identificador de clase o interfaz.
     * @param array $args Argumentos opcionales para el constructor (actualmente no usados en resolución automática).
     * @return object|null Instancia resuelta.
     *
     * @throws \Exception Si no puede crear la instancia o resolver dependencias.
     */
    public function resolveInstance(string $id, array $args = []): ?object
    {

        if (str_starts_with($id, 'Framework\\Static\\')) {
            throw new \Exception("La clase estática {$id} no puede ser instanciada");
        }

        if (isset($this->bindings[$id])) {
            return ($this->bindings[$id])();
        }

        $reflector = new ReflectionClass($id);
        if (!$reflector->isInstantiable()) {
            throw new \Exception("La clase {$id} no es instanciable");
        }

        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            return new $id();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                throw new \Exception("No se puede resolver el parámetro {$param->getName()}");
            }

            $depClass = $type->getName();
            $dependencies[] = $this->resolveInstance($depClass);
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
