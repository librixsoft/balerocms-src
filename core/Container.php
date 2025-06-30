<?php

class Container
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function singleton(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function resolve(string $class, array $args = []): object
    {
        // Si ya existe la instancia, la devuelvo directamente (singleton automático)
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        // Si hay un binding definido, ejecuto la fábrica y guardo la instancia
        if (isset($this->bindings[$class])) {
            $instance = $this->bindings[$class]($this);
            $this->instances[$class] = $instance;
            return $instance;
        }

        // Verifico que la clase exista y sea instanciable
        if (!class_exists($class)) {
            throw new Exception("Class '$class' not found");
        }

        $reflector = new ReflectionClass($class);
        if (!$reflector->isInstantiable()) {
            throw new Exception("Class '$class' is not instantiable.");
        }

        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            $instance = new $class;
            $this->instances[$class] = $instance;
            return $instance;
        }

        // Resuelvo dependencias recursivamente
        $params = $constructor->getParameters();
        $dependencies = [];

        foreach ($params as $i => $param) {
            if (array_key_exists($i, $args)) {
                $dependencies[] = $args[$i];
                continue;
            }

            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new Exception("Cannot resolve parameter \${$param->getName()} for class $class");
            }

            $dependencies[] = $this->resolve($type->getName());
        }

        $instance = $reflector->newInstanceArgs($dependencies);
        // GUARDO automáticamente la instancia (singleton automático)
        $this->instances[$class] = $instance;

        return $instance;
    }
}
