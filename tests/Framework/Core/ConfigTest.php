<?php
use PHPUnit\Framework\TestCase;
use Framework\Core\XMLHandler;
use Framework\Core\ConfigSettings;

final class ConfigTest extends TestCase
{
    private string $xmlFile;

    public function setUp(): void
    {
        // Ruta temporal para el XML de prueba
        $this->xmlFile = __DIR__ . '/../../resources/config/test_configsettings.xml';

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
    <admin>
        <username>admin</username>
        <passwd>admin123</passwd>
        <email>admin@test.com</email>
        <firstname>Anibal</firstname>
        <lastname>Gomez</lastname>
    </admin>
    <system>
        <installed>1</installed>
    </system>
    <site>
        <title>Mi CMS</title>
        <description>Descripción del sitio</description>
        <url>http://localhost/</url>
        <keywords>cms,php</keywords>
        <basepath>/</basepath>
        <theme>default</theme>
        <footer>© 2025 Mi CMS</footer>
        <multilang>0</multilang>
        <editor>tiny</editor>
    </site>
</config>
XML;

        // Crear directorio si no existe
        if (!is_dir(dirname($this->xmlFile))) {
            mkdir(dirname($this->xmlFile), 0777, true);
        }

        file_put_contents($this->xmlFile, $xmlContent);
    }

    public function tearDown(): void
    {
        if (file_exists($this->xmlFile)) {
            unlink($this->xmlFile);
        }
    }

    public function testXMLHandlerGetAndSet(): void
    {
        $xml = new XMLHandler($this->xmlFile);

        // Leer valor
        $dbHost = $xml->get('/config/database/dbhost');
        $this->assertEquals('localhost', $dbHost);

        // Modificar valor
        $xml->set('/config/database/dbhost', '127.0.0.1');
        $dbHostModified = $xml->get('/config/database/dbhost');
        $this->assertEquals('127.0.0.1', $dbHostModified);
    }

    public function testConfigSettingsGetAndSet(): void
    {
        // Pasamos la ruta del XML de prueba al constructor
        $config = new ConfigSettings($this->xmlFile);

        // Comprobamos lectura inicial
        $this->assertEquals('localhost', $config->dbhost);
        $this->assertEquals('default', $config->theme);
        $this->assertEquals('admin', $config->username);

        // Cambiamos valores usando magic setter
        $config->dbhost = '192.168.0.1';
        $config->theme  = 'darkmode';
        $config->username = 'superadmin';

        // Validamos cambios usando magic getter
        $this->assertEquals('192.168.0.1', $config->dbhost);
        $this->assertEquals('darkmode', $config->theme);
        $this->assertEquals('superadmin', $config->username);

        // Validar que también se modificó en el XML
        $xml = new XMLHandler($this->xmlFile);
        $this->assertEquals('192.168.0.1', $xml->get('/config/database/dbhost'));
        $this->assertEquals('darkmode', $xml->get('/config/site/theme'));
        $this->assertEquals('superadmin', $xml->get('/config/admin/username'));
    }

    public function testInvalidPropertyThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $config = new ConfigSettings($this->xmlFile);
        $config->nonexistent = 'value'; // debería lanzar Exception
    }
}
