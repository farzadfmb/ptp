<?php

class UtilityHelper
{
    /**
     * Redirect to a URL
     */
    public static function redirect($url, $statusCode = 302)
    {
        header("Location: $url", true, $statusCode);
        exit();
    }

    /**
     * Get base URL
     */
    public static function baseUrl($path = '')
    {
        static $cachedBaseUrl = null;
        static $cachedSignature = null;

        $configuredBaseUrl = self::getConfiguredBaseUrl();

        if ($configuredBaseUrl !== null) {
            $resolvedBaseUrl = rtrim($configuredBaseUrl, '/');
        } else {
            $protocol = self::getProtocol();
            $host = self::detectHost();
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $baseDir = '';

            if ($scriptName !== '' && strpos($scriptName, '/index.php') !== false) {
                $baseDir = dirname($scriptName);
                if ($baseDir === '/' || $baseDir === '\\') {
                    $baseDir = '';
                }
            }

            $resolvedBaseUrl = rtrim(sprintf('%s://%s%s', $protocol, $host, $baseDir), '/');
        }

        $currentSignature = $resolvedBaseUrl;
        if ($cachedBaseUrl === null || $cachedSignature !== $currentSignature) {
            $cachedBaseUrl = $resolvedBaseUrl;
            $cachedSignature = $currentSignature;
        }

        if ($path !== '') {
            return $cachedBaseUrl . '/' . ltrim($path, '/');
        }

        return $cachedBaseUrl;
    }

    private static function getConfiguredBaseUrl(): ?string
    {
        static $configBaseUrl = false;

        if ($configBaseUrl !== false) {
            return $configBaseUrl ?: null;
        }

        $appConfigPath = __DIR__ . '/../../config/app.php';
        if (file_exists($appConfigPath)) {
            $config = include $appConfigPath;
            if (is_array($config)) {
                $url = $config['url'] ?? null;
                if (is_string($url) && $url !== '') {
                    $configBaseUrl = rtrim($url, '/');
                    return $configBaseUrl;
                }
            }
        }

        $envUrl = $_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL');
        if (is_string($envUrl) && $envUrl !== '') {
            $configBaseUrl = rtrim($envUrl, '/');
            return $configBaseUrl;
        }

        $configBaseUrl = '';
        return null;
    }

    private static function detectHost(): string
    {
        $forwardedHostHeader = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
        if ($forwardedHostHeader !== '') {
            $hosts = array_map('trim', explode(',', $forwardedHostHeader));
            $host = end($hosts);
            if ($host !== '') {
                return $host;
            }
        }

        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }

        if (!empty($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        if (!empty($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        return 'localhost';
    }
    
    /**
     * Get protocol (http or https)
     */
    public static function getProtocol()
    {
        if (isset($_SERVER['HTTPS'])) {
            $httpsValue = strtolower((string) $_SERVER['HTTPS']);
            if (in_array($httpsValue, ['on', '1', 'true'], true)) {
                return 'https';
            }
        }

        $forwardedHeaders = [
            'HTTP_X_FORWARDED_PROTO',
            'HTTP_X_FORWARDED_SCHEME',
            'HTTP_REQUEST_SCHEME',
        ];
        foreach ($forwardedHeaders as $headerKey) {
            if (!isset($_SERVER[$headerKey])) {
                continue;
            }
            $forwardedValue = strtolower(trim((string) $_SERVER[$headerKey]));
            if ($forwardedValue === '') {
                continue;
            }

            $protoParts = array_map('trim', explode(',', $forwardedValue));
            if (in_array('https', $protoParts, true)) {
                return 'https';
            }
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_SSL'])) {
            $forwardedSsl = strtolower((string) $_SERVER['HTTP_X_FORWARDED_SSL']);
            if (in_array($forwardedSsl, ['on', '1', 'true'], true)) {
                return 'https';
            }
        }

        if (isset($_SERVER['HTTP_CF_VISITOR'])) {
            $cfVisitor = trim((string) $_SERVER['HTTP_CF_VISITOR']);
            $decoded = json_decode($cfVisitor, true);
            if (is_array($decoded)) {
                $scheme = strtolower((string) ($decoded['scheme'] ?? ''));
                if ($scheme === 'https') {
                    return 'https';
                }
            }
        }

        if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return 'https';
        }
        
        return 'http';
    }
    
    /**
     * Get current URL
     */
    public static function currentUrl()
    {
        $protocol = self::getProtocol();
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        return "$protocol://$host$uri";
    }
    
    /**
     * Check if running on localhost
     */
    public static function isLocalhost()
    {
        $host = $_SERVER['HTTP_HOST'];
        return in_array($host, ['localhost', '127.0.0.1']) || strpos($host, 'localhost:') === 0;
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Time ago function
     */
    public static function timeAgo($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $weeks = floor($diff->d / 7);
        $days = $diff->d - ($weeks * 7);

        $string = [];
        
        if ($diff->y > 0) $string[] = $diff->y . ' سال';
        if ($diff->m > 0) $string[] = $diff->m . ' ماه';
        if ($weeks > 0) $string[] = $weeks . ' هفته';
        if ($days > 0) $string[] = $days . ' روز';
        if ($diff->h > 0) $string[] = $diff->h . ' ساعت';
        if ($diff->i > 0) $string[] = $diff->i . ' دقیقه';
        if ($diff->s > 0) $string[] = $diff->s . ' ثانیه';

        if (!$full && count($string) > 0) {
            $string = array_slice($string, 0, 1);
        }
        
        return count($string) > 0 ? implode(', ', $string) . ' پیش' : 'اکنون';
    }

    /**
     * Slugify text
     */
    public static function slugify($text)
    {
        $text = (string) $text;

        // Replace Persian/Arabic characters with English equivalents
        $persian = ['ا', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ه', 'ی'];
        $english = ['a', 'b', 'p', 't', 's', 'j', 'ch', 'h', 'kh', 'd', 'z', 'r', 'z', 'zh', 's', 'sh', 's', 'z', 't', 'z', 'a', 'gh', 'f', 'gh', 'k', 'g', 'l', 'm', 'n', 'v', 'h', 'y'];

        $text = str_replace($persian, $english, $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);

        if ($text === '') {
            return '';
        }

        return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    }

    /**
     * Debug dump and die
     */
    public static function dd($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }

    /**
     * Get client IP address
     */
    public static function getClientIP()
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Convert Persian numbers to English
     */
    public static function persianToEnglish($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        return str_replace($persian, $english, $string);
    }

    /**
     * Convert English numbers to Persian
     */
    public static function englishToPersian($string)
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        
        return str_replace($english, $persian, $string);
    }

    /**
     * Get today's date formatted in Persian locale
     */
    public static function getTodayDate($locale = 'fa_IR', $timezone = 'Asia/Tehran')
    {
        try {
            $date = new DateTime('now', new DateTimeZone($timezone));

            if (class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    $locale,
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                    $timezone,
                    IntlDateFormatter::TRADITIONAL
                );

                if ($formatter !== false) {
                    $formatter->setPattern('EEEE d MMMM yyyy');
                    $formattedDate = $formatter->format($date);

                    if ($locale === 'fa_IR') {
                        return self::englishToPersian($formattedDate);
                    }

                    return $formattedDate;
                }
            }

            $fallback = $date->format('Y/m/d');

            return $locale === 'fa_IR' ? self::englishToPersian($fallback) : $fallback;
        } catch (Exception $e) {
            return date('Y/m/d');
        }
    }
}