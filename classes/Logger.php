<?php

class Logger {
    private static $logFile = 'app.log';

   
    public static function log(string $message, string $level = 'INFO'): bool {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Output to terminal
        error_log($logMessage);
        
        // Write to file
        return file_put_contents(self::$logFile, $logMessage, FILE_APPEND) !== false;
    }

    
    public static function info(string $message): void {
        self::log($message, 'INFO');
    }

    
    public static function error(string $message): void {
        self::log($message, 'ERROR');
    }

    
    public static function warning(string $message): void {
        self::log($message, 'WARNING');
    }
}
