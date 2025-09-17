<?php
use PHPUnit\Framework\TestCase;
use Framework\Core\XMLHandler;

final class XMLHandlerTest extends TestCase
{
    private string $xmlFile;

    public function setUp(): void
    {
        // Ruta temporal para el XML de prueba
        $this->xmlFile = __DIR__ . '/../../resources/config/test_config.xml';

        // Creamos un XML de prueba dinámicamente
        $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <database>
        <dbhost>localhost</dbhost>
        <dbuser>root</dbuser>
        <dbpass>1234</dbpass>
        <dbname>cms</dbname>
    </database>
    <site>
        <title>Mi CMS</title>
        <theme>default</theme>
    </site>
</config>
XML;

        file_put_contents($this->xmlFile, $xmlContent);
    }

    public function tearDown(): void
    {
        if (file_exists($this->xmlFile)) {
            unlink($this->xmlFile);
        }
    }

    public function testGetValue(): void
    {
        $xmlHandler = new XMLHandler($this->xmlFile);

        $dbHost = $xmlHandler->get('/config/database/dbhost');
        $this->assertEquals('localhost', $dbHost);

        $theme = $xmlHandler->get('/config/site/theme');
        $this->assertEquals('default', $theme);

        // Valor no existente devuelve ''
        $nonExistent = $xmlHandler->get('/config/nonexistent');
        $this->assertEquals('', $nonExistent);
    }

    public function testSetValue(): void
    {
        $xmlHandler = new XMLHandler($this->xmlFile);

        // Cambiar valores
        $xmlHandler->set('/config/database/dbhost', '127.0.0.1');
        $xmlHandler->set('/config/site/theme', 'darkmode');

        // Verificar cambios
        $this->assertEquals('127.0.0.1', $xmlHandler->get('/config/database/dbhost'));
        $this->assertEquals('darkmode', $xmlHandler->get('/config/site/theme'));
    }

    public function testSetValueBlank(): void
    {
        $xmlHandler = new XMLHandler($this->xmlFile);

        // Asignar valor vacío se convierte en '_blank' en el XML
        $xmlHandler->set('/config/site/theme', '');

        // Leer devuelve ''
        $this->assertEquals('', $xmlHandler->get('/config/site/theme'));
    }

    public function testInvalidPathThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $xmlHandler = new XMLHandler($this->xmlFile);
        $xmlHandler->set('/config/invalid/path', 'value');
    }
}
