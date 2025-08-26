<?php

namespace Framework\Rendering;

use Framework\I18n\LangManager;

/**
 * Processor para el reemplazo de claves de idioma en la plantilla.
 *
 * Esta clase maneja la sustitución de las claves de idioma dentro del contenido de la plantilla en el formato {lang.key}.
 * Utiliza la clase LangManager para obtener las traducciones correspondientes.
 */
class ProcessorLang
{
    /**
     * Procesa el contenido reemplazando las claves de idioma {lang.key} por su traducción.
     *
     * @param string $content El contenido de la plantilla.
     *
     * @return string El contenido con las claves de idioma reemplazadas.
     */
    public function process(string $content): string
    {
        return preg_replace_callback(
            '/\{lang\.([a-zA-Z0-9_]+)\}/',
            function ($matches) {
                return LangManager::get($matches[1], $matches[1]); // fallback: clave original
            },
            $content
        );
    }
}
