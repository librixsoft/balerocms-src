<?php

namespace Framework\Core;

use Framework\Static\Constant;
use Exception;

class ConfigSettings
{
    private array $fields = [
        // Database
        'dbhost' => '/config/database/dbhost',
        'dbuser' => '/config/database/dbuser',
        'dbpass' => '/config/database/dbpass',
        'dbname' => '/config/database/dbname',

        // Admin
        'username' => '/config/admin/username',
        'pass'     => '/config/admin/passwd',
        'email'    => '/config/admin/email',
        'firstname'=> '/config/admin/firstname',
        'lastname' => '/config/admin/lastname',

        // System
        'installed'=> '/config/system/installed',

        // Site
        'title'     => '/config/site/title',
        'description'=> '/config/site/description',
        'url'       => '/config/site/url',
        'keywords'  => '/config/site/keywords',
        'basepath'  => '/config/site/basepath',
        'theme'     => '/config/site/theme',
        'footer'    => '/config/site/footer',
        'multilang' => '/config/site/multilang',
        'editor'    => '/config/site/editor'
    ];

    private array $data = [];

    private XMLHandler $xmlHandler;

    /**
     * Constructor flexible
     * @param string|null $xmlFile Ruta del XML de configuración (opcional)
     */
    public function __construct(?string $xmlFile = null)
    {
        // Si se pasa XML, usarlo; si no, fallback a la constante
        $this->xmlHandler = new XMLHandler($xmlFile ?? Constant::CONFIG_PATH);

        // Cargar todos los valores del XML
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        foreach ($this->fields as $prop => $xpath) {
            $this->data[$prop] = $this->xmlHandler->get($xpath);
        }
    }

    // Magic getter dinámico
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    // Magic setter dinámico
    public function __set(string $name, string $value)
    {
        if (!isset($this->fields[$name])) {
            throw new Exception("Propiedad no existe: $name");
        }

        $this->data[$name] = $value;
        $this->xmlHandler->set($this->fields[$name], $value);
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

    /**
     * Guardar cambios en el XML
     */
    public function save(): void
    {
        $this->xmlHandler->save();
    }
}
