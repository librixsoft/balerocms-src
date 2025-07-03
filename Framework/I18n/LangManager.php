<?php

namespace Framework\I18n;

class LangManager
{
    private static array $translations = [];
    private static string $currentLang = 'en';

    public static function load(string $lang, string $path): void
    {
        $file = rtrim($path, '/') . "/$lang.php";

        if (file_exists($file)) {
            self::$translations = require $file;
            self::$currentLang = $lang;
        } else {
            self::$translations = []; // fallback vacío
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$translations[$key] ?? $default;
    }

    public static function current(): string
    {
        return self::$currentLang;
    }
}
