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

    private ProcessorIncludes $processorIncludes;
    private ProcessorFlattenParams $processFlattenParams;
    private ProcessorForEach $processorForEach;
    private ProcessorIfBlocks $processorIfBlocks;

    public function __construct(
        ProcessorIncludes $processorIncludes,
        ProcessorFlattenParams $processFlattenParams,
        ProcessorForEach $processorForEach,
        ProcessorIfBlocks $processorIfBlocks) {
        $this->processorIncludes = $processorIncludes;
        $this->processFlattenParams = $processFlattenParams;
        $this->processorForEach = $processorForEach;
        $this->processorIfBlocks = $processorIfBlocks;
    }

    public function processTemplate(string $content, array $params): string
    {

        $this->processorIncludes->setBaseDir($this->getBaseDir());
        $content = $this->processorIncludes->process($content, $params);

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

    public function setBaseDir(string $path): void
    {
        $this->baseDir = rtrim($path, '/') . '/';
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

}
