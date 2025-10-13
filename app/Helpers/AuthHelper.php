<?php

class AuthHelper
{
    /**
     * Start session if not already started
     */
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        self::startSession();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function getUserId()
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public static function getUser()
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Login user
     */
    public static function login($user)
    {
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['login_time'] = time();
    }

    /**
     * Logout user
     */
    public static function logout()
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        $user = self::getUser();
        return $user && isset($user['role']) && $user['role'] === $role;
    }

    /**
     * Redirect to login if not authenticated
     */
    public static function requireAuth($redirectTo = null)
    {
        if (!self::isLoggedIn()) {
            if ($redirectTo === null) {
                $redirectTo = UtilityHelper::baseUrl('user/login');
            }
            header("Location: $redirectTo");
            exit();
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken()
    {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token)
    {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}