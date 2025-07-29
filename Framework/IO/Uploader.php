<?php

namespace Framework\IO;

use Exception;

use Framework\Core\ConfigSettings;
use Framework\Core\ErrorConsole;

class Uploader {

    private ConfigSettings $configSettings;

    private const UPLOADS_FOLDER = '/public/assets/images/uploads/';

    public function __construct(
        ConfigSettings $configSettings
    ) {
        $this->configSettings = $configSettings;
    }

    public function image($file, $path) {
        if (!$file['error']) {
            if (!$file['error']) {
                $name = md5(rand(100, 200));
                $ext = explode('.', $file['name']);
                $filename = $name . '.' . $ext[1];
                if(!is_writable($path . self::UPLOADS_FOLDER)) {
                    ErrorConsole::handleException(new Exception("Directory " . $path . self::UPLOADS_FOLDER . " is not writable.
                    Set chmod permissions to 777."));
                }
                $destination = $path . self::UPLOADS_FOLDER . $filename; //change this directory
                $location = $file["tmp_name"];
                move_uploaded_file($location, $destination);
            } else {
                ErrorConsole::handleException(new Exception("Ooops!  Your upload triggered
                the following error:  " . $file['error']));
            }
        }
        return $this->configSettings->getBasepath($path) . self::UPLOADS_FOLDER . $filename;
    }

}