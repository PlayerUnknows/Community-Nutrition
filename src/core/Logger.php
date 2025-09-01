<?php

class Logger {
    private static $logPath;
    private static $instance = null;
    
    private function __construct() {
        self::$logPath = __DIR__ . '/../view/logs/';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log info messages
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log error messages
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log debug messages
     */
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    /**
     * Log warning messages
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Main logging method
     */
    private static function log($level, $message, $context = []) {
        $logger = self::getInstance();
        
        $timestamp = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $logFile = self::$logPath . $date . '.log';
        
        // Format the log entry
        $logEntry = "[{$timestamp}] {$level}: {$message}";
        
        // Add context if provided
        if (!empty($context)) {
            $logEntry .= " | Context: " . json_encode($context);
        }
        
        $logEntry .= PHP_EOL;
        
        // Write to log file
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also output to PHP error log for compatibility
        error_log("Custom Logger [{$level}]: {$message}");
    }
    
    /**
     * Get recent logs
     */
    public static function getRecentLogs($lines = 50, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $logFile = self::$logPath . $date . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($logs, -$lines);
    }
    
    /**
     * Clear logs for a specific date
     */
    public static function clearLogs($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $logFile = self::$logPath . $date . '.log';
        
        if (file_exists($logFile)) {
            unlink($logFile);
            return true;
        }
        
        return false;
    }
}
