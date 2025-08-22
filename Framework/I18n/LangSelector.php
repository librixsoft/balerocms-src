<?php

namespace Framework\I18n;

class LangSelector
{
    /**
     * Retorna los parámetros necesarios para mostrar el selector de idioma.
     * Prioriza la query string (?lang=es) sobre la sesión.
     *
     * @return array
     */
    public static function getParams(): array
    {
        // Priorizar GET sobre SESSION
        $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';

        // Guardar en sesión para persistencia
        $_SESSION['lang'] = $lang;

        return [
            // Para <select> en formularios
            'lang_selected_en' => $lang === 'en' ? 'selected' : '',
            'lang_selected_es' => $lang === 'es' ? 'selected' : '',

            // Textos generales del idioma
            'lang' => [
                'select_language' => __('select_language'),
                // Aquí puedes agregar más claves traducibles
            ],
        ];
    }
}
