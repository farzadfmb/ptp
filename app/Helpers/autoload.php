<?php

/**
 * Helper Autoloader
 * Include this file to automatically load all helper classes
 */

// Define the helpers directory
$helpersDir = __DIR__;

// Array of helper files to include
$helperFiles = [
    'AuthHelper.php',
    'ValidationHelper.php',
    'UtilityHelper.php',
    'DatabaseHelper.php',
    'FileHelper.php',
    'SecurityHelper.php',
    'ResponseHelper.php',
    'LogHelper.php',
    'PermissionHelper.php',
    'ErrorHandlerHelper.php',
    'TrafficHelper.php',
    'JalaliHelper.php'
];

// Include all helper files
foreach ($helperFiles as $file) {
    $filePath = $helpersDir . '/' . $file;
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

/**
 * Convenience functions that can be used globally
 */

if (!function_exists('dd')) {
    function dd($data) {
        UtilityHelper::dd($data);
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $statusCode = 302) {
        UtilityHelper::redirect($url, $statusCode);
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl($path = '') {
        return UtilityHelper::baseUrl($path);
    }
}

if (!function_exists('auth')) {
    function auth() {
        return [
            'check' => function() { return AuthHelper::isLoggedIn(); },
            'user' => function() { return AuthHelper::getUser(); },
            'id' => function() { return AuthHelper::getUserId(); },
            'logout' => function() { AuthHelper::logout(); }
        ];
    }
}

if (!function_exists('flash')) {
    function flash($type = null, $message = null) {
        if ($message !== null) {
            ResponseHelper::flash($type, $message);
        } else {
            return ResponseHelper::getFlash($type);
        }
    }
}

if (!function_exists('old')) {
    function old($key, $default = '') {
        AuthHelper::startSession();
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        return AuthHelper::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        $token = csrf_token();
        return "<input type='hidden' name='_token' value='$token'>";
    }
}