<?php

namespace Framework;

use Framework\ConfigSettings;
use Database\MySQL;
use Framework\ErrorConsole;
use Throwable;
use Exception;

class Model
{
    protected MySQL $db;
    protected ConfigSettings $configSettings;

    public function __construct(ConfigSettings $configSettings)
    {
        $this->configSettings = $configSettings;
    }

    public function dbConnect(): void
    {
        try {
            // Primer intento: conectar sin base de datos
            $this->db = new MySQL(
                $this->configSettings->getDbhost(),
                $this->configSettings->getDbuser(),
                $this->configSettings->getDbpass()
            );

            if ($this->db->isStatus()) {
                // Crea la base de datos si no existe
                $this->db->query("CREATE DATABASE IF NOT EXISTS " . $this->configSettings->getDbname() . ";");

                // Reconecta a la base de datos recién creada
                $this->db = new MySQL(
                    $this->configSettings->getDbhost(),
                    $this->configSettings->getDbuser(),
                    $this->configSettings->getDbpass(),
                    $this->configSettings->getDbname()
                );
            } else {
                throw new Exception("Failed to connect to the database.");
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error in Model: " . $e->getMessage(), 0, $e)
            );
        }
    }

    // En caso de que quieras exponer $db como getter
    public function getDb(): MySQL
    {
        return $this->db;
    }
}
