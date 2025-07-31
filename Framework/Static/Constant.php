<?php

namespace Framework\Static;

class Constant
{
    public const BASEPATH = LOCAL_DIR;
    public const TABLES_SQL_PATH = self::BASEPATH . "/Modules/Installer/sql/tables.sql";
    public const CONFIG_PATH = self::BASEPATH . '/resources/config/balero.config.xml';
    public const VIEWS_PATH = self::BASEPATH . '/resources/views';
    public const LANG_HELPER = self::BASEPATH . '/Framework/I18n/lang_helper.php';
    public const LANG_PATH = self::BASEPATH . '/resources/lang';
    public const UPLOADS_PATH =  self::BASEPATH . '/public/assets/images/uploads/'; // Local path
    public const REMOTE_UPLOADS_PATH =  '/public/assets/images/uploads/'; // Remote path

}
