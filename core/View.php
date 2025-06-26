<?php

// core/View.php
class View
{
    protected string $layoutPath;
    public string $content = '';

    protected ConfigSettings $configSettings;

    public function __construct(string $layoutPath)
    {
        $this->layoutPath = LOCAL_DIR . $layoutPath;
        $this->configSettings = new ConfigSettings();
        $this->configSettings->LoadSettings();
    }

    protected function getDefaultParams(): array
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

    public function renderLayout(array $extraParams = []): void
    {
        $params = array_merge($this->getDefaultParams(), $extraParams);
        $params['content'] = $params['content'] ?? $this->content;

        $layout = new ThemeLoader($this->layoutPath);
        echo $layout->renderPage($params);
    }

    protected function renderFragment(string $templatePath, array $params = []): string
    {
        $loader = new ThemeLoader($templatePath);
        return $loader->renderPage($params);
    }
}
