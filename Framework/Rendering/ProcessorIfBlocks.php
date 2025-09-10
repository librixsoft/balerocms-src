<?php

namespace Framework\Rendering;

class ProcessorIfBlocks
{
    public function process(string $content, array $flatParams): string
    {
        // Procesar recursivamente bloques @if
        while (preg_match('/<!--\s*@if\b.*?<!--\s*@endif\s*-->/is', $content)) {
            $content = preg_replace_callback(
            // Bloque más interno sin otros @if dentro
                '/<!--\s*@if\s+([^\n]+?)\s*-->(?:(?:(?!<!--\s*@if).)*?)'
                . '(?:<!--\s*@else\s*-->(?:(?:(?!<!--\s*@if).)*?))?<!--\s*@endif\s*-->/is',
                function ($matches) use ($flatParams) {
                    $fullMatch = $matches[0];

                    // Extraer expresión, ifBlock y elseBlock
                    if (preg_match(
                        '/<!--\s*@if\s+(.*?)\s*-->(.*?)'
                        . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
                        $fullMatch,
                        $parts
                    )) {
                        $expression = $parts[1] ?? '';
                        $ifBlock = $parts[2] ?? '';
                        $elseBlock = $parts[3] ?? '';

                        // Procesar bloques internos recursivamente
                        $ifBlockProcessed = $this->process($ifBlock, $flatParams);
                        $elseBlockProcessed = $this->process($elseBlock ?? '', $flatParams);

                        return $this->evaluateExpression($expression, $flatParams)
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

    private function evaluateExpression(string $expression, array $flatParams): bool
    {
        $expression = trim($expression);

        // Primero separar por OR (cada fragmento de OR se evalúa como AND)
        $orParts = preg_split('/\s+OR\s+/i', $expression);
        foreach ($orParts as $orPart) {
            $andParts = preg_split('/\s+AND\s+/i', $orPart);
            $allAndTrue = true;
            foreach ($andParts as $and) {
                if (!$this->evaluateSimpleCondition(trim($and), $flatParams)) {
                    $allAndTrue = false;
                    break;
                }
            }
            if ($allAndTrue) {
                return true; // Si alguna combinación AND dentro de OR es verdadera
            }
        }

        return false;
    }

    private function evaluateSimpleCondition(string $condition, array $flatParams): bool
    {
        $condition = trim($condition);

        // !var
        if (preg_match('/^!(.+)$/', $condition, $matches)) {
            $key = trim($matches[1]);
            return empty($flatParams[$key] ?? null);
        }

        // var == 'value' o var == var2
        if (preg_match('/^([\w\.]+)\s*==\s*([\'"]?)([\w\.]+)\2$/', $condition, $matches)) {
            $var1 = $matches[1];
            $quote = $matches[2];
            $var2orVal = $matches[3];

            $val1 = $flatParams[$var1] ?? null;
            $val2 = ($quote !== '') ? $var2orVal : ($flatParams[$var2orVal] ?? null);

            return strcasecmp((string)$val1, (string)$val2) === 0;
        }

        // var simple (truthy)
        return !empty($flatParams[$condition] ?? null);
    }
}
