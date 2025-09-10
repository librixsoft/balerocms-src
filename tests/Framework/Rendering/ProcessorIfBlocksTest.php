<?php

use PHPUnit\Framework\TestCase;
use Framework\Rendering\ProcessorIfBlocks;

class ProcessorIfBlocksTest extends TestCase
{
    private ProcessorIfBlocks $processor;
    private string $viewsDir;

    protected function setUp(): void
    {
        $this->processor = new ProcessorIfBlocks();
        // Ruta relativa a los templates
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

    public function testIfEquals()
    {
        $template = $this->loadTemplate('if_equals.html');

        $flatParams = ['theme' => 'active'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Active Theme', $result);

        $flatParams['theme'] = 'inactive';
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Inactive Theme', $result);
    }

    public function testIfNegation()
    {
        $template = $this->loadTemplate('if_negation.html');

        $flatParams = ['errors.username' => null];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('No Errors', $result);

        $flatParams['errors.username'] = 'Required';
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Has Errors', $result);
    }

    public function testIfSimpleVar()
    {
        $template = $this->loadTemplate('if_simple.html');

        $flatParams = ['success' => true];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Success!', $result);

        $flatParams['success'] = false;
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Failed!', $result);
    }
}
