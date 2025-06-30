<?php


class ContainerConfigurator
{
    public static function configure(Container $container): void
    {
        // Instancias singleton
        $container->instance(ConfigSettings::class, new ConfigSettings());
        $container->instance(Security::class, new Security());

        // Instancia con dependencia inyectada
        $container->instance(RequestHelper::class, new RequestHelper($container->make(Security::class)));

        $container->instance(Language::class, new Language());
        $container->instance(AdminElements::class, new AdminElements());

        // Si quieres bindear con factory para casos más complejos
        // $container->bind(SomeClass::class, function($c) {
        //    return new SomeClass($c->make(Dependency::class));
        // });
    }
}
