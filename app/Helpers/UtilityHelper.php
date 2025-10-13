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
        static $baseUrl = null;
        
        if ($baseUrl === null) {
            $protocol = self::getProtocol();
            $host = $_SERVER['HTTP_HOST'];
            
            // Get the directory where index.php is located
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $requestUri = $_SERVER['REQUEST_URI'];
            
            // If we're accessing through a subdirectory (like /ptp/)
            $baseDir = '';
            if (strpos($scriptName, '/index.php') !== false) {
                $baseDir = dirname($scriptName);
                if ($baseDir === '/' || $baseDir === '\\') {
                    $baseDir = '';
                }
            }
            
            $baseUrl = "$protocol://$host$baseDir";
        }
        
        if ($path) {
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        
        return rtrim($baseUrl, '/');
    }
    
    /**
     * Get protocol (http or https)
     */
    public static function getProtocol()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return 'https';
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return 'https';
        }
        
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
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