<?php

namespace Framework\Rendering;

/**
 * Processor para el reemplazo directo de variables.
 *
 * Esta clase maneja la sustitución de las variables en el formato {key} dentro del contenido de la plantilla.
 * Los valores son obtenidos de los parámetros proporcionados en el método processTemplate.
 */
class ProcessorVariables
{
    /**
     * Procesa el contenido reemplazando las variables {key} por sus valores correspondientes.
     *
     * @param string $content El contenido de la plantilla.
     * @param array $flatParams Los parámetros planos con las claves como 'key' y sus valores asociados.
     *
     * @return string El contenido con las variables reemplazadas.
     */
    public function process(string $content, array $flatParams): string
    {
        foreach ($flatParams as $key => $value) {
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $content = str_replace('{' . $key . '}', $safeValue, $content);
        }

        return $content;
    }
}
