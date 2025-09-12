<?php

namespace Framework\Rendering\Conditions;

interface CompositeConditionInterface extends ConditionInterface
{
    public function addCondition(ConditionInterface $condition): void;
}
