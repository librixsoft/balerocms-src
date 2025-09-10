<?php

use PHPUnit\Framework\TestCase;
use Framework\Rendering\ProcessorForEach;
use Framework\Rendering\ProcessorFlattenParams;
use Framework\Security\Security;

class ProcessorForEachTest extends TestCase
{
    private ProcessorForEach $processor;
    private string $viewsDir;
    private $mockFlatten;
    private $mockSecurity;

    protected function setUp(): void
    {
        // Mocks
        $this->mockFlatten = $this->createMock(ProcessorFlattenParams::class);
        $this->mockFlatten->method('process')
            ->willReturnCallback(function ($array) {
                // Devuelve array con notación de puntos para cada elemento
                $flat = [];
                foreach ($array as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $subk => $subv) {
                            $flat["$k.$subk"] = $subv;
                        }
                    } else {
                        $flat[$k] = $v;
                    }
                }
                return $flat;
            });

        $this->mockSecurity = $this->createMock(Security::class);

        $this->processor = new ProcessorForEach($this->mockFlatten, $this->mockSecurity);

        // Carpeta de plantillas
        $this->viewsDir = __DIR__ . '/../../resources/views/';
    }

    private function loadTemplate(string $filename): string
    {
        $path = $this->viewsDir . $filename;
        if (!file_exists($path)) {
            throw new \RuntimeException("Template file not found: $path");
        }
        return file_get_contents($path);
    }

    public function testSimpleForeach()
    {
        $template = $this->loadTemplate('foreach_simple.html');

        $params = [
            'items' => [
                ['name' => 'One', 'value' => 10],
                ['name' => 'Two', 'value' => 20],
            ]
        ];

        $result = $this->processor->process($template, $params);

        $this->assertStringContainsString('Item: One, Value: 10', $result);
        $this->assertStringContainsString('Item: Two, Value: 20', $result);
    }

    public function testForeachEmptyArray()
    {
        $template = $this->loadTemplate('foreach_simple.html');
        $params = ['items' => []];

        $result = $this->processor->process($template, $params);

        $this->assertEquals('', trim($result));
    }

    public function testForeachMissingArray()
    {
        $template = $this->loadTemplate('foreach_simple.html');
        $params = []; // No existe la clave "items"

        $result = $this->processor->process($template, $params);

        $this->assertEquals('', trim($result));
    }
}
