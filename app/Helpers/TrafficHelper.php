<?php

class TrafficHelper
{
    private static bool $tablesEnsured = false;
    private static bool $cleanupScheduled = false;

    /**
     * Capture the current request and update traffic telemetry.
     */
    public static function capture(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        try {
            self::ensureTables();

            if (class_exists('AuthHelper')) {
                AuthHelper::startSession();
            } elseif (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sessionId = self::resolveSessionIdentifier();
            $sessionId = self::sanitizeString($sessionId, 191);
            if ($sessionId === null) {
                return;
            }

            $now = date('Y-m-d H:i:s');
            $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
            $path = strtok($requestUri, '?') ?: '/';
            $path = self::sanitizeString($path, 255) ?? '/';
            $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
            $ip = class_exists('UtilityHelper') ? UtilityHelper::getClientIP() : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 250);
            $referer = self::sanitizeString($_SERVER['HTTP_REFERER'] ?? null, 255);
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $currentUrl = $host !== '' ? $scheme . '://' . $host . ($path ?: '/') : ($path ?: '/');
            $currentUrl = self::sanitizeString($currentUrl, 255);

            $deviceType = self::detectDeviceType($userAgent);
            $os = self::detectOperatingSystem($userAgent);
            $browser = self::detectBrowser($userAgent);

            $userId = null;
            $userName = null;
            $role = null;
            $organizationId = null;
            $isLoggedIn = 0;

            if (class_exists('AuthHelper')) {
                $userId = AuthHelper::getUserId();
                $user = AuthHelper::getUser();

                if ($userId !== null) {
                    $isLoggedIn = 1;
                }

                if (is_array($user)) {
                    $userName = $user['full_name'] ?? ($user['name'] ?? ($user['email'] ?? null));
                    $role = $user['role'] ?? ($user['role_slug'] ?? null);
                    $organizationId = $user['organization_id'] ?? null;
                }
            }

            $sessionRow = [
                'user_id' => self::toNullableInt($userId),
                'user_name' => self::sanitizeString($userName, 191),
                'organization_id' => self::toNullableInt($organizationId),
                'role' => self::sanitizeString($role, 100),
                'ip' => self::sanitizeString($ip, 64),
                'user_agent' => self::sanitizeString($userAgent, 255),
                'browser' => self::sanitizeString($browser, 100),
                'os' => self::sanitizeString($os, 100),
                'device_type' => self::sanitizeString($deviceType, 30),
                'is_logged_in' => $isLoggedIn,
                'last_path' => self::sanitizeString($path, 255),
                'last_url' => self::sanitizeString($currentUrl, 255),
                'referer' => $referer,
                'last_activity' => $now,
            ];

            $existingSession = DatabaseHelper::fetchOne(
                'SELECT id, requests_count FROM traffic_sessions WHERE session_id = :session_id LIMIT 1',
                ['session_id' => $sessionId]
            );

            if ($existingSession) {
                $sessionRow['requests_count'] = max(1, (int) ($existingSession['requests_count'] ?? 0)) + 1;
                DatabaseHelper::update('traffic_sessions', $sessionRow, 'session_id = :session_id', ['session_id' => $sessionId]);
            } else {
                $sessionRow['session_id'] = $sessionId;
                $sessionRow['requests_count'] = 1;
                $sessionRow['first_activity'] = $now;
                DatabaseHelper::insert('traffic_sessions', $sessionRow);
            }

            $eventRow = [
                'session_id' => $sessionId,
                'user_id' => self::toNullableInt($userId),
                'user_name' => self::sanitizeString($userName, 191),
                'role' => self::sanitizeString($role, 100),
                'organization_id' => self::toNullableInt($organizationId),
                'ip' => self::sanitizeString($ip, 64),
                'device_type' => self::sanitizeString($deviceType, 30),
                'os' => self::sanitizeString($os, 100),
                'browser' => self::sanitizeString($browser, 100),
                'path' => self::sanitizeString($path, 255),
                'method' => self::sanitizeString($method, 10),
                'referer' => $referer,
                'user_agent' => self::sanitizeString($userAgent, 255),
            ];

            DatabaseHelper::insert('traffic_events', $eventRow);

            self::maybeCleanup();
        } catch (Throwable $exception) {
            error_log('[TrafficHelper] capture failed: ' . $exception->getMessage());
        }
    }

    /**
     * Provide dashboard-ready traffic analytics data.
     */
    public static function getDashboardData(int $activeWindowMinutes = 10): array
    {
        $activeWindowMinutes = max(1, $activeWindowMinutes);
        $since = date('Y-m-d H:i:s', time() - ($activeWindowMinutes * 60));

        $result = [
            'summary' => [
                'online_total' => 0,
                'online_logged_in' => 0,
                'online_guests' => 0,
                'views_last_15_minutes' => 0,
                'views_last_hour' => 0,
                'unique_today' => 0,
                'page_views_today' => 0,
                'avg_session_duration' => 0,
                'avg_requests_per_session' => 0,
            ],
            'active_sessions' => [],
            'top_pages_today' => [],
            'device_breakdown' => [],
            'browser_breakdown' => [],
            'os_breakdown' => [],
            'top_referers' => [],
            'top_users' => [],
            'activity_trend' => [],
            'recent_events' => [],
            'daily_unique_trend' => [],
        ];

        try {
            self::ensureTables();

            $activeSessions = DatabaseHelper::fetchAll(
                'SELECT * FROM traffic_sessions WHERE last_activity >= :since ORDER BY last_activity DESC',
                ['since' => $since]
            );

            $mappedSessions = [];
            $totalDuration = 0;
            $totalRequests = 0;

            foreach ($activeSessions as $session) {
                $formatted = self::formatSessionRow($session);
                $mappedSessions[] = $formatted;
                $totalDuration += $formatted['session_duration_seconds'];
                $totalRequests += (int) $session['requests_count'];
            }

            $result['active_sessions'] = $mappedSessions;
            $onlineTotal = count($mappedSessions);
            $onlineLoggedIn = count(array_filter($mappedSessions, static function ($row) {
                return $row['is_logged_in'] === true;
            }));

            $result['summary']['online_total'] = $onlineTotal;
            $result['summary']['online_logged_in'] = $onlineLoggedIn;
            $result['summary']['online_guests'] = max(0, $onlineTotal - $onlineLoggedIn);
            $result['summary']['avg_session_duration'] = $onlineTotal > 0 ? (int) round($totalDuration / $onlineTotal) : 0;
            $result['summary']['avg_requests_per_session'] = $onlineTotal > 0 ? round($totalRequests / $onlineTotal, 1) : 0;

            $views15 = DatabaseHelper::fetchOne(
                'SELECT COUNT(*) AS cnt FROM traffic_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
            );
            $result['summary']['views_last_15_minutes'] = (int) ($views15['cnt'] ?? 0);

            $views60 = DatabaseHelper::fetchOne(
                'SELECT COUNT(*) AS cnt FROM traffic_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)'
            );
            $result['summary']['views_last_hour'] = (int) ($views60['cnt'] ?? 0);

            $pageViewsToday = DatabaseHelper::fetchOne(
                "SELECT COUNT(*) AS cnt FROM traffic_events WHERE DATE(created_at) = CURDATE()"
            );
            $result['summary']['page_views_today'] = (int) ($pageViewsToday['cnt'] ?? 0);

            $uniqueToday = DatabaseHelper::fetchOne(
                "SELECT COUNT(DISTINCT session_id) AS cnt FROM traffic_events WHERE DATE(created_at) = CURDATE()"
            );
            $result['summary']['unique_today'] = (int) ($uniqueToday['cnt'] ?? 0);

            $topPages = DatabaseHelper::fetchAll(
                "SELECT path, COUNT(*) AS views FROM traffic_events WHERE DATE(created_at) = CURDATE() GROUP BY path ORDER BY views DESC LIMIT 10"
            );
            $result['top_pages_today'] = self::formatTopPages($topPages, max(1, $result['summary']['page_views_today']));

            $deviceBreakdown = DatabaseHelper::fetchAll(
                'SELECT device_type, COUNT(*) AS total FROM traffic_sessions WHERE last_activity >= :since GROUP BY device_type',
                ['since' => $since]
            );
            $result['device_breakdown'] = self::formatBreakdown($deviceBreakdown, $onlineTotal, 'device_type');

            $browserBreakdown = DatabaseHelper::fetchAll(
                'SELECT browser, COUNT(*) AS total FROM traffic_sessions WHERE last_activity >= :since GROUP BY browser',
                ['since' => $since]
            );
            $result['browser_breakdown'] = self::formatBreakdown($browserBreakdown, $onlineTotal, 'browser');

            $osBreakdown = DatabaseHelper::fetchAll(
                'SELECT os, COUNT(*) AS total FROM traffic_sessions WHERE last_activity >= :since GROUP BY os',
                ['since' => $since]
            );
            $result['os_breakdown'] = self::formatBreakdown($osBreakdown, $onlineTotal, 'os');

            $topReferers = DatabaseHelper::fetchAll(
                "SELECT referer, COUNT(*) AS total FROM traffic_events WHERE referer IS NOT NULL AND referer <> '' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY referer ORDER BY total DESC LIMIT 10"
            );
            $result['top_referers'] = array_map(static function ($row) {
                return [
                    'referer' => $row['referer'],
                    'total' => (int) $row['total'],
                ];
            }, $topReferers);

            $topUsers = DatabaseHelper::fetchAll(
                "SELECT user_id, user_name, role, COUNT(*) AS views FROM traffic_events WHERE user_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY user_id, user_name, role ORDER BY views DESC LIMIT 10"
            );
            $result['top_users'] = array_map(static function ($row) {
                return [
                    'user_id' => (int) $row['user_id'],
                    'user_name' => $row['user_name'],
                    'role' => $row['role'] ?? null,
                    'views' => (int) $row['views'],
                ];
            }, $topUsers);

            $recentEvents = DatabaseHelper::fetchAll(
                'SELECT * FROM traffic_events ORDER BY created_at DESC LIMIT 30'
            );
            $result['recent_events'] = array_map([self::class, 'formatEventRow'], $recentEvents);

            $trend = DatabaseHelper::fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS bucket, COUNT(*) AS total
                 FROM traffic_events
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
                 GROUP BY bucket
                 ORDER BY bucket ASC"
            );
            $result['activity_trend'] = array_map(static function ($row) {
                return [
                    'bucket' => $row['bucket'],
                    'total' => (int) $row['total'],
                ];
            }, $trend);

            $daysBack = 14;
            $startDateObj = new DateTime('today');
            $startDateObj->modify('-' . ($daysBack - 1) . ' days');
            $startDate = $startDateObj->format('Y-m-d');

            $dailyUniqueRows = DatabaseHelper::fetchAll(
                'SELECT DATE(created_at) AS day, COUNT(DISTINCT ip) AS unique_visitors
                 FROM traffic_events
                 WHERE created_at >= :startDate
                 GROUP BY day
                 ORDER BY day ASC',
                ['startDate' => $startDate . ' 00:00:00']
            );

            $countsByDay = [];
            foreach ($dailyUniqueRows as $row) {
                $dayKey = isset($row['day']) ? (string) $row['day'] : null;
                if ($dayKey !== null && $dayKey !== '') {
                    $countsByDay[$dayKey] = (int) ($row['unique_visitors'] ?? 0);
                }
            }

            $dailyTrend = [];
            $cursor = clone $startDateObj;
            for ($i = 0; $i < $daysBack; $i++) {
                $dayKey = $cursor->format('Y-m-d');
                $dailyTrend[] = [
                    'date' => $dayKey,
                    'unique_visitors' => $countsByDay[$dayKey] ?? 0,
                ];
                $cursor->modify('+1 day');
            }

            $result['daily_unique_trend'] = $dailyTrend;
        } catch (Throwable $exception) {
            error_log('[TrafficHelper] getDashboardData failed: ' . $exception->getMessage());
        }

        return $result;
    }

    private static function ensureTables(): void
    {
        if (self::$tablesEnsured) {
            return;
        }

        $pdo = DatabaseHelper::getConnection();

        $sessionsSql = "CREATE TABLE IF NOT EXISTS `traffic_sessions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(191) NOT NULL UNIQUE,
            `user_id` INT NULL,
            `user_name` VARCHAR(191) NULL,
            `organization_id` INT NULL,
            `role` VARCHAR(100) NULL,
            `ip` VARCHAR(64) NULL,
            `user_agent` VARCHAR(255) NULL,
            `browser` VARCHAR(100) NULL,
            `os` VARCHAR(100) NULL,
            `device_type` VARCHAR(30) NULL,
            `is_logged_in` TINYINT(1) NOT NULL DEFAULT 0,
            `last_path` VARCHAR(255) NULL,
            `last_url` VARCHAR(255) NULL,
            `referer` VARCHAR(255) NULL,
            `requests_count` INT UNSIGNED NOT NULL DEFAULT 1,
            `first_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `last_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_traffic_sessions_last_activity` (`last_activity`),
            INDEX `idx_traffic_sessions_user_id` (`user_id`),
            INDEX `idx_traffic_sessions_logged_in` (`is_logged_in`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $eventsSql = "CREATE TABLE IF NOT EXISTS `traffic_events` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(191) NOT NULL,
            `user_id` INT NULL,
            `user_name` VARCHAR(191) NULL,
            `role` VARCHAR(100) NULL,
            `organization_id` INT NULL,
            `ip` VARCHAR(64) NULL,
            `device_type` VARCHAR(30) NULL,
            `os` VARCHAR(100) NULL,
            `browser` VARCHAR(100) NULL,
            `path` VARCHAR(255) NULL,
            `method` VARCHAR(10) NULL,
            `referer` VARCHAR(255) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_traffic_events_created_at` (`created_at`),
            INDEX `idx_traffic_events_session_id` (`session_id`),
            INDEX `idx_traffic_events_path` (`path`),
            INDEX `idx_traffic_events_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sessionsSql);
        $pdo->exec($eventsSql);

        self::$tablesEnsured = true;
    }

    private static function resolveSessionIdentifier(): ?string
    {
        $sessionId = session_id();
        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        $cookieName = 'traffic_vid';
        if (!empty($_COOKIE[$cookieName])) {
            return substr((string) $_COOKIE[$cookieName], 0, 191);
        }

        $generated = bin2hex(random_bytes(16));
        setcookie($cookieName, $generated, time() + (86400 * 30), '/');

        return $generated;
    }

    private static function detectDeviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent)) {
            return 'bot';
        }

        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|iphone|android|blackberry|phone|opera mini|iemobile/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function detectOperatingSystem(string $userAgent): string
    {
        $map = [
            'Windows 10/11' => '/windows nt 10\.0/i',
            'Windows 8.1' => '/windows nt 6\.3/i',
            'Windows 8' => '/windows nt 6\.2/i',
            'Windows 7' => '/windows nt 6\.1/i',
            'Windows Vista' => '/windows nt 6\.0/i',
            'Windows XP' => '/windows nt 5\.1|windows xp/i',
            'macOS' => '/macintosh|mac os x/i',
            'iOS' => '/iphone|ipad|ipod/i',
            'Android' => '/android/i',
            'Linux' => '/linux/i',
            'Chrome OS' => '/cros/i',
        ];

        foreach ($map as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $name;
            }
        }

        return 'Other';
    }

    private static function detectBrowser(string $userAgent): string
    {
        $map = [
            'Edge' => '/edg|edge/i',
            'Chrome' => '/chrome|crios/i',
            'Firefox' => '/firefox|fxios/i',
            'Safari' => '/safari/i',
            'Opera' => '/opera|opr/i',
            'Internet Explorer' => '/msie|trident/i',
        ];

        foreach ($map as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                if ($name === 'Safari' && preg_match('/chrome|crios|opr|fxios/i', $userAgent)) {
                    continue;
                }
                return $name;
            }
        }

        return 'Other';
    }

    private static function sanitizeString($value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = (string) $value;
        $string = trim($string);

        if ($string === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($string, 0, $length, 'UTF-8');
        }

        return substr($string, 0, $length);
    }

    private static function toNullableInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private static function maybeCleanup(): void
    {
        if (self::$cleanupScheduled) {
            return;
        }

        if (mt_rand(1, 200) !== 42) {
            return;
        }

        self::$cleanupScheduled = true;

        try {
            DatabaseHelper::query('DELETE FROM traffic_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 2 DAY)');
            DatabaseHelper::query('DELETE FROM traffic_events WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)');
        } catch (Throwable $exception) {
            error_log('[TrafficHelper] cleanup failed: ' . $exception->getMessage());
        }
    }

    private static function formatSessionRow(array $session): array
    {
        $lastActivity = $session['last_activity'] ?? null;
        $firstActivity = $session['first_activity'] ?? $lastActivity;

        $lastTimestamp = $lastActivity ? strtotime($lastActivity) : time();
        $firstTimestamp = $firstActivity ? strtotime($firstActivity) : $lastTimestamp;
        $durationSeconds = max(0, $lastTimestamp - $firstTimestamp);

        $durationLabel = self::formatDuration($durationSeconds);
        $lastRelative = ($lastActivity && class_exists('UtilityHelper'))
            ? UtilityHelper::timeAgo($lastActivity)
            : ($lastActivity ?? '-');

        return [
            'session_id' => $session['session_id'] ?? null,
            'user_id' => self::toNullableInt($session['user_id'] ?? null),
            'user_name' => $session['user_name'] ?? null,
            'organization_id' => self::toNullableInt($session['organization_id'] ?? null),
            'role' => $session['role'] ?? null,
            'ip' => $session['ip'] ?? null,
            'browser' => $session['browser'] ?? null,
            'os' => $session['os'] ?? null,
            'device_type' => $session['device_type'] ?? null,
            'is_logged_in' => (int) ($session['is_logged_in'] ?? 0) === 1,
            'last_path' => $session['last_path'] ?? '/',
            'last_url' => $session['last_url'] ?? null,
            'referer' => $session['referer'] ?? null,
            'requests_count' => (int) ($session['requests_count'] ?? 0),
            'last_activity' => $lastActivity,
            'first_activity' => $firstActivity,
            'session_duration_seconds' => $durationSeconds,
            'session_duration_label' => $durationLabel,
            'last_activity_relative' => $lastRelative,
        ];
    }

    private static function formatEventRow(array $event): array
    {
        $createdAt = $event['created_at'] ?? null;
        $relative = ($createdAt && class_exists('UtilityHelper'))
            ? UtilityHelper::timeAgo($createdAt)
            : ($createdAt ?? '-');

        return [
            'session_id' => $event['session_id'] ?? null,
            'user_id' => self::toNullableInt($event['user_id'] ?? null),
            'user_name' => $event['user_name'] ?? null,
            'role' => $event['role'] ?? null,
            'organization_id' => self::toNullableInt($event['organization_id'] ?? null),
            'ip' => $event['ip'] ?? null,
            'device_type' => $event['device_type'] ?? null,
            'os' => $event['os'] ?? null,
            'browser' => $event['browser'] ?? null,
            'path' => $event['path'] ?? null,
            'method' => $event['method'] ?? null,
            'referer' => $event['referer'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
            'created_at' => $createdAt,
            'created_at_relative' => $relative,
        ];
    }

    private static function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return 'کمتر از یک دقیقه';
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes === 0) {
            return $remainingSeconds . ' ثانیه';
        }

        if ($minutes < 60) {
            return $remainingSeconds === 0
                ? $minutes . ' دقیقه'
                : $minutes . ' دقیقه و ' . $remainingSeconds . ' ثانیه';
        }

        $hours = intdiv($minutes, 60);
        $minutes = $minutes % 60;

        $parts = [$hours . ' ساعت'];
        if ($minutes > 0) {
            $parts[] = $minutes . ' دقیقه';
        }

        return implode(' و ', $parts);
    }

    private static function formatTopPages(array $rows, int $total): array
    {
        if ($total <= 0) {
            $total = 1;
        }

        return array_map(static function ($row) use ($total) {
            $views = (int) ($row['views'] ?? 0);
            $percentage = round(($views / $total) * 100, 1);
            return [
                'path' => $row['path'] ?: '-',
                'views' => $views,
                'percentage' => $percentage,
            ];
        }, $rows);
    }

    private static function formatBreakdown(array $rows, int $total, string $keyName): array
    {
        if ($total <= 0) {
            $total = array_sum(array_map(static function ($row) {
                return (int) ($row['total'] ?? 0);
            }, $rows));
        }

        if ($total <= 0) {
            $total = 1;
        }

        return array_map(static function ($row) use ($total, $keyName) {
            $count = (int) ($row['total'] ?? 0);
            $label = $row[$keyName] ?? 'نامشخص';
            if ($label === null || $label === '') {
                $label = 'نامشخص';
            }

            return [
                'label' => $label,
                'total' => $count,
                'percentage' => round(($count / $total) * 100, 1),
            ];
        }, $rows);
    }
}
