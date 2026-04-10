<?php
session_start();

/**
 * Generate a CSRF token and store it in the session
 * 
 * @return string The generated CSRF token
 */
function generate_csrf_token() {
    // Generate a cryptographically secure random token
    $token = bin2hex(random_bytes(32));
    
    // Store the token in the session
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

/**
 * Validate a CSRF token against the one stored in the session
 * 
 * @param string $token The token to validate
 * @param int $max_age Maximum age of token in seconds (default: 3600 = 1 hour)
 * @return bool True if the token is valid, false otherwise
 */
function validate_csrf_token($token, $max_age = 3600) {
    // Check if token exists in session
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token has expired
    if (time() - $_SESSION['csrf_token_time'] > $max_age) {
        // Token has expired, remove it
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    // Validate token using constant time comparison to prevent timing attacks
    if (hash_equals($_SESSION['csrf_token'], $token)) {
        // Valid token, remove it to prevent reuse
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return true;
    }
    
    return false;
}