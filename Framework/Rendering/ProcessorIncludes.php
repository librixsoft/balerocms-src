<?php

namespace Framework\Rendering;

class ProcessorIncludes
{
    private string $baseDir = '';

    public function setBaseDir(string $baseDir): void
    {
        $this->baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function process(string $content, array $params): string
    {
        if (!$this->baseDir) {
            return '<!-- INCLUDE ERROR: baseDir no definido -->';
        }

        return preg_replace_callback(
            '/<!--\s*@include\s+"([^"]+)"\s*-->/',
            function ($matches) {
                $includePath = $matches[1];
                $fullPath = realpath($this->baseDir . $includePath);

                if (!$fullPath || !file_exists($fullPath)) {
                    return "<!-- INCLUDE ERROR: Archivo no encontrado $includePath -->";
                }

                return file_get_contents($fullPath);
            },
            $content
        );
    }
}
