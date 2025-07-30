<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Http;

use Framework\Security\Security;

class RequestHelper
{
    private Security $security;

    public function __construct()
    {
        $this->security = new Security();
    }

    public function get($key, $default = null)
    {
        return $this->filter($_GET[$key] ?? $default);
    }

    public function post($key, $default = null)
    {
        return $this->filter($_POST[$key] ?? $default);
    }

    public function hasPost($key): bool
    {
        return isset($_POST[$key]);
    }

    public function request($key, $default = null)
    {
        return $this->filter($_REQUEST[$key] ?? $default);
    }

    protected function filter($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            if ($this->security && method_exists($this->security, 'antiXSS')) {
                return $this->security->sanitizeUrlSlug($value);
            }
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }
}
