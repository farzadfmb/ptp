<?php

class ValidationHelper
{
    /**
     * Validate email address
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     */
    public static function isValidPassword($password, $minLength = 8)
    {
        if (strlen($password) < $minLength) {
            return false;
        }
        
        // Check for at least one uppercase, one lowercase, and one number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password);
    }

    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields)
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[$field] = "فیلد $field الزامی است";
            }
        }
        
        return $errors;
    }

    /**
     * Validate string length
     */
    public static function validateLength($string, $min = null, $max = null)
    {
        $length = mb_strlen($string, 'UTF-8');
        
        if ($min !== null && $length < $min) {
            return "حداقل $min کاراکتر مورد نیاز است";
        }
        
        if ($max !== null && $length > $max) {
            return "حداکثر $max کاراکتر مجاز است";
        }
        
        return true;
    }

    /**
     * Sanitize input data
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate Iranian mobile number
     */
    public static function isValidMobile($mobile)
    {
        return preg_match('/^09\d{9}$/', $mobile);
    }

    /**
     * Validate Iranian national code
     */
    public static function isValidNationalCode($code)
    {
        if (!preg_match('/^\d{10}$/', $code)) {
            return false;
        }
        
        $check = intval($code[9]);
        $sum = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($code[$i]) * (10 - $i);
        }
        
        $remainder = $sum % 11;
        
        return ($remainder < 2 && $check == $remainder) || ($remainder >= 2 && $check == 11 - $remainder);
    }

    /**
     * Validate numeric value
     */
    public static function isValidNumber($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        $num = floatval($value);
        
        if ($min !== null && $num < $min) {
            return false;
        }
        
        if ($max !== null && $num > $max) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate date format
     */
    public static function isValidDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}