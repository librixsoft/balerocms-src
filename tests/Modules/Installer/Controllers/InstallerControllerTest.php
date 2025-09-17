<?php

use PHPUnit\Framework\TestCase;
use Modules\Installer\Controllers\InstallerController;
use Modules\Installer\Models\InstallerModel;
use Modules\Installer\Views\InstallerViewModel;
use Framework\Core\View;
use Framework\Static\Flash;

class InstallerControllerTest extends TestCase
{
    private $modelMock;
    private $viewModelMock;
    private $viewStub;
    private $controller;

    protected function setUp(): void
    {
        // Mock del modelo
        $this->modelMock = $this->createMock(InstallerModel::class);
        $this->modelMock->method('canConnectToDatabase')->willReturn(true);

        // Mock del ViewModel
        $this->viewModelMock = $this->createMock(InstallerViewModel::class);
        $this->viewModelMock->method('setInstallerParams')
            ->willReturn(['db_ok' => true]);

        // Stub de View que devuelve HTML genérico
        $this->viewStub = $this->createStub(View::class);
        $this->viewStub->method('render')->willReturn('<html>HTML de prueba</html>');

        // Controller con render() que llama al View real
        $this->controller = new class($this->modelMock, $this->viewModelMock) extends InstallerController {
            public View $view;

            protected function render($template, $params = [], $useTheme = true): string {
                return $this->view->render($template, $params, $useTheme);
            }
        };

        // Inyectamos el stub
        $this->controller->view = $this->viewStub;
    }

    public function testHomeReturnsHtml()
    {
        $html = $this->controller->home();
        $this->assertStringContainsString('HTML', $html);
    }

    public function testHomeFlowWithFlashErrors()
    {
        // Expectation: el método del modelo será llamado
        $this->modelMock->expects($this->once())
            ->method('canConnectToDatabase');

        // Expectation: setInstallerParams será llamado y recibirá los errores de Flash
        $this->viewModelMock->expects($this->once())
            ->method('setInstallerParams')
            ->with($this->callback(fn($params) => isset($params['errors']) && $params['errors'] === ['username' => 'required']))
            ->willReturnCallback(fn($params) => $params);

        // Simular errores en Flash
        Flash::set('errors', ['username' => 'required']);

        // Llamar al método
        $html = $this->controller->home();

        // Revisar que la salida sigue siendo HTML
        $this->assertStringContainsString('<html>', $html);

        // Limpiar Flash para otros tests
        Flash::delete('errors');
    }

}
