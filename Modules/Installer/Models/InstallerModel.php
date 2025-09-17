<?php

namespace Modules\Installer\Models;

use Framework\Core\Model;
use Framework\Core\ErrorConsole;
use Framework\Static\Constant;
use Exception;
use Throwable;

class InstallerModel extends Model
{

    public function setInstalled(): void
    {
        $this->configSettings->installed = "yes";
    }

    public function install(): void
    {
        try {
            // Obtener parámetros de conexión desde ConfigSettings
            $host = $this->configSettings->dbhost;
            $user = $this->configSettings->dbuser;
            $pass = $this->configSettings->dbpass;
            $dbname = $this->configSettings->dbname;

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
            $sqlFile = Constant::TABLES_SQL_PATH;

            if (!file_exists($sqlFile)) {
                throw new Exception("Archivo SQL no encontrado: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            if ($query === false) {
                throw new Exception("No se pudo leer el archivo SQL: $sqlFile");
            }

            $query = str_replace("{dbname}", $dbname, $query);
            $this->db->create($query);

        } catch (Throwable $e) {
            $this->configSettings->istalled = "no";
            ErrorConsole::handleException(
                new Exception("Installation failed: " . $e->getMessage(), 0, $e)
            );
        }
    }

    public function canConnectToDatabase(): bool
    {
        try {
            $host = $this->configSettings->dbhost;
            $user = $this->configSettings->dbuser;
            $pass = $this->configSettings->dbpass;
            $dbname = $this->configSettings->dbname;

            $this->db->connect($host, $user, $pass, $dbname);
            return $this->db->isStatus();
        } catch (\Throwable $e) {
            return false;
        }
    }

}
