<?php

namespace Framework\Core;

use Framework\Core\ConfigSettings;

class Redirect
{

    private ConfigSettings $config;

    public function __construct(ConfigSettings $config)
    {
        $this->config = $config;
    }

    public function to(string $url): void
    {
        $basepath = rtrim($this->config->getBasepath(), '/');
        $url = ltrim($url, '/');
        $combinedUrl = $basepath . '/' . $url;
        $normalizedUrl = preg_replace('#(?<!:)//+#', '/', $combinedUrl);
        header("Location: " . $normalizedUrl);
        exit;
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
