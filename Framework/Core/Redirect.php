<?php

namespace Framework\Core;

class Redirect
{
    private static ?self $instance = null;
    private ConfigSettings $config;

    private function __construct(ConfigSettings $config)
    {
        $this->config = $config;
    }

    public static function init(ConfigSettings $config): void
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
    }

    public static function to(string $url): void
    {
        if (self::$instance === null) {
            throw new \RuntimeException("Redirect no ha sido inicializado. Usa Redirect::init() primero.");
        }

        self::$instance->redirect($url);
    }

    private function redirect(string $url): void
    {
        $basepath = rtrim($this->config->getBasepath(), '/');
        $url = ltrim($url, '/');

        $combinedUrl = $basepath . '/' . $url;

        // Normalizar URL para quitar dobles barras (excepto protocolo)
        $normalizedUrl = $this->removeDoubleSlashes($combinedUrl);

        header("Location: " . $normalizedUrl);
        exit;
    }

    /**
     * Elimina dobles (o múltiples) barras consecutivas en la URL,
     * excepto después de '://' para no romper el protocolo.
     */
    private function removeDoubleSlashes(string $url): string
    {
        return preg_replace('#(?<!:)//+#', '/', $url);
    }
}
