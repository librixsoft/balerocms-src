<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Core\ConfigSettings;
use Framework\Database\MySQL;
use Framework\Core\ErrorConsole;
use Throwable;
use Exception;

class Model
{
    protected MySQL $db;
    protected ConfigSettings $configSettings;

    public function __construct(ConfigSettings $configSettings, MySQL $db)
    {
        $this->configSettings = $configSettings;
        $this->db = $db;

        $this->connectOnInit(); // Nueva función encargada de conectar
    }

    protected function connectOnInit(): void
    {
        try {
            if ($this->db->isStatus()) {
                $this->db->query("CREATE DATABASE IF NOT EXISTS " . $this->configSettings->getDbname() . ";");
            } else {
                throw new Exception("Failed to connect to the database.");
            }
        } catch (Throwable $e) {
            ErrorConsole::handleException(
                new Exception("Error in Model: " . $e->getMessage(), 0, $e)
            );
        }
    }

}
