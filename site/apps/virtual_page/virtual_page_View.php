<?php

class virtual_page_View extends View {
    public string $theme;
    public array $rows = [];
    public string $content = "";
    public array $virtual_pages = [];
    public virtual_page_Model $objModel;
    public string $lang;
    public string $page;
    public string $active = "class=\"active\"";
    public string $css_active = "";

    public function __construct() {
        parent::__construct("/themes/tundra/main.html");

        $this->objModel = new virtual_page_Model();
        $this->theme = $this->objModel->theme();
        $this->configSettings = new ConfigSettings();
        $this->configSettings->LoadSettings();
    }

    public function virtual_pages_menu(): string {
        $html = "<li $this->active><a href=\"./\">" . _HOME . "</a></li>";

        $this->objModel->lang = $this->lang;
        $pages = $this->objModel->get_virtual_pages();

        if (empty($pages)) {
            return "<li><a href=\"#\">" . _NO_VIRTUAL_PAGES . "</a></li>";
        }

        foreach ($pages as $page) {
            $title = $page['virtual_title'] ?? '';
            $id = $page['id'] ?? 0;

            if ($title !== '') {
                if ($this->active === $title) {
                    $this->css_active = 'class="active"';
                }

                $url = ($this->lang === "main" || empty($this->lang))
                    ? "./virtual_page/main/id-$id"
                    : "./virtual_page/{$this->lang}/id-$id";

                $html .= "<li $this->css_active><a href=\"$url\">$title</a></li>";
                $this->css_active = "";
            }
        }

        return $html;
    }

    public function print_virtual_page(array $db_array): string {
        $html = "";

        foreach ($db_array as $page) {
            $this->page = $page['virtual_title'];
            $this->active = $this->page;

            $html .= "## " . $page['virtual_title'] . "\n";
            $html .= "*" . $page['date'] . "*\n";
            $html .= $page['virtual_content'] . "\n";
        }

        return $html;
    }

    public function Render(): void {
        $lang = new Language();
        $lang->app = "virtual_page";
        $lang->defaultLang = $this->objModel->getLang();

        $data = [
            'title' => $this->configSettings->getTitle(),
            'url' => $this->configSettings->getUrl(),
            'keywords' => $this->configSettings->getKeywords(),
            'description' => $this->configSettings->getDescription(),
            'content' => $this->content,
            'virtual_pages' => $this->virtual_pages_menu(),
            'basepath' => $this->configSettings->getBasepath(),
            'page' => $this->page,
            'langs' => ''
        ];

        $theme = new ThemeLoader(LOCAL_DIR . "/themes/{$this->theme}/main.html");
        echo $theme->renderPage($data);
    }

    public function print_all_pages(): void {
        $this->content .= "<h3>" . _INDEXOF . "</h3>";

        foreach ($this->rows as $row) {
            try {
                $this->content .= $row['virtual_title'];
            } catch (Exception $e) {
                // Ignorar errores individuales
            }
        }
    }
} // fin clase
