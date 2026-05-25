<?php

namespace App\Core;

class Env
{
    private static $loadedPaths = [];

    public static function load($filePath)
    {
        $path = (string)$filePath;
        if ($path === '' || isset(self::$loadedPaths[$path]) || !is_file($path)) {
            return;
        }

        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            self::$loadedPaths[$path] = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));
            if ($key === '') {
                continue;
            }

            $len = strlen($value);
            if ($len >= 2) {
                $first = $value[0];
                $last = $value[$len - 1];
                if (($first === '"' && $last === '"') || ($first === '\'' && $last === '\'')) {
                    $value = substr($value, 1, -1);
                }
            }

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }

            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }

            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }

        self::$loadedPaths[$path] = true;
    }

    public static function get($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }
}
