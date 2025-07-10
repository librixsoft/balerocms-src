<?php

namespace Framework\Core;

use Framework\I18n\LangManager;

class TemplateEngine
{
    protected function processTemplate(string $content, array $params): string
    {
        // Aplanar los parámetros con claves anidadas: 'errors.username' => 'Mensaje'
        $flatParams = $this->flattenParams($params);

        // Reemplazo directo de variables {key}
        foreach ($flatParams as $key => $value) {
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{' . $key . '}', $safeValue, $content);
        }

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
