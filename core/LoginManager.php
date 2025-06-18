<?php

class LoginManager
{
    private $security;

    private $message;

    public function __construct($security)
    {
        $this->security = $security;
    }

    public function handleLogin()
    {
        if (isset($_COOKIE['counter'])) {
            $counter = $this->security->toInt($_COOKIE['counter']);
            if ($counter >= 5) {
                die(_LOGIN_ATTEMPS);
            }
        }

        if (!isset($_COOKIE['admin_god_balero'])) {
            if (isset($_POST['login'])) {
                $cfg = new configSettings();
                $login = new Blowfish();
                $verify = $login->verify_hash($_POST['pwd'], $cfg->pass);

                if ($_POST['usr'] == $cfg->user && $verify == TRUE) {
                    $value = base64_encode($cfg->user . ":" . $cfg->pass);
                    setcookie("admin_god_balero", $value, time() + 3600 * 24);
                    header("Location: ./admin");
                    exit;
                } else {
                    if (!isset($_COOKIE['counter'])) {
                        setcookie("counter", 1, time() + 120);
                    } else {
                        $counter = $this->security->toInt($_COOKIE['counter']);
                        setcookie("counter", $counter + 1, time() + 120);
                        echo $counter;
                    }
                    $this->message = _LOGIN_ERROR;
                }
            }
        }

        if (isset($_COOKIE['admin_god_balero'])) {
            $cfg = new configSettings();
            $login = new Blowfish();

            $cookie = base64_decode($_COOKIE['admin_god_balero']);
            $pieces = explode(":", $cookie);
            $cookie_usr = $pieces[0];
            $cookie_pwd = $pieces[1];

            if ($cfg->user == $cookie_usr && $cfg->pass == $cookie_pwd) {
                $ldr = new autoloader("admin");
                return true;
            } else {
                setcookie("admin_god_balero", "", time() - 3600);
                die("Hash Error");
            }
        }

        return false;
    }

    public function showLoginForm()
    {
        $cfg = new configSettings();
        $login = new Blowfish();
        $login->message = $this->message;
        $login->basepath = $cfg->basepath;

        echo $login->login_form(APPS_DIR . "admin/panel/login.html");
    }

    public static function logout()
    {
        if (isset($_COOKIE['admin_god_balero'])) {
            try {
                setcookie("admin_god_balero", "", time() - 3600);
                header("Location: ./admin");
            } catch (Exception $e) {
                setcookie("admin_god_balero", "", time() - 1);
            }
        }
    }
    
}
