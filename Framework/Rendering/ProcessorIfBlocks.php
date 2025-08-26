<?php

/**
 * @author Anibal Gomez <balerocms@gmail.com>
 * @copyright Copyright (c) 2025 Anibal Gomez
 * @license GNU General Public License
 */

namespace Framework\Rendering;

class ProcessorIfBlocks
{

    public function process(string $content, array $flatParams): string
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

}