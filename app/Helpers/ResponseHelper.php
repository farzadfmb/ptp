<?php

class ResponseHelper
{
    /**
     * Return JSON response
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Return success response
     */
    public static function success($message = 'عملیات با موفقیت انجام شد', $data = null, $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        self::json($response, $statusCode);
    }

    /**
     * Return error response
     */
    public static function error($message = 'خطایی رخ داده است', $errors = null, $statusCode = 400)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        self::json($response, $statusCode);
    }

    /**
     * Return validation error response
     */
    public static function validationError($errors, $message = 'داده‌های ورودی نامعتبر است')
    {
        self::error($message, $errors, 422);
    }

    /**
     * Return unauthorized response
     */
    public static function unauthorized($message = 'دسترسی غیرمجاز')
    {
        self::error($message, null, 401);
    }

    /**
     * Return forbidden response
     */
    public static function forbidden($message = 'دسترسی ممنوع')
    {
        self::error($message, null, 403);
    }

    /**
     * Return not found response
     */
    public static function notFound($message = 'صفحه یافت نشد')
    {
        self::error($message, null, 404);
    }

    /**
     * Return server error response
     */
    public static function serverError($message = 'خطای سرور')
    {
        self::error($message, null, 500);
    }

    /**
     * Render view
     */
    public static function view($viewPath, $data = [])
    {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $fullViewPath = __DIR__ . '/../../app/Views/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (file_exists($fullViewPath)) {
            include $fullViewPath;
        } else {
            throw new Exception("View file not found: $viewPath");
        }
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // If there's a layout, wrap the content
        if (isset($layout)) {
            $layoutPath = __DIR__ . '/../../app/Views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                ob_start();
                include $layoutPath;
                $content = ob_get_clean();
            }
        }
        
        echo $content;
    }

    /**
     * Set flash message
     */
    public static function flash($type, $message)
    {
        AuthHelper::startSession();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message
     */
    public static function getFlash($type = null)
    {
        AuthHelper::startSession();
        
        if ($type === null) {
            $flash = $_SESSION['flash'] ?? [];
            unset($_SESSION['flash']);
            return $flash;
        }
        
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    /**
     * Check if flash message exists
     */
    public static function hasFlash($type)
    {
        AuthHelper::startSession();
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Set success flash message
     */
    public static function flashSuccess($message)
    {
        self::flash('success', $message);
    }

    /**
     * Set error flash message
     */
    public static function flashError($message)
    {
        self::flash('error', $message);
    }

    /**
     * Set warning flash message
     */
    public static function flashWarning($message)
    {
        self::flash('warning', $message);
    }

    /**
     * Set info flash message
     */
    public static function flashInfo($message)
    {
        self::flash('info', $message);
    }

    /**
     * Redirect with flash message
     */
    public static function redirectWithFlash($url, $type, $message)
    {
        self::flash($type, $message);
        UtilityHelper::redirect($url);
    }

    /**
     * Redirect with success message
     */
    public static function redirectWithSuccess($url, $message)
    {
        self::redirectWithFlash($url, 'success', $message);
    }

    /**
     * Redirect with error message
     */
    public static function redirectWithError($url, $message)
    {
        self::redirectWithFlash($url, 'error', $message);
    }

    /**
     * Return paginated response
     */
    public static function paginated($data, $message = 'داده‌ها با موفقیت دریافت شد')
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data['data'],
            'pagination' => [
                'total' => $data['total'],
                'per_page' => $data['per_page'],
                'current_page' => $data['current_page'],
                'last_page' => $data['last_page'],
                'from' => $data['from'],
                'to' => $data['to']
            ]
        ];
        
        self::json($response);
    }
}