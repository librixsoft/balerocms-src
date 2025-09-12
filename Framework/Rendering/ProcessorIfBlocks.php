<?php

namespace Framework\Rendering;

use Framework\Rendering\Conditions\ConditionFactory;

class ProcessorIfBlocks
{
    public function process(string $content, array $flatParams): string
    {
        // Procesar recursivamente bloques @if
        while (preg_match('/<!--\s*@if\b.*?<!--\s*@endif\s*-->/is', $content)) {
            $content = preg_replace_callback(
                '/<!--\s*@if\s+([^\n]+?)\s*-->(?:(?:(?!<!--\s*@if).)*?)'
                . '(?:<!--\s*@else\s*-->(?:(?:(?!<!--\s*@if).)*?))?<!--\s*@endif\s*-->/is',
                function ($matches) use ($flatParams) {
                    $fullMatch = $matches[0];

                    if (preg_match(
                        '/<!--\s*@if\s+(.*?)\s*-->(.*?)'
                        . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
                        $fullMatch,
                        $parts
                    )) {
                        $expression = $parts[1] ?? '';
                        $ifBlock = $parts[2] ?? '';
                        $elseBlock = $parts[3] ?? '';

                        $ifBlockProcessed = $this->process($ifBlock, $flatParams);
                        $elseBlockProcessed = $this->process($elseBlock ?? '', $flatParams);

                        $condition = ConditionFactory::parseExpression($expression);

                        return $condition->evaluate($flatParams)
                            ? $ifBlockProcessed
                            : $elseBlockProcessed;
                    }

                    return $fullMatch;
                },
                $content
            );
        }

        return $content;
    }
}
