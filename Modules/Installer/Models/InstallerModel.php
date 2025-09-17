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
            $host = $this->configSettings->dbhost;
            $user = $this->configSettings->dbuser;
            $pass = $this->configSettings->dbpass;
            $dbname = $this->configSettings->dbname;

            // Verificar conexión y que la base de datos exista
            if (!$this->canConnectToDatabase()) {
                throw new \Exception("No se pudo conectar o crear la base de datos.");
            }

            // Reconectar usando la base de datos
            $this->db->connect($host, $user, $pass, $dbname);

            // Cargar y ejecutar el archivo SQL
            $sqlFile = \Framework\Static\Constant::TABLES_SQL_PATH;

            if (!file_exists($sqlFile)) {
                throw new \Exception("Archivo SQL no encontrado: $sqlFile");
            }

            $query = file_get_contents($sqlFile);
            if ($query === false) {
                throw new \Exception("No se pudo leer el archivo SQL: $sqlFile");
            }

            $query = str_replace("{dbname}", $dbname, $query);
            $this->db->create($query);

        } catch (\Throwable $e) {
            $this->configSettings->installed = "no";
            \Framework\Core\ErrorConsole::handleException(
                new \Exception("Installation failed: " . $e->getMessage(), 0, $e)
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

            // Conectar al servidor (sin DB)
            $this->db->connect($host, $user, $pass);

            if (!$this->db->isStatus()) {
                return false;
            }

            // Crear la base de datos si no existe directamente aquí
            $this->db->query("CREATE DATABASE IF NOT EXISTS `$dbname`;");

            // Reconectar usando la base de datos
            $this->db->connect($host, $user, $pass, $dbname);

            return $this->db->isStatus();
        } catch (\Throwable $e) {
            return false;
        }
    }



}
