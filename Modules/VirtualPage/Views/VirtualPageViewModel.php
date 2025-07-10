<?php

namespace Modules\VirtualPage\Views;

use Framework\Core\ConfigSettings;

class VirtualPageViewModel
{

    public function print_virtual_page(array $rows): string
    {
        if (empty($rows)) {
            return "<p>No se encontró la página solicitada.</p>";
        }

        $html = "";

        foreach ($rows as $page) {
            $title = htmlspecialchars($page['virtual_title'] ?? '', ENT_QUOTES, 'UTF-8');
            $date = htmlspecialchars($page['date'] ?? '', ENT_QUOTES, 'UTF-8');
            $content = $page['virtual_content'] ?? '';

            $html .= "<h2>{$title}</h2>";
            $html .= "<p><em>{$date}</em></p>";
            $html .= "<div class=\"vp-body\">{$content}</div>";
        }

        return $html;
    }


    public static function getDefaultParams(ConfigSettings $config): array
    {
        return [
            'title' => $config->getTitle(),
            'page' => defined('_PAGE') ? _PAGE : '',
            'keywords' => $config->getKeywords(),
            'description' => $config->getDescription(),
            'basepath' => $config->getBasepath(),

            // Etiquetas
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
            'lbl_no_pages' => 'No virtual pages available.',

            // Valores configurables
            'txt_title' => $config->getTitle(),
            'txt_keywords' => $config->getKeywords(),
            'txt_description' => $config->getDescription(),

            // Botones o textos varios
            'btn_refresh' => 'Refresh',
        ];
    }

    public static function getViewParams(ConfigSettings $config, array $extra = []): array
    {
        $params = self::getDefaultParams($config);

        // Puedes añadir más parámetros por defecto aquí, por ejemplo:
        $params['welcome_message'] = "Welcome to the Virtual Page section.";
        $params['current_language'] = defined('CURRENT_LANG') ? CURRENT_LANG : 'main';

        return array_merge($params, $extra);
    }
}
