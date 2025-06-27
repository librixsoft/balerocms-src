<?php

class ConfigSettings
{
    private XMLHandler $xml;
    private string $cfgFile;

    // Configuracion DB
    private string $dbhost;
    private string $dbuser;
    private string $dbpass;
    private string $dbname;


    // Admin
    private string $user;
    private string $pass;
    private string $email;
    private string $firstname;
    private string $lastname;

    // System
    private String $installed;

    // Site
    private string $title;
    private string $description;
    private string $url;
    private string $keywords;
    private string $basepath;

    // Opciones
    private string $multilang;
    private string $editor;

    public function __construct()
    {
        $this->cfgFile = LOCAL_DIR . "/site/etc/balero.config.xml";
        $this->xml = new XMLHandler($this->cfgFile); // ✅ primero inicializar
        $this->LoadSettings(); // ✅ luego llamar
    }

    public function LoadSettings(): void
    {
        try {
            $xml = $this->xml;

            $this->dbhost     = $xml->Child("database", "dbhost");
            $this->dbuser     = $xml->Child("database", "dbuser");
            $this->dbpass     = $xml->Child("database", "dbpass");
            $this->dbname     = $xml->Child("database", "dbname");
            $this->user       = $xml->Child("admin", "username");
            $this->pass       = $xml->Child("admin", "passwd");
            $this->email      = $xml->Child("admin", "email");
            $this->firstname  = $xml->Child("admin", "firstname");
            $this->lastname   = $xml->Child("admin", "lastname");
            $this->installed      = $xml->Child("system", "installed");
            $this->title      = $xml->Child("site", "title");
            $this->url        = $xml->Child("site", "url");
            $this->description= $xml->Child("site", "description");
            $this->keywords   = $xml->Child("site", "keywords");
            $this->basepath   = $xml->Child("site", "basepath");
            $this->multilang  = $xml->Child("site", "multilang");
            $this->editor     = $xml->Child("site", "editor");

        } catch (Exception $e) {
            $title = "ERROR IN CLASS: " . get_class($this);
            $test = new MsgBox($title, $e->getMessage());
            echo $test->Show();
        }
    }

    // Getters y Setters con escritura en XML

    public function getDbhost(): string { return $this->dbhost; }
    public function setDbhost(string $value): void {
        $this->dbhost = $value;
        $this->xml->editChild("/config/database/dbhost", $value);
    }

    public function getDbuser(): string { return $this->dbuser; }
    public function setDbuser(string $value): void {
        $this->dbuser = $value;
        $this->xml->editChild("/config/database/dbuser", $value);
    }

    public function getDbpass(): string { return $this->dbpass; }
    public function setDbpass(string $value): void {
        $this->dbpass = $value;
        $this->xml->editChild("/config/database/dbpass", $value);
    }

    public function getDbname(): string { return $this->dbname; }
    public function setDbname(string $value): void {
        $this->dbname = $value;
        $this->xml->editChild("/config/database/dbname", $value);
    }

    public function getUser(): string { return $this->user; }
    public function setUser(string $value): void {
        $this->user = $value;
        $this->xml->editChild("/config/admin/username", $value);
    }

    public function getPass(): string { return $this->pass; }
    public function setPass(string $value): void {
        $this->pass = $value;
        $this->xml->editChild("/config/admin/passwd", $value);
    }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $value): void {
        $this->email = $value;
        $this->xml->editChild("/config/admin/email", $value);
    }

    public function getInstalled(): string { return $this->installed; }
    public function setInstalled(string $value): void {
        $this->installed = $value;
        $this->xml->editChild("/config/system/installed", $value);
    }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $value): void {
        $this->title = $value;
        $this->xml->editChild("/config/site/title", $value);
    }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $value): void {
        $this->url = $value;
        $this->xml->editChild("/config/site/url", $value);
    }

    public function getBasepath(): string { return $this->basepath; }
    public function setBasepath(string $value): void {
        $this->basepath = $value;
        $this->xml->editChild("/config/site/basepath", $value);
    }

    public function getEditor(): string { return $this->editor; }
    public function setEditor(string $value): void {
        $this->editor = $value;
        $this->xml->editChild("/config/site/editor", $value);
    }

    public function getMultilang(): string { return $this->multilang; }
    public function setMultilang(string $value): void {
        $this->multilang = $value;
        $this->xml->editChild("/config/site/multilang", $value);
    }

    public function getFullBasepath(): string
    {
        $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 's' : '';
        $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        $uri = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
        $segments = explode('?', $uri, 2);
        return str_replace("index.php", "", $segments[0]);
    }

    // Edición genérica opcional
    public function edit(string $section, string $key, string $value): void
    {
        $this->xml->editChild("/config/{$section}/{$key}", $value);
    }

    // Métodos ya existentes omitidos por brevedad...

    public function getFirstname(): string { return $this->firstname; }
    public function setFirstname(string $value): void {
        $this->firstname = $value;
        $this->xml->editChild("/config/admin/firstname", $value);
    }

    public function getLastname(): string { return $this->lastname; }
    public function setLastname(string $value): void {
        $this->lastname = $value;
        $this->xml->editChild("/config/admin/lastname", $value);
    }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $value): void {
        $this->description = $value;
        $this->xml->editChild("/config/site/description", $value);
    }

    public function getKeywords(): string { return $this->keywords; }
    public function setKeywords(string $value): void {
        $this->keywords = $value;
        $this->xml->editChild("/config/site/keywords", $value);
    }


}
