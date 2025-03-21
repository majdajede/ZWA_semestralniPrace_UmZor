<?php
/**
 * This file contains utility functions for web security.
 *
 * Functions included:
 * - escape: Escapes special characters in a string for use in HTML.
 * - csrf_token: Generates and returns a CSRF token.
 * - check_csrf_token: Checks if the provided CSRF token matches the one stored in the session.
 *
 * These functions help prevent XSS (Cross-Site Scripting) and CSRF (Cross-Site Request Forgery) attacks.
 *
 * WebSecurityUtilities
 */
if (!function_exists('escape')) {
    /**
     * Escapes special characters in a string for use in HTML.
     *
     * This function converts special characters to HTML entities to prevent
     * XSS (Cross-Site Scripting) attacks. It uses htmlspecialchars with the
     * ENT_QUOTES flag to convert both double and single quotes.
     *
     * @param string $data The input string to be escaped.
     * @return string The escaped string, safe for use in HTML.
     */
    function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generates and returns a CSRF token.
     *
     * If there is no CSRF token in the session, a new one is generated using
     * random_bytes. The token is then stored in $_SESSION['csrf_token'].
     *
     * @return string The CSRF token.
     */
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('check_csrf_token')) {
    /**
     * Checks if the provided CSRF token matches the one stored in the session.
     *
     * @param string $token The CSRF token to be checked.
     * @return bool Returns true if the token matches the session token, false otherwise.
     */
    function check_csrf_token($token) {
        return (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token));
    }
}
?>
