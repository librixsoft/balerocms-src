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

    public function testIfConcatenated()
    {
        $template = $this->loadTemplate('if_concatenated.html');

        $flatParams = ['theme' => 'active', 'mode' => 'dark'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Active Dark Mode', $result);

        $flatParams = ['theme' => 'inactive', 'mode' => 'dark'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Inactive or Light Mode', $result);

        $flatParams = ['theme' => 'active', 'mode' => 'light'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Inactive or Light Mode', $result);
    }

    public function testIfNested()
    {
        $template = $this->loadTemplate('if_nested.html');

        // Caso: theme active, mode dark, installed yes
        $flatParams = ['theme' => 'active', 'mode' => 'dark', 'installed' => 'yes'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Active Dark Theme', $result);
        $this->assertStringContainsString('Installed', $result);

        // Caso: theme active, mode dark, installed no
        $flatParams['installed'] = 'no';
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Active Dark Theme', $result);
        $this->assertStringContainsString('Not Installed', $result);

        // Caso: theme inactive
        $flatParams = ['theme' => 'inactive', 'mode' => 'dark', 'installed' => 'yes'];
        $result = $this->processor->process($template, $flatParams);
        $this->assertStringContainsString('Inactive Dark Theme', $result);
        $this->assertStringNotContainsString('Active Dark Theme', $result);
    }


}
