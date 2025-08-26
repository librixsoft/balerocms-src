<?php

namespace Framework\Rendering;

use Framework\I18n\LangManager;

/**
 * Processor para el reemplazo de claves en notación de punto.
 *
 * Esta clase maneja la sustitución de las claves en el formato {module.key} o {auth.login} dentro del contenido de la plantilla.
 * Primero busca el valor en los parámetros proporcionados y, si no lo encuentra, recurre a LangManager para obtener una posible traducción.
 */
class ProcessorKeyPath
{
    /**
     * Procesa el contenido reemplazando las claves {module.key} por su valor correspondiente.
     *
     * @param string $content El contenido de la plantilla.
     * @param array $flatParams Los parámetros planos con claves como 'module.key' y sus valores asociados.
     *
     * @return string El contenido con las claves reemplazadas.
     */
    public function process(string $content, array $flatParams): string
    {
        return preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}/',
            function ($matches) use ($flatParams) {
                $fullKey = $matches[1] . '.' . $matches[2];

                // Verifica si la clave existe en los parámetros planos
                if (array_key_exists($fullKey, $flatParams)) {
                    return $flatParams[$fullKey];
                }

                // Si no, busca la traducción en LangManager
                return LangManager::get($fullKey, $matches[0]);
            },
            $content
        );
    }
}
