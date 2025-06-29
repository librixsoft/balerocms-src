<?php

class Container
{
    protected array $bindings = [];

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function resolve(string $class, array $args = []): object
    {
        try {
            if (isset($this->bindings[$class])) {
                return $this->bindings[$class]($this);
            }

            if (!class_exists($class)) {
                throw new Exception("Class '$class' not found");
            }

            $reflector = new ReflectionClass($class);

            if (!$reflector->isInstantiable()) {
                throw new Exception("Class '$class' is not instantiable.");
            }

            $constructor = $reflector->getConstructor();
            if (!$constructor) {
                return new $class;
            }

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

            return $reflector->newInstanceArgs($dependencies);
        } catch (Throwable $e) {
            ErrorConsole::handleException(new Exception("Error resolving '$class': " . $e->getMessage(), 0, $e));
            exit;
        }
    }
}
