<?php

namespace Framework\Core;

use Framework\Config\Context;
use Framework\Core\ConfigSettings;

class Redirect
{
    private ConfigSettings $config;

    public function __construct()
    {
        // Se obtiene desde el contenedor al construirse (aún sin inyección automática)
        $this->config = Context::get(ConfigSettings::class);
    }

    /**
     * Método estático que actúa como fachada limpia.
     */
    public static function to(string $url): void
    {
        $instance = Context::get(self::class);
        $instance->redirect($url);
    }

    /**
     * Redirección interna usando basepath
     */
    private function redirect(string $url): void
    {
        $basepath = rtrim($this->config->getBasepath(), '/');
        $url = ltrim($url, '/');

        $combinedUrl = $basepath . '/' . $url;

        // Elimina dobles slashes (excepto después de protocolo)
        $normalizedUrl = $this->removeDoubleSlashes($combinedUrl);

        header("Location: " . $normalizedUrl);
        exit;
    }

    /**
     * Elimina dobles slashes pero respeta http:// o https://
     */
    private function removeDoubleSlashes(string $url): string
    {
        return preg_replace('#(?<!:)//+#', '/', $url);
    }
}
