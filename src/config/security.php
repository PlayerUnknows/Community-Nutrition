<?php
/**
 * Security Configuration
 * This file contains security settings and functions for the application
 */

// Force HTTPS in production
function enforceHTTPS() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location: $redirect");
            exit();
        }
    }
}

// Set security headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdn.jsdelivr.net; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";
    
    header("Content-Security-Policy: $csp");
    
    // Strict Transport Security (HSTS) - only for HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

// Sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Generate secure random token
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Validate CSRF token
function validateCSRFToken($token) {
    if (empty($token)) {
        return false;
    }
    
    // Check if token format is valid
    if (!preg_match('/^csrf_[a-zA-Z0-9]{9}_\d+$/', $token)) {
        return false;
    }
    
    return true;
}

// Rate limiting function
function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 900) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $ip = getClientIP();
    $rateLimitKey = "rate_limit_{$key}_{$ip}";
    $lockoutKey = "lockout_{$key}_{$ip}";
    
    // Check if IP is locked out
    if (isset($_SESSION[$lockoutKey])) {
        $lockoutTime = $_SESSION[$lockoutKey];
        if (time() - $lockoutTime < $timeWindow) {
            return false;
        } else {
            // Clear lockout if time has passed
            unset($_SESSION[$lockoutKey]);
            unset($_SESSION[$rateLimitKey]);
        }
    }
    
    // Check number of attempts
    $attempts = isset($_SESSION[$rateLimitKey]) ? $_SESSION[$rateLimitKey] : 0;
    if ($attempts >= $maxAttempts) {
        $_SESSION[$lockoutKey] = time();
        return false;
    }
    
    return true;
}

// Log rate limit attempt
function logRateLimitAttempt($key) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $ip = getClientIP();
    $rateLimitKey = "rate_limit_{$key}_{$ip}";
    
    $attempts = isset($_SESSION[$rateLimitKey]) ? $_SESSION[$rateLimitKey] : 0;
    $_SESSION[$rateLimitKey] = $attempts + 1;
}

// Clear rate limit
function clearRateLimit($key) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $ip = getClientIP();
    $rateLimitKey = "rate_limit_{$key}_{$ip}";
    $lockoutKey = "lockout_{$key}_{$ip}";
    
    unset($_SESSION[$rateLimitKey]);
    unset($_SESSION[$lockoutKey]);
}

// Get client IP address
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Log security events
function logSecurityEvent($event, $details = '', $level = 'INFO') {
    $logFile = __DIR__ . '/../../logs/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logEntry = "[$timestamp] [$level] [$ip] [$event] $details User-Agent: $userAgent\n";
    
    // Ensure logs directory exists
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Initialize security
function initSecurity() {
    // Enforce HTTPS in production
    enforceHTTPS();
    
    // Set security headers
    setSecurityHeaders();
    
    // Start session with secure settings
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

// Call security initialization
initSecurity();
?>
