<?php

namespace Framework\Rendering\Conditions;

interface ConditionInterface
{
    /**
     * Evalúa la condición usando los parámetros planos.
     */
    public function evaluate(array $flatParams): bool;

    /**
     * Indica si esta condición puede manejar la expresión dada.
     */
    public static function supports(string $expression): bool;
}
