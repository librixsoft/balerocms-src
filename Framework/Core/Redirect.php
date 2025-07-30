<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Config\Context;

class Redirect
{
    private static ?self $instance = null;
    private ConfigSettings $config;

    public function __construct(ConfigSettings $config)
    {
        $this->config = $config;
        self::$instance = $this; // Guarda la instancia creada
    }

    /**
     * Obtiene la instancia singleton desde Context (lazy load).
     */
    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = Context::get(self::class);
        }
        return self::$instance;
    }

    /**
     * Método estático para redirigir.
     */
    public static function to(string $url): void
    {
        self::getInstance()->redirect($url);
    }

    /**
     * Realiza la redirección usando ConfigSettings inyectado.
     */
    private function redirect(string $url): void
    {
        $basepath = rtrim($this->config->getBasepath(), '/');
        $url = ltrim($url, '/');
        $combinedUrl = $basepath . '/' . $url;
        $normalizedUrl = preg_replace('#(?<!:)//+#', '/', $combinedUrl);

        header("Location: " . $normalizedUrl);
        exit;
    }
}
