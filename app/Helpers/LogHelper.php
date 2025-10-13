<?php

class LogHelper
{
    private static bool $tableEnsured = false;

    private static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        '_token',
        'remember_token',
        'api_key',
        'secret',
        'authorization',
    ];

    /**
     * Persist a log entry into the database (or fallback file if unavailable).
     */
    public static function record(string $level, string $action, array $context = [], ?string $entityType = null, $entityId = null): void
    {
        $level = strtolower(trim($level));
        if ($level === '') {
            $level = 'info';
        }

        try {
            self::ensureLogsTable();

            $userId = AuthHelper::getUserId();
            $user = AuthHelper::getUser();
            $userName = null;
            if (is_array($user)) {
                $userName = $user['name'] ?? ($user['full_name'] ?? ($user['email'] ?? null));
            }

            $ip = UtilityHelper::getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
            $requestUri = $_SERVER['REQUEST_URI'] ?? null;
            $requestPath = $requestUri ? strtok($requestUri, '?') : null;

            $contextSanitized = self::sanitizeContext($context);
            $contextJson = !empty($contextSanitized)
                ? json_encode($contextSanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;

            DatabaseHelper::insert('system_logs', [
                'level' => substr($level, 0, 30),
                'action' => substr($action, 0, 191),
                'context' => $contextJson,
                'user_id' => $userId !== null ? (int) $userId : null,
                'user_name' => $userName ? mb_substr($userName, 0, 191) : null,
                'ip' => $ip,
                'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
                'entity_type' => $entityType ? substr($entityType, 0, 100) : null,
                'entity_id' => $entityId !== null ? (string) $entityId : null,
                'request_method' => $requestMethod ? substr(strtoupper($requestMethod), 0, 10) : null,
                'request_path' => $requestPath ? substr($requestPath, 0, 255) : null,
            ]);
        } catch (Exception $exception) {
            self::writeFallbackLog($level, $action, $context, $exception);
        }
    }

    public static function info(string $action, array $context = [], ?string $entityType = null, $entityId = null): void
    {
        self::record('info', $action, $context, $entityType, $entityId);
    }

    public static function warning(string $action, array $context = [], ?string $entityType = null, $entityId = null): void
    {
        self::record('warning', $action, $context, $entityType, $entityId);
    }

    public static function error(string $action, array $context = [], ?string $entityType = null, $entityId = null): void
    {
        self::record('error', $action, $context, $entityType, $entityId);
    }

    public static function levels(): array
    {
        return [
            'info' => 'اطلاعات',
            'notice' => 'اعلان',
            'warning' => 'هشدار',
            'error' => 'خطا',
            'critical' => 'بحرانی',
        ];
    }

    /**
     * Ensure the backing database table exists before reading from it.
     */
    public static function ensureStorageReady(): void
    {
        self::ensureLogsTable();
    }

    private static function ensureLogsTable(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'system_logs'")->fetch();

        if (!$tableExists) {
            $sql = "CREATE TABLE IF NOT EXISTS `system_logs` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `level` VARCHAR(30) NOT NULL DEFAULT 'info',
                `action` VARCHAR(191) NOT NULL,
                `context` LONGTEXT NULL,
                `user_id` INT NULL,
                `user_name` VARCHAR(191) NULL,
                `ip` VARCHAR(64) NULL,
                `user_agent` VARCHAR(255) NULL,
                `entity_type` VARCHAR(100) NULL,
                `entity_id` VARCHAR(64) NULL,
                `request_method` VARCHAR(10) NULL,
                `request_path` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_system_logs_level` (`level`),
                INDEX `idx_system_logs_user_id` (`user_id`),
                INDEX `idx_system_logs_action` (`action`),
                INDEX `idx_system_logs_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $pdo->exec($sql);
        } else {
            $columns = $pdo->query("SHOW COLUMNS FROM `system_logs`")->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'Field');

            $alterStatements = [];
            if (!in_array('request_method', $columnNames, true)) {
                $alterStatements[] = "ALTER TABLE `system_logs` ADD `request_method` VARCHAR(10) NULL AFTER `user_agent`";
            }
            if (!in_array('request_path', $columnNames, true)) {
                $alterStatements[] = "ALTER TABLE `system_logs` ADD `request_path` VARCHAR(255) NULL AFTER `request_method`";
            }
            if (!in_array('created_at', $columnNames, true)) {
                $alterStatements[] = "ALTER TABLE `system_logs` ADD `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `request_path`";
            }

            foreach ($alterStatements as $statement) {
                try {
                    $pdo->exec($statement);
                } catch (Exception $exception) {
                    // Ignore column alteration errors (e.g., if already added concurrently)
                }
            }
        }

        self::$tableEnsured = true;
    }

    private static function sanitizeContext($value, array $keyPath = [])
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $originalKey = (string) $key;
                $lowerKey = strtolower($originalKey);

                if (in_array($lowerKey, self::$sensitiveKeys, true)) {
                    $sanitized[$originalKey] = '***';
                    continue;
                }

                $sanitized[$originalKey] = self::sanitizeContext($item, array_merge($keyPath, [$lowerKey]));
            }
            return $sanitized;
        }

        if (is_object($value)) {
            $value = method_exists($value, '__toString') ? (string) $value : json_decode(json_encode($value), true);
            return self::sanitizeContext($value, $keyPath);
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return $trimmed;
            }

            if (mb_strlen($trimmed, 'UTF-8') > 500) {
                return mb_substr($trimmed, 0, 497, 'UTF-8') . '...';
            }

            return $trimmed;
        }

        return $value;
    }

    private static function writeFallbackLog(string $level, string $action, array $context, Exception $exception): void
    {
        $logDir = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'action' => $action,
            'context' => $context,
            'error' => $exception->getMessage(),
        ];

        $logFile = $logDir . 'system_fallback.log';
        file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND | LOCK_EX);
    }
}
