<?php

namespace Framework\Rendering\Conditions;

class ConditionFactory
{
    private array $conditionClasses;
    private OrCondition $orPrototype;
    private AndCondition $andPrototype;

    public function __construct(
        OrCondition $orPrototype,
        AndCondition $andPrototype,
        array $conditionClasses = [
            NotCondition::class,
            EqualsCondition::class,
            NotEqualsCondition::class,
            TruthyCondition::class
        ]
    ) {
        $this->orPrototype = $orPrototype;
        $this->andPrototype = $andPrototype;
        $this->conditionClasses = $conditionClasses;
    }

    public function create(string $expression): ConditionInterface
    {
        $expression = trim($expression);

        foreach ($this->conditionClasses as $class) {
            if ($class::supports($expression)) {
                return $class::fromExpression($expression);
            }
        }

        throw new \InvalidArgumentException("Expresión no soportada: $expression");
    }

    public function createOr(): OrCondition
    {
        return clone $this->orPrototype;
    }

    public function createAnd(): AndCondition
    {
        return clone $this->andPrototype;
    }

    public function parseExpression(string $expression): ConditionInterface
    {
        $orCondition = $this->createOr();

        foreach (OrCondition::splitExpression($expression) as $orPart) {
            $andCondition = $this->createAnd();
            foreach (AndCondition::splitExpression($orPart) as $and) {
                $andCondition->addCondition($this->create(trim($and)));
            }
            $orCondition->addCondition($andCondition);
        }

        return $orCondition;
    }
}
