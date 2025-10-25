<?php

class ErrorHandlerHelper
{
    private static bool $registered = false;
    private static $previousErrorHandler = null;
    private static $previousExceptionHandler = null;

    /**
     * Register global error, exception, and shutdown handlers.
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$previousErrorHandler = set_error_handler([self::class, 'handleError']);
        self::$previousExceptionHandler = set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    /**
     * Handle standard PHP errors.
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Normalize the path to avoid exposing base directory structure in logs.
        $normalizedFile = self::normalizePath($file);

        self::logContext(
            'php_error',
            [
                'message' => $message,
                'file' => $normalizedFile,
                'line' => $line,
                'severity_code' => $severity,
                'severity_label' => self::mapSeverityLabel($severity),
                'display_errors' => ini_get('display_errors'),
            ],
            self::mapSeverityLevel($severity)
        );

        if (self::$previousErrorHandler && self::$previousErrorHandler !== [self::class, 'handleError']) {
            return (bool) call_user_func(self::$previousErrorHandler, $severity, $message, $file, $line);
        }

        // Returning false lets PHP fallback to its internal handler if needed.
        return false;
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handleException(Throwable $exception): void
    {
        self::logException('uncaught_exception', $exception);

        if (self::$previousExceptionHandler && self::$previousExceptionHandler !== [self::class, 'handleException']) {
            call_user_func(self::$previousExceptionHandler, $exception);
            return;
        }

        self::renderFallbackResponse($exception);
    }

    /**
     * Handle shutdown to catch fatal errors.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        $severity = (int) ($error['type'] ?? 0);
        if (!self::isFatalSeverity($severity)) {
            return;
        }

        self::logContext(
            'fatal_error',
            [
                'message' => $error['message'] ?? '',
                'file' => self::normalizePath($error['file'] ?? ''),
                'line' => $error['line'] ?? 0,
                'severity_code' => $severity,
                'severity_label' => self::mapSeverityLabel($severity),
            ],
            'critical'
        );
    }

    /**
     * Persist exception details into the logging storage.
     */
    private static function logException(string $action, Throwable $exception): void
    {
        $context = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => self::normalizePath($exception->getFile()),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => self::buildTrace($exception),
        ];

        $previous = $exception->getPrevious();
        if ($previous !== null) {
            $context['previous_chain'] = self::buildPreviousChain($previous);
        }

        self::logContext($action, $context, 'critical');
    }

    /**
     * Common logic for persisting errors to the log storage.
     */
    private static function logContext(string $action, array $details, string $level): void
    {
        try {
            $context = [
                'details' => self::sanitizeValue($details),
                'request' => self::buildRequestSnapshot(),
                'user' => self::buildUserSnapshot(),
            ];

            if (class_exists('LogHelper')) {
                LogHelper::record($level, $action, $context, 'system', null);
            }
        } catch (Throwable $loggingException) {
            // As a final fallback, write to error_log so nothing is lost.
            error_log('[ErrorHandlerHelper] Failed to record log: ' . $loggingException->getMessage());
        }
    }

    /**
     * Build a compact trace array from an exception.
     */
    private static function buildTrace(Throwable $exception, int $depth = 15): array
    {
        $frames = [];
        $trace = $exception->getTrace();
        $depth = max(1, $depth);

        foreach ($trace as $index => $frame) {
            if ($index >= $depth) {
                break;
            }

            $frames[] = [
                'file' => isset($frame['file']) ? self::normalizePath($frame['file']) : null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
                'class' => $frame['class'] ?? null,
                'type' => $frame['type'] ?? null,
            ];
        }

        return $frames;
    }

    /**
     * Build a flattened chain of previous exceptions.
     */
    private static function buildPreviousChain(Throwable $exception, int $limit = 5): array
    {
        $chain = [];
        $depth = 0;
        $cursor = $exception;

        while ($cursor !== null && $depth < $limit) {
            $chain[] = [
                'exception_class' => get_class($cursor),
                'message' => $cursor->getMessage(),
                'file' => self::normalizePath($cursor->getFile()),
                'line' => $cursor->getLine(),
            ];

            $cursor = $cursor->getPrevious();
            $depth++;
        }

        return $chain;
    }

    /**
     * Snapshot of request information.
     */
    private static function buildRequestSnapshot(): array
    {
        $isCli = PHP_SAPI === 'cli';

        if ($isCli) {
            return [
                'environment' => 'cli',
                'script' => $_SERVER['SCRIPT_FILENAME'] ?? null,
                'arguments' => isset($_SERVER['argv']) ? self::sanitizeValue($_SERVER['argv']) : null,
            ];
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        return [
            'environment' => 'web',
            'method' => $method,
            'uri' => $uri,
            'referer' => $referer,
            'query' => self::sanitizeValue($_GET ?? []),
            'payload' => self::sanitizeValue(self::getPostPayload()),
            'ip' => class_exists('UtilityHelper') ? UtilityHelper::getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];
    }

    /**
     * Snapshot of the authenticated user (if any).
     */
    private static function buildUserSnapshot(): array
    {
        if (!class_exists('AuthHelper')) {
            return [];
        }

        $user = AuthHelper::getUser();
        $userId = AuthHelper::getUserId();

        if (!$user && $userId === null) {
            return [];
        }

        $name = null;
        if (is_array($user)) {
            $name = $user['name'] ?? ($user['full_name'] ?? null);
            if ($name === null && isset($user['first_name'], $user['last_name'])) {
                $name = trim($user['first_name'] . ' ' . $user['last_name']);
            }
        }

        return [
            'id' => $userId,
            'name' => $name,
            'role' => $user['role'] ?? null,
            'organization_id' => $user['organization_id'] ?? null,
        ];
    }

    /**
     * Determine whether the severity represents a fatal error.
     */
    private static function isFatalSeverity(int $severity): bool
    {
        return in_array($severity, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_RECOVERABLE_ERROR,
            E_USER_ERROR,
        ], true);
    }

    /**
     * Map PHP severity constants to log levels.
     */
    private static function mapSeverityLevel(int $severity): string
    {
        if (in_array($severity, [E_ERROR, E_RECOVERABLE_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE], true)) {
            return 'error';
        }

        if (in_array($severity, [E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING], true)) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Human friendly label for severity constants.
     */
    private static function mapSeverityLabel(int $severity): string
    {
        $map = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        return $map[$severity] ?? 'E_UNKNOWN';
    }

    /**
     * Gather POST payload safely.
     */
    private static function getPostPayload()
    {
        if ($_POST) {
            return $_POST;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $contentType = strtolower((string) $contentType);

        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }

                return self::limitString($raw);
            }
        }

        return null;
    }

    /**
     * Ensure strings/arrays do not exceed reasonable size in the log context.
     */
    private static function sanitizeValue($value, int $depth = 0)
    {
        $maxDepth = 4;
        if ($depth > $maxDepth) {
            return '...';
        }

        if (is_array($value)) {
            $sanitized = [];
            $count = 0;
            foreach ($value as $key => $item) {
                if ($count >= 50) {
                    $sanitized['__truncated'] = 'Array truncated to 50 items';
                    break;
                }
                $sanitized[$key] = self::sanitizeValue($item, $depth + 1);
                $count++;
            }
            return $sanitized;
        }

        if (is_object($value)) {
            if ($value instanceof Throwable) {
                return [
                    'exception_class' => get_class($value),
                    'message' => self::limitString($value->getMessage()),
                ];
            }

            if (method_exists($value, '__toString')) {
                return self::limitString((string) $value);
            }

            return self::sanitizeValue(json_decode(json_encode($value), true) ?? [], $depth + 1);
        }

        if (is_string($value)) {
            return self::limitString($value);
        }

        return $value;
    }

    /**
     * Limit string length and normalize line breaks.
     */
    private static function limitString(string $value, int $limit = 2000): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        if (mb_strlen($value, 'UTF-8') > $limit) {
            return mb_substr($value, 0, $limit, 'UTF-8') . '...';
        }
        return $value;
    }

    /**
     * Normalize path output to avoid exposing full system structure.
     */
    private static function normalizePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $basePath = realpath(__DIR__ . '/../../');

        if (is_string($basePath)) {
            $basePath = str_replace('\\', '/', $basePath);
            if (strpos($path, $basePath) === 0) {
                return '.' . substr($path, strlen($basePath));
            }
        }

        return $path;
    }

    /**
     * Render a generic response when no previous exception handler exists.
     */
    private static function renderFallbackResponse(Throwable $exception): void
    {
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "Unhandled exception: " . $exception->getMessage() . "\n");
            exit(1);
        }

        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html; charset=utf-8');
        }

        if (ini_get('display_errors')) {
            echo '<h1>Unhandled exception</h1>';
            echo '<pre>' . htmlspecialchars($exception->__toString(), ENT_QUOTES, 'UTF-8') . '</pre>';
        } else {
            echo '<h1>خطای غیرمنتظره‌ای رخ داد</h1>';
            echo '<p>لطفاً با پشتیبانی سامانه تماس بگیرید.</p>';
        }

        exit(1);
    }
}
