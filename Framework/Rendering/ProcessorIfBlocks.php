<?php

namespace Framework\Rendering;

class ProcessorIfBlocks
{
    public function process(string $content, array $flatParams): string
    {
        // Soporte para condiciones concatenadas con AND/OR
        $content = preg_replace_callback(
            '/<!--\s*@if\s+(.*?)\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($flatParams) {
                $expression = $matches[1] ?? '';
                $ifBlock = $matches[2] ?? '';
                $elseBlock = $matches[3] ?? '';

                if ($this->evaluateExpression($expression, $flatParams)) {
                    return $ifBlock;
                }
                return $elseBlock;
            },
            $content
        );

        return $content;
    }

    private function evaluateExpression(string $expression, array $flatParams): bool
    {
        // Dividir por operadores AND / OR (respetando mayúsculas)
        $expression = trim($expression);

        // Primero manejar AND
        if (stripos($expression, ' AND ') !== false) {
            $parts = preg_split('/\s+AND\s+/i', $expression);
            foreach ($parts as $part) {
                if (!$this->evaluateSimpleCondition(trim($part), $flatParams)) {
                    return false;
                }
            }
            return true;
        }

        // Luego manejar OR
        if (stripos($expression, ' OR ') !== false) {
            $parts = preg_split('/\s+OR\s+/i', $expression);
            foreach ($parts as $part) {
                if ($this->evaluateSimpleCondition(trim($part), $flatParams)) {
                    return true;
                }
            }
            return false;
        }

        // Condición simple
        return $this->evaluateSimpleCondition($expression, $flatParams);
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
