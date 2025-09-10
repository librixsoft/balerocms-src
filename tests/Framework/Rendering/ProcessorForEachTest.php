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

        $this->viewsDir = __DIR__ . '/../../resources/views/foreach/';
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

    public function testForeachWithIfNested()
    {
        $template = $this->loadTemplate('foreach_with_if_nested.html');

        $params = [
            'themes' => [
                ['name' => 'Blue', 'value' => 'blue'],
                ['name' => 'Dark', 'value' => 'dark'],
                ['name' => 'Light', 'value' => 'light'],
            ],
            'defaultTheme' => 'dark'
        ];

        $result = '';

        // Extraer solo el bloque foreach
        preg_match('/<!-- @foreach themes as t -->(.*)<!-- @endforeach -->/s', $template, $matches);
        $templateIteration = $matches[1] ?? '';

        // Procesar cada iteración
        foreach ($params['themes'] as $t) {
            // Aplanar parámetros de la iteración
            $flatParams = [
                't.name' => $t['name'],
                't.value' => $t['value'],
                'defaultTheme' => $params['defaultTheme']
            ];

            // Reemplazar placeholders {t.name}, {t.value}, etc.
            $iterTemplate = str_replace(
                array_map(fn($k) => '{'.$k.'}', array_keys($flatParams)),
                array_values($flatParams),
                $templateIteration
            );

            // Procesar bloques @if dentro de la iteración
            $result .= $this->processorIfBlocks->process($iterTemplate, $flatParams);
        }

        // Normalizar espacios y saltos de línea
        $resultNormalized = preg_replace('/\s+/', ' ', $result);

        // Comprobaciones
        $this->assertStringContainsString('Theme: Blue, Value: blue', $resultNormalized);
        $this->assertStringContainsString('Theme: Dark, Value: dark', $resultNormalized);
        $this->assertStringContainsString('Theme: Light, Value: light', $resultNormalized);

        $this->assertStringContainsString('(Not Default)', $resultNormalized);
        $this->assertStringContainsString('(Default)', $resultNormalized);

        $this->assertStringContainsString('- Dark Mode Active', $resultNormalized);
        $this->assertStringContainsString('- Light Mode', $resultNormalized);
    }

    public function testForeachWithIfNestedAndOr()
    {
        $template = $this->loadTemplate('foreach_with_if_nested_and_or.html');

        $params = [
            'themes' => [
                ['name' => 'Blue', 'value' => 'blue'],
                ['name' => 'Dark', 'value' => 'dark'],
            ],
            'defaultTheme' => 'dark',
            'theme' => 'active',
            'mode' => 'dark',
            'admin' => 'yes',
            'installed' => 'yes',
            'version' => '2.0',
            'beta' => 'no',
            'premium' => 'no',
        ];

        $result = '';

        // Extraer el bloque foreach
        preg_match('/<!-- @foreach themes as t -->(.*)<!-- @endforeach -->/s', $template, $matches);
        $templateIteration = $matches[1] ?? '';

        foreach ($params['themes'] as $t) {
            $flatParams = [
                't.name' => $t['name'],
                't.value' => $t['value'],
                'defaultTheme' => $params['defaultTheme'],
                'theme' => $params['theme'],
                'mode' => $params['mode'],
                'admin' => $params['admin'],
                'installed' => $params['installed'],
                'version' => $params['version'],
                'beta' => $params['beta'],
                'premium' => $params['premium'],
            ];

            // Reemplazar placeholders
            $iterTemplate = str_replace(
                array_map(fn($k) => '{'.$k.'}', array_keys($flatParams)),
                array_values($flatParams),
                $templateIteration
            );

            // Procesar IFs internos
            $result .= $this->processorIfBlocks->process($iterTemplate, $flatParams);
        }

        // Normalizar espacios y saltos de línea
        $resultNormalized = preg_replace('/\s+/', ' ', $result);

        // Comprobaciones
        $this->assertStringContainsString('Theme: Blue, Value: blue', $resultNormalized);
        $this->assertStringContainsString('Theme: Dark, Value: dark', $resultNormalized);

        $this->assertStringContainsString('(Not Default)', $resultNormalized);
        $this->assertStringContainsString('(Default)', $resultNormalized);

        $this->assertStringContainsString('- Dark Mode Active', $resultNormalized);
        $this->assertStringContainsString('- Light Mode', $resultNormalized);

        // Verificar niveles de IF anidados con AND/OR
        $this->assertStringContainsString('Level 1: Active Dark Theme', $resultNormalized);
        $this->assertStringContainsString('Level 2: Admin Access', $resultNormalized);
        $this->assertStringContainsString('Level 3: Installed or Version 2.0', $resultNormalized);
        $this->assertStringContainsString('Level 4: Not Beta', $resultNormalized);
        $this->assertStringContainsString('Level 5: Regular User', $resultNormalized);
    }


}
