<?php

// core/View.php
class View
{
    protected string $layoutPath;
    public string $content = '';

    protected ConfigSettings $configSettings;

    public function __construct()
    {
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
            'virtual_pages' => '',
            'langs' => '',
        ];
    }

    function render(string $templatePath, array $params): void
    {
        $templateFullPath = LOCAL_DIR . $templatePath;
        $content = file_get_contents($templateFullPath);

        foreach ($params as $key => $value) {
            $content = str_replace('{' . $key . '}', htmlspecialchars($value), $content);
        }

        echo $content;
    }

    /**
     * @deprecated eliminar este metodo solo por renderLayout
     * @param string $templatePath
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function renderFragment(string $templatePath, array $params = []): string
    {
        $loader = new ThemeLoader($templatePath);
        return $loader->renderPage($params);
    }
}
