<?php
/**
 * Environment Configuration for DigitalOcean Deployment
 * This file sets up environment-specific settings
 */

// Set error reporting based on environment
if (getenv('APP_ENV') === 'production') {
    // Production settings
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'logs/error.log');
    
    // Security settings
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
} else {
    // Development settings
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set timezone
date_default_timezone_set('UTC');

// Security headers function
function setSecurityHeaders() {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // HTTPS enforcement in production
        if (getenv('APP_ENV') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

// Call security headers
setSecurityHeaders();

// Environment-specific constants
define('IS_PRODUCTION', getenv('APP_ENV') === 'production');
define('APP_URL', IS_PRODUCTION ? 'https://your-app-name.ondigitalocean.app' : 'http://localhost:8000');

// Database configuration constants (fallback if environment variables are not set)
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DATABASE_URL') ? 'managed-db' : 'localhost');
}

// Create logs directory if it doesn't exist
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
}
?>