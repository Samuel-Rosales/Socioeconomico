<?php

namespace Utils;

class LogDebugger
{
    public static function log($logEntry = [], $nameFile = 'debug')
    {
        $logLine = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . str_repeat('-', 80) . PHP_EOL;

        file_put_contents(__DIR__ . '/' . $nameFile . '.log', $logLine, FILE_APPEND | LOCK_EX);
    }
}