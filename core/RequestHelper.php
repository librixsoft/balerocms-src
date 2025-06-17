<?php

class RequestHelper
{
    protected $security;

    public function __construct($security = null)
    {
        $this->security = $security;
    }

    public function get($key, $default = null)
    {
        return $this->filter($_GET[$key] ?? $default);
    }

    public function post($key, $default = null)
    {
        return $this->filter($_POST[$key] ?? $default);
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
                return $this->security->antiXSS($value);
            }
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }
}
