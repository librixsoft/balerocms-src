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

    public function __construct(
        ProcessorForEach $processorForEach,
        ProcessorFlattenParams $processFlattenParams
    ) {
        $this->processorForEach = $processorForEach;
        $this->processFlattenParams = $processFlattenParams;
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


    public function processIfBlocks(string $content, array $flatParams): string
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
