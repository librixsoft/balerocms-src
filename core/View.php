<?php

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
            // otros parámetros...
        ];
    }

    public function render(string $templatePath, array $params = []): string
    {
        $templateFullPath = $this->baseDir . ltrim($templatePath, '/');

        if (!file_exists($templateFullPath)) {
            return "<b>Error:</b> plantilla no encontrada: $templateFullPath";
        }

        $content = file_get_contents($templateFullPath);
        if ($content === false) {
            return "<b>Error:</b> no se pudo leer la plantilla: $templateFullPath";
        }

        // Mezclamos parámetros por defecto con los que pasan
        $params = array_merge($this->getDefaultParams(), $params);

        // Reemplazar variables {var} antes de procesar condicionales
        foreach ($params as $key => $value) {
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{' . $key . '}', $safeValue, $content);
        }

        // Ahora procesa condicionales, etc. con el motor
        $content = $this->processTemplate($content, $params);

        return $content;
    }
}