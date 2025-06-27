<?php

class ErrorConsole
{
    public static function register()
    {
        ini_set('display_errors', -1);  // Debug: Developer (-1) / User (0)
        error_reporting(E_ALL);       // Reporta todo

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $message = "Error [$errno]: $errstr in $errfile on line $errline";
        self::renderConsole($message);
    }

    public static function handleException(Throwable $e)
    {
        $message = "Uncaught Exception: " . $e->getMessage();
        self::renderConsole($message, $e);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $message = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            self::renderConsole($message);
        }
    }

    private static function renderConsole($message, Throwable $e = null)
    {
        http_response_code(500); // Opcional: marca como error en HTTP

        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error Console</title>';
        echo '<style>
            body { background: #1e1e1e; color: #f2994a; font-family: monospace; padding: 20px; }
            .console { background: #121212; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #000; }
            .trace { color: #ccc; margin-top: 20px; font-size: 14px; }
            .trace-item { margin-bottom: 5px; }
            code { color: #8abeb7; }
        </style>';
        echo '</head><body><div class="console">';
        echo "<h2>$message</h2>";

        if ($e) {
            echo "<div class='trace'>";
            foreach ($e->getTrace() as $i => $trace) {
                $file = $trace['file'] ?? '[internal]';
                $line = $trace['line'] ?? '?';
                $func = $trace['function'] ?? '???';
                echo "<div class='trace-item'>#$i <code>$func()</code> in <code>$file</code> on line <code>$line</code></div>";
            }
            echo "</div>";
        }

        echo "</div></body></html>";
        exit;
    }
}
