<?php

namespace Framework\Core;

class TemplateEngine
{
    protected function processTemplate(string $content, array $params): string
    {
        // @if var == 'value' ... @else ... @endif
        $content = preg_replace_callback(
            '/<!--\s*@if\s+(\w+)\s*==\s*[\'"]([^\'"]+)[\'"]\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($params) {
                $var      = $matches[1] ?? null;
                $expected = $matches[2] ?? null;
                $ifBlock  = $matches[3] ?? '';
                $elseBlock = $matches[4] ?? '';

                $actual = $params[$var] ?? null;
                return ($actual == $expected) ? $ifBlock : $elseBlock;
            },
            $content
        );

        // @if !var ... @else ... @endif
        $content = preg_replace_callback(
            '/<!--\s*@if\s+!(\w+)\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($params) {
                $var       = $matches[1] ?? null;
                $ifBlock   = $matches[2] ?? '';
                $elseBlock = $matches[3] ?? '';

                return empty($params[$var]) ? $ifBlock : $elseBlock;
            },
            $content
        );

        // @if var ... @else ... @endif
        $content = preg_replace_callback(
            '/<!--\s*@if\s+(\w+)\s*-->(.*?)'
            . '(?:<!--\s*@else\s*-->(.*?))?<!--\s*@endif\s*-->/is',
            function ($matches) use ($params) {
                $var       = $matches[1] ?? null;
                $ifBlock   = $matches[2] ?? '';
                $elseBlock = $matches[3] ?? '';

                return !empty($params[$var]) ? $ifBlock : $elseBlock;
            },
            $content
        );

        return $content;
    }
}
