<?php

namespace Framework\Security;

use Framework\Core\ConfigSettings;
use Framework\Http\RequestHelper;
use Framework\Library\Blowfish;

class LoginManager
{
    private Security $security;
    private ConfigSettings $config;
    private Blowfish $blowfish;
    private RequestHelper $request;
    private string $message = '';

    public function __construct(
        Security $security,
        ConfigSettings $config,
        Blowfish $blowfish,
        RequestHelper $request
    ) {
        $this->security = $security;
        $this->config = $config;
        $this->blowfish = $blowfish;
        $this->request = $request;
    }

    /**
     * Intenta autenticar usuario, devuelve true si login válido
     */
    public function handleLogin(): bool
    {
        $counter = $this->security->toInt($this->request->cookie('counter', 0));
        if ($counter >= 5) {
            $this->message = _LOGIN_ATTEMPS; // TODO: Return error messages
            return false;
        }

        // Intento de login por formulario
        if ($this->request->hasPost('login')) {
            $usr = $this->request->post('usr', '');
            $pwd = $this->request->post('pwd', '');

            $verify = $this->blowfish->verify_hash($pwd, $this->config->getPass());

            if ($usr === $this->config->getUsername() && $verify) {
                $value = base64_encode($usr . ':' . $this->config->getPass());
                $this->setCookie('admin_god_balero', $value, 86400); // 1 día
                return true;
            }

            // Falló el login → incrementar contador
            $this->setCookie('counter', $counter + 1, 120);
            $this->message = _LOGIN_ERROR;  // TODO: Return error messages
            return false;
        }

        // Validar cookie existente
        if ($cookie = $this->request->cookie('admin_god_balero')) {
            $decoded = base64_decode($cookie, true);
            if ($decoded !== false && str_contains($decoded, ':')) {
                [$cookieUsr, $cookiePwd] = explode(':', $decoded, 2);

                if ($cookieUsr === $this->config->getUsername() && $cookiePwd === $this->config->getPass()) {
                    return true;
                }
            }

            // Si llega aquí, la cookie no coincide
            $this->clearCookie('admin_god_balero');
            $this->message = 'Hash Error';
            return false;
        }

        return false;
    }

    /**
     * Devuelve true si ya hay login válido (cookie o post)
     */
    public function isLoggedIn(): bool
    {
        return $this->handleLogin();
    }

    /**
     * Devuelve mensaje de error
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    private function setCookie(string $name, string $value, int $lifetime): void
    {
        setcookie($name, $value, time() + $lifetime, '/', '', false, true);
    }

    /**
     * Cierra sesión
     */
    public function logout(): void
    {
        $this->clearCookie('admin_god_balero');
    }

    private function clearCookie(string $name): void
    {
        setcookie($name, '', time() - 3600, '/', '', false, true);
    }
}
