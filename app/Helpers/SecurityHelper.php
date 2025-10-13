<?php

class SecurityHelper
{
    /**
     * Hash password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Encrypt data
     */
    public static function encrypt($data, $key = null)
    {
        if ($key === null) {
            $key = self::getEncryptionKey();
        }
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data
     */
    public static function decrypt($encryptedData, $key = null)
    {
        if ($key === null) {
            $key = self::getEncryptionKey();
        }
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Get encryption key from config or environment
     */
    private static function getEncryptionKey()
    {
        // You should store this in your config or environment variables
        return 'your-secret-encryption-key-32-chars';
    }

    /**
     * Clean input to prevent XSS
     */
    public static function cleanInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'cleanInput'], $input);
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate secure password
     */
    public static function generateSecurePassword($length = 12)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        
        // Ensure at least one character from each set
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill the rest randomly
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Rate limiting
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300)
    {
        AuthHelper::startSession();
        
        $key = 'rate_limit_' . md5($identifier);
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        // Clean old attempts
        $_SESSION[$key] = array_filter($_SESSION[$key], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }
        
        $_SESSION[$key][] = $now;
        return true;
    }

    /**
     * Check if request is from bot
     */
    public static function isBot()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $botPatterns = [
            'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
            'yandexbot', 'facebookexternalhit', 'twitterbot', 'linkedinbot',
            'whatsapp', 'telegrambot', 'crawler', 'spider', 'bot'
        ];
        
        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate file upload security
     */
    public static function validateFileUpload($file)
    {
        $errors = [];
        
        // Check file size (5MB limit)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'حجم فایل نباید بیش از 5 مگابایت باشد';
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'نوع فایل مجاز نیست';
        }
        
        // Check for malicious content in filename
        if (preg_match('/[<>:"\/\\|?*]/', $file['name'])) {
            $errors[] = 'نام فایل شامل کاراکترهای غیرمجاز است';
        }
        
        // Additional security checks for images
        if (strpos($file['type'], 'image/') === 0) {
            $imageInfo = getimagesize($file['tmp_name']);
            if (!$imageInfo) {
                $errors[] = 'فایل تصویری معتبر نیست';
            }
        }
        
        return $errors;
    }

    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = [])
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => UtilityHelper::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => AuthHelper::getUserId(),
            'details' => $details
        ];
        
        $logDir = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'security_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Check for SQL injection patterns
     */
    public static function detectSQLInjection($input)
    {
        $patterns = [
            '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)(\s|$)/i',
            '/(\s|^)(or|and)(\s|$)[\'"]\s*[\'"]/i',
            '/[\'"](\s|;)*(union|select|insert|update|delete)/i',
            '/[\'"];[\s]*--/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
}