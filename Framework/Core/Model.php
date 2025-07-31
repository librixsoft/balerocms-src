<?php

/**
 * Balero CMS
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Core;

use Framework\Core\ConfigSettings;
use Framework\Database\MySQL;

class Model
{
    protected MySQL $db;
    protected ConfigSettings $configSettings;

    public function __construct(ConfigSettings $configSettings, MySQL $db)
    {
        $this->configSettings = $configSettings;
        $this->db = $db;

        // Solo conecta si la app ya está instalada
        if ($this->configSettings->getInstalled() === 'yes') {
            $this->db->connect(
                $this->configSettings->getDbhost(),
                $this->configSettings->getDbuser(),
                $this->configSettings->getDbpass(),
                $this->configSettings->getDbname()
            );
        }
    }
}
