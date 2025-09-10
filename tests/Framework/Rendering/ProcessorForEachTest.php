<?php

use PHPUnit\Framework\TestCase;
use Framework\Rendering\ProcessorForEach;
use Framework\Rendering\ProcessorFlattenParams;
use Framework\Rendering\ProcessorIfBlocks;

class ProcessorForEachTest extends TestCase
{
    private ProcessorForEach $processorForEach;
    private ProcessorIfBlocks $processorIfBlocks;
    private $mockFlatten;
    private string $viewsDir;

    protected function setUp(): void
    {
        // Mock ProcessorFlattenParams
        $this->mockFlatten = $this->createMock(ProcessorFlattenParams::class);
        $this->mockFlatten->method('process')
            ->willReturnCallback(function ($array) {
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

        $this->processorForEach = new ProcessorForEach($this->mockFlatten);
        $this->processorIfBlocks = new ProcessorIfBlocks();

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

    public function testForeachSimple()
    {
        $template = $this->loadTemplate('foreach_simple.html');

        $params = [
            'items' => [
                ['name' => 'One', 'value' => 10],
                ['name' => 'Two', 'value' => 20],
            ]
        ];

        $result = $this->processorForEach->process($template, $params);

        // Normalizar espacios y saltos de línea
        $resultNormalized = preg_replace('/\s+/', ' ', $result);

        $this->assertStringContainsString('Item: One, Value: 10', $resultNormalized);
        $this->assertStringContainsString('Item: Two, Value: 20', $resultNormalized);
    }

    public function testForeachWithIf()
    {
        $template = $this->loadTemplate('foreach_with_if.html');

        $params = [
            'themes' => [
                ['name' => 'Light', 'value' => 'light'],
                ['name' => 'Dark', 'value' => 'dark'],
            ],
            'defaultTheme' => 'dark'
        ];

        // Primero procesamos el foreach
        $result = $this->processorForEach->process($template, $params);
        // Luego procesamos los bloques @if
        $result = $this->processorIfBlocks->process($result, $params);

        // Normalizar espacios y saltos de línea
        $resultNormalized = preg_replace('/\s+/', ' ', $result);

        $this->assertStringContainsString('Theme: Light, Value: light (Not Default)', $resultNormalized);
        $this->assertStringContainsString('Theme: Dark, Value: dark (Default)', $resultNormalized);
    }
}
