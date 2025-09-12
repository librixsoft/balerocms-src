<?php

namespace Framework\Rendering\Conditions;

class ConditionFactory
{
    private static array $conditionClasses = [
        NotCondition::class,
        EqualsCondition::class,
        NotEqualsCondition::class,
        TruthyCondition::class
    ];

    public static function create(string $expression): ConditionInterface
    {
        $expression = trim($expression);

        foreach (self::$conditionClasses as $class) {
            if ($class::supports($expression)) {
                return $class::fromExpression($expression);
            }
        }

        throw new \InvalidArgumentException("Expresión no soportada: $expression");
    }

    public static function createOr(): OrCondition
    {
        return new OrCondition();
    }

    public static function createAnd(): AndCondition
    {
        return new AndCondition();
    }

    public static function parseExpression(string $expression): ConditionInterface
    {
        $orCondition = self::createOr();

        foreach (OrCondition::splitExpression($expression) as $orPart) {
            $andCondition = self::createAnd();
            foreach (AndCondition::splitExpression($orPart) as $and) {
                $andCondition->addCondition(self::create(trim($and)));
            }
            $orCondition->addCondition($andCondition);
        }

        return $orCondition;
    }
}
