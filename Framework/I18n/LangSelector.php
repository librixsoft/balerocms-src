<?php

namespace Framework\I18n;

class LangSelector
{
    /**
     * Retorna los parámetros necesarios para mostrar el selector de idioma
     *
     * @return array
     */
    public static function getParams(): array
    {
        $lang = $_SESSION['lang'] ?? 'en';

        return [
            'lang_selected_en' => $lang === 'en' ? 'selected' : '',
            'lang_selected_es' => $lang === 'es' ? 'selected' : '',
            'lang' => [
                'select_language' => __('select_language'),
            ],
        ];
    }
}
