<?php

namespace Modules\Installer\Models;

use Framework\Core\Model;
use Framework\Core\ErrorConsole;
use Framework\Static\CONSTANTS;
use Exception;
use Throwable;

class InstallerModel extends Model
{
    public function install(): void
    {
        try {
            // Obtener parámetros de conexión desde ConfigSettings
            $host = $this->configSettings->getDbhost();
            $user = $this->configSettings->getDbuser();
            $pass = $this->configSettings->getDbpass();
            $dbname = $this->configSettings->getDbname();

            // 1. Conectar sin base de datos (solo al servidor)
            $this->db->connect($host, $user, $pass);

            if (!$this->db->isStatus()) {
                throw new Exception("No se pudo conectar al servidor de base de datos.");
            }

            // 2. Crear la base de datos si no existe
            $this->db->query("CREATE DATABASE IF NOT EXISTS `$dbname`;");

            // 3. Reconectar usando la base de datos recién creada
            $this->db->connect($host, $user, $pass, $dbname);

            // 4. Cargar y ejecutar el archivo SQL
            $sqlFile = CONSTANTS::TABLES_SQL_PATH;

            if (!file_exists($sqlFile)) {
                throw new Exception("Archivo SQL no encontrado: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            if ($query === false) {
                throw new Exception("No se pudo leer el archivo SQL: $sqlFile");
            }

            $query = str_replace("{dbname}", $dbname, $query);
            $this->db->create($query);

            // 5. Marcar la instalación como exitosa
            $this->configSettings->setInstalled("yes");
        } catch (Throwable $e) {
            $this->configSettings->setInstalled("no");
            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
        }
    }
}
