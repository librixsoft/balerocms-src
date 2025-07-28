<?php

namespace Framework\Core;

use Framework\I18n\LangManager;

class TemplateEngine
{
    protected function processTemplate(string $content, array $params): string
    {
        // Aplanar los parámetros con claves anidadas: 'errors.username' => 'Mensaje'
        $flatParams = $this->flattenParams($params);

        // Primero procesar los foreach para evitar conflictos con variables internas
        $content = $this->processForeach($content, $params);

        // Reemplazo directo de variables {key}
        foreach ($flatParams as $key => $value) {
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{' . $key . '}', $safeValue, $content);
        }

        // Procesar condicionales @if
        $content = $this->processIfBlocks($content, $flatParams);

        // Reemplazo de claves de idioma: {lang.welcome}
        $content = preg_replace_callback(
            '/\{lang\.([a-zA-Z0-9_]+)\}/',
            function ($matches) {
                return LangManager::get($matches[1], $matches[1]); // fallback: clave original
            },
            $content
        );

        // Reemplazo de {installer.welcome}, {auth.login}, etc.
        $content = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/',
            function ($matches) use ($flatParams) {
                $fullKey = $matches[1] . '.' . $matches[2];

                if (array_key_exists($fullKey, $flatParams)) {
                    return $flatParams[$fullKey];
                }

                return LangManager::get($fullKey, $matches[0]);
            },
            $content
        );

        return $content;
    }

    /**
     * Procesa bloques @foreach var as item ... @endforeach
     */
    private function processForeach(string $content, array $params): string
    {
        return preg_replace_callback(
            '/<!--\s*@foreach\s+(\w+)\s+as\s+(\w+)\s*-->(.*?)<!--\s*@endforeach\s*-->/is',
            function ($matches) use ($params) {
                $arrayKey = $matches[1];    // ej: 'virtual_pages' o 'themes'
                $itemKey  = $matches[2];    // ej: 'page' o 't'
                $block    = $matches[3];    // contenido dentro del foreach

                if (!isset($params[$arrayKey]) || !is_array($params[$arrayKey])) {
                    return ''; // Si no existe o no es array, no imprime nada
                }

                $result = '';
                foreach ($params[$arrayKey] as $item) {
                    $flatItem = $this->flattenParams([$itemKey => $item]);

                    $blockCopy = $block;
                    foreach ($flatItem as $k => $v) {
                        $safeValue = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $blockCopy = str_replace('{' . $k . '}', $safeValue, $blockCopy);
                    }

                    // FIX corregido con clave completa
                    $blockCopy = preg_replace_callback(
                        '/<!--\s*@if\s+defaultTheme\s*==\s*t\.value\s*-->/i',
                        function() use ($flatItem, $itemKey) {
                            $val = $flatItem[$itemKey . '.value'] ?? '';
                            $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            return "<!-- @if defaultTheme == '{$val}' -->";
                        },
                        $blockCopy
                    );

                    $result .= $blockCopy;
                }


                return $result;
            },
            $content
        );
    }

    private function processIfBlocks(string $content, array $flatParams): string
    {
        // @if var == var2 o var == 'string' ... @else ... @endif
        $content = preg_replace_callback(
            '/<!--\s*@if\s+([\w\.]+)\s*==\s*([\'"]?)([\w\.]+)\2\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($flatParams) {
                $var1 = $matches[1] ?? null;        // Ej: theme
                $quote = $matches[2] ?? '';         // Comilla simple/doble o vacío
                $var2orVal = $matches[3] ?? null;  // Ej: t.value o 'dark'
                $ifBlock = $matches[4] ?? '';
                $elseBlock = $matches[5] ?? '';

                $val1 = $flatParams[$var1] ?? null;

                if ($quote !== '') {
                    // var2orVal es un string literal (entre comillas)
                    $val2 = $var2orVal;
                } else {
                    // var2orVal es una variable (sin comillas), buscar su valor
                    $val2 = $flatParams[$var2orVal] ?? null;
                }

                // Comparación insensible a mayúsculas/minúsculas
                return (strcasecmp((string)$val1, (string)$val2) === 0) ? $ifBlock : $elseBlock;
            },
            $content
        );

        // @if !var ... @else ... @endif (soporte para !errors.username)
        $content = preg_replace_callback(
            '/<!--\s*@if\s+!([\w\.]+)\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($flatParams) {
                $key       = $matches[1] ?? null;
                $ifBlock   = $matches[2] ?? '';
                $elseBlock = $matches[3] ?? '';

                $value = $flatParams[$key] ?? null;

                return (empty($value)) ? $ifBlock : $elseBlock;
            },
            $content
        );

        // @if var ... @else ... @endif (soporte para errors.username)
        $content = preg_replace_callback(
            '/<!--\s*@if\s+([\w\.]+)\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($flatParams) {
                $key       = $matches[1] ?? null;
                $ifBlock   = $matches[2] ?? '';
                $elseBlock = $matches[3] ?? '';

                $value = $flatParams[$key] ?? null;

                return (!empty($value)) ? $ifBlock : $elseBlock;
            },
            $content
        );

        return $content;
    }


    /**
     * Aplana un array multidimensional para claves como 'errors.username'
     */
    private function flattenParams(array $params, string $prefix = ''): array
    {
        $result = [];

        foreach ($params as $key => $value) {
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += $this->flattenParams($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }
}
