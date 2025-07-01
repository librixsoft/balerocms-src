<?php

namespace Framework\Core;

use Closure;
use ReflectionClass;

class Container
{
    private array $bindings = [];

    public function singleton(string $id, object $instance): void
    {
        $this->bindings[$id] = fn() => $instance;
    }

    public function bind(string $id, Closure $concrete): void
    {
        $this->bindings[$id] = $concrete;
    }

    public function resolve(string $id, array $args = []): object
    {
        if (isset($this->bindings[$id])) {
            return ($this->bindings[$id])();
        }

        $reflector = new ReflectionClass($id);
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$id} is not instantiable");
        }

        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            return new $id();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if (!$type || $type->isBuiltin()) {
                throw new \Exception("Cannot resolve parameter {$param->getName()}");
            }

            $depClass = $type->getName();
            $dependencies[] = $this->resolve($depClass);
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
