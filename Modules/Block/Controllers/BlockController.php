<?php

/**
 * Balero CMS
 * @author Anibal Gomez
 * @license GNU General Public License
 */

namespace Modules\Block\Controllers;

use Framework\Core\Controller;
use Modules\Block\Models\BlockModel;
use Modules\Block\Views\BlockViewModel;
use Framework\Http\Get;
use Exception;

class BlockController extends Controller
{
    protected BlockViewModel $viewModel;
    protected BlockModel $model;

    public function __construct(
        BlockModel $model,
        BlockViewModel $viewModel
    ) {
        $this->model = $model;
        $this->viewModel = $viewModel;
    }

    #[Get('/')]
    public function index()
    {
        return $this->render("layouts/main.html", $this->viewModel->setBlockParams());
    }

}
