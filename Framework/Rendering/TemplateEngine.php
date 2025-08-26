<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Rendering;

use Framework\I18n\LangManager;

class TemplateEngine
{

    private string $baseDir;

    private ProcessorForEach $processorForEach;
    private ProcessorFlattenParams $processFlattenParams;
    private ProcessorIfBlocks $processorIfBlocks;

    public function __construct(
        ProcessorForEach $processorForEach,
        ProcessorFlattenParams $processFlattenParams,
        ProcessorIfBlocks $processorIfBlocks
    ) {
        $this->processorForEach = $processorForEach;
        $this->processFlattenParams = $processFlattenParams;
        $this->processorIfBlocks = $processorIfBlocks;
    }

    public function processTemplate(string $content, array $params): string
    {

        $content = $this->processIncludes($content, $params);

        // Aplanar los parámetros con claves anidadas: 'errors.username' => 'Mensaje'
        $flatParams = $this->processFlattenParams->process($params);

        // Primero procesar los foreach para evitar conflictos con variables internas
        $content = $this->processorForEach->process($content, $params);

        // Reemplazo directo de variables {key}
        foreach ($flatParams as $key => $value) {
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{' . $key . '}', $safeValue, $content);
        }

        // Procesar condicionales @if
        $content = $this->processorIfBlocks->process($content, $flatParams);

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
     * Procesa inclusiones tipo <!-- @include "ruta/al/archivo.html" -->
     */
    public function processIncludes(string $content, array $params): string
    {
        return preg_replace_callback(
            '/<!--\s*@include\s+"([^"]+)"\s*-->/',
            function ($matches) use ($params) {
                $includePath = $matches[1];

                // Aquí tienes que construir la ruta completa, usando baseDir o similar
                // Pero TemplateEngine no conoce $baseDir de View, entonces una solución es:
                // Que la clase TemplateEngine tenga una propiedad $baseDir, que le pases desde View

                if (!isset($this->baseDir)) {
                    // fallback: sin baseDir no puede incluir
                    return "<!-- INCLUDE ERROR: baseDir no definido -->";
                }

                $fullPath = realpath($this->baseDir . $includePath);
                if (!$fullPath || !file_exists($fullPath)) {
                    return "<!-- INCLUDE ERROR: Archivo no encontrado $includePath -->";
                }

                $includedContent = file_get_contents($fullPath);
                // Procesamos recursivamente includes y otras cosas en el contenido incluido
                return $this->processTemplate($includedContent, $params);
            },
            $content
        );
    }


    public function setBaseDir(string $path): void
    {
        $this->baseDir = rtrim($path, '/') . '/';
    }

}
