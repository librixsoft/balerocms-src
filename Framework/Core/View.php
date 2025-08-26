<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Core\ConfigSettings;
use Framework\Static\Constant;
use Framework\Rendering\TemplateEngine;

class View extends TemplateEngine
{

    protected string $baseDir;
    protected ConfigSettings $configSettings;

    public function __construct(ConfigSettings $config)
    {
        $this->baseDir = $this->normalizePath(Constant::VIEWS_PATH);
        $this->configSettings = $config;
        $this->configSettings->LoadSettings();
        $this->setBaseDir($this->baseDir);
    }

    public function getDefaultParams(): array
    {
        return [
            'title' => $this->configSettings->getTitle(),
            'url' => $this->configSettings->getUrl(),
            'page' => defined('_PAGE') ? _PAGE : '',
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'basepath' => $this->configSettings->getBasepath(),
        ];
    }

    public function render(string $templatePath, array $params = []): string
    {
        try {
            $templateFullPath = $this->baseDir . ltrim($templatePath, '/');

            if (!file_exists($templateFullPath)) {
                throw new \RuntimeException("Plantilla no encontrada: $templateFullPath");
            }

            $content = file_get_contents($templateFullPath);
            if ($content === false) {
                throw new \RuntimeException("No se pudo leer la plantilla: $templateFullPath");
            }

            $params = array_merge($this->getDefaultParams(), $params);

            return $this->processTemplate($content, $params);
        } catch (\Throwable $e) {
            ErrorConsole::handleException($e);
            return '';
        }
    }

    /**
     * Normaliza un path para que termine con exactamente una sola barra.
     */
    private function normalizePath(string $path): string
    {
        return rtrim($path, '/') . '/';
    }

    public function setBaseDir(string $path): void
    {
        $this->baseDir = $path;
    }

}
