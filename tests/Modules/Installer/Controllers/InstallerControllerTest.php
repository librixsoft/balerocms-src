<?php

use PHPUnit\Framework\TestCase;
use Modules\Installer\Controllers\InstallerController;
use Modules\Installer\Models\InstallerModel;
use Modules\Installer\Views\InstallerViewModel;

class InstallerControllerTest extends TestCase
{
    private $modelMock;
    private $viewModelMock;
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

        // Controller con render() sobrescrito
        $this->controller = new class($this->modelMock, $this->viewModelMock) extends InstallerController {
            protected function render($template, $params = [], $useTheme = true): string {
                return json_encode($params);
            }
        };
    }

    public function testHomeReturnsDbOkParam()
    {
        $resultJson = $this->controller->home();
        $result = json_decode($resultJson, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('db_ok', $result);
        $this->assertTrue($result['db_ok']);
    }
}
