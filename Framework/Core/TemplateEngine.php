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
                $arrayKey = $matches[1];    // ej: 'virtual_pages'
                $itemKey  = $matches[2];    // ej: 'page'
                $block    = $matches[3];    // contenido dentro del foreach

                if (!isset($params[$arrayKey]) || !is_array($params[$arrayKey])) {
                    return ''; // Si no existe o no es array, no imprime nada
                }

                $result = '';
                foreach ($params[$arrayKey] as $item) {
                    // Aplana el array del ítem con el prefijo del itemKey
                    $flatItem = $this->flattenParams([$itemKey => $item]);

                    // Reemplazar {page.virtual_title}, etc.
                    $blockCopy = $block;
                    foreach ($flatItem as $k => $v) {
                        $safeValue = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $blockCopy = str_replace('{' . $k . '}', $safeValue, $blockCopy);
                    }
                    $result .= $blockCopy;
                }

                return $result;
            },
            $content
        );
    }

    /**
     * Procesa los bloques condicionales @if, @else, @endif
     */
    private function processIfBlocks(string $content, array $flatParams): string
    {
        // @if var == 'value' ... @else ... @endif
        $content = preg_replace_callback(
            '/<!--\s*@if\s+([\w\.]+)\s*==\s*[\'"]([^\'"]+)[\'"]\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($flatParams) {
                $var       = $matches[1] ?? null;
                $expected  = $matches[2] ?? null;
                $ifBlock   = $matches[3] ?? '';
                $elseBlock = $matches[4] ?? '';

                $actual = $flatParams[$var] ?? null;
                return ($actual == $expected) ? $ifBlock : $elseBlock;
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
