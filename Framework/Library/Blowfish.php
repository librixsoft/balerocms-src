<?php

namespace Framework\Library;

class Blowfish
{

    private $pwd;
    private $pwd_string;

    public $message;
    public $basepath;

    public function genpwd($pwd = "")
    {

        /**
         *
         * generar salt
         */

        $salt = "";
        $salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));

        for ($i = 0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }

        return crypt($pwd, sprintf('$2a$%02d$', 7) . $salt);

    }

    public function verify_hash($text, $hash)
    {

        if (crypt($text, $hash) == $hash) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    /**
     *
     * require ThemeLoder class
     */

    public function login_form($view)
    {


        /**
         * Debug
         */

        //echo $view;

        /**
         *
         * Login view {vars}
         */

        $array = array(
            'message' => $this->message,
            'basepath' => $this->basepath
        );


        /**
         *
         * Render page
         */


        //require_once(LOCAL_DIR . "/core/ThemeLoader.php");

        try {

            //if(!file_exists($view)) {
            //throw new Exception(_THEME_DONT_EXIST);
            //}

            $objTheme = new ThemeLoader($view);

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }

        return $objTheme->renderPage($array);

    }


}