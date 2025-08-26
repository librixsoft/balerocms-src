<?php

namespace Framework\Rendering;

class ProcessorIncludes
{

    public function process(string $content, string $baseDir): string
    {
        if (!$baseDir) {
            return '<!-- INCLUDE ERROR: baseDir no definido -->';
        }

        return preg_replace_callback(
            '/<!--\s*@include\s+"([^"]+)"\s*-->/',
            function ($matches) {
                $includePath = $matches[1];
                $fullPath = realpath(baseDir . $includePath);

                if (!$fullPath || !file_exists($fullPath)) {
                    return "<!-- INCLUDE ERROR: Archivo no encontrado $includePath -->";
                }

                return file_get_contents($fullPath);
            },
            $content
        );
    }
}
