<?php

class View
{
    public string $content = '';
    public string $layoutPath;

    public function __construct(string $layout = "/themes/tundra/main.html")
    {
        $this->layoutPath = LOCAL_DIR . $layout;
    }

    protected function renderPartial(string $templatePath, array $params = []): string
    {
        $loader = new ThemeLoader($templatePath);
        return $loader->renderPage($params);
    }

    public function render(array $params): void
    {
        $params['content'] = $params['content'] ?? $this->content;

        $layout = new ThemeLoader($this->layoutPath);
        echo $layout->renderPage($params);
    }
}
