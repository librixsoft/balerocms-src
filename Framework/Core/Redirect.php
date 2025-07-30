<?php

namespace Framework\Core;

use Framework\Config\Context;

class Redirect
{
    private static ?self $instance = null;
    private ConfigSettings $config;

    private function __construct()
    {
        // Tomar instancia global sin inyectar
        $this->config = Context::get('config');// Obtiene ConfigSettings desde el contenedor
    }

    /**
     * Inicializa Redirect singleton (solo si aún no existe)
     */
    public static function init(): void
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    /**
     * Redirecciona a una ruta relativa (usando basepath del config)
     */
    public static function to(string $url): void
    {
        if (self::$instance === null) {
            throw new \RuntimeException("Redirect no ha sido inicializado. Usa Redirect::init() primero.");
        }

        self::$instance->redirect($url);
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
