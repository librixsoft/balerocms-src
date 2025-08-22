<?php

/**
 * @author Anibal Gomez <balerocms@gmail.com>
 * @copyright Copyright (c) 2025 Anibal Gomez
 * @license GNU General Public License
 */


/**
 * Balero CMS - Flash helper
 *
 * Clase estática para almacenar mensajes temporales en sesión
 * (como errores y datos de formularios) siguiendo el patrón PRG.
 *
 * @author Anibal ...
 * @license GNU General Public License
 */

namespace Framework\Static;

class Flash
{
    private const FLASH_KEY = '_flash';

    /**
     * Guarda un valor en la sesión flash
     */
    public static function set(string $key, mixed $value): void
    {
        self::ensureSessionStarted();
        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    /**
     * Obtiene un valor flash (y lo elimina).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::ensureSessionStarted();

        // TODO: Add $_SESSION wrapper to RequestHelper
        if (isset($_SESSION[self::FLASH_KEY][$key])) {
            $value = $_SESSION[self::FLASH_KEY][$key];
            unset($_SESSION[self::FLASH_KEY][$key]);
            return $value;
        }

        return $default;
    }

    /**
     * Verifica si existe un valor flash.
     */
    public static function has(string $key): bool
    {
        self::ensureSessionStarted();
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    /**
     * Limpia todos los valores flash.
     */
    public static function clear(): void
    {
        self::ensureSessionStarted();
        unset($_SESSION[self::FLASH_KEY]);
    }

    /**
     * Asegura que la sesión esté iniciada.
     */
    private static function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
