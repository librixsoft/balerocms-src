<?php

namespace Modules\Page\Views;

use Framework\Core\ConfigSettings;
use Modules\Page\Models\PageModel;

class PageViewModel
{
    /**
     * Diccionario específico para la vista principal.
     */
    public static function getHomeParams(PageModel $model): array
    {
        return [
            'virtual_pages' => $model->getVirtualPages(),

            // Etiquetas
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
            'lbl_no_pages' => 'No virtual pages available.',

            // Botones o textos varios
            'btn_refresh' => 'Refresh',
        ];
    }

    /**
     * Diccionario para página individual.
     */
    public static function getDetailParams(PageModel $model, array $page): array
    {
        return [
            'virtual_pages' => $model->getVirtualPages(),
            'page' => $page,

            // Etiquetas
            'lbl_virtual_pages' => 'Virtual Pages',
            'lbl_home' => 'Home',
        ];
    }

    /**
     * Diccionario para error de página no encontrada.
     */
    public static function getNotFoundParams(): array
    {
        return [
            'error_message' => "La página solicitada no existe.",
        ];
    }
}
