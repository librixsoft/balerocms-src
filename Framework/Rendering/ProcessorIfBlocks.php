<?php

namespace Framework\Rendering;

class ProcessorIfBlocks
{
    public function process(string $content, array $flatParams): string
    {
        // Mientras haya bloques @if
        while (preg_match('/<!--\s*@if\b.*?<!--\s*@endif\s*-->/is', $content)) {

            // Encontrar el bloque más interno
            $content = preg_replace_callback(
            // Bloque sin otros @if dentro
                '/<!--\s*@if\s+([^\n]+?)\s*-->(?:(?:(?!<!--\s*@if).)*?)'
                . '(?:<!--\s*@else\s*-->(?:(?:(?!<!--\s*@if).)*?))?<!--\s*@endif\s*-->/is',
                function ($matches) use ($flatParams) {

                    $fullMatch = $matches[0];

                    // Extraer expresión, ifBlock y elseBlock manualmente
                    if (preg_match(
                        '/<!--\s*@if\s+(.*?)\s*-->(.*?)'
                        . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
                        $fullMatch,
                        $parts
                    )) {
                        $expression = $parts[1] ?? '';
                        $ifBlock = $parts[2] ?? '';
                        $elseBlock = $parts[3] ?? '';

                        // Procesar recursivamente los bloques internos
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

        if (stripos($expression, ' AND ') !== false) {
            $parts = preg_split('/\s+AND\s+/i', $expression);
            foreach ($parts as $part) {
                if (!$this->evaluateSimpleCondition(trim($part), $flatParams)) return false;
            }
            return true;
        }

        if (stripos($expression, ' OR ') !== false) {
            $parts = preg_split('/\s+OR\s+/i', $expression);
            foreach ($parts as $part) {
                if ($this->evaluateSimpleCondition(trim($part), $flatParams)) return true;
            }
            return false;
        }

        return $this->evaluateSimpleCondition($expression, $flatParams);
    }

    private function evaluateSimpleCondition(string $condition, array $flatParams): bool
    {
        $condition = trim($condition);

        if (preg_match('/^!(.+)$/', $condition, $matches)) {
            $key = trim($matches[1]);
            return empty($flatParams[$key] ?? null);
        }

        if (preg_match('/^([\w\.]+)\s*==\s*([\'"]?)([\w\.]+)\2$/', $condition, $matches)) {
            $var1 = $matches[1];
            $quote = $matches[2];
            $var2orVal = $matches[3];
            $val1 = $flatParams[$var1] ?? null;
            $val2 = ($quote !== '') ? $var2orVal : ($flatParams[$var2orVal] ?? null);
            return strcasecmp((string)$val1, (string)$val2) === 0;
        }

        return !empty($flatParams[$condition] ?? null);
    }
}
