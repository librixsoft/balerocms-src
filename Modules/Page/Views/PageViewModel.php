<?php

namespace Modules\Page\Views;

use Framework\Core\ConfigSettings;

class PageViewModel
{

    public static function getDefaultParams(ConfigSettings $config): array
    {
        return [
            'title' => $config->getTitle(),
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

}
