<?php

/**
 * Balero CMS
 * @author Anibal Gomez
 * @license GNU General Public License
 */

namespace Modules\Block\Views;

use Framework\Core\ConfigSettings;
use Framework\Core\ViewModel;
use Modules\Block\Models\BlockModel;

class BlockViewModel
{
    private BlockModel $model;
    private ConfigSettings $config;
    private ViewModel $viewModel;

    public function __construct(BlockModel $model, ConfigSettings $config)
    {
        $this->config = $config;
        $this->model = $model;
        $this->viewModel = new ViewModel();
    }

    /**
     * Prepara los parámetros para renderizar vistas de bloques.
     *
     * @param array $extraParams Parámetros adicionales.
     */
    public function setBlockParams(array $extraParams = []): array
    {
        $this->viewModel->addAll([
            // Colecciones
            'blocks' => $this->model->getBlocks(),

            // Etiquetas
            'lbl_blocks' => 'Blocks',
            'lbl_no_blocks' => 'No blocks available.',

            // Botones
            'btn_refresh' => 'Refresh',
        ]);

        if (!empty($extraParams)) {
            $this->viewModel->addAll($extraParams);
        }

        return $this->viewModel->all();
    }
}
