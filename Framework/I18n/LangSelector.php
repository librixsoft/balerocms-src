<?php
namespace Framework\I18n;

use Framework\Http\RequestHelper;

class LangSelector
{
    public static function getParams(RequestHelper $request): array
    {
        $lang = $request->hasGet('lang') ? $request->get('lang') : ($_SESSION['lang'] ?? 'en');
        $_SESSION['lang'] = $lang;

        return [
            'lang_selected_en' => $lang === 'en' ? 'selected' : '',
            'lang_selected_es' => $lang === 'es' ? 'selected' : '',
            'lang' => ['select_language' => __('select_language')],
        ];
    }
}
