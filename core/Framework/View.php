<?php

namespace Framework;

use Framework\ConfigSettings;
use Framework\ErrorConsole;

use Framework\TemplateEngine;

class View extends TemplateEngine
{
    protected string $baseDir;
    protected ConfigSettings $configSettings;

    public function __construct(string $baseDir = LOCAL_DIR)
    {
        $this->baseDir = rtrim($baseDir, '/') . '/';
        $this->configSettings = new ConfigSettings();
        $this->configSettings->LoadSettings();
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

            foreach ($params as $key => $value) {
                $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $content = str_replace('{' . $key . '}', $safeValue, $content);
            }

            return $this->processTemplate($content, $params);
        } catch (\Throwable $e) {
            // Aquí usamos ErrorConsole para mostrar el error con consola negra
            ErrorConsole::handleException($e);
            return ''; // En caso de que ErrorConsole no salga (por si acaso)
        }
    }
}
