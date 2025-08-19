<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */
namespace Modules\Installer\Mapper;

use Framework\Core\ConfigSettings;
use Modules\Installer\DTO\InstallerDTO;
use Framework\Static\Hash;

class InstallerMapper
{
    public static function map(InstallerDTO $dto, ConfigSettings $config): void
    {
        $config->setDbhost($dto->dbhost);
        $config->setDbuser($dto->dbuser);
        $config->setDbpass($dto->dbpass);
        $config->setDbname($dto->dbname);
        $config->setTitle($dto->title);
        $config->setUrl($dto->url);
        $config->setDescription($dto->description);
        $config->setKeywords($dto->keywords);
        $config->setBasepath($dto->basepath ?: $config->getFullBasepath());
        $config->setLastname($dto->lastname);
        $config->setFirstname($dto->firstname);
        $config->setUsername($dto->username);
        $config->setEmail($dto->email);

        $pwd = Hash::genpwd($dto->passwd);
        $config->setPass($pwd);
    }
}
