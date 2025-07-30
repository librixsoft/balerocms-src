<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Static;

use Framework\Config\Context;
use Framework\Core\ConfigSettings;

class Redirect
{

    /**
     * Redirige a una URL relativa al basepath.
     *
     * @param string $url Ruta relativa (ej. 'admin/dashboard')
     */
    public static function to(string $url): void
    {
        $config = self::getConfig();

        $basepath = rtrim($config->getBasepath(), '/');
        $url = ltrim($url, '/');
        $combinedUrl = $basepath . '/' . $url;
        $normalizedUrl = preg_replace('#(?<!:)//+#', '/', $combinedUrl);

        header("Location: " . $normalizedUrl);
        exit;
    }

    private static function getConfig(): ConfigSettings
    {
        $config = Context::get(ConfigSettings::class);

        if (!$config) {
            throw new \RuntimeException('No se pudo acceder a ConfigSettings desde Redirect.');
        }

        return $config;
    }
}
