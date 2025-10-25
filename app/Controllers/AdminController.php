<?php

class AdminController {
    
    /**
     * Show the admin dashboard
     */
    public function dashboard() {
        $this->ensureAdminSession();

        // Check if user is logged in and has admin privileges
        // TODO: Uncomment this when authentication system is fully implemented
        // if (!AuthHelper::isLoggedIn()) {
        //     header('Location: ' . UtilityHelper::baseUrl('user/login'));
        //     exit;
        // }
        
        // $user = AuthHelper::getUser();
        
        // You can add admin role check here
        // if ($user['role'] !== 'admin') {
        //     header('Location: ' . UtilityHelper::baseUrl());
        //     exit;
        // }
        
        // Set any data needed for the dashboard
        $dashboardData = [
            'total_users' => 150,
            'total_courses' => 45,
            'total_revenue' => 25000,
            'active_sessions' => 23
        ];
        
        // Include the dashboard view
        include __DIR__ . '/../Views/supperAdmin/dashboard/index.php';
    }
    
    /**
     * Show admin profile settings
     */
    public function settings()
    {
        $this->ensureAdminSession();
        $this->ensureGeneralSettingsTable();

        AuthHelper::startSession();

        $settings = $this->getGeneralSettings();
        $fallbackDefaultAvatarPath = $this->getDefaultAvatarFallbackPath();

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

    $successMessage = flash('success');
    $errorMessage = flash('error');
    $warningMessage = flash('warning');
    $infoMessage = flash('info');

        $languageOptions = [
            'fa' => 'فارسی (پیش‌فرض)',
            'en' => 'English',
            'ar' => 'العربية'
        ];

        $timezoneOptions = [
            'Asia/Tehran' => 'Asia/Tehran (GMT+03:30)',
            'UTC' => 'UTC',
            'Europe/Berlin' => 'Europe/Berlin',
            'America/New_York' => 'America/New_York'
        ];

        include __DIR__ . '/../Views/supperAdmin/settings/general.php';
    }

    public function updateGeneralSettings()
    {
        $this->ensureAdminSession();

        $this->ensureGeneralSettingsTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/settings/general');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $allowedLanguages = ['fa', 'en', 'ar'];
        $allowedTimezones = ['Asia/Tehran', 'UTC', 'Europe/Berlin', 'America/New_York'];

        $hasLogoUpload = isset($_FILES['system_logo']) && !empty($_FILES['system_logo']['name']);
        $hasDefaultAvatarUpload = isset($_FILES['system_default_avatar']) && !empty($_FILES['system_default_avatar']['name']);
        $resetDefaultAvatar = isset($_POST['reset_system_default_avatar']) && $_POST['reset_system_default_avatar'] === '1';

        $rawData = [
            'site_name' => trim($_POST['site_name'] ?? ''),
            'site_tagline' => trim($_POST['site_tagline'] ?? ''),
            'support_email' => trim($_POST['support_email'] ?? ''),
            'support_phone' => trim($_POST['support_phone'] ?? ''),
            'default_language' => trim($_POST['default_language'] ?? ''),
            'timezone' => trim($_POST['timezone'] ?? ''),
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
            'allow_registration' => isset($_POST['allow_registration']) ? '1' : '0',
            'analytics_script' => trim($_POST['analytics_script'] ?? ''),
            'dashboard_welcome_message' => trim($_POST['dashboard_welcome_message'] ?? ''),
            'reset_system_default_avatar' => $resetDefaultAvatar ? '1' : '0',
        ];

        $_SESSION['old_input'] = $rawData;

        $validationErrors = [];

        if ($rawData['site_name'] === '') {
            $validationErrors['site_name'] = 'نام سیستم الزامی است.';
        }

        if ($rawData['support_email'] !== '' && !filter_var($rawData['support_email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors['support_email'] = 'ایمیل پشتیبانی معتبر نیست.';
        }

        if ($rawData['support_phone'] !== '' && !preg_match('/^[0-9+\-\s()]+$/u', $rawData['support_phone'])) {
            $validationErrors['support_phone'] = 'فرمت شماره تماس معتبر نیست.';
        }

        if (!in_array($rawData['default_language'], $allowedLanguages, true)) {
            $validationErrors['default_language'] = 'زبان انتخاب شده معتبر نیست.';
        }

        if (!in_array($rawData['timezone'], $allowedTimezones, true)) {
            $validationErrors['timezone'] = 'منطقه زمانی انتخاب شده معتبر نیست.';
        }

        if ($hasLogoUpload && !FileHelper::isValidImage($_FILES['system_logo'])) {
            $validationErrors['system_logo'] = 'فایل لوگوی انتخاب شده معتبر نیست.';
        }

        if ($hasDefaultAvatarUpload && !FileHelper::isValidImage($_FILES['system_default_avatar'])) {
            $validationErrors['system_default_avatar'] = 'فایل تصویر پیش‌فرض انتخاب شده معتبر نیست.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $currentSettings = $this->getGeneralSettings();
        $systemLogoPath = $currentSettings['system_logo_path'] ?? null;
        $fallbackDefaultAvatarPath = $this->getDefaultAvatarFallbackPath();
        $previousSystemDefaultAvatarPath = $currentSettings['system_default_avatar_path'] ?? null;
        $systemDefaultAvatarPath = $previousSystemDefaultAvatarPath ?: $fallbackDefaultAvatarPath;

        if ($hasLogoUpload) {
            $upload = FileHelper::uploadFile($_FILES['system_logo'], 'uploads/system/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                if (!empty($systemLogoPath) && $systemLogoPath !== $upload['path']) {
                    FileHelper::deleteFile($systemLogoPath);
                }
                $systemLogoPath = $upload['path'];
            } else {
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($redirectUrl);
            }
        }

        if ($hasDefaultAvatarUpload) {
            $upload = FileHelper::uploadFile($_FILES['system_default_avatar'], 'uploads/system/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                if (!empty($previousSystemDefaultAvatarPath) && $previousSystemDefaultAvatarPath !== $upload['path'] && $previousSystemDefaultAvatarPath !== $fallbackDefaultAvatarPath) {
                    FileHelper::deleteFile($previousSystemDefaultAvatarPath);
                }
                $systemDefaultAvatarPath = $upload['path'];
            } else {
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($redirectUrl);
            }
        } elseif ($resetDefaultAvatar) {
            if (!empty($previousSystemDefaultAvatarPath) && $previousSystemDefaultAvatarPath !== $fallbackDefaultAvatarPath) {
                FileHelper::deleteFile($previousSystemDefaultAvatarPath);
            }
            $systemDefaultAvatarPath = $fallbackDefaultAvatarPath;
        }

        $settingsToSave = [
            'site_name' => $rawData['site_name'],
            'site_tagline' => $rawData['site_tagline'],
            'support_email' => $rawData['support_email'],
            'support_phone' => $rawData['support_phone'],
            'default_language' => $rawData['default_language'],
            'timezone' => $rawData['timezone'],
            'maintenance_mode' => $rawData['maintenance_mode'] === '1',
            'allow_registration' => $rawData['allow_registration'] === '1',
            'analytics_script' => $rawData['analytics_script'],
            'dashboard_welcome_message' => $rawData['dashboard_welcome_message'],
            'system_logo_path' => $systemLogoPath,
            'system_default_avatar_path' => ($systemDefaultAvatarPath && $systemDefaultAvatarPath !== $fallbackDefaultAvatarPath) ? $systemDefaultAvatarPath : null,
        ];

        try {
            $this->saveGeneralSettings($settingsToSave);
        } catch (Exception $exception) {
            ResponseHelper::flashError('در ذخیره تنظیمات خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('تنظیمات با موفقیت ذخیره شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function securityLogs()
    {
        $this->ensureAdminSession();

        AuthHelper::startSession();

        $levels = LogHelper::levels();

        try {
            LogHelper::ensureStorageReady();
        } catch (Exception $exception) {
            ResponseHelper::flashError('در آماده‌سازی جدول لاگ‌ها خطایی رخ داد: ' . $exception->getMessage());
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $levelFilter = trim($_GET['level'] ?? '');
        $methodFilter = strtoupper(trim($_GET['method'] ?? ''));
        $keyword = trim($_GET['keyword'] ?? '');

        $perPage = 20;
        $whereClauses = ['1=1'];
        $params = [];

        if ($levelFilter !== '' && array_key_exists($levelFilter, $levels)) {
            $whereClauses[] = 'level = :level';
            $params['level'] = $levelFilter;
        }

        if ($methodFilter !== '' && in_array($methodFilter, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $whereClauses[] = 'request_method = :method';
            $params['method'] = $methodFilter;
        }

        if ($keyword !== '') {
            $whereClauses[] = '(action LIKE :keyword OR user_name LIKE :keyword OR request_path LIKE :keyword OR ip LIKE :keyword OR context LIKE :keyword)';
            $params['keyword'] = '%' . $keyword . '%';
        }

        $where = implode(' AND ', $whereClauses);

        try {
            LogHelper::info('security.logs_viewed', [
                'level' => $levelFilter,
                'method' => $methodFilter,
                'keyword' => $keyword,
                'page' => $page,
            ], 'security_view');

            $logsPagination = DatabaseHelper::paginate('system_logs', $page, $perPage, $where, $params, 'created_at DESC');
        } catch (Exception $exception) {
            $logsPagination = [
                'data' => [],
                'total' => 0,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => 1,
                'from' => 0,
                'to' => 0,
            ];
            ResponseHelper::flashError('در دریافت لاگ‌ها خطایی رخ داد: ' . $exception->getMessage());
        }

        foreach ($logsPagination['data'] as &$log) {
            $log['context_array'] = $this->decodeLogContext($log['context'] ?? null);
            $log['level_label'] = $levels[$log['level']] ?? $log['level'];
            $log['created_at_formatted'] = $this->formatLogDate($log['created_at'] ?? null);
            $log['created_at_relative'] = $this->formatLogRelative($log['created_at'] ?? null);
        }
        unset($log);

        $stats = $this->getLogStatistics();
        $recentSecurityEvents = $this->readSecurityLogFiles(30);
        $fallbackEvents = $this->readFallbackLogEntries(30);

        $successMessage = flash('success');
        $errorMessage = flash('error');
        $warningMessage = flash('warning');
        $infoMessage = flash('info');

        include __DIR__ . '/../Views/supperAdmin/settings/security.php';
    }

    public function trafficReport()
    {
        $this->ensureAdminSession();

        $window = isset($_GET['window']) ? (int) $_GET['window'] : 10;
        if ($window <= 0) {
            $window = 10;
        }

        $trafficData = class_exists('TrafficHelper')
            ? TrafficHelper::getDashboardData($window)
            : [
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
            LogHelper::info('traffic.report_viewed', [
                'window_minutes' => $window,
                'online_total' => $trafficData['summary']['online_total'] ?? 0,
            ], 'traffic_dashboard');
        } catch (Exception $exception) {
            // Logging failures should not block the dashboard.
        }

        $activeWindowMinutes = $window;

        include __DIR__ . '/../Views/supperAdmin/traffic/report.php';
    }

    public function trafficReportData()
    {
        $this->ensureAdminSession();

        $window = isset($_GET['window']) ? (int) $_GET['window'] : 10;
        if ($window <= 0) {
            $window = 10;
        }

        $trafficData = class_exists('TrafficHelper')
            ? TrafficHelper::getDashboardData($window)
            : [
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

        ResponseHelper::json([
            'success' => true,
            'generated_at' => date('Y-m-d H:i:s'),
            'window_minutes' => $window,
            'data' => $trafficData,
        ]);
    }

    public function roles()
    {
        $this->ensureAdminSession();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $organizations = DatabaseHelper::fetchAll('SELECT id, name FROM organizations ORDER BY name ASC');
        $roles = DatabaseHelper::fetchAll('SELECT r.*, o.name AS organization_name FROM roles r LEFT JOIN organizations o ON r.organization_id = o.id ORDER BY r.created_at DESC');
        $permissionsCatalog = $this->getPermissionsCatalog();

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');

        include __DIR__ . '/../Views/supperAdmin/roles/index.php';
    }

    public function storeRole()
    {
        $this->ensureAdminSession();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/roles');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $isCoreSuperAdmin = (($existingRole['slug'] ?? '') === 'super-admin') || (($existingRole['scope_type'] ?? '') === 'superadmin');

        $rawData = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'scope_type' => trim($_POST['scope_type'] ?? ''),
            'organization_id' => isset($_POST['organization_id']) ? (int) $_POST['organization_id'] : null,
            'permissions' => isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [],
        ];

        $isCoreSuperAdmin = (($existingRole['slug'] ?? '') === 'super-admin') || (($existingRole['scope_type'] ?? '') === 'superadmin');

        if ($isCoreSuperAdmin) {
            $rawData['scope_type'] = 'superadmin';
            $rawData['organization_id'] = null;
        }

        $permissionsCatalog = $this->getPermissionsCatalog();
        $allPermissions = [];
        foreach ($permissionsCatalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $allPermissions[$permission] = true;
            }
        }

        $sanitizedPermissions = [];
        foreach ($rawData['permissions'] as $permission) {
            if (isset($allPermissions[$permission])) {
                $sanitizedPermissions[$permission] = $permission;
            }
        }
        $rawData['permissions'] = array_values($sanitizedPermissions);

        if ($isCoreSuperAdmin) {
            $rawData['scope_type'] = 'superadmin';
            $rawData['organization_id'] = null;
        }

        $_SESSION['old_input'] = [
            'name' => $rawData['name'],
            'description' => $rawData['description'],
            'scope_type' => $rawData['scope_type'],
            'organization_id' => $rawData['organization_id'],
            'permissions' => $rawData['permissions'],
        ];

        $validationErrors = [];
    $allowedScopes = ['global', 'organization', 'superadmin'];

        if ($rawData['name'] === '') {
            $validationErrors['name'] = 'نام نقش الزامی است.';
        }

        if (!in_array($rawData['scope_type'], $allowedScopes, true)) {
            $validationErrors['scope_type'] = 'نوع سطح دسترسی انتخاب شده معتبر نیست.';
        }

        if ($rawData['scope_type'] === 'organization') {
            if (empty($rawData['organization_id']) || $rawData['organization_id'] <= 0) {
                $validationErrors['organization_id'] = 'برای نقش‌های سازمانی باید یک سازمان انتخاب کنید.';
            } else {
                $organizationExists = DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']]);
                if (!$organizationExists) {
                    $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
                }
            }
        } else {
            $rawData['organization_id'] = null;
        }

        if (!empty($rawData['name'])) {
            $slug = UtilityHelper::slugify($rawData['name']);
            if ($slug === '') {
                $slug = strtolower(str_replace(' ', '-', $rawData['name']));
            }

            $duplicateRole = DatabaseHelper::exists('roles', 'slug = :slug', ['slug' => $slug]);
            if ($duplicateRole) {
                $validationErrors['name'] = 'نقشی با این نام قبلاً تعریف شده است.';
            }
        }

        if (empty($rawData['permissions'])) {
            $validationErrors['permissions'] = 'حداقل یک دسترسی باید انتخاب شود.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $slug = UtilityHelper::slugify($rawData['name']);
        if ($slug === '') {
            $slug = strtolower(str_replace(' ', '-', UtilityHelper::persianToEnglish($rawData['name'])));
        }

        $permissionsJson = json_encode(array_values($rawData['permissions']), JSON_UNESCAPED_UNICODE);

        DatabaseHelper::insert('roles', [
            'name' => $rawData['name'],
            'slug' => $slug,
            'description' => $rawData['description'] !== '' ? $rawData['description'] : null,
            'scope_type' => $rawData['scope_type'],
            'organization_id' => $rawData['organization_id'],
            'permissions' => $permissionsJson,
        ]);

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('نقش جدید با موفقیت ثبت شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function editRole()
    {
        $this->ensureAdminSession();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $roleId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($roleId <= 0) {
            ResponseHelper::flashError('نقش مورد نظر یافت نشد.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/roles'));
        }

        $role = DatabaseHelper::fetchOne('SELECT * FROM roles WHERE id = :id LIMIT 1', ['id' => $roleId]);
        if (!$role) {
            ResponseHelper::flashError('نقش انتخاب شده وجود ندارد.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/roles'));
        }

        $organizations = DatabaseHelper::fetchAll('SELECT id, name FROM organizations ORDER BY name ASC');
        $permissionsCatalog = $this->getPermissionsCatalog();
        $rolePermissions = json_decode($role['permissions'] ?? '[]', true) ?: [];

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');

        include __DIR__ . '/../Views/supperAdmin/roles/edit.php';
    }

    public function updateRole()
    {
        $this->ensureAdminSession();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $roleId = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;
        $redirectUrl = UtilityHelper::baseUrl('supperadmin/roles');
        $editUrl = UtilityHelper::baseUrl('supperadmin/roles/edit') . '?id=' . $roleId;

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ($roleId <= 0) {
            ResponseHelper::flashError('شناسه نقش معتبر نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        $existingRole = DatabaseHelper::fetchOne('SELECT * FROM roles WHERE id = :id LIMIT 1', ['id' => $roleId]);
        if (!$existingRole) {
            ResponseHelper::flashError('نقش انتخاب شده وجود ندارد.');
            UtilityHelper::redirect($redirectUrl);
        }

        $isCoreSuperAdmin = (($existingRole['slug'] ?? '') === 'super-admin') || (($existingRole['scope_type'] ?? '') === 'superadmin');

        $rawData = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'scope_type' => trim($_POST['scope_type'] ?? ''),
            'organization_id' => isset($_POST['organization_id']) ? (int) $_POST['organization_id'] : null,
            'permissions' => isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : [],
        ];

        $permissionsCatalog = $this->getPermissionsCatalog();
        $allPermissions = [];
        foreach ($permissionsCatalog as $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                $allPermissions[$permission] = true;
            }
        }

        $sanitizedPermissions = [];
        foreach ($rawData['permissions'] as $permission) {
            if (isset($allPermissions[$permission])) {
                $sanitizedPermissions[$permission] = $permission;
            }
        }
        $rawData['permissions'] = array_values($sanitizedPermissions);

        if ($isCoreSuperAdmin) {
            $rawData['scope_type'] = 'superadmin';
            $rawData['organization_id'] = null;
        }

        $_SESSION['old_input'] = [
            'name' => $rawData['name'],
            'description' => $rawData['description'],
            'scope_type' => $rawData['scope_type'],
            'organization_id' => $rawData['organization_id'],
            'permissions' => $rawData['permissions'],
        ];

        $validationErrors = [];
        $allowedScopes = ['global', 'organization', 'superadmin'];

        if ($rawData['name'] === '') {
            $validationErrors['name'] = 'نام نقش الزامی است.';
        }

        if (!in_array($rawData['scope_type'], $allowedScopes, true)) {
            $validationErrors['scope_type'] = 'نوع سطح دسترسی انتخاب شده معتبر نیست.';
        }

        if ($rawData['scope_type'] === 'organization') {
            if (empty($rawData['organization_id']) || $rawData['organization_id'] <= 0) {
                $validationErrors['organization_id'] = 'برای نقش‌های سازمانی باید یک سازمان انتخاب کنید.';
            } else {
                $organizationExists = DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']]);
                if (!$organizationExists) {
                    $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
                }
            }
        } else {
            $rawData['organization_id'] = null;
        }

        if (empty($rawData['permissions'])) {
            $validationErrors['permissions'] = 'حداقل یک دسترسی باید انتخاب شود.';
        }

        if ($isCoreSuperAdmin) {
            $slug = 'super-admin';
        } else {
            $slug = UtilityHelper::slugify($rawData['name']);
            if ($slug === '') {
                $slug = strtolower(str_replace(' ', '-', UtilityHelper::persianToEnglish($rawData['name'])));
            }
        }

        if (!empty($slug)) {
            $duplicateRole = DatabaseHelper::exists('roles', 'slug = :slug AND id != :id', ['slug' => $slug, 'id' => $roleId]);
            if ($duplicateRole) {
                $validationErrors['name'] = 'نقشی با این نام قبلاً تعریف شده است.';
            }
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($editUrl);
        }

        $permissionsJson = json_encode(array_values($rawData['permissions']), JSON_UNESCAPED_UNICODE);

        try {
            DatabaseHelper::update('roles', [
                'name' => $rawData['name'],
                'slug' => $slug,
                'description' => $rawData['description'] !== '' ? $rawData['description'] : null,
                'scope_type' => $rawData['scope_type'],
                'organization_id' => $rawData['organization_id'],
                'permissions' => $permissionsJson,
            ], 'id = :id', ['id' => $roleId]);
        } catch (Exception $exception) {
            ResponseHelper::flashError('در به‌روزرسانی نقش خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($editUrl);
        }

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('نقش با موفقیت بروزرسانی شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function deleteRole()
    {
        $this->ensureAdminSession();
        $this->ensureRolesTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/roles');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $roleId = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;
        if ($roleId <= 0) {
            ResponseHelper::flashError('شناسه نقش معتبر نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        $role = DatabaseHelper::fetchOne('SELECT id, name, slug, scope_type FROM roles WHERE id = :id LIMIT 1', ['id' => $roleId]);
        if (!$role) {
            ResponseHelper::flashError('نقش انتخاب شده وجود ندارد.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (($role['slug'] ?? '') === 'super-admin') {
            ResponseHelper::flashError('حذف نقش سوپر ادمین مجاز نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        try {
            DatabaseHelper::delete('roles', 'id = :id', ['id' => $roleId]);
        } catch (Exception $exception) {
            ResponseHelper::flashError('در حذف نقش خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        ResponseHelper::flashSuccess('نقش با موفقیت حذف شد.');
        UtilityHelper::redirect($redirectUrl);
    }
    
    /**
     * Handle admin logout
     */
    public function logout() {
        AuthHelper::logout();
        header('Location: ' . UtilityHelper::baseUrl('supperadmin/login'));
        exit;
    }
    
    /**
     * Show users management page
     */
    public function users()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $successMessage = flash('success');
        $errorMessage = flash('error');

        try {
            $users = DatabaseHelper::fetchAll(
                'SELECT u.*, r.name AS role_name, r.slug AS role_slug, o.name AS organization_name
                 FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 LEFT JOIN organizations o ON u.organization_id = o.id
                 ORDER BY u.created_at DESC'
            );
        } catch (Exception $exception) {
            $users = [];
            $errorMessage = $errorMessage ?: 'در واکشی فهرست کاربران خطایی رخ داد: ' . $exception->getMessage();
        }

        include __DIR__ . '/../Views/supperAdmin/users/index.php';
    }

    public function createUser()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $successMessage = flash('success');
        $errorMessage = flash('error');

        try {
            $roles = DatabaseHelper::fetchAll('SELECT id, name, scope_type FROM roles ORDER BY name ASC');
        } catch (Exception $exception) {
            $roles = [];
            $errorMessage = $errorMessage ?: 'در واکشی فهرست نقش‌ها خطایی رخ داد: ' . $exception->getMessage();
        }

        try {
            $organizations = DatabaseHelper::fetchAll('SELECT id, name FROM organizations ORDER BY name ASC');
        } catch (Exception $exception) {
            $organizations = [];
            $errorMessage = $errorMessage ?: 'در واکشی فهرست سازمان‌ها خطایی رخ داد: ' . $exception->getMessage();
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        include __DIR__ . '/../Views/supperAdmin/users/create.php';
    }

    public function storeUser()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/users/create');
        $listUrl = UtilityHelper::baseUrl('supperadmin/users');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $rawData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'national_code' => trim($_POST['national_code'] ?? ''),
            'role_id' => isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0,
            'organization_id' => isset($_POST['organization_id']) ? (int) $_POST['organization_id'] : null,
            'status' => trim($_POST['status'] ?? 'active'),
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? '',
        ];

        $_SESSION['old_input'] = $rawData;
        $_SESSION['old_input']['password'] = '';
        $_SESSION['old_input']['password_confirmation'] = '';

        $validationErrors = [];

        $rawData['email'] = mb_strtolower($rawData['email']);
        $englishMobile = $rawData['mobile'] !== '' ? UtilityHelper::persianToEnglish($rawData['mobile']) : '';
        $normalizedMobile = $englishMobile !== '' ? preg_replace('/\D+/', '', $englishMobile) : '';
        $englishNationalCode = $rawData['national_code'] !== '' ? UtilityHelper::persianToEnglish($rawData['national_code']) : '';
        $normalizedNational = $englishNationalCode !== '' ? preg_replace('/\D+/', '', $englishNationalCode) : '';

        if ($rawData['first_name'] === '') {
            $validationErrors['first_name'] = 'نام کاربر الزامی است.';
        }

        if ($rawData['last_name'] === '') {
            $validationErrors['last_name'] = 'نام خانوادگی کاربر الزامی است.';
        }

        if ($rawData['email'] === '' || !filter_var($rawData['email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors['email'] = 'ایمیل معتبر وارد کنید.';
        } elseif (DatabaseHelper::exists('users', 'email = :email', ['email' => $rawData['email']])) {
            $validationErrors['email'] = 'این ایمیل قبلاً ثبت شده است.';
        }

        if ($rawData['mobile'] !== '' && $normalizedMobile === '') {
            $validationErrors['mobile'] = 'شماره تماس معتبر نیست.';
        }

        if ($normalizedMobile !== '') {
            if (strlen($normalizedMobile) < 10) {
                $validationErrors['mobile'] = 'شماره تماس معتبر نیست.';
            } elseif (DatabaseHelper::exists('users', 'mobile = :mobile', ['mobile' => $normalizedMobile])) {
                $validationErrors['mobile'] = 'این شماره تماس قبلاً ثبت شده است.';
            }
        }

        if ($rawData['national_code'] !== '' && $normalizedNational === '') {
            $validationErrors['national_code'] = 'کد ملی باید فقط شامل ارقام باشد.';
        }

        if ($normalizedNational !== '') {
            if (strlen($normalizedNational) !== 10) {
                $validationErrors['national_code'] = 'کد ملی باید ۱۰ رقم باشد.';
            }
        }

        $selectedRole = null;
        if ($rawData['role_id'] <= 0) {
            $validationErrors['role_id'] = 'انتخاب نقش کاربری الزامی است.';
        } else {
            $selectedRole = DatabaseHelper::fetchOne('SELECT id, scope_type FROM roles WHERE id = :id LIMIT 1', ['id' => $rawData['role_id']]);
            if (!$selectedRole) {
                $validationErrors['role_id'] = 'نقش انتخاب شده معتبر نیست.';
            }
        }

        $selectedScopeType = $selectedRole ? mb_strtolower($selectedRole['scope_type'] ?? '', 'UTF-8') : '';

        if ($selectedScopeType === 'organization') {
            if (empty($rawData['organization_id']) || $rawData['organization_id'] <= 0) {
                $validationErrors['organization_id'] = 'برای نقش‌های سازمانی انتخاب سازمان الزامی است.';
            } elseif (!DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']])) {
                $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
            }
        } else {
            if (!empty($rawData['organization_id']) && $rawData['organization_id'] > 0) {
                if (!DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']])) {
                    $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
                }
            } else {
                $rawData['organization_id'] = null;
            }
        }

        $allowedStatuses = ['active', 'inactive'];
        if (!in_array($rawData['status'], $allowedStatuses, true)) {
            $validationErrors['status'] = 'وضعیت انتخاب شده معتبر نیست.';
        }

        if ($rawData['password'] === '') {
            $validationErrors['password'] = 'رمز عبور الزامی است.';
        } elseif (mb_strlen($rawData['password']) < 8) {
            $validationErrors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }

        if ($rawData['password_confirmation'] === '') {
            $validationErrors['password_confirmation'] = 'تکرار رمز عبور الزامی است.';
        } elseif ($rawData['password'] !== $rawData['password_confirmation']) {
            $validationErrors['password_confirmation'] = 'رمز عبور و تکرار آن یکسان نیست.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $hashedPassword = password_hash($rawData['password'], PASSWORD_DEFAULT);

        try {
            DatabaseHelper::insert('users', [
                'first_name' => $rawData['first_name'],
                'last_name' => $rawData['last_name'],
                'email' => $rawData['email'],
                'mobile' => $normalizedMobile !== '' ? $normalizedMobile : null,
                'national_code' => $normalizedNational !== '' ? $normalizedNational : null,
                'password' => $hashedPassword,
                'role_id' => $rawData['role_id'],
                'organization_id' => $rawData['organization_id'],
                'status' => $rawData['status'],
            ]);
        } catch (Exception $exception) {
            $_SESSION['validation_errors'] = ['general' => 'در ذخیره کاربر خطایی رخ داد.'];
            ResponseHelper::flashError('در ذخیره اطلاعات خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('کاربر جدید با موفقیت ایجاد شد.');
        UtilityHelper::redirect($listUrl);
    }

    public function editUser()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($userId <= 0) {
            ResponseHelper::flashError('کاربر مورد نظر یافت نشد.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/users'));
        }

        $userData = DatabaseHelper::fetchOne('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);
        if (!$userData) {
            ResponseHelper::flashError('کاربر انتخاب شده وجود ندارد.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/users'));
        }

        $successMessage = flash('success');
        $errorMessage = flash('error');

        try {
            $roles = DatabaseHelper::fetchAll('SELECT id, name, scope_type FROM roles ORDER BY name ASC');
        } catch (Exception $exception) {
            $roles = [];
            $errorMessage = $errorMessage ?: 'در واکشی فهرست نقش‌ها خطایی رخ داد: ' . $exception->getMessage();
        }

        try {
            $organizations = DatabaseHelper::fetchAll('SELECT id, name FROM organizations ORDER BY name ASC');
        } catch (Exception $exception) {
            $organizations = [];
            $errorMessage = $errorMessage ?: 'در واکشی فهرست سازمان‌ها خطایی رخ داد: ' . $exception->getMessage();
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        include __DIR__ . '/../Views/supperAdmin/users/edit.php';
    }

    public function updateUser()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $redirectUrl = UtilityHelper::baseUrl('supperadmin/users');
        $editUrl = UtilityHelper::baseUrl('supperadmin/users/edit') . '?id=' . $userId;

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ($userId <= 0) {
            ResponseHelper::flashError('شناسه کاربر معتبر نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        $existingUser = DatabaseHelper::fetchOne('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $userId]);
        if (!$existingUser) {
            ResponseHelper::flashError('کاربر انتخاب شده وجود ندارد.');
            UtilityHelper::redirect($redirectUrl);
        }

        $rawData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mobile' => trim($_POST['mobile'] ?? ''),
            'national_code' => trim($_POST['national_code'] ?? ''),
            'role_id' => isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0,
            'organization_id' => isset($_POST['organization_id']) ? (int) $_POST['organization_id'] : null,
            'status' => trim($_POST['status'] ?? 'active'),
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? '',
        ];

        $_SESSION['old_input'] = $rawData;
        $_SESSION['old_input']['password'] = '';
        $_SESSION['old_input']['password_confirmation'] = '';

        $validationErrors = [];

        $rawData['email'] = mb_strtolower($rawData['email']);
        $englishMobile = $rawData['mobile'] !== '' ? UtilityHelper::persianToEnglish($rawData['mobile']) : '';
        $normalizedMobile = $englishMobile !== '' ? preg_replace('/\D+/', '', $englishMobile) : '';
        $englishNationalCode = $rawData['national_code'] !== '' ? UtilityHelper::persianToEnglish($rawData['national_code']) : '';
        $normalizedNational = $englishNationalCode !== '' ? preg_replace('/\D+/', '', $englishNationalCode) : '';

        if ($rawData['first_name'] === '') {
            $validationErrors['first_name'] = 'نام کاربر الزامی است.';
        }

        if ($rawData['last_name'] === '') {
            $validationErrors['last_name'] = 'نام خانوادگی کاربر الزامی است.';
        }

        if ($rawData['email'] === '' || !filter_var($rawData['email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors['email'] = 'ایمیل معتبر وارد کنید.';
        } else {
            $duplicateEmail = DatabaseHelper::fetchOne('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1', ['email' => $rawData['email'], 'id' => $userId]);
            if ($duplicateEmail) {
                $validationErrors['email'] = 'این ایمیل قبلاً برای کاربر دیگری ثبت شده است.';
            }
        }

        if ($rawData['mobile'] !== '' && $normalizedMobile === '') {
            $validationErrors['mobile'] = 'شماره تماس معتبر نیست.';
        }

        if ($normalizedMobile !== '') {
            if (strlen($normalizedMobile) < 10) {
                $validationErrors['mobile'] = 'شماره تماس معتبر نیست.';
            } else {
                $duplicateMobile = DatabaseHelper::fetchOne('SELECT id FROM users WHERE mobile = :mobile AND id != :id LIMIT 1', ['mobile' => $normalizedMobile, 'id' => $userId]);
                if ($duplicateMobile) {
                    $validationErrors['mobile'] = 'این شماره تماس قبلاً برای کاربر دیگری ثبت شده است.';
                }
            }
        }

        if ($rawData['national_code'] !== '' && $normalizedNational === '') {
            $validationErrors['national_code'] = 'کد ملی باید فقط شامل ارقام باشد.';
        }

        if ($normalizedNational !== '' && strlen($normalizedNational) !== 10) {
            $validationErrors['national_code'] = 'کد ملی باید ۱۰ رقم باشد.';
        }

        $selectedRole = null;
        if ($rawData['role_id'] <= 0) {
            $validationErrors['role_id'] = 'انتخاب نقش کاربری الزامی است.';
        } else {
            $selectedRole = DatabaseHelper::fetchOne('SELECT id, scope_type FROM roles WHERE id = :id LIMIT 1', ['id' => $rawData['role_id']]);
            if (!$selectedRole) {
                $validationErrors['role_id'] = 'نقش انتخاب شده معتبر نیست.';
            }
        }

        $selectedScopeType = $selectedRole ? mb_strtolower($selectedRole['scope_type'] ?? '', 'UTF-8') : '';

        if ($selectedScopeType === 'organization') {
            if (empty($rawData['organization_id']) || $rawData['organization_id'] <= 0) {
                $validationErrors['organization_id'] = 'برای نقش‌های سازمانی انتخاب سازمان الزامی است.';
            } elseif (!DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']])) {
                $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
            }
        } else {
            if (!empty($rawData['organization_id']) && $rawData['organization_id'] > 0) {
                if (!DatabaseHelper::exists('organizations', 'id = :id', ['id' => $rawData['organization_id']])) {
                    $validationErrors['organization_id'] = 'سازمان انتخاب شده معتبر نیست.';
                }
            } else {
                $rawData['organization_id'] = null;
            }
        }

        $allowedStatuses = ['active', 'inactive'];
        if (!in_array($rawData['status'], $allowedStatuses, true)) {
            $validationErrors['status'] = 'وضعیت انتخاب شده معتبر نیست.';
        }

        $passwordProvided = $rawData['password'] !== '' || $rawData['password_confirmation'] !== '';
        if ($passwordProvided) {
            if ($rawData['password'] === '') {
                $validationErrors['password'] = 'رمز عبور را وارد کنید یا این فیلد را خالی بگذارید.';
            } elseif (mb_strlen($rawData['password']) < 8) {
                $validationErrors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
            }

            if ($rawData['password_confirmation'] === '') {
                $validationErrors['password_confirmation'] = 'تکرار رمز عبور را وارد کنید یا هر دو فیلد را خالی بگذارید.';
            } elseif ($rawData['password'] !== $rawData['password_confirmation']) {
                $validationErrors['password_confirmation'] = 'رمز عبور و تکرار آن یکسان نیست.';
            }
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($editUrl);
        }

        $updateData = [
            'first_name' => $rawData['first_name'],
            'last_name' => $rawData['last_name'],
            'email' => $rawData['email'],
            'mobile' => $normalizedMobile !== '' ? $normalizedMobile : null,
            'national_code' => $normalizedNational !== '' ? $normalizedNational : null,
            'role_id' => $rawData['role_id'],
            'organization_id' => $rawData['organization_id'],
            'status' => $rawData['status'],
        ];

        if ($passwordProvided && $rawData['password'] !== '' && $rawData['password'] === $rawData['password_confirmation']) {
            $updateData['password'] = password_hash($rawData['password'], PASSWORD_DEFAULT);
        }

        try {
            DatabaseHelper::update('users', $updateData, 'id = :id', ['id' => $userId]);
        } catch (Exception $exception) {
            $_SESSION['validation_errors'] = ['general' => 'در به‌روزرسانی اطلاعات کاربر خطایی رخ داد.'];
            ResponseHelper::flashError('در ذخیره اطلاعات خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($editUrl);
        }

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function deleteUser()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/users');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($userId <= 0) {
            ResponseHelper::flashError('شناسه کاربر معتبر نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        $userRecord = DatabaseHelper::fetchOne(
            'SELECT u.id, r.slug AS role_slug FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id LIMIT 1',
            ['id' => $userId]
        );

        if (!$userRecord) {
            ResponseHelper::flashError('کاربر انتخاب شده وجود ندارد.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (($userRecord['role_slug'] ?? '') === 'super-admin') {
            ResponseHelper::flashError('حذف کاربر دارای نقش سوپر ادمین مجاز نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        try {
            DatabaseHelper::delete('users', 'id = :id', ['id' => $userId]);
        } catch (Exception $exception) {
            ResponseHelper::flashError('در حذف کاربر خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        ResponseHelper::flashSuccess('کاربر با موفقیت حذف شد.');
        UtilityHelper::redirect($redirectUrl);
    }

    public function profile()
    {
        $this->ensureAdminSession();
        $this->ensureUsersTable();
        $this->ensureRolesTable();
        $this->ensureOrganizationsTable();
        $this->ensureGeneralSettingsTable();

        AuthHelper::startSession();

        $successMessage = flash('success');
        $errorMessage = flash('error');

        $currentUser = AuthHelper::getUser();
        if (!$currentUser) {
            $currentUser = [
                'id' => 0,
                'name' => 'مدیر سیستم',
                'email' => 'admin@example.com',
                'role' => 'supperadmin',
            ];
        }

        $userId = (int) ($currentUser['id'] ?? 0);

        $userRecord = null;

        if ($userId > 0) {
            $userRecord = DatabaseHelper::fetchOne(
                'SELECT u.*, r.name AS role_name, r.scope_type, o.name AS organization_name
                 FROM users u
                 LEFT JOIN roles r ON u.role_id = r.id
                 LEFT JOIN organizations o ON u.organization_id = o.id
                 WHERE u.id = :id LIMIT 1',
                ['id' => $userId]
            );
        }

        if (!$userRecord) {
            $fullName = trim($currentUser['name'] ?? '');
            if ($fullName === '') {
                $fullName = 'مدیر سیستم';
            }

            $nameParts = preg_split('/\s+/', $fullName, 2);
            $firstName = $nameParts[0] ?? 'مدیر';
            $lastName = $nameParts[1] ?? ($nameParts[0] ?? 'سیستم');

            $userRecord = [
                'id' => $userId,
                'first_name' => $currentUser['first_name'] ?? $firstName,
                'last_name' => $currentUser['last_name'] ?? $lastName,
                'email' => $currentUser['email'] ?? 'admin@example.com',
                'mobile' => $currentUser['mobile'] ?? '',
                'national_code' => $currentUser['national_code'] ?? '',
                'role_name' => $currentUser['role_name'] ?? 'سوپر ادمین',
                'scope_type' => $currentUser['scope_type'] ?? 'superadmin',
                'organization_name' => $currentUser['organization_name'] ?? 'سراسری',
                'status' => $currentUser['status'] ?? 'active',
            ];
        }

        $generalSettings = $this->getGeneralSettings();
        $fallbackDefaultAvatarPath = $this->getDefaultAvatarFallbackPath();
        $profileAvatarPath = $generalSettings['system_default_avatar_path'] ?? $fallbackDefaultAvatarPath;
        if (!$profileAvatarPath) {
            $profileAvatarPath = $fallbackDefaultAvatarPath;
        }
        $profileAvatarUrl = UtilityHelper::baseUrl('public/' . ltrim($profileAvatarPath, '/'));

        $recentActivities = [];

        include __DIR__ . '/../Views/supperAdmin/profile/show.php';
    }
    
    /**
     * Show courses management page
     */
    public function courses() {
        $this->ensureAdminSession();
        
        // Get all courses
        // $courses = Course::getAllCourses();
        
        // Include courses management view
        include __DIR__ . '/../Views/admin/courses.php';
    }

    public function exams()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();

        AuthHelper::startSession();

        $successMessage = flash('success');
        $errorMessage = flash('error');

        try {
            $exams = DatabaseHelper::fetchAll('SELECT * FROM exams ORDER BY created_at DESC');
        } catch (Exception $exception) {
            $exams = [];
            if (!$errorMessage) {
                $errorMessage = 'در واکشی فهرست آزمون‌ها مشکلی رخ داد: ' . $exception->getMessage();
            }
        }

        $examTypeDefinitions = $this->getExamTypeDefinitions();
        $examTypesMeta = [];

        foreach ($examTypeDefinitions as $typeKey => $definition) {
            $examTypesMeta[$typeKey] = [
                'label' => $definition['label'],
                'description' => $definition['description'] ?? '',
            ];
        }

        foreach ($exams as &$exam) {
            $typeKey = $exam['type'] ?? null;
            $exam['type_label'] = $examTypesMeta[$typeKey]['label'] ?? 'نوع نامشخص';

            $configData = [];
            if (!empty($exam['config'])) {
                $decoded = json_decode($exam['config'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $configData = $decoded;
                }
            }

            $exam['config_data'] = $configData;
        }
        unset($exam);

    include __DIR__ . '/../Views/supperAdmin/exams/index.php';
    }

    public function createExam()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();

        AuthHelper::startSession();

        $examTypeDefinitions = $this->getExamTypeDefinitions();

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');

        include __DIR__ . '/../Views/supperAdmin/exams/create.php';
    }

    public function storeExam()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();

        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/exams/create');
        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $examTypeDefinitions = $this->getExamTypeDefinitions();
        $allowedStatuses = ['draft', 'scheduled', 'published', 'archived'];

        $rawData = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => trim($_POST['status'] ?? 'draft'),
            'exam_type' => trim($_POST['exam_type'] ?? ''),
            'start_at' => trim($_POST['start_at'] ?? ''),
            'end_at' => trim($_POST['end_at'] ?? ''),
            'passing_score' => trim($_POST['passing_score'] ?? ''),
        ];

        $_SESSION['old_input'] = array_merge($rawData, $_POST);

        $errors = [];

        $currentUser = AuthHelper::getUser();
        $creatorType = 'superadmin';
        $creatorId = null;
        $organizationId = null;

        if ($currentUser) {
            $creatorId = isset($currentUser['id']) ? (int) $currentUser['id'] : null;
            $scopeType = $currentUser['scope_type'] ?? '';
            $roleSlug = $currentUser['role_slug'] ?? ($currentUser['role'] ?? '');
            $userOrganizationId = $currentUser['organization_id'] ?? null;

            if ($userOrganizationId) {
                $creatorType = 'organization';
                $organizationId = (int) $userOrganizationId;
            } elseif (in_array($scopeType, ['superadmin', 'admin'], true) || in_array($roleSlug, ['super-admin', 'supperadmin'], true)) {
                $creatorType = 'superadmin';
            } else {
                $creatorType = 'user';
            }
        }

        if ($rawData['title'] === '') {
            $errors['title'] = 'عنوان آزمون الزامی است.';
        }

        if ($rawData['exam_type'] === '' || !isset($examTypeDefinitions[$rawData['exam_type']])) {
            $errors['exam_type'] = 'نوع آزمون انتخاب شده معتبر نیست.';
        }

        if (!in_array($rawData['status'], $allowedStatuses, true)) {
            $errors['status'] = 'وضعیت انتخاب شده معتبر نیست.';
        }

        $normalizedStart = $this->normalizeDateTimeInput($rawData['start_at']);
        $normalizedEnd = $this->normalizeDateTimeInput($rawData['end_at']);

        if ($rawData['start_at'] !== '' && $normalizedStart === null) {
            $errors['start_at'] = 'فرمت تاریخ شروع معتبر نیست.';
        }

        if ($rawData['end_at'] !== '' && $normalizedEnd === null) {
            $errors['end_at'] = 'فرمت تاریخ پایان معتبر نیست.';
        }

        if ($normalizedStart && $normalizedEnd && strtotime($normalizedEnd) <= strtotime($normalizedStart)) {
            $errors['end_at'] = 'تاریخ پایان باید بعد از تاریخ شروع باشد.';
        }

        if (in_array($rawData['status'], ['scheduled', 'published'], true) && !$normalizedStart) {
            $errors['start_at'] = 'برای وضعیت انتخاب شده، تعیین تاریخ شروع الزامی است.';
        }

        $passingScore = null;
        if ($rawData['passing_score'] !== '') {
            if (!is_numeric($rawData['passing_score'])) {
                $errors['passing_score'] = 'نمره قبولی باید عددی باشد.';
            } else {
                $passingScore = (float) $rawData['passing_score'];
                if ($passingScore < 0 || $passingScore > 100) {
                    $errors['passing_score'] = 'نمره قبولی باید بین 0 تا 100 باشد.';
                }
            }
        }

        $config = [];

        if (isset($examTypeDefinitions[$rawData['exam_type']])) {
            $typeDefinition = $examTypeDefinitions[$rawData['exam_type']];
            [$typeConfig, $typeErrors] = $this->validateExamTypeFields($typeDefinition, $_POST);
            $config = $typeConfig;
            $errors = array_merge($errors, $typeErrors);
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای مشخص شده در فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $slug = $this->generateUniqueExamSlug($rawData['title']);

        $insertData = [
            'title' => $rawData['title'],
            'slug' => $slug,
            'description' => $rawData['description'] !== '' ? $rawData['description'] : null,
            'status' => $rawData['status'],
            'type' => $rawData['exam_type'],
            'config' => !empty($config) ? json_encode($config, JSON_UNESCAPED_UNICODE) : null,
            'start_at' => $normalizedStart,
            'end_at' => $normalizedEnd,
            'passing_score' => $passingScore,
            'creator_type' => $creatorType,
            'creator_id' => $creatorId,
            'organization_id' => $organizationId,
        ];

        try {
            $createdExamId = (int) DatabaseHelper::insert('exams', $insertData);
            LogHelper::info('exam.created', [
                'exam_id' => $createdExamId,
                'title' => $insertData['title'],
                'status' => $insertData['status'],
                'type' => $insertData['type'],
            ], 'exam', $createdExamId);
        } catch (Exception $exception) {
            LogHelper::error('exam.create_failed', [
                'title' => $insertData['title'],
                'status' => $insertData['status'],
                'type' => $insertData['type'],
                'error' => $exception->getMessage(),
            ], 'exam');

            $_SESSION['validation_errors'] = ['general' => 'در ذخیره آزمون خطایی رخ داد.'];
            ResponseHelper::flashError('در ذخیره آزمون خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($redirectUrl);
        }

        try {
            $this->seedDefaultExamQuestions($createdExamId, $rawData['exam_type']);
            LogHelper::info('exam.seed_default_questions', [
                'exam_id' => $createdExamId,
                'type' => $rawData['exam_type'],
            ], 'exam', $createdExamId);
        } catch (Exception $exception) {
            LogHelper::warning('exam.seed_default_questions_failed', [
                'exam_id' => $createdExamId,
                'type' => $rawData['exam_type'],
                'error' => $exception->getMessage(),
            ], 'exam', $createdExamId);

            ResponseHelper::flashWarning('آزمون ذخیره شد اما در ایجاد سوالات پیش‌فرض خطایی رخ داد: ' . $exception->getMessage());
        }

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('آزمون جدید با موفقیت ایجاد شد.');
        UtilityHelper::redirect($listUrl);
    }

    public function editExam()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();

        AuthHelper::startSession();

        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');
        $examId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($examId <= 0) {
            ResponseHelper::flashError('شناسه آزمون معتبر نیست.');
            UtilityHelper::redirect($listUrl);
        }

        $exam = DatabaseHelper::fetchOne('SELECT * FROM exams WHERE id = :id LIMIT 1', ['id' => $examId]);

        if (!$exam) {
            ResponseHelper::flashError('آزمون مورد نظر یافت نشد.');
            UtilityHelper::redirect($listUrl);
        }

        $configData = [];
        if (!empty($exam['config'])) {
            $decoded = json_decode($exam['config'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $configData = $decoded;
            }
        }

        $exam['config_data'] = $configData;

        $examTypeDefinitions = $this->getExamTypeDefinitions();

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');
        $warningMessage = flash('warning');
        $infoMessage = flash('info');

        include __DIR__ . '/../Views/supperAdmin/exams/edit.php';
    }

    public function deleteExam()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();
        $this->ensureExamQuestionsTable();

        AuthHelper::startSession();

        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($listUrl);
        }

        $examId = isset($_POST['exam_id']) ? (int) $_POST['exam_id'] : 0;
        if ($examId <= 0) {
            ResponseHelper::flashError('شناسه آزمون معتبر نیست.');
            UtilityHelper::redirect($listUrl);
        }

        $exam = DatabaseHelper::fetchOne('SELECT id, title FROM exams WHERE id = :id LIMIT 1', ['id' => $examId]);
        if (!$exam) {
            ResponseHelper::flashError('آزمون انتخاب شده یافت نشد یا قبلاً حذف شده است.');
            UtilityHelper::redirect($listUrl);
        }

        try {
            DatabaseHelper::beginTransaction();

            DatabaseHelper::delete('exam_questions', 'exam_id = :exam_id', ['exam_id' => $examId]);
            DatabaseHelper::delete('exams', 'id = :id', ['id' => $examId]);

            DatabaseHelper::commit();

            $examTitle = trim((string) ($exam['title'] ?? ''));
            if ($examTitle === '') {
                $examTitle = 'آزمون شماره ' . $examId;
            }

            $safeExamTitle = htmlspecialchars($examTitle, ENT_QUOTES, 'UTF-8');
            ResponseHelper::flashSuccess('آزمون «' . $safeExamTitle . '» با موفقیت حذف شد.');

            LogHelper::info('exam.deleted', [
                'exam_id' => $examId,
                'title' => $exam['title'] ?? null,
            ], 'exam', $examId);
        } catch (Exception $exception) {
            DatabaseHelper::rollback();
            LogHelper::error('exam.delete_failed', [
                'exam_id' => $examId,
                'title' => $exam['title'] ?? null,
                'error' => $exception->getMessage(),
            ], 'exam', $examId);
            ResponseHelper::flashError('در حذف آزمون مشکلی رخ داد: ' . $exception->getMessage());
        }

        UtilityHelper::redirect($listUrl);
    }

    public function updateExam()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();

        AuthHelper::startSession();

        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            LogHelper::warning('exam.questions_update_csrf_failed', [
                'provided_exam_id' => $_POST['exam_id'] ?? null,
            ], 'exam');
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($listUrl);
        }

        $examId = isset($_POST['exam_id']) ? (int) $_POST['exam_id'] : 0;
        if ($examId <= 0) {
            LogHelper::warning('exam.questions_update_invalid_exam', [
                'exam_id' => $examId,
            ], 'exam');
            ResponseHelper::flashError('شناسه آزمون معتبر نیست.');
            UtilityHelper::redirect($listUrl);
        }

        $exam = DatabaseHelper::fetchOne('SELECT * FROM exams WHERE id = :id LIMIT 1', ['id' => $examId]);
        if (!$exam) {
            LogHelper::warning('exam.questions_update_exam_not_found', [
                'exam_id' => $examId,
            ], 'exam', $examId);
            ResponseHelper::flashError('آزمون انتخاب شده یافت نشد یا قبلاً حذف شده است.');
            UtilityHelper::redirect($listUrl);
        }

        $editUrl = UtilityHelper::baseUrl('supperadmin/exams/edit') . '?id=' . $examId;

        $examTypeDefinitions = $this->getExamTypeDefinitions();
        $allowedStatuses = ['draft', 'scheduled', 'published', 'archived'];

        $rawData = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status' => trim($_POST['status'] ?? 'draft'),
            'exam_type' => trim($_POST['exam_type'] ?? ''),
            'start_at' => trim($_POST['start_at'] ?? ''),
            'end_at' => trim($_POST['end_at'] ?? ''),
            'passing_score' => trim($_POST['passing_score'] ?? ''),
        ];

        $_SESSION['old_input'] = array_merge($rawData, $_POST);

        $errors = [];

        if ($rawData['title'] === '') {
            $errors['title'] = 'عنوان آزمون الزامی است.';
        }

        if ($rawData['exam_type'] === '' || !isset($examTypeDefinitions[$rawData['exam_type']])) {
            $errors['exam_type'] = 'نوع آزمون انتخاب شده معتبر نیست.';
        }

        if (!in_array($rawData['status'], $allowedStatuses, true)) {
            $errors['status'] = 'وضعیت انتخاب شده معتبر نیست.';
        }

        $normalizedStart = $this->normalizeDateTimeInput($rawData['start_at']);
        $normalizedEnd = $this->normalizeDateTimeInput($rawData['end_at']);

        if ($rawData['start_at'] !== '' && $normalizedStart === null) {
            $errors['start_at'] = 'فرمت تاریخ شروع معتبر نیست.';
        }

        if ($rawData['end_at'] !== '' && $normalizedEnd === null) {
            $errors['end_at'] = 'فرمت تاریخ پایان معتبر نیست.';
        }

        if ($normalizedStart && $normalizedEnd && strtotime($normalizedEnd) <= strtotime($normalizedStart)) {
            $errors['end_at'] = 'تاریخ پایان باید بعد از تاریخ شروع باشد.';
        }

        if (in_array($rawData['status'], ['scheduled', 'published'], true) && !$normalizedStart) {
            $errors['start_at'] = 'برای وضعیت انتخاب شده، تعیین تاریخ شروع الزامی است.';
        }

        $passingScore = null;
        if ($rawData['passing_score'] !== '') {
            if (!is_numeric($rawData['passing_score'])) {
                $errors['passing_score'] = 'نمره قبولی باید عددی باشد.';
            } else {
                $passingScore = (float) $rawData['passing_score'];
                if ($passingScore < 0 || $passingScore > 100) {
                    $errors['passing_score'] = 'نمره قبولی باید بین 0 تا 100 باشد.';
                }
            }
        }

        $config = [];

        if (isset($examTypeDefinitions[$rawData['exam_type']])) {
            $typeDefinition = $examTypeDefinitions[$rawData['exam_type']];
            [$typeConfig, $typeErrors] = $this->validateExamTypeFields($typeDefinition, $_POST);
            $config = $typeConfig;
            $errors = array_merge($errors, $typeErrors);
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای مشخص شده در فرم را بررسی کنید.');
            UtilityHelper::redirect($editUrl);
        }

        $currentTitle = $exam['title'] ?? '';
        $currentSlug = $exam['slug'] ?? '';

        if ($rawData['title'] !== $currentTitle) {
            $slug = $this->generateUniqueExamSlugForUpdate($rawData['title'], $examId);
        } else {
            $slug = $currentSlug !== '' ? $currentSlug : $this->generateUniqueExamSlugForUpdate($rawData['title'], $examId);
        }

        $updateData = [
            'title' => $rawData['title'],
            'slug' => $slug,
            'description' => $rawData['description'] !== '' ? $rawData['description'] : null,
            'status' => $rawData['status'],
            'type' => $rawData['exam_type'],
            'config' => !empty($config) ? json_encode($config, JSON_UNESCAPED_UNICODE) : null,
            'start_at' => $normalizedStart,
            'end_at' => $normalizedEnd,
            'passing_score' => $passingScore,
        ];

        try {
            DatabaseHelper::update('exams', $updateData, 'id = :id', ['id' => $examId]);
            LogHelper::info('exam.updated', [
                'exam_id' => $examId,
                'title' => $updateData['title'],
                'status' => $updateData['status'],
                'type' => $updateData['type'],
            ], 'exam', $examId);
        } catch (Exception $exception) {
            LogHelper::error('exam.update_failed', [
                'exam_id' => $examId,
                'title' => $updateData['title'] ?? null,
                'status' => $updateData['status'] ?? null,
                'type' => $updateData['type'] ?? null,
                'error' => $exception->getMessage(),
            ], 'exam', $examId);

            $_SESSION['validation_errors'] = ['general' => 'در ذخیره آزمون خطایی رخ داد.'];
            ResponseHelper::flashError('در بروزرسانی آزمون خطایی رخ داد: ' . $exception->getMessage());
            UtilityHelper::redirect($editUrl);
        }

        unset($_SESSION['old_input']);

        if (($exam['type'] ?? '') !== $rawData['exam_type']) {
            ResponseHelper::flashSuccess('آزمون با موفقیت ویرایش شد.');
            ResponseHelper::flashWarning('نوع آزمون تغییر کرد. لطفاً سوالات آزمون را بررسی و در صورت نیاز به‌روزرسانی کنید.');
        } else {
            ResponseHelper::flashSuccess('آزمون با موفقیت ویرایش شد.');
        }

        UtilityHelper::redirect($listUrl);
    }

    public function manageExamQuestions()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();
        $this->ensureExamQuestionsTable();

        AuthHelper::startSession();

        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');
        $examId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($examId <= 0) {
            ResponseHelper::flashError('شناسه آزمون معتبر نیست.');
            UtilityHelper::redirect($listUrl);
        }

        $exam = DatabaseHelper::fetchOne('SELECT * FROM exams WHERE id = :id LIMIT 1', ['id' => $examId]);
        if (!$exam) {
            ResponseHelper::flashError('آزمون مورد نظر یافت نشد.');
            UtilityHelper::redirect($listUrl);
        }

        $questions = DatabaseHelper::fetchAll(
            'SELECT * FROM exam_questions WHERE exam_id = :exam_id ORDER BY (question_code IS NULL) ASC, question_code ASC, id ASC',
            ['exam_id' => $examId]
        );

        foreach ($questions as &$question) {
            $question['options_json'] = $this->prepareJsonForForm($question['options']);
            $question['metadata_json'] = $this->prepareJsonForForm($question['metadata']);
        }
        unset($question);

        $questionTypeOptions = $this->getQuestionTypeOptions($questions);

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');
        $warningMessage = flash('warning');
        $infoMessage = flash('info');

        include __DIR__ . '/../Views/supperAdmin/exams/questions.php';
    }

    public function updateExamQuestions()
    {
        $this->ensureAdminSession();
        $this->ensureExamsTable();
        $this->ensureExamQuestionsTable();

        AuthHelper::startSession();

        $listUrl = UtilityHelper::baseUrl('supperadmin/exams');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($listUrl);
        }

        $examId = isset($_POST['exam_id']) ? (int) $_POST['exam_id'] : 0;
        if ($examId <= 0) {
            ResponseHelper::flashError('شناسه آزمون معتبر نیست.');
            UtilityHelper::redirect($listUrl);
        }

        $exam = DatabaseHelper::fetchOne('SELECT * FROM exams WHERE id = :id LIMIT 1', ['id' => $examId]);
        if (!$exam) {
            ResponseHelper::flashError('آزمون انتخاب شده یافت نشد یا قبلاً حذف شده است.');
            UtilityHelper::redirect($listUrl);
        }

        $manageUrl = UtilityHelper::baseUrl('supperadmin/exams/questions') . '?id=' . $examId;

        $submittedQuestions = $_POST['questions'] ?? [];

        if (empty($submittedQuestions) || !is_array($submittedQuestions)) {
            LogHelper::warning('exam.questions_update_empty_payload', [
                'exam_id' => $examId,
            ], 'exam', $examId);
            ResponseHelper::flashError('هیچ داده‌ای برای سوالات ارسال نشد.');
            UtilityHelper::redirect($manageUrl);
        }

        $existingQuestions = DatabaseHelper::fetchAll('SELECT * FROM exam_questions WHERE exam_id = :exam_id', ['exam_id' => $examId]);
        $existingMap = [];
        foreach ($existingQuestions as $question) {
            $existingMap[(int) $question['id']] = $question;
        }

        $errors = [];
        $updates = [];

        foreach ($submittedQuestions as $questionId => $payload) {
            $questionId = (int) $questionId;
            if ($questionId <= 0 || !isset($existingMap[$questionId])) {
                LogHelper::warning('exam.question_payload_invalid', [
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                ], 'exam', $examId);
                $errors['questions'][$questionId]['general'] = 'سوال انتخاب شده معتبر نیست.';
                continue;
            }

            $questionErrors = [];

            $questionText = trim($payload['question_text'] ?? '');
            if ($questionText === '') {
                $questionErrors['question_text'] = 'متن سوال نمی‌تواند خالی باشد.';
            }

            $questionCode = trim($payload['question_code'] ?? '');
            if ($questionCode === '') {
                $questionCode = null;
            }

            $questionType = trim($payload['question_type'] ?? '');
            if ($questionType === '') {
                $questionErrors['question_type'] = 'نوع سوال را مشخص کنید.';
            }

            $answerKey = trim($payload['answer_key'] ?? '');
            if ($answerKey === '') {
                $answerKey = null;
            }

            $weightRaw = trim((string) ($payload['weight'] ?? ''));
            if ($weightRaw === '') {
                $weight = 1.0;
            } elseif (!is_numeric($weightRaw)) {
                $questionErrors['weight'] = 'وزن سوال باید یک مقدار عددی باشد.';
                $weight = null;
            } else {
                $weight = (float) $weightRaw;
            }

            $optionsFormat = isset($payload['options_format']) ? trim((string) $payload['options_format']) : 'list';
            $optionsEncoded = null;
            if (array_key_exists('options', $payload)) {
                if (!is_array($payload['options'])) {
                    $questionErrors['options'] = 'ساختار گزینه‌ها معتبر نیست.';
                } else {
                    if ($optionsFormat === 'map') {
                        $optionsPrepared = $this->buildAssociativeArrayFromPairs($payload['options']);
                    } else {
                        $optionsPrepared = $this->normalizeQuestionArrayInput($payload['options']);
                    }

                    if (!empty($optionsPrepared)) {
                        $optionsEncoded = json_encode($optionsPrepared, JSON_UNESCAPED_UNICODE);
                        if ($optionsEncoded === false) {
                            $questionErrors['options'] = 'در ذخیره گزینه‌ها خطایی رخ داد.';
                        }
                    } else {
                        $optionsEncoded = null;
                    }
                }
            } elseif (isset($payload['options_json'])) {
                $optionsJsonRaw = trim((string) $payload['options_json']);
                if ($optionsJsonRaw !== '') {
                    $optionsDecoded = json_decode($optionsJsonRaw, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $questionErrors['options'] = 'ساختار گزینه‌ها معتبر نیست: ' . json_last_error_msg();
                    } else {
                        $optionsEncoded = json_encode($optionsDecoded, JSON_UNESCAPED_UNICODE);
                    }
                }
            }

            $metadataEncoded = null;
            if (array_key_exists('metadata', $payload)) {
                if (!is_array($payload['metadata'])) {
                    $questionErrors['metadata'] = 'ساختار متادیتا معتبر نیست.';
                } else {
                    $metadataPrepared = $this->normalizeQuestionArrayInput($payload['metadata']);

                    if (!empty($metadataPrepared)) {
                        $metadataEncoded = json_encode($metadataPrepared, JSON_UNESCAPED_UNICODE);
                        if ($metadataEncoded === false) {
                            $questionErrors['metadata'] = 'در ذخیره متادیتا خطایی رخ داد.';
                        }
                    } else {
                        $metadataEncoded = null;
                    }
                }
            } elseif (isset($payload['metadata_json'])) {
                $metadataJsonRaw = trim((string) $payload['metadata_json']);
                if ($metadataJsonRaw !== '') {
                    $metadataDecoded = json_decode($metadataJsonRaw, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $questionErrors['metadata'] = 'ساختار متادیتا معتبر نیست: ' . json_last_error_msg();
                    } else {
                        $metadataEncoded = json_encode($metadataDecoded, JSON_UNESCAPED_UNICODE);
                    }
                }
            }

            if (!empty($questionErrors)) {
                LogHelper::warning('exam.question_validation_failed', [
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'issues' => array_keys($questionErrors),
                ], 'exam', $examId);
                $errors['questions'][$questionId] = $questionErrors;
                continue;
            }

            $updates[$questionId] = [
                'question_text' => $questionText,
                'question_code' => $questionCode,
                'question_type' => $questionType,
                'answer_key' => $answerKey,
                'weight' => $weight ?? 1.0,
                'options' => $optionsEncoded,
                'metadata' => $metadataEncoded,
            ];
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            ResponseHelper::flashError('لطفاً خطاهای مشخص شده در فرم سوالات را بررسی کنید.');
            UtilityHelper::redirect($manageUrl);
        }

        foreach ($updates as $questionId => $data) {
            try {
                DatabaseHelper::update('exam_questions', $data, 'id = :id', ['id' => $questionId]);
            } catch (Exception $exception) {
                LogHelper::error('exam.question_update_failed', [
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'error' => $exception->getMessage(),
                ], 'exam', $examId);
                $_SESSION['validation_errors']['questions'][$questionId]['general'] = 'در بروزرسانی این سوال خطایی رخ داد: ' . $exception->getMessage();
            }
        }

        if (!empty($_SESSION['validation_errors'] ?? [])) {
            LogHelper::error('exam.questions_partial_failure', [
                'exam_id' => $examId,
                'failed_questions' => array_keys($_SESSION['validation_errors']['questions'] ?? []),
            ], 'exam', $examId);
            $_SESSION['old_input'] = $_POST;
            ResponseHelper::flashError('در بروزرسانی برخی سوالات خطا رخ داد.');
            UtilityHelper::redirect($manageUrl);
        }

        unset($_SESSION['old_input']);

        LogHelper::info('exam.questions_updated', [
            'exam_id' => $examId,
            'updated_questions' => count($updates),
        ], 'exam', $examId);

        ResponseHelper::flashSuccess('سوالات آزمون با موفقیت بروزرسانی شدند.');
        UtilityHelper::redirect($manageUrl);
    }

    /**
     * List organizations
     */
    public function listOrganizations()
    {
        $this->ensureAdminSession();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $successMessage = flash('success');
        $errorMessage = flash('error');

        $organizations = DatabaseHelper::fetchAll('SELECT * FROM organizations ORDER BY created_at DESC');

        include __DIR__ . '/../Views/supperAdmin/organizations/index.php';
    }
    
    /**
     * Show organization creation form
     */
    public function createOrganization()
    {
        $this->ensureAdminSession();
        $this->ensureOrganizationsTable();

        AuthHelper::startSession();

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');

        include __DIR__ . '/../Views/supperAdmin/organizations/create.php';
    }

    /**
     * Handle organization creation submission
     */
    public function storeOrganization()
    {
        $this->ensureAdminSession();
        AuthHelper::startSession();
        $this->ensureOrganizationsTable();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/organizations/create');
        $listUrl = UtilityHelper::baseUrl('supperadmin/organizations');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $rawData = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'latin_name' => trim($_POST['latin_name'] ?? ''),
            'subdomain' => trim($_POST['subdomain'] ?? ''),
            'organization_code' => trim($_POST['organization_code'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'evaluation_unit' => trim($_POST['evaluation_unit'] ?? ''),
            'credit_amount' => trim($_POST['credit_amount'] ?? ''),
            'exam_fee_per_participant' => trim($_POST['exam_fee_per_participant'] ?? ''),
            'close_washup_after_confirmation' => isset($_POST['close_washup_after_confirmation']) ? '1' : '0',
            'enable_region_area' => isset($_POST['enable_region_area']) ? '1' : '0',
            'allow_competency_notes' => isset($_POST['allow_competency_notes']) ? '1' : '0',
            'score_range_1' => trim($_POST['score_range_1'] ?? ''),
            'score_range_2' => trim($_POST['score_range_2'] ?? ''),
            'score_range_3' => trim($_POST['score_range_3'] ?? ''),
            'score_range_4' => trim($_POST['score_range_4'] ?? ''),
            'score_range_5' => trim($_POST['score_range_5'] ?? ''),
            'deactivate_organization' => isset($_POST['deactivate_organization']) ? '1' : '0',
        ];

        $_SESSION['old_input'] = $rawData;
        $_SESSION['old_input']['password'] = '';

        $input = $rawData;
        $creditAmountValue = null;
        $examFeeValue = null;

        if ($rawData['subdomain'] !== '') {
            $normalizedSubdomain = UtilityHelper::slugify($rawData['subdomain']);
            if ($normalizedSubdomain === '') {
                $normalizedSubdomain = strtolower(str_replace(' ', '-', UtilityHelper::persianToEnglish($rawData['subdomain'])));
            }

            $input['subdomain'] = $normalizedSubdomain;
        } else {
            $input['subdomain'] = '';
        }

        $errors = [];
        $requiredFields = ['code', 'name', 'latin_name', 'organization_code', 'username', 'password'];
        foreach ($requiredFields as $field) {
            if ($field === 'password') {
                if (empty($rawData[$field])) {
                    $errors[$field] = 'وارد کردن رمز عبور الزامی است';
                }
            } elseif (empty($rawData[$field])) {
                $errors[$field] = 'وارد کردن این فیلد الزامی است';
            }
        }

        if ($input['subdomain'] !== '' && !preg_match('/^[a-z0-9-]+$/', $input['subdomain'])) {
            $errors['subdomain'] = 'ساب‌دومین باید فقط شامل حروف لاتین، اعداد و خط تیره باشد';
        }

        if (!empty($rawData['password']) && strlen($rawData['password']) < 8) {
            $errors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد';
        }

        $scoreFields = ['score_range_1', 'score_range_2', 'score_range_3', 'score_range_4', 'score_range_5'];
        foreach ($scoreFields as $scoreField) {
            if ($rawData[$scoreField] !== '' && !preg_match('/^-?\d+(\.\d+)?(\s*-\s*-?\d+(\.\d+)?)?$/', str_replace(',', '.', $rawData[$scoreField]))) {
                $errors[$scoreField] = 'فرمت محدوده امتیاز نامعتبر است';
            }
        }

        $creditAmountValue = $this->normalizeOptionalAmount(
            $rawData['credit_amount'],
            $errors,
            'credit_amount',
            'مبلغ اعتبار سازمان باید به صورت عددی وارد شود.'
        );

        $examFeeValue = $this->normalizeOptionalAmount(
            $rawData['exam_fee_per_participant'],
            $errors,
            'exam_fee_per_participant',
            'مبلغ آزمون باید به صورت عددی وارد شود.'
        );

        if (empty($errors)) {
            if (DatabaseHelper::exists('organizations', 'code = :code', ['code' => $rawData['code']])) {
                $errors['code'] = 'کد وارد شده قبلاً استفاده شده است';
            }
        }

        if (empty($errors) && $input['subdomain'] !== '') {
            if (DatabaseHelper::exists('organizations', 'subdomain = :subdomain', ['subdomain' => $input['subdomain']])) {
                $errors['subdomain'] = 'این ساب‌دومین قبلاً ثبت شده است';
            }
        }

        if (empty($errors) && !empty($rawData['username'])) {
            if (DatabaseHelper::exists('organizations', 'username = :username', ['username' => $rawData['username']])) {
                $errors['username'] = 'این نام کاربری قبلاً استفاده شده است';
            }
        }

        if (!empty($_FILES['logo']['name']) && !FileHelper::isValidImage($_FILES['logo'])) {
            $errors['logo'] = 'فایل لوگوی انتخاب شده معتبر نیست';
        }

        if (!empty($_FILES['report_cover_logo']['name']) && !FileHelper::isValidImage($_FILES['report_cover_logo'])) {
            $errors['report_cover_logo'] = 'فایل لوگوی صفحه اول گزارش معتبر نیست';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload = FileHelper::uploadFile($_FILES['logo'], 'uploads/organizations/logos/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                $logoPath = $upload['path'];
            } else {
                $_SESSION['validation_errors'] = ['logo' => $upload['error']];
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($redirectUrl);
            }
        }

        $reportCoverLogoPath = null;
        if (!empty($_FILES['report_cover_logo']['name'])) {
            $upload = FileHelper::uploadFile($_FILES['report_cover_logo'], 'uploads/organizations/report-covers/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                $reportCoverLogoPath = $upload['path'];
            } else {
                $_SESSION['validation_errors'] = ['report_cover_logo' => $upload['error']];
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($redirectUrl);
            }
        }

        $insertData = [
            'code' => $rawData['code'],
            'name' => $rawData['name'],
            'latin_name' => $rawData['latin_name'],
            'subdomain' => $input['subdomain'] !== '' ? $input['subdomain'] : null,
            'organization_code' => $rawData['organization_code'],
            'username' => $rawData['username'],
            'password' => password_hash($rawData['password'], PASSWORD_BCRYPT),
            'evaluation_unit' => $rawData['evaluation_unit'] !== '' ? $rawData['evaluation_unit'] : null,
            'close_washup_after_confirmation' => $rawData['close_washup_after_confirmation'],
            'enable_region_area' => $rawData['enable_region_area'],
            'allow_competency_notes' => $rawData['allow_competency_notes'],
            'credit_amount' => $creditAmountValue,
            'exam_fee_per_participant' => $examFeeValue,
            'score_range_1' => $rawData['score_range_1'] !== '' ? $rawData['score_range_1'] : null,
            'score_range_2' => $rawData['score_range_2'] !== '' ? $rawData['score_range_2'] : null,
            'score_range_3' => $rawData['score_range_3'] !== '' ? $rawData['score_range_3'] : null,
            'score_range_4' => $rawData['score_range_4'] !== '' ? $rawData['score_range_4'] : null,
            'score_range_5' => $rawData['score_range_5'] !== '' ? $rawData['score_range_5'] : null,
            'logo_path' => $logoPath,
            'report_cover_logo_path' => $reportCoverLogoPath,
            'is_active' => $rawData['deactivate_organization'] === '1' ? 0 : 1,
        ];

        DatabaseHelper::insert('organizations', $insertData);

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('سازمان جدید با موفقیت ایجاد شد.');
        UtilityHelper::redirect($listUrl);
    }

    /**
     * Show edit form for an organization
     */
    public function editOrganization()
    {
        $this->ensureAdminSession();
        $this->ensureOrganizationsTable();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $listUrl = UtilityHelper::baseUrl('supperadmin/organizations');

        if ($id <= 0) {
            ResponseHelper::flashError('شناسه سازمان نامعتبر است.');
            UtilityHelper::redirect($listUrl);
        }

        $organization = DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE id = :id', ['id' => $id]);

        if (!$organization) {
            ResponseHelper::flashError('سازمانی با این مشخصات یافت نشد.');
            UtilityHelper::redirect($listUrl);
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $successMessage = flash('success');
        $errorMessage = flash('error');

        include __DIR__ . '/../Views/supperAdmin/organizations/edit.php';
    }

    /**
     * Handle updating an organization
     */
    public function updateOrganization()
    {
        $this->ensureAdminSession();
        AuthHelper::startSession();
        $this->ensureOrganizationsTable();

        $listUrl = UtilityHelper::baseUrl('supperadmin/organizations');

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $editUrl = UtilityHelper::baseUrl('supperadmin/organizations/edit?id=' . $id);

        if ($id <= 0) {
            ResponseHelper::flashError('شناسه سازمان نامعتبر است.');
            UtilityHelper::redirect($listUrl);
        }

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($editUrl);
        }

        $organization = DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE id = :id', ['id' => $id]);
        if (!$organization) {
            ResponseHelper::flashError('سازمانی با این مشخصات یافت نشد.');
            UtilityHelper::redirect($listUrl);
        }

        $rawData = [
            'code' => trim($_POST['code'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'latin_name' => trim($_POST['latin_name'] ?? ''),
            'subdomain' => trim($_POST['subdomain'] ?? ''),
            'organization_code' => trim($_POST['organization_code'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'evaluation_unit' => trim($_POST['evaluation_unit'] ?? ''),
            'credit_amount' => trim($_POST['credit_amount'] ?? ''),
            'exam_fee_per_participant' => trim($_POST['exam_fee_per_participant'] ?? ''),
            'close_washup_after_confirmation' => isset($_POST['close_washup_after_confirmation']) ? '1' : '0',
            'enable_region_area' => isset($_POST['enable_region_area']) ? '1' : '0',
            'allow_competency_notes' => isset($_POST['allow_competency_notes']) ? '1' : '0',
            'score_range_1' => trim($_POST['score_range_1'] ?? ''),
            'score_range_2' => trim($_POST['score_range_2'] ?? ''),
            'score_range_3' => trim($_POST['score_range_3'] ?? ''),
            'score_range_4' => trim($_POST['score_range_4'] ?? ''),
            'score_range_5' => trim($_POST['score_range_5'] ?? ''),
            'deactivate_organization' => isset($_POST['deactivate_organization']) ? '1' : '0',
        ];

        $_SESSION['old_input'] = $rawData;
        $_SESSION['old_input']['password'] = '';

        $input = $rawData;
        $creditAmountValue = null;
        $examFeeValue = null;

        if ($rawData['subdomain'] !== '') {
            $normalizedSubdomain = UtilityHelper::slugify($rawData['subdomain']);
            if ($normalizedSubdomain === '') {
                $normalizedSubdomain = strtolower(str_replace(' ', '-', UtilityHelper::persianToEnglish($rawData['subdomain'])));
            }

            $input['subdomain'] = $normalizedSubdomain;
        } else {
            $input['subdomain'] = '';
        }

        $errors = [];
        $requiredFields = ['code', 'name', 'latin_name', 'organization_code', 'username'];
        foreach ($requiredFields as $field) {
            if (empty($rawData[$field])) {
                $errors[$field] = 'وارد کردن این فیلد الزامی است';
            }
        }

        if ($input['subdomain'] !== '' && !preg_match('/^[a-z0-9-]+$/', $input['subdomain'])) {
            $errors['subdomain'] = 'ساب‌دومین باید فقط شامل حروف لاتین، اعداد و خط تیره باشد';
        }

        if (!empty($rawData['password']) && strlen($rawData['password']) < 8) {
            $errors['password'] = 'رمز عبور باید حداقل ۸ کاراکتر باشد';
        }

        $scoreFields = ['score_range_1', 'score_range_2', 'score_range_3', 'score_range_4', 'score_range_5'];
        foreach ($scoreFields as $scoreField) {
            if ($rawData[$scoreField] !== '' && !preg_match('/^-?\d+(\.\d+)?(\s*-\s*-?\d+(\.\d+)?)?$/', str_replace(',', '.', $rawData[$scoreField]))) {
                $errors[$scoreField] = 'فرمت محدوده امتیاز نامعتبر است';
            }
        }

        $creditAmountValue = $this->normalizeOptionalAmount(
            $rawData['credit_amount'],
            $errors,
            'credit_amount',
            'مبلغ اعتبار سازمان باید به صورت عددی وارد شود.'
        );

        $examFeeValue = $this->normalizeOptionalAmount(
            $rawData['exam_fee_per_participant'],
            $errors,
            'exam_fee_per_participant',
            'مبلغ آزمون باید به صورت عددی وارد شود.'
        );

        if (empty($errors)) {
            if (DatabaseHelper::exists('organizations', 'code = :code AND id <> :id', ['code' => $rawData['code'], 'id' => $id])) {
                $errors['code'] = 'کد وارد شده قبلاً استفاده شده است';
            }
        }

        if (empty($errors) && $input['subdomain'] !== '') {
            if (DatabaseHelper::exists('organizations', 'subdomain = :subdomain AND id <> :id', ['subdomain' => $input['subdomain'], 'id' => $id])) {
                $errors['subdomain'] = 'این ساب‌دومین قبلاً ثبت شده است';
            }
        }

        if (empty($errors) && !empty($rawData['username'])) {
            if (DatabaseHelper::exists('organizations', 'username = :username AND id <> :id', ['username' => $rawData['username'], 'id' => $id])) {
                $errors['username'] = 'این نام کاربری قبلاً استفاده شده است';
            }
        }

        if (!empty($_FILES['logo']['name']) && !FileHelper::isValidImage($_FILES['logo'])) {
            $errors['logo'] = 'فایل لوگوی انتخاب شده معتبر نیست';
        }

        if (!empty($_FILES['report_cover_logo']['name']) && !FileHelper::isValidImage($_FILES['report_cover_logo'])) {
            $errors['report_cover_logo'] = 'فایل لوگوی صفحه اول گزارش معتبر نیست';
        }

        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($editUrl);
        }

        $logoPath = $organization['logo_path'];
        if (!empty($_FILES['logo']['name'])) {
            $upload = FileHelper::uploadFile($_FILES['logo'], 'uploads/organizations/logos/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                if (!empty($organization['logo_path'])) {
                    FileHelper::deleteFile($organization['logo_path']);
                }
                $logoPath = $upload['path'];
            } else {
                $_SESSION['validation_errors'] = ['logo' => $upload['error']];
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($editUrl);
            }
        }

        $reportCoverLogoPath = $organization['report_cover_logo_path'];
        if (!empty($_FILES['report_cover_logo']['name'])) {
            $upload = FileHelper::uploadFile($_FILES['report_cover_logo'], 'uploads/organizations/report-covers/', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            if ($upload['success']) {
                if (!empty($organization['report_cover_logo_path'])) {
                    FileHelper::deleteFile($organization['report_cover_logo_path']);
                }
                $reportCoverLogoPath = $upload['path'];
            } else {
                $_SESSION['validation_errors'] = ['report_cover_logo' => $upload['error']];
                ResponseHelper::flashError($upload['error']);
                UtilityHelper::redirect($editUrl);
            }
        }

        $updateData = [
            'code' => $rawData['code'],
            'name' => $rawData['name'],
            'latin_name' => $rawData['latin_name'],
            'subdomain' => $input['subdomain'] !== '' ? $input['subdomain'] : null,
            'organization_code' => $rawData['organization_code'],
            'username' => $rawData['username'],
            'evaluation_unit' => $rawData['evaluation_unit'] !== '' ? $rawData['evaluation_unit'] : null,
            'close_washup_after_confirmation' => $rawData['close_washup_after_confirmation'],
            'enable_region_area' => $rawData['enable_region_area'],
            'allow_competency_notes' => $rawData['allow_competency_notes'],
            'credit_amount' => $creditAmountValue,
            'exam_fee_per_participant' => $examFeeValue,
            'score_range_1' => $rawData['score_range_1'] !== '' ? $rawData['score_range_1'] : null,
            'score_range_2' => $rawData['score_range_2'] !== '' ? $rawData['score_range_2'] : null,
            'score_range_3' => $rawData['score_range_3'] !== '' ? $rawData['score_range_3'] : null,
            'score_range_4' => $rawData['score_range_4'] !== '' ? $rawData['score_range_4'] : null,
            'score_range_5' => $rawData['score_range_5'] !== '' ? $rawData['score_range_5'] : null,
            'logo_path' => $logoPath,
            'report_cover_logo_path' => $reportCoverLogoPath,
            'is_active' => $rawData['deactivate_organization'] === '1' ? 0 : 1,
        ];

        if (!empty($rawData['password'])) {
            $updateData['password'] = password_hash($rawData['password'], PASSWORD_BCRYPT);
        }

        DatabaseHelper::update('organizations', $updateData, 'id = :id', ['id' => $id]);

        unset($_SESSION['old_input']);

        ResponseHelper::flashSuccess('اطلاعات سازمان با موفقیت ویرایش شد.');
        UtilityHelper::redirect($listUrl);
    }

    /**
     * Delete an organization
     */
    public function deleteOrganization()
    {
        $this->ensureAdminSession();
        $this->ensureOrganizationsTable();

        $listUrl = UtilityHelper::baseUrl('supperadmin/organizations');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($listUrl);
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            ResponseHelper::flashError('شناسه سازمان نامعتبر است.');
            UtilityHelper::redirect($listUrl);
        }

        $organization = DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE id = :id', ['id' => $id]);
        if (!$organization) {
            ResponseHelper::flashError('سازمانی با این مشخصات یافت نشد.');
            UtilityHelper::redirect($listUrl);
        }

        if (!empty($organization['logo_path'])) {
            FileHelper::deleteFile($organization['logo_path']);
        }

        if (!empty($organization['report_cover_logo_path'])) {
            FileHelper::deleteFile($organization['report_cover_logo_path']);
        }

        DatabaseHelper::delete('organizations', 'id = :id', ['id' => $id]);

        ResponseHelper::flashSuccess('سازمان با موفقیت حذف شد.');
        UtilityHelper::redirect($listUrl);
    }

    /**
     * Show analytics page
     */
    public function analytics() {
        $this->ensureAdminSession();
        
        // Get analytics data
        $analyticsData = [
            'page_views' => 12500,
            'unique_visitors' => 3200,
            'bounce_rate' => 35.5,
            'avg_session_duration' => '4:32'
        ];
        
        // Include analytics view
        include __DIR__ . '/../Views/admin/analytics.php';
    }

    private function ensureAdminSession()
    {
        AuthHelper::startSession();

        if (!AuthHelper::isLoggedIn()) {
            ResponseHelper::flashError('لطفاً برای دسترسی به این بخش وارد شوید.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/login'));
        }

        $user = AuthHelper::getUser();
        $roleSlug = $user['role_slug'] ?? '';
        $scopeType = $user['scope_type'] ?? '';
        $role = $user['role'] ?? '';

        if ($roleSlug !== 'super-admin' && $scopeType !== 'superadmin' && $role !== 'supperadmin') {
            AuthHelper::logout();
            AuthHelper::startSession();
            ResponseHelper::flashError('دسترسی شما به این بخش مجاز نیست.');
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/login'));
        }

        $this->recordAdministrativeRequest();
    }

    private function recordAdministrativeRequest(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = $requestUri !== '' ? strtok($requestUri, '?') : null;

        $payloadKeys = array_keys($_POST ?? []);
        $filteredPayloadKeys = array_values(array_filter($payloadKeys, function ($key) {
            $lower = strtolower((string) $key);
            return !in_array($lower, ['password', 'password_confirmation', 'current_password', '_token'], true);
        }));

        $context = [
            'method' => $method,
            'path' => $path,
            'payload_keys' => $filteredPayloadKeys,
            'query_keys' => array_keys($_GET ?? []),
        ];

        LogHelper::info('request.' . strtolower($method), $context, 'http_request', null);
    }

    private function getPermissionsCatalog()
    {
        return [
            'مدیریت کاربران' => [
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
            ],
            'مدیریت سازمان‌ها' => [
                'organizations.view',
                'organizations.create',
                'organizations.edit',
                'organizations.delete',
            ],
            'مدیریت محتوا' => [
                'content.view',
                'content.publish',
                'content.delete',
            ],
            'گزارش‌ها و تحلیل' => [
                'reports.view',
                'analytics.view',
                'finance.view',
            ],
            'تنظیمات سیستم' => [
                'settings.general',
                'settings.security',
                'settings.notifications',
            ],
        ];
    }

    private function getDefaultAvatarFallbackPath()
    {
        return 'assets/images/thumbs/user-img.png';
    }

    private function getGeneralSettingsDefaults()
    {
        return [
            'site_name' => 'سامانه ارزیابی عملکرد',
            'site_tagline' => 'مدیریت یکپارچه سازمان‌ها و ارزیابی‌ها',
            'support_email' => 'support@example.com',
            'support_phone' => '+98 21 0000 0000',
            'default_language' => 'fa',
            'timezone' => 'Asia/Tehran',
            'maintenance_mode' => false,
            'allow_registration' => true,
            'analytics_script' => '',
            'dashboard_welcome_message' => 'به پنل مدیریتی سامانه ارزیابی خوش آمدید.',
            'system_logo_path' => null,
            'system_default_avatar_path' => $this->getDefaultAvatarFallbackPath(),
            'updated_at' => null,
        ];
    }

    private function getGeneralSettings()
    {
        $this->ensureGeneralSettingsTable();

        $defaults = $this->getGeneralSettingsDefaults();
        $record = DatabaseHelper::fetchOne('SELECT * FROM system_settings ORDER BY id ASC LIMIT 1');

        if (!$record) {
            return $defaults;
        }

        $record['maintenance_mode'] = isset($record['maintenance_mode']) ? (bool) $record['maintenance_mode'] : $defaults['maintenance_mode'];
        $record['allow_registration'] = isset($record['allow_registration']) ? (bool) $record['allow_registration'] : $defaults['allow_registration'];
        if (!isset($record['system_default_avatar_path']) || $record['system_default_avatar_path'] === null || $record['system_default_avatar_path'] === '') {
            $record['system_default_avatar_path'] = $defaults['system_default_avatar_path'];
        }

        return array_merge($defaults, $record);
    }

    private function saveGeneralSettings(array $settings)
    {
        $this->ensureGeneralSettingsTable();

        $existing = DatabaseHelper::fetchOne('SELECT id FROM system_settings ORDER BY id ASC LIMIT 1');

        $dataToStore = [
            'site_name' => $settings['site_name'],
            'site_tagline' => $settings['site_tagline'],
            'support_email' => $settings['support_email'],
            'support_phone' => $settings['support_phone'],
            'default_language' => $settings['default_language'],
            'timezone' => $settings['timezone'],
            'maintenance_mode' => $settings['maintenance_mode'] ? 1 : 0,
            'allow_registration' => $settings['allow_registration'] ? 1 : 0,
            'analytics_script' => $settings['analytics_script'],
            'dashboard_welcome_message' => $settings['dashboard_welcome_message'],
            'system_logo_path' => $settings['system_logo_path'],
            'system_default_avatar_path' => $settings['system_default_avatar_path'],
        ];

        if ($existing) {
            DatabaseHelper::update('system_settings', $dataToStore, 'id = :id', ['id' => $existing['id']]);
        } else {
            DatabaseHelper::insert('system_settings', $dataToStore);
        }
    }

    private function ensureGeneralSettingsTable()
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'system_settings'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `system_settings` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `site_name` VARCHAR(255) NOT NULL,
                `site_tagline` VARCHAR(255) DEFAULT NULL,
                `support_email` VARCHAR(255) DEFAULT NULL,
                `support_phone` VARCHAR(50) DEFAULT NULL,
                `default_language` VARCHAR(10) NOT NULL DEFAULT 'fa',
                `timezone` VARCHAR(64) NOT NULL DEFAULT 'Asia/Tehran',
                `maintenance_mode` TINYINT(1) NOT NULL DEFAULT 0,
                `allow_registration` TINYINT(1) NOT NULL DEFAULT 1,
                `analytics_script` TEXT DEFAULT NULL,
                `dashboard_welcome_message` TEXT DEFAULT NULL,
                `system_logo_path` VARCHAR(255) DEFAULT NULL,
                `system_default_avatar_path` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $pdo->exec($createSql);
        } else {
            try {
                $logoColumn = $pdo->query("SHOW COLUMNS FROM `system_settings` LIKE 'system_logo_path'")->fetch();
                if (!$logoColumn) {
                    $pdo->exec("ALTER TABLE `system_settings` ADD `system_logo_path` VARCHAR(255) DEFAULT NULL AFTER `dashboard_welcome_message`");
                }

                $defaultAvatarColumn = $pdo->query("SHOW COLUMNS FROM `system_settings` LIKE 'system_default_avatar_path'")->fetch();
                if (!$defaultAvatarColumn) {
                    $pdo->exec("ALTER TABLE `system_settings` ADD `system_default_avatar_path` VARCHAR(255) DEFAULT NULL AFTER `system_logo_path`");
                }
            } catch (PDOException $exception) {
                // Ignore alteration errors to keep flow
            }
        }

        $existing = DatabaseHelper::fetchOne('SELECT id FROM system_settings LIMIT 1');
        if (!$existing) {
            $defaults = $this->getGeneralSettingsDefaults();
            DatabaseHelper::insert('system_settings', [
                'site_name' => $defaults['site_name'],
                'site_tagline' => $defaults['site_tagline'],
                'support_email' => $defaults['support_email'],
                'support_phone' => $defaults['support_phone'],
                'default_language' => $defaults['default_language'],
                'timezone' => $defaults['timezone'],
                'maintenance_mode' => $defaults['maintenance_mode'] ? 1 : 0,
                'allow_registration' => $defaults['allow_registration'] ? 1 : 0,
                'analytics_script' => $defaults['analytics_script'],
                'dashboard_welcome_message' => $defaults['dashboard_welcome_message'],
                'system_logo_path' => $defaults['system_logo_path'],
                'system_default_avatar_path' => $defaults['system_default_avatar_path'],
            ]);
        }
    }

    private function ensureUsersTable()
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `first_name` VARCHAR(100) NOT NULL,
                `last_name` VARCHAR(100) NOT NULL,
                `email` VARCHAR(190) NOT NULL,
                `mobile` VARCHAR(20) DEFAULT NULL,
                `national_code` VARCHAR(20) DEFAULT NULL,
                `password` VARCHAR(255) NOT NULL,
                `role_id` INT UNSIGNED NOT NULL,
                `organization_id` INT UNSIGNED DEFAULT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'active',
                `last_login_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_users_email` (`email`),
                UNIQUE KEY `uniq_users_mobile` (`mobile`),
                KEY `idx_users_role` (`role_id`),
                KEY `idx_users_org` (`organization_id`),
                CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_users_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            try {
                $pdo->exec($createSql);
            } catch (PDOException $exception) {
                $createSqlWithoutFk = "CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `first_name` VARCHAR(100) NOT NULL,
                    `last_name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(190) NOT NULL,
                    `mobile` VARCHAR(20) DEFAULT NULL,
                    `national_code` VARCHAR(20) DEFAULT NULL,
                    `password` VARCHAR(255) NOT NULL,
                    `role_id` INT UNSIGNED NOT NULL,
                    `organization_id` INT UNSIGNED DEFAULT NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
                    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uniq_users_email` (`email`),
                    UNIQUE KEY `uniq_users_mobile` (`mobile`),
                    KEY `idx_users_role` (`role_id`),
                    KEY `idx_users_org` (`organization_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $pdo->exec($createSqlWithoutFk);
            }
        } else {
            try {
                $statusColumn = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'status'")->fetch();
                if (!$statusColumn) {
                    $pdo->exec("ALTER TABLE `users` ADD `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `organization_id`");
                }

                $roleColumn = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role_id'")->fetch();
                if (!$roleColumn) {
                    $pdo->exec("ALTER TABLE `users` ADD `role_id` INT UNSIGNED NOT NULL AFTER `password`");
                }
            } catch (PDOException $exception) {
                // نادیده گرفتن خطاهای تغییر ساختار جدول
            }
        }
    }

    private function ensureRolesTable()
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'roles'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `roles` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `slug` VARCHAR(180) NOT NULL,
                `description` TEXT DEFAULT NULL,
                `scope_type` VARCHAR(32) NOT NULL DEFAULT 'global',
                `organization_id` INT UNSIGNED DEFAULT NULL,
                `permissions` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_roles_slug` (`slug`),
                KEY `idx_roles_org` (`organization_id`),
                CONSTRAINT `fk_roles_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            try {
                $pdo->exec($createSql);
            } catch (PDOException $exception) {
                // اگر جدول organizations وجود نداشت، محدودیت خارجی را حذف کرده و دوباره تلاش می‌کنیم
                $createSqlWithoutFk = "CREATE TABLE IF NOT EXISTS `roles` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(150) NOT NULL,
                    `slug` VARCHAR(180) NOT NULL,
                    `description` TEXT DEFAULT NULL,
                    `scope_type` VARCHAR(32) NOT NULL DEFAULT 'global',
                    `organization_id` INT UNSIGNED DEFAULT NULL,
                    `permissions` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY `uniq_roles_slug` (`slug`),
                    KEY `idx_roles_org` (`organization_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                $pdo->exec($createSqlWithoutFk);
            }
        } else {
            try {
                $slugColumn = $pdo->query("SHOW COLUMNS FROM `roles` LIKE 'slug'")->fetch();
                if (!$slugColumn) {
                    $pdo->exec("ALTER TABLE `roles` ADD `slug` VARCHAR(180) NOT NULL AFTER `name`");
                }

                $scopeColumn = $pdo->query("SHOW COLUMNS FROM `roles` LIKE 'scope_type'")->fetch();
                if (!$scopeColumn) {
                    $pdo->exec("ALTER TABLE `roles` ADD `scope_type` VARCHAR(32) NOT NULL DEFAULT 'global' AFTER `description`");
                }

                $permissionsColumn = $pdo->query("SHOW COLUMNS FROM `roles` LIKE 'permissions'")->fetch();
                if (!$permissionsColumn) {
                    $pdo->exec("ALTER TABLE `roles` ADD `permissions` TEXT NOT NULL AFTER `organization_id`");
                }
            } catch (PDOException $exception) {
                // نادیده گرفتن خطاهای تغییر ساختار جدول
            }
        }

        // اطمینان از وجود نقش سوپر ادمین پیش‌فرض
        try {
            $permissionsCatalog = $this->getPermissionsCatalog();
            $allPermissions = [];
            foreach ($permissionsCatalog as $groupPermissions) {
                foreach ($groupPermissions as $permission) {
                    $allPermissions[$permission] = $permission;
                }
            }
            $permissionsJson = json_encode(array_values($allPermissions), JSON_UNESCAPED_UNICODE);

            $superAdminRole = DatabaseHelper::fetchOne('SELECT * FROM roles WHERE slug = :slug LIMIT 1', ['slug' => 'super-admin']);

            if ($superAdminRole) {
                $needsUpdate = ($superAdminRole['scope_type'] ?? '') !== 'superadmin' || empty($superAdminRole['permissions']);
                if ($needsUpdate) {
                    DatabaseHelper::update('roles', [
                        'scope_type' => 'superadmin',
                        'permissions' => $permissionsJson,
                    ], 'id = :id', ['id' => $superAdminRole['id']]);
                }
                return;
            }

            $fallbackSuperAdmin = DatabaseHelper::fetchOne('SELECT * FROM roles WHERE scope_type = :scope LIMIT 1', ['scope' => 'superadmin']);
            if (!$fallbackSuperAdmin) {
                $fallbackSuperAdmin = DatabaseHelper::fetchOne('SELECT * FROM roles WHERE name = :name LIMIT 1', ['name' => 'سوپر ادمین']);
            }

            if ($fallbackSuperAdmin) {
                DatabaseHelper::update('roles', [
                    'slug' => 'super-admin',
                    'scope_type' => 'superadmin',
                    'permissions' => $permissionsJson,
                ], 'id = :id', ['id' => $fallbackSuperAdmin['id']]);
            } else {
                DatabaseHelper::insert('roles', [
                    'name' => 'سوپر ادمین',
                    'slug' => 'super-admin',
                    'description' => 'دسترسی کامل به تمام بخش‌های سامانه.',
                    'scope_type' => 'superadmin',
                    'organization_id' => null,
                    'permissions' => $permissionsJson,
                ]);
            }
        } catch (Exception $exception) {
            // در صورت بروز خطا در ایجاد یا بروزرسانی نقش سوپر ادمین، جریان ادامه می‌یابد
        }
    }

    private function ensureExamsTable()
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'exams'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `exams` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) DEFAULT NULL,
                `description` TEXT DEFAULT NULL,
                `type` VARCHAR(100) NOT NULL DEFAULT 'generic',
                `config` LONGTEXT DEFAULT NULL,
                `passing_score` DECIMAL(5,2) DEFAULT NULL,
                `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
                `start_at` DATETIME DEFAULT NULL,
                `end_at` DATETIME DEFAULT NULL,
                `creator_type` VARCHAR(32) NOT NULL DEFAULT 'superadmin',
                `creator_id` INT UNSIGNED DEFAULT NULL,
                `organization_id` INT UNSIGNED DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_exams_slug` (`slug`),
                KEY `idx_exams_creator` (`creator_type`, `creator_id`),
                KEY `idx_exams_org` (`organization_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $pdo->exec($createSql);
        } else {
            try {
                $statusColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'status'")->fetch();
                if (!$statusColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `status` VARCHAR(32) NOT NULL DEFAULT 'draft' AFTER `description`");
                }

                $slugColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'slug'")->fetch();
                if (!$slugColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `slug` VARCHAR(255) DEFAULT NULL AFTER `title`");
                }

                $typeColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'type'")->fetch();
                if (!$typeColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `type` VARCHAR(100) NOT NULL DEFAULT 'generic' AFTER `description`");
                }

                $configColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'config'")->fetch();
                if (!$configColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `config` LONGTEXT DEFAULT NULL AFTER `type`");
                }

                $passingScoreColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'passing_score'")->fetch();
                if (!$passingScoreColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `passing_score` DECIMAL(5,2) DEFAULT NULL AFTER `config`");
                }

                $creatorTypeColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'creator_type'")->fetch();
                if (!$creatorTypeColumn) {
                    $pdo->exec("ALTER TABLE `exams` ADD `creator_type` VARCHAR(32) NOT NULL DEFAULT 'superadmin' AFTER `end_at`");
                }

                $creatorIdColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'creator_id'")->fetch();
                if (!$creatorIdColumn) {
                    $position = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'creator_type'")->fetch() ? ' AFTER `creator_type`' : ' AFTER `end_at`';
                    $pdo->exec("ALTER TABLE `exams` ADD `creator_id` INT UNSIGNED DEFAULT NULL" . $position);
                }

                $organizationIdColumn = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'organization_id'")->fetch();
                if (!$organizationIdColumn) {
                    $position = $pdo->query("SHOW COLUMNS FROM `exams` LIKE 'creator_id'")->fetch() ? ' AFTER `creator_id`' : ' AFTER `end_at`';
                    $pdo->exec("ALTER TABLE `exams` ADD `organization_id` INT UNSIGNED DEFAULT NULL" . $position);
                }

                $indexes = $pdo->query("SHOW INDEXES FROM `exams`")->fetchAll();
                $hasCreatorIndex = false;
                $hasOrgIndex = false;
                foreach ($indexes as $index) {
                    if (($index['Key_name'] ?? '') === 'idx_exams_creator') {
                        $hasCreatorIndex = true;
                    }
                    if (($index['Key_name'] ?? '') === 'idx_exams_org') {
                        $hasOrgIndex = true;
                    }
                }

                if (!$hasCreatorIndex) {
                    $pdo->exec("CREATE INDEX `idx_exams_creator` ON `exams`(`creator_type`, `creator_id`)");
                }

                if (!$hasOrgIndex) {
                    $pdo->exec("CREATE INDEX `idx_exams_org` ON `exams`(`organization_id`)");
                }
            } catch (PDOException $exception) {
                // Ignore alteration errors quietly
            }
        }

        try {
            $existingCount = DatabaseHelper::fetchOne('SELECT COUNT(*) AS cnt FROM exams');
            if (!$existingCount || (int) ($existingCount['cnt'] ?? 0) === 0) {
                DatabaseHelper::insert('exams', [
                    'title' => 'نمونه آزمون مقدماتی',
                    'slug' => 'sample-intro-exam',
                    'description' => 'این یک آزمون نمونه است تا ساختار صفحه نمایش آزمون‌ها خالی نباشد.',
                    'type' => 'mbti',
                    'config' => json_encode([
                        'mbti_question_count' => 60,
                        'mbti_duration_minutes' => 45,
                        'mbti_include_explanations' => true,
                        'mbti_language' => 'fa',
                    ], JSON_UNESCAPED_UNICODE),
                    'status' => 'published',
                    'passing_score' => 70,
                    'start_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
                    'end_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                    'creator_type' => 'superadmin',
                    'creator_id' => null,
                    'organization_id' => null,
                ]);
            }
        } catch (Exception $exception) {
            // Ignore seeding errors
        }
    }

    private function getExamTypeDefinitions(): array
    {
        return [
            'mbti' => [
                'label' => 'آزمون MBTI',
                'description' => 'ارزیابی تیپ‌های شخصیتی مایرز-بریگز (MBTI) بر اساس ترجیحات چهارگانه.',
                'icon' => 'fa-brain',
                'fields' => [
                    [
                        'name' => 'mbti_question_count',
                        'label' => 'تعداد سوالات',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 20, 'max' => 120],
                        'attributes' => ['min' => 20, 'max' => 120, 'step' => 1],
                        'help' => 'معمولاً بین ۶۰ تا ۸۰ پرسش طراحی می‌شود.',
                        'default' => 60,
                    ],
                    [
                        'name' => 'mbti_duration_minutes',

                        'label' => 'مدت آزمون (دقیقه)',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 10, 'max' => 240],
                        'attributes' => ['min' => 10, 'max' => 240, 'step' => 5],
                        'default' => 45,
                    ],
                    [
                        'name' => 'mbti_include_explanations',
                        'label' => 'نمایش توضیحات تیپ شخصیتی پس از اتمام آزمون',
                        'type' => 'checkbox',
                        'required' => false,
                        'default' => true,
                    ],
                    [
                        'name' => 'mbti_language',
                        'label' => 'زبان نمایش پرسش‌ها',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'fa' => 'فارسی',
                            'en' => 'انگلیسی',
                            'bilingual' => 'دو زبانه',
                        ],
                        'rules' => ['in' => ['fa', 'en', 'bilingual']],
                        'default' => 'fa',
                    ],
                ],
            ],
            'disc' => [
                'label' => 'آزمون DISC',
                'description' => 'تحلیل سبک‌های رفتاری DISC بر پایه ابعاد تسلط، نفوذ، ثبات و وظیفه‌شناسی.',
                'icon' => 'fa-users',
                'fields' => [
                    [
                        'name' => 'disc_question_count',
                        'label' => 'تعداد آیتم‌ها',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 12, 'max' => 120],
                        'attributes' => ['min' => 12, 'max' => 120, 'step' => 1],
                        'default' => 28,
                    ],
                    [
                        'name' => 'disc_duration_minutes',
                        'label' => 'مدت آزمون (دقیقه)',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 10, 'max' => 180],
                        'attributes' => ['min' => 10, 'max' => 180, 'step' => 5],
                        'default' => 30,
                    ],
                    [
                        'name' => 'disc_primary_focus',
                        'label' => 'تمرکز اصلی گزارش',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'behavioral' => 'الگوهای رفتاری فردی',
                            'leadership' => 'سبک رهبری',
                            'sales' => 'سبک فروش و مذاکره',
                            'team' => 'تحلیل تیمی',
                        ],
                        'rules' => ['in' => ['behavioral', 'leadership', 'sales', 'team']],
                        'default' => 'behavioral',
                    ],
                    [
                        'name' => 'disc_include_team_profile',
                        'label' => 'ایجاد پروفایل تیمی پس از جمع‌آوری نتایج',
                        'type' => 'checkbox',
                        'required' => false,
                        'default' => false,
                    ],
                    [
                        'name' => 'disc_show_graphs',
                        'label' => 'نمایش نمودارهای سبک رفتاری در گزارش نهایی',
                        'type' => 'checkbox',
                        'required' => false,
                        'default' => true,
                    ],
                ],
            ],
            'analytical' => [
                'label' => 'آزمون تفکر تحلیلی',
                'description' => 'سنجش توانایی تحلیل، استدلال منطقی و حل مسئله در شرایط پیچیده.',
                'icon' => 'fa-chart-line',
                'fields' => [
                    [
                        'name' => 'analytical_scenario_count',
                        'label' => 'تعداد سناریوها',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 5, 'max' => 40],
                        'attributes' => ['min' => 5, 'max' => 40, 'step' => 1],
                        'default' => 12,
                    ],
                    [
                        'name' => 'analytical_duration_minutes',
                        'label' => 'مدت آزمون (دقیقه)',
                        'type' => 'number',
                        'required' => true,
                        'rules' => ['type' => 'int', 'min' => 15, 'max' => 240],
                        'attributes' => ['min' => 15, 'max' => 240, 'step' => 5],
                        'default' => 90,
                    ],
                    [
                        'name' => 'analytical_scoring_method',
                        'label' => 'روش نمره‌دهی',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'auto' => 'خودکار (سیستمی)',
                            'manual' => 'دستی توسط ارزیاب',
                            'hybrid' => 'ترکیبی',
                        ],
                        'rules' => ['in' => ['auto', 'manual', 'hybrid']],
                        'default' => 'hybrid',
                    ],
                    [
                        'name' => 'analytical_difficulty_level',
                        'label' => 'سطح دشواری سوالات',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            'basic' => 'مقدماتی',
                            'intermediate' => 'متوسط',
                            'advanced' => 'پیشرفته',
                        ],
                        'rules' => ['in' => ['basic', 'intermediate', 'advanced']],
                        'default' => 'intermediate',
                    ],
                    [
                        'name' => 'analytical_requires_proctor',
                        'label' => 'لزوم حضور ناظر (پرکتور) در حین آزمون',
                        'type' => 'checkbox',
                        'required' => false,
                        'default' => false,
                    ],
                ],
            ],
        ];
    }

    private function validateExamTypeFields(array $typeDefinition, array $input): array
    {
        $config = [];
        $errors = [];

        if (empty($typeDefinition['fields']) || !is_array($typeDefinition['fields'])) {
            return [$config, $errors];
        }

        foreach ($typeDefinition['fields'] as $field) {
            $name = $field['name'];
            $label = $field['label'] ?? $name;
            $type = $field['type'] ?? 'text';
            $required = !empty($field['required']);

            if ($type === 'checkbox') {
                $config[$name] = isset($input[$name]) && (string) $input[$name] !== '' && $input[$name] !== '0';
                continue;
            }

            $rawValue = $input[$name] ?? null;

            if (is_array($rawValue)) {
                $rawValue = null;
            }

            $value = $rawValue !== null ? trim((string) $rawValue) : '';

            if ($required && $value === '') {
                $errors[$name] = $label . ' الزامی است.';
                continue;
            }

            if ($value === '') {
                $config[$name] = null;
                continue;
            }

            $rules = $field['rules'] ?? [];
            if ($type === 'number' || (($rules['type'] ?? '') === 'int')) {
                if (!is_numeric($value)) {
                    $errors[$name] = $label . ' باید عددی باشد.';
                    continue;
                }

                $numericValue = (($rules['type'] ?? '') === 'int') ? (int) $value : (float) $value;

                if (isset($rules['min']) && $numericValue < $rules['min']) {
                    $errors[$name] = $label . ' نمی‌تواند کمتر از ' . UtilityHelper::englishToPersian($rules['min']) . ' باشد.';
                    continue;
                }

                if (isset($rules['max']) && $numericValue > $rules['max']) {
                    $errors[$name] = $label . ' نمی‌تواند بیشتر از ' . UtilityHelper::englishToPersian($rules['max']) . ' باشد.';
                    continue;
                }

                $config[$name] = $numericValue;
                continue;
            }

            if ($type === 'select') {
                $options = array_keys($field['options'] ?? []);
                if (!in_array($value, $options, true)) {
                    $errors[$name] = $label . ' انتخاب شده معتبر نیست.';
                    continue;
                }
            }

            if (!empty($rules['in']) && !in_array($value, (array) $rules['in'], true)) {
                $errors[$name] = $label . ' انتخاب شده معتبر نیست.';
                continue;
            }

            $config[$name] = $value;
        }

        return [$config, $errors];
    }

    private function normalizeDateTimeInput(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function generateUniqueExamSlug(string $title): string
    {
        $baseSlug = UtilityHelper::slugify($title);
        if ($baseSlug === '') {
            $baseSlug = 'exam';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (DatabaseHelper::exists('exams', 'slug = :slug', ['slug' => $slug])) {
            $slugCandidate = $baseSlug . '-' . $counter;
            $slug = UtilityHelper::slugify($slugCandidate);
            if ($slug === '') {
                $slug = $slugCandidate;
            }
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueExamSlugForUpdate(string $title, int $examId): string
    {
        $baseSlug = UtilityHelper::slugify($title);
        if ($baseSlug === '') {
            $baseSlug = 'exam';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (DatabaseHelper::exists('exams', 'slug = :slug AND id != :id', ['slug' => $slug, 'id' => $examId])) {
            $slugCandidate = $baseSlug . '-' . $counter;
            $slug = UtilityHelper::slugify($slugCandidate);
            if ($slug === '') {
                $slug = $slugCandidate;
            }
            $counter++;
        }

        return $slug;
    }

    private function prepareJsonForForm($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }
        }

        return (string) $value;
    }

    private function getQuestionTypeOptions(array $questions): array
    {
        $types = ['single_choice', 'multiple_choice', 'dual_choice', 'forced_choice', 'likert', 'descriptive'];

        foreach ($questions as $question) {
            $type = $question['question_type'] ?? '';
            if ($type !== '' && !in_array($type, $types, true)) {
                $types[] = $type;
            }
        }

        return $types;
    }

    private function decodeLogContext($context): array
    {
        if ($context === null) {
            return [];
        }

        if (is_array($context)) {
            return $context;
        }

        if (is_string($context) && $context !== '') {
            $decoded = json_decode($context, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return ['value' => $context];
        }

        return is_scalar($context) ? ['value' => $context] : [];
    }

    private function formatLogDate(?string $timestamp): string
    {
        if ($timestamp === null || $timestamp === '') {
            return '-';
        }

        $time = strtotime($timestamp);
        if ($time === false) {
            return UtilityHelper::englishToPersian($timestamp);
        }

        return UtilityHelper::englishToPersian(date('Y-m-d H:i', $time));
    }

    private function formatLogRelative(?string $timestamp): string
    {
        if ($timestamp === null || $timestamp === '') {
            return '-';
        }

        try {
            $relative = UtilityHelper::timeAgo($timestamp);
            return UtilityHelper::englishToPersian($relative);
        } catch (Exception $exception) {
            return '-';
        }
    }

    private function getLogStatistics(): array
    {
        try {
            $total = (int) DatabaseHelper::count('system_logs');

            $todayRow = DatabaseHelper::fetchOne('SELECT COUNT(*) AS cnt FROM system_logs WHERE DATE(created_at) = CURDATE()');
            $errorsRow = DatabaseHelper::fetchOne("SELECT COUNT(*) AS cnt FROM system_logs WHERE level IN ('error', 'critical')");
            $uniqueRow = DatabaseHelper::fetchOne('SELECT COUNT(DISTINCT user_id) AS cnt FROM system_logs WHERE user_id IS NOT NULL');

            return [
                'total' => $total,
                'today' => (int) ($todayRow['cnt'] ?? 0),
                'errors' => (int) ($errorsRow['cnt'] ?? 0),
                'unique_users' => (int) ($uniqueRow['cnt'] ?? 0),
            ];
        } catch (Exception $exception) {
            LogHelper::error('security.logs_statistics_failed', [
                'error' => $exception->getMessage(),
            ], 'security');

            return [
                'total' => 0,
                'today' => 0,
                'errors' => 0,
                'unique_users' => 0,
            ];
        }
    }

    private function readSecurityLogFiles(int $limit = 30): array
    {
        $logDir = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logDir)) {
            return [];
        }

        $files = glob($logDir . 'security_*.log');
        if (!$files) {
            return [];
        }

        rsort($files);

        $entries = [];

        foreach ($files as $file) {
            $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) {
                continue;
            }

            $lines = array_reverse($lines);
            foreach ($lines as $line) {
                $decoded = json_decode(trim($line), true);
                if (!is_array($decoded)) {
                    continue;
                }

                $decoded['file'] = basename($file);
                $decoded['timestamp_formatted'] = $this->formatLogDate($decoded['timestamp'] ?? null);
                $decoded['timestamp_relative'] = $this->formatLogRelative($decoded['timestamp'] ?? null);

                $entries[] = $decoded;

                if (count($entries) >= $limit) {
                    break 2;
                }
            }
        }

        return $entries;
    }

    private function readFallbackLogEntries(int $limit = 30): array
    {
        $logFile = __DIR__ . '/../../storage/logs/system_fallback.log';
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $lines = array_reverse($lines);
        $entries = [];

        foreach ($lines as $line) {
            $decoded = json_decode(trim($line), true);
            if (!is_array($decoded)) {
                continue;
            }

            $decoded['timestamp_formatted'] = $this->formatLogDate($decoded['timestamp'] ?? null);
            $decoded['timestamp_relative'] = $this->formatLogRelative($decoded['timestamp'] ?? null);

            $entries[] = $decoded;

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    private function normalizeQuestionArrayInput($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $isAssoc = $this->isAssocArray($data);
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $this->normalizeQuestionArrayInput($value);
            } else {
                if (is_string($value)) {
                    $value = trim($value);
                }

                if (is_numeric($value) && $value !== '') {
                    $normalized[$key] = strpos((string) $value, '.') !== false ? (float) $value : (int) $value;
                } else {
                    $normalized[$key] = $value;
                }
            }
        }

        if (!$isAssoc) {
            $normalized = array_values($normalized);
        }

        return $normalized;
    }

    private function buildAssociativeArrayFromPairs(array $pairs): array
    {
        $result = [];

        foreach ($pairs as $pair) {
            if (!is_array($pair)) {
                continue;
            }

            $key = '';
            if (isset($pair['value'])) {
                $key = trim((string) $pair['value']);
            } elseif (isset($pair['key'])) {
                $key = trim((string) $pair['key']);
            }

            if ($key === '') {
                continue;
            }

            $label = $pair['label'] ?? ($pair['text'] ?? '');
            if (is_array($label)) {
                $label = json_encode($label, JSON_UNESCAPED_UNICODE);
            }

            if (is_string($label)) {
                $label = trim($label);
            }

            $result[$key] = $label;
        }

        return $result;
    }

    private function isAssocArray(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function seedDefaultExamQuestions(int $examId, string $examType): void
    {
        if ($examId <= 0) {
            return;
        }

        $this->ensureExamQuestionsTable();

        $defaultQuestionsCatalog = $this->getDefaultExamQuestionBlueprints();
        if (!isset($defaultQuestionsCatalog[$examType]) || empty($defaultQuestionsCatalog[$examType])) {
            return;
        }

        $existingCount = DatabaseHelper::fetchOne(
            'SELECT COUNT(*) AS cnt FROM exam_questions WHERE exam_id = :exam_id LIMIT 1',
            ['exam_id' => $examId]
        );

        if ($existingCount && (int) ($existingCount['cnt'] ?? 0) > 0) {
            return;
        }

        foreach ($defaultQuestionsCatalog[$examType] as $questionBlueprint) {
            $questionData = [
                'exam_id' => $examId,
                'question_code' => $questionBlueprint['code'] ?? null,
                'question_text' => $questionBlueprint['text'] ?? '',
                'question_type' => $questionBlueprint['type'] ?? 'single_choice',
                'options' => isset($questionBlueprint['options']) ? json_encode($questionBlueprint['options'], JSON_UNESCAPED_UNICODE) : null,
                'answer_key' => $questionBlueprint['answer'] ?? null,
                'weight' => isset($questionBlueprint['weight']) ? (float) $questionBlueprint['weight'] : 1.0,
                'metadata' => isset($questionBlueprint['metadata']) ? json_encode($questionBlueprint['metadata'], JSON_UNESCAPED_UNICODE) : null,
            ];

            try {
                DatabaseHelper::insert('exam_questions', $questionData);
            } catch (Exception $exception) {
                // Continue with remaining questions even if one insert fails
            }
        }
    }

    private function getDefaultExamQuestionBlueprints(): array
    {
        return [
            'mbti' => $this->generateMbtiQuestionBlueprints(),
            'disc' => $this->generateDiscQuestionBlueprints(),
            'analytical' => $this->generateAnalyticalQuestionBlueprints(),
        ];
    }

    private function ensureExamQuestionsTable(): void
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'exam_questions'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `exam_questions` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `exam_id` INT UNSIGNED NOT NULL,
                `question_code` VARCHAR(64) DEFAULT NULL,
                `question_text` TEXT NOT NULL,
                `question_type` VARCHAR(50) NOT NULL DEFAULT 'single_choice',
                `options` LONGTEXT DEFAULT NULL,
                `answer_key` VARCHAR(255) DEFAULT NULL,
                `weight` DECIMAL(6,2) NOT NULL DEFAULT 1.00,
                `metadata` LONGTEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY `idx_exam_questions_exam` (`exam_id`),
                KEY `idx_exam_questions_code` (`question_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $pdo->exec($createSql);
        } else {
            try {
                $questionTypeColumn = $pdo->query("SHOW COLUMNS FROM `exam_questions` LIKE 'question_type'")->fetch();
                if (!$questionTypeColumn) {
                    $pdo->exec("ALTER TABLE `exam_questions` ADD `question_type` VARCHAR(50) NOT NULL DEFAULT 'single_choice' AFTER `question_text`");
                }

                $weightColumn = $pdo->query("SHOW COLUMNS FROM `exam_questions` LIKE 'weight'")->fetch();
                if (!$weightColumn) {
                    $pdo->exec("ALTER TABLE `exam_questions` ADD `weight` DECIMAL(6,2) NOT NULL DEFAULT 1.00 AFTER `answer_key`");
                }

                $metadataColumn = $pdo->query("SHOW COLUMNS FROM `exam_questions` LIKE 'metadata'")->fetch();
                if (!$metadataColumn) {
                    $pdo->exec("ALTER TABLE `exam_questions` ADD `metadata` LONGTEXT DEFAULT NULL AFTER `weight`");
                }

                $indexes = $pdo->query("SHOW INDEXES FROM `exam_questions`")->fetchAll();
                $hasExamIndex = false;
                $hasCodeIndex = false;
                foreach ($indexes as $index) {
                    if (($index['Key_name'] ?? '') === 'idx_exam_questions_exam') {
                        $hasExamIndex = true;
                    }
                    if (($index['Key_name'] ?? '') === 'idx_exam_questions_code') {
                        $hasCodeIndex = true;
                    }
                }

                if (!$hasExamIndex) {
                    $pdo->exec("CREATE INDEX `idx_exam_questions_exam` ON `exam_questions`(`exam_id`)");
                }

                if (!$hasCodeIndex) {
                    $pdo->exec("CREATE INDEX `idx_exam_questions_code` ON `exam_questions`(`question_code`)");
                }
            } catch (PDOException $exception) {
                // Ignore alteration errors to keep flow
            }
        }
    }

    private function generateMbtiQuestionBlueprints(): array
    {
        $dimensionPrompts = [
            'EI' => [
                [
                    'text' => 'در جلسات گروهی چگونه رفتار می‌کنید؟',
                    'option_a' => 'سریعاً گفت‌وگو را آغاز می‌کنم و با همه تعامل دارم.',
                    'option_b' => 'ترجیح می‌دهم ابتدا گوش کنم و زمانی که لازم شد صحبت کنم.',
                ],
                [
                    'text' => 'در مهمانی‌های بزرگ چه حسی دارید؟',
                    'option_a' => 'بودن در جمع به من انرژی می‌دهد و با افراد جدید آشنا می‌شوم.',
                    'option_b' => 'بعد از مدتی خسته می‌شوم و ترجیح می‌دهم زودتر به خانه برگردم.',
                ],
                [
                    'text' => 'برای حل یک مسئله کاری چه مسیری را انتخاب می‌کنید؟',
                    'option_a' => 'با همکارانم بحث می‌کنم تا ایده‌های بیشتری جمع شود.',
                    'option_b' => 'به تنهایی روی مسئله تمرکز می‌کنم و بعدها نتیجه را مطرح می‌کنم.',
                ],
                [
                    'text' => 'در زمان استراحت محل کار معمولاً چه می‌کنید؟',
                    'option_a' => 'به میز مشترک می‌پیوندم و با دیگران صحبت می‌کنم.',
                    'option_b' => 'ترجیح می‌دهم در فضایی آرام یا با یک نفر نزدیک وقت بگذرانم.',
                ],
                [
                    'text' => 'در شروع یک پروژه جدید چگونه آغاز می‌کنید؟',
                    'option_a' => 'با معرفی و ایجاد ارتباطات تیمی شروع می‌کنم.',
                    'option_b' => 'ابتدا به تنهایی برنامه‌ریزی و بررسی می‌کنم و بعد وارد جمع می‌شوم.',
                ],
                [
                    'text' => 'در رویدادهای شبکه‌سازی چه رفتاری دارید؟',
                    'option_a' => 'شروع‌کننده گفت‌وگو هستم و کارت ویزیت رد و بدل می‌کنم.',
                    'option_b' => 'صبر می‌کنم تا دیگران سر صحبت را باز کنند یا گفتگوهای کوچک‌تر انتخاب می‌کنم.',
                ],
                [
                    'text' => 'وقتی به شهر جدیدی سفر می‌کنید چگونه مکان‌ها را می‌شناسید؟',
                    'option_a' => 'با افراد محلی صحبت می‌کنم و پیشنهاد می‌گیرم.',
                    'option_b' => 'با تحقیقات شخصی و آرام مکان‌ها را کشف می‌کنم.',
                ],
                [
                    'text' => 'در کلاس یا کارگاه آموزشی چه رویکردی دارید؟',
                    'option_a' => 'بلافاصله سوال می‌پرسم و نظر می‌دهم.',
                    'option_b' => 'بیشتر یادداشت برمی‌دارم و بعداً سوال‌هایم را مرور می‌کنم.',
                ],
                [
                    'text' => 'هنگام تصمیم‌گیری فوری چه کار می‌کنید؟',
                    'option_a' => 'با چند نفر تماس می‌گیرم و نظرشان را می‌پرسم.',
                    'option_b' => 'در فضای شخصی خلوت می‌کنم تا تصمیم بگیرم.',
                ],
                [
                    'text' => 'برای جشن تولد خود چه برنامه‌ای ترجیح می‌دهید؟',
                    'option_a' => 'برنامه‌ای شاد با حضور افراد زیاد برگزار می‌کنم.',
                    'option_b' => 'جمعی صمیمی یا زمانی آرام با افراد محدود را می‌پسندم.',
                ],
                [
                    'text' => 'پس از یک روز کاری پرمشغله چگونه انرژی می‌گیرید؟',
                    'option_a' => 'دیدار با دوستان یا رویدادهای اجتماعی به من انرژی می‌دهد.',
                    'option_b' => 'تنهایی و سکوت برای بازیابی انرژی لازم است.',
                ],
                [
                    'text' => 'در جلسه‌ای که افراد جدید حضور دارند چه می‌کنید؟',
                    'option_a' => 'سریع معرفی می‌کنم و گفتگو را شروع می‌کنم.',
                    'option_b' => 'ابتدا محیط را می‌سنجم و سپس وارد گفتگو می‌شوم.',
                ],
                [
                    'text' => 'در اوقات فراغت آخر هفته چه انتخابی دارید؟',
                    'option_a' => 'فعالیت‌های اجتماعی و گروهی را برنامه‌ریزی می‌کنم.',
                    'option_b' => 'فعالیت‌های فردی مثل مطالعه یا فیلم دیدن را ترجیح می‌دهم.',
                ],
                [
                    'text' => 'در سفرهای کاری بعد از اتمام جلسات چه می‌کنید؟',
                    'option_a' => 'همراهی همکاران در برنامه‌های جانبی را دوست دارم.',
                    'option_b' => 'پس از کار استراحت آرام در هتل را ترجیح می‌دهم.',
                ],
                [
                    'text' => 'در اولین روز حضور در تیم جدید چگونه ظاهر می‌شوید؟',
                    'option_a' => 'با همه آشنا می‌شوم و پرسش‌های زیادی می‌پرسم.',
                    'option_b' => 'در ابتدا بیشتر مشاهده می‌کنم و قدم‌به‌قدم وارد جمع می‌شوم.',
                ],
            ],
            'SN' => [
                [
                    'text' => 'هنگام دریافت دستورالعمل جدید چه چیزی توجه شما را جلب می‌کند؟',
                    'option_a' => 'جزئیات دقیق و مراحل مشخص هر کار.',
                    'option_b' => 'هدف کلی و ایده پشت دستورالعمل.',
                ],
                [
                    'text' => 'در یادگیری ابزار تازه چه روشی را ترجیح می‌دهید؟',
                    'option_a' => 'با مثال‌های واقعی و تمرین عملی پیش می‌روم.',
                    'option_b' => 'در مورد کاربردهای احتمالی آینده آن فکر می‌کنم.',
                ],
                [
                    'text' => 'در گفت‌وگو درباره آینده شرکت به چه چیزی تکیه می‌کنید؟',
                    'option_a' => 'به اعداد و اطلاعات موجود.',
                    'option_b' => 'به احتمالات و سناریوهای نو.',
                ],
                [
                    'text' => 'وقتی گزارش می‌نویسید بیشتر چه چیز را برجسته می‌کنید؟',
                    'option_a' => 'واقعیت‌ها و مشاهدات قابل اندازه‌گیری.',
                    'option_b' => 'برداشت‌ها و فرصت‌های جدید.',
                ],
                [
                    'text' => 'در مواجهه با مشکل فنی چه رویکردی دارید؟',
                    'option_a' => 'مرحله‌به‌مرحله علت ملموس را پیدا می‌کنم.',
                    'option_b' => 'به دنبال الگوهای پنهان و ایده‌های تازه می‌گردم.',
                ],
                [
                    'text' => 'در بحث درباره محصول به چه موضوعی می‌پردازید؟',
                    'option_a' => 'ویژگی‌های کنونی و عملکرد اثبات‌شده.',
                    'option_b' => 'قابلیت‌های بالقوه آینده و مسیر رشد.',
                ],
                [
                    'text' => 'در تحلیل تجربه مشتری چه چیزی برایتان مهم‌تر است؟',
                    'option_a' => 'بازخوردهای مشخص و مثال‌های واقعی.',
                    'option_b' => 'احساس کلی مخاطب و روندهای احتمالی.',
                ],
                [
                    'text' => 'در برنامه‌ریزی آموزشی چه سبکی را انتخاب می‌کنید؟',
                    'option_a' => 'تقسیم سرفصل‌ها به بخش‌های دقیق و زمانی.',
                    'option_b' => 'چیدن موضوعات به صورت ایده‌های ترکیبی و الهام‌بخش.',
                ],
                [
                    'text' => 'در یادگیری مهارت جدید چه تمرینی را می‌پسندید؟',
                    'option_a' => 'تمرین‌های تکراری و استاندارد.',
                    'option_b' => 'روش‌های خلاقانه و غیرمعمول.',
                ],
                [
                    'text' => 'در خواندن یک مقاله علمی بر چه بخشی تمرکز دارید؟',
                    'option_a' => 'داده‌ها، نمودارها و مثال‌های واقعی.',
                    'option_b' => 'نظریه‌ها، استعاره‌ها و پیام کلی.',
                ],
                [
                    'text' => 'در ارزیابی پیشنهاد همکار دنبال چه هستید؟',
                    'option_a' => 'شواهد و تجربه‌های مشابه.',
                    'option_b' => 'نوآوری و فرصت‌های بلندمدت.',
                ],
                [
                    'text' => 'وقتی دستور آشپزی می‌خوانید چگونه پیش می‌روید؟',
                    'option_a' => 'مقادیر دقیق و گام‌به‌گام را دنبال می‌کنم.',
                    'option_b' => 'با توجه به طعم نهایی دستور را تغییر می‌دهم.',
                ],
                [
                    'text' => 'در بازدید از نمایشگاه چه چیز برایتان جذاب‌تر است؟',
                    'option_a' => 'جزئیات فیزیکی و ویژگی‌های قابل مشاهده آثار.',
                    'option_b' => 'مفهوم کلی و پیام نهفته هر اثر.',
                ],
                [
                    'text' => 'در برنامه‌ریزی مالی به چه موضوعی توجه می‌کنید؟',
                    'option_a' => 'درآمدها و هزینه‌های فعلی.',
                    'option_b' => 'سناریوهای سرمایه‌گذاری و ایده‌های آینده‌نگر.',
                ],
                [
                    'text' => 'در گفتگو درباره تحول دیجیتال چه چیزی برایتان مهم‌تر است؟',
                    'option_a' => 'قابلیت‌های موجود و زیرساخت فعلی.',
                    'option_b' => 'فرصت‌های نو و راه‌حل‌های خلاقانه آینده.',
                ],
            ],
            'TF' => [
                [
                    'text' => 'در تصمیم‌گیری‌های مدیریتی چه چیزی تعیین‌کننده است؟',
                    'option_a' => 'تحلیل منطقی داده‌ها و معیارهای عینی.',
                    'option_b' => 'تأثیر تصمیم بر افراد تیم و روابط انسانی.',
                ],
                [
                    'text' => 'در بازخورد دادن کدام رویکرد را ترجیح می‌دهید؟',
                    'option_a' => 'صریح و بر اساس شاخص‌های عملکردی صحبت می‌کنم.',
                    'option_b' => 'لحن حمایتی انتخاب می‌کنم تا احساسات آسیب نبیند.',
                ],
                [
                    'text' => 'در حل اختلاف بین همکاران چه می‌کنید؟',
                    'option_a' => 'به حقایق و قراردادها استناد می‌کنم.',
                    'option_b' => 'احساسات هر فرد را شنیده و به سازش فکر می‌کنم.',
                ],
                [
                    'text' => 'در انتخاب راه‌حل به چه چیز بیشتر تکیه دارید؟',
                    'option_a' => 'مزایا و معایب عینی را می‌سنجم.',
                    'option_b' => 'بر اساس ارزش‌ها و اثر اجتماعی تصمیم می‌گیرم.',
                ],
                [
                    'text' => 'در زمان فشار کاری اولویت شما چیست؟',
                    'option_a' => 'تمرکز بر نتیجه و کارایی.',
                    'option_b' => 'حفظ روحیه و حمایت از تیم.',
                ],
                [
                    'text' => 'در ارزیابی عملکرد چه چیز را ملاک قرار می‌دهید؟',
                    'option_a' => 'شاخص‌های کمی و سنجش‌پذیر.',
                    'option_b' => 'تلاش‌ها و شرایط فردی.',
                ],
                [
                    'text' => 'در مذاکره کدام روش را انتخاب می‌کنید؟',
                    'option_a' => 'استدلال منطقی و شواهد را مطرح می‌کنم.',
                    'option_b' => 'رابطه و اعتماد طرف مقابل را حفظ می‌کنم.',
                ],
                [
                    'text' => 'در انتخاب شریک کاری چه چیزی مهم‌تر است؟',
                    'option_a' => 'قابلیت‌ها و سوابق حرفه‌ای.',
                    'option_b' => 'تناسب ارزش‌ها و هماهنگی شخصیتی.',
                ],
                [
                    'text' => 'در بازخورد به شکست پروژه چگونه عمل می‌کنید؟',
                    'option_a' => 'علت‌ها و خطاهای سیستم را تحلیل می‌کنم.',
                    'option_b' => 'ابتدا به احساسات تیم می‌پردازم و سپس بهبود را بررسی می‌کنم.',
                ],
                [
                    'text' => 'در اولویت‌بندی کارها چه ملاکی دارید؟',
                    'option_a' => 'کارهایی که بیشترین اثر منطقی دارند.',
                    'option_b' => 'کارهایی که برای افراد مهم‌تر است.',
                ],
                [
                    'text' => 'در مواجهه با اختلاف نظر چگونه پیش می‌روید؟',
                    'option_a' => 'به اصول و قوانین تکیه می‌کنم.',
                    'option_b' => 'راه‌حلی می‌یابم که به رابطه صدمه نزند.',
                ],
                [
                    'text' => 'در طراحی سیاست‌های سازمان چه رویکردی دارید؟',
                    'option_a' => 'عدالت و استاندارد یکسان را اجرا می‌کنم.',
                    'option_b' => 'استثناهایی برای شرایط انسانی در نظر می‌گیرم.',
                ],
                [
                    'text' => 'در تحلیل یک تصمیم قدیمی به چه چیزی نگاه می‌کنید؟',
                    'option_a' => 'به سود و زیان واقعی تصمیم.',
                    'option_b' => 'به میزان رضایت یا نارضایتی افراد مرتبط.',
                ],
                [
                    'text' => 'در انتخاب تیم پروژه چه عاملی مهم است؟',
                    'option_a' => 'مهارت‌های مکمل و کارایی اعضا.',
                    'option_b' => 'هماهنگی احساسی و همکاری صمیمی.',
                ],
                [
                    'text' => 'در ارتباط روزمره چه سبکی را می‌پسندید؟',
                    'option_a' => 'مستقیم، خلاصه و بدون حاشیه صحبت می‌کنم.',
                    'option_b' => 'با توجه به احساس مخاطب صحبت را تنظیم می‌کنم.',
                ],
            ],
            'JP' => [
                [
                    'text' => 'در شروع پروژه جدید چه کاری انجام می‌دهید؟',
                    'option_a' => 'زمان‌بندی دقیق و فهرست وظایف می‌سازم.',
                    'option_b' => 'با طرح کلی شروع می‌کنم و در مسیر تکمیل می‌کنم.',
                ],
                [
                    'text' => 'در مدیریت ایمیل‌ها چه روشی دارید؟',
                    'option_a' => 'صندوق را همواره مرتب و خالی نگه می‌دارم.',
                    'option_b' => 'بر اساس اولویت لحظه‌ای پاسخ می‌دهم.',
                ],
                [
                    'text' => 'اگر مهلت کاری نزدیک باشد چگونه عمل می‌کنید؟',
                    'option_a' => 'از قبل کار را تمام می‌کنم تا خیال راحت باشد.',
                    'option_b' => 'تا نزدیک ددلاین روی موضوع کار می‌کنم و به انعطاف تکیه دارم.',
                ],
                [
                    'text' => 'در برنامه‌ریزی سفر چه رویکردی را ترجیح می‌دهید؟',
                    'option_a' => 'برنامه دقیق روزانه تنظیم می‌کنم.',
                    'option_b' => 'به برنامه‌های ناگهانی و کشف مسیر علاقه دارم.',
                ],
                [
                    'text' => 'در سازماندهی میز کار اخلاق شما چیست؟',
                    'option_a' => 'وسایل باید جای مشخص داشته باشند.',
                    'option_b' => 'اگرچه شلوغ است اما می‌دانم هر چیز کجاست.',
                ],
                [
                    'text' => 'در مواجهه با تغییر برنامه چه واکنشی دارید؟',
                    'option_a' => 'ترجیح می‌دهم حداقل از قبل اطلاع داشته باشم.',
                    'option_b' => 'با تغییر ناگهانی مشکلی ندارم و با جریان حرکت می‌کنم.',
                ],
                [
                    'text' => 'در تهیه گزارش چه سبکی را دنبال می‌کنید؟',
                    'option_a' => 'طبق قالب ثابت و زمان‌بندی‌شده پیش می‌روم.',
                    'option_b' => 'اگر ایده بهتری باشد قالب را تغییر می‌دهم.',
                ],
                [
                    'text' => 'هنگام مطالعه چه برنامه‌ای دارید؟',
                    'option_a' => 'برنامه روزانه مشخص و ثابت دارم.',
                    'option_b' => 'مطالعه را بر اساس علاقه لحظه‌ای انتخاب می‌کنم.',
                ],
                [
                    'text' => 'در فعالیت‌های گروهی نقش شما چیست؟',
                    'option_a' => 'مسئول پیگیری و اطمینان از اتمام کار هستم.',
                    'option_b' => 'نقش ایجاد انعطاف و سازگاری با شرایط را می‌پذیرم.',
                ],
                [
                    'text' => 'در تصمیم‌گیری شخصی چه روشی دارید؟',
                    'option_a' => 'لیست مزایا و معایب می‌نویسم.',
                    'option_b' => 'احساس لحظه‌ای و گزینه‌های در دسترس را می‌سنجم.',
                ],
                [
                    'text' => 'در مدیریت کارهای خانه چگونه عمل می‌کنید؟',
                    'option_a' => 'برنامه هفتگی می‌چینم و اجرا می‌کنم.',
                    'option_b' => 'کارها را زمانی انجام می‌دهم که لازم شود.',
                ],
                [
                    'text' => 'در مواجهه با فرصت غیرمنتظره چه کار می‌کنید؟',
                    'option_a' => 'بررسی می‌کنم با برنامه فعلی سازگار است یا نه.',
                    'option_b' => 'با اشتیاق امتحان می‌کنم و سپس برنامه را تنظیم می‌کنم.',
                ],
                [
                    'text' => 'در یادگیری مهارت جدید چه شیوه‌ای دارید؟',
                    'option_a' => 'مسیر آموزشی مشخص و گام‌به‌گام را دنبال می‌کنم.',
                    'option_b' => 'ترکیبی از منابع مختلف را بر اساس علاقه انتخاب می‌کنم.',
                ],
                [
                    'text' => 'در پایان هفته چه رویکردی دارید؟',
                    'option_a' => 'برای هفته بعد برنامه‌ریزی از پیش انجام می‌دهم.',
                    'option_b' => 'اجازه می‌دهم برنامه‌ها به‌صورت خودجوش شکل بگیرند.',
                ],
                [
                    'text' => 'در مدیریت پروژه‌های چندگانه چگونه عمل می‌کنید؟',
                    'option_a' => 'از ابزارهای کنترل پیشرفت استفاده می‌کنم.',
                    'option_b' => 'با اولویت‌های در حال تغییر کنار می‌آیم و برنامه را بازتنظیم می‌کنم.',
                ],
            ],
        ];

        $dimensionCodes = [
            'EI' => ['positive' => 'E', 'negative' => 'I'],
            'SN' => ['positive' => 'S', 'negative' => 'N'],
            'TF' => ['positive' => 'T', 'negative' => 'F'],
            'JP' => ['positive' => 'J', 'negative' => 'P'],
        ];

        $questions = [];
        $counter = 1;

        foreach (['EI', 'SN', 'TF', 'JP'] as $dimensionKey) {
            foreach ($dimensionPrompts[$dimensionKey] as $prompt) {
                $positive = $dimensionCodes[$dimensionKey]['positive'];
                $negative = $dimensionCodes[$dimensionKey]['negative'];

                $questions[] = [
                    'code' => sprintf('MBTI-Q%02d', $counter),
                    'text' => $prompt['text'],
                    'type' => 'dual_choice',
                    'options' => [
                        [
                            'value' => 'A',
                            'label' => $prompt['option_a'],
                            'score' => ['dimension' => $positive, 'points' => 1],
                        ],
                        [
                            'value' => 'B',
                            'label' => $prompt['option_b'],
                            'score' => ['dimension' => $negative, 'points' => 1],
                        ],
                    ],
                    'answer_key' => null,
                    'weight' => 1,
                    'metadata' => [
                        'dimension_pair' => $dimensionKey,
                        'option_scores' => ['A' => $positive, 'B' => $negative],
                    ],
                ];

                $counter++;
            }
        }

        return $questions;
    }

    private function generateDiscQuestionBlueprints(): array
    {
        $instruction = 'یکی از عبارت‌های زیر را به عنوان «بهترین توصیف» و یکی دیگر را به عنوان «ضعیف‌ترین توصیف» از خودتان انتخاب کنید.';

        $optionGroups = [
            [
                ['dimension' => 'D', 'label' => 'قاطع و مصمم'],
                ['dimension' => 'I', 'label' => 'صمیمی و پرانرژی'],
                ['dimension' => 'S', 'label' => 'صبور و قابل اعتماد'],
                ['dimension' => 'C', 'label' => 'دقیق و منظم'],
            ],
            [
                ['dimension' => 'D', 'label' => 'رقابتی و نتیجه‌گرا'],
                ['dimension' => 'I', 'label' => 'خلاق و الهام‌بخش'],
                ['dimension' => 'S', 'label' => 'حامی و همدل'],
                ['dimension' => 'C', 'label' => 'موشکاف و محتاط'],
            ],
            [
                ['dimension' => 'D', 'label' => 'مستقل و جسور'],
                ['dimension' => 'I', 'label' => 'اجتماعی و تاثیرگذار'],
                ['dimension' => 'S', 'label' => 'قابل پیش‌بینی و آرام'],
                ['dimension' => 'C', 'label' => 'قانون‌مند و ساختارگرا'],
            ],
            [
                ['dimension' => 'D', 'label' => 'سرسخت و تصمیم‌گیر'],
                ['dimension' => 'I', 'label' => 'شوخ‌طبع و خوش‌صحبت'],
                ['dimension' => 'S', 'label' => 'همکار و تیم‌محور'],
                ['dimension' => 'C', 'label' => 'منطقی و تحلیل‌گر'],
            ],
            [
                ['dimension' => 'D', 'label' => 'متمرکز بر هدف'],
                ['dimension' => 'I', 'label' => 'الهام‌بخش و انگیزه‌دهنده'],
                ['dimension' => 'S', 'label' => 'وفادار و قابل اتکا'],
                ['dimension' => 'C', 'label' => 'دقیق و حسابگر'],
            ],
            [
                ['dimension' => 'D', 'label' => 'مقتدر و بی‌پروا'],
                ['dimension' => 'I', 'label' => 'پرشور و تاثیرگذار'],
                ['dimension' => 'S', 'label' => 'متین و آرام'],
                ['dimension' => 'C', 'label' => 'نظام‌مند و منضبط'],
            ],
            [
                ['dimension' => 'D', 'label' => 'سریع و قاطع'],
                ['dimension' => 'I', 'label' => 'دوستانه و پرانرژی'],
                ['dimension' => 'S', 'label' => 'ثابت‌قدم و با ثبات'],
                ['dimension' => 'C', 'label' => 'منظم و محتاط'],
            ],
            [
                ['dimension' => 'D', 'label' => 'پیگیر و نتیجه‌محور'],
                ['dimension' => 'I', 'label' => 'انعطاف‌پذیر و شاد'],
                ['dimension' => 'S', 'label' => 'متعادل و آرام‌بخش'],
                ['dimension' => 'C', 'label' => 'دقیق و مستند'],
            ],
            [
                ['dimension' => 'D', 'label' => 'بااعتمادبه‌نفس و فوری'],
                ['dimension' => 'I', 'label' => 'گفتگو محور و گرم'],
                ['dimension' => 'S', 'label' => 'صبور و دلگرم‌کننده'],
                ['dimension' => 'C', 'label' => 'دقیق و استاندارد'],
            ],
            [
                ['dimension' => 'D', 'label' => 'ریسک‌پذیر و جسور'],
                ['dimension' => 'I', 'label' => 'مشتاق و تشویق‌کننده'],
                ['dimension' => 'S', 'label' => 'حمایتگر و مراقب'],
                ['dimension' => 'C', 'label' => 'تحقیق‌گر و منطقی'],
            ],
            [
                ['dimension' => 'D', 'label' => 'قاطع در تصمیم'],
                ['dimension' => 'I', 'label' => 'بی‌باک در معاشرت'],
                ['dimension' => 'S', 'label' => 'پایدار و آرام'],
                ['dimension' => 'C', 'label' => 'دقیق در جزئیات'],
            ],
            [
                ['dimension' => 'D', 'label' => 'پیشرو و هدایتگر'],
                ['dimension' => 'I', 'label' => 'خوش‌بین و الهام‌بخش'],
                ['dimension' => 'S', 'label' => 'متواضع و آرام'],
                ['dimension' => 'C', 'label' => 'ساختارگرا و منظم'],
            ],
            [
                ['dimension' => 'D', 'label' => 'محکم و استوار'],
                ['dimension' => 'I', 'label' => 'زنده‌دل و پرهیجان'],
                ['dimension' => 'S', 'label' => 'دلپذیر و مهربان'],
                ['dimension' => 'C', 'label' => 'تحلیل‌گر و سنجیده'],
            ],
            [
                ['dimension' => 'D', 'label' => 'محرک و فعال'],
                ['dimension' => 'I', 'label' => 'مهمان‌نواز و جذاب'],
                ['dimension' => 'S', 'label' => 'قابل پیش‌بینی و منظم'],
                ['dimension' => 'C', 'label' => 'دقیق و حساب‌شده'],
            ],
            [
                ['dimension' => 'D', 'label' => 'تصمیم‌گیر سریع'],
                ['dimension' => 'I', 'label' => 'دوست‌داشتنی و متقاعدکننده'],
                ['dimension' => 'S', 'label' => 'ثابت و قابل اتکا'],
                ['dimension' => 'C', 'label' => 'موشکاف و دقیق'],
            ],
            [
                ['dimension' => 'D', 'label' => 'بی‌پروا و مصمم'],
                ['dimension' => 'I', 'label' => 'سرگرم‌کننده و پرنشاط'],
                ['dimension' => 'S', 'label' => 'مهربان و پشتیبان'],
                ['dimension' => 'C', 'label' => 'کامل‌گرا و دقیق'],
            ],
            [
                ['dimension' => 'D', 'label' => 'مهاجم و مستقیم'],
                ['dimension' => 'I', 'label' => 'الهام‌بخش و پرحرف'],
                ['dimension' => 'S', 'label' => 'وفادار و هماهنگ'],
                ['dimension' => 'C', 'label' => 'تحلیل‌گر و محتاط'],
            ],
            [
                ['dimension' => 'D', 'label' => 'سریع در اقدام'],
                ['dimension' => 'I', 'label' => 'حضور اجتماعی قوی'],
                ['dimension' => 'S', 'label' => 'ملایم و سازگار'],
                ['dimension' => 'C', 'label' => 'منظم و حساب‌شده'],
            ],
            [
                ['dimension' => 'D', 'label' => 'محکم در مذاکره'],
                ['dimension' => 'I', 'label' => 'احساساتی و تاثیرگذار'],
                ['dimension' => 'S', 'label' => 'سازگار و آرام'],
                ['dimension' => 'C', 'label' => 'دقیق و با کیفیت'],
            ],
            [
                ['dimension' => 'D', 'label' => 'قاطع و صریح'],
                ['dimension' => 'I', 'label' => 'دوستانه و صمیمی'],
                ['dimension' => 'S', 'label' => 'دلگرم و آرامش‌بخش'],
                ['dimension' => 'C', 'label' => 'منطقی و دقیق'],
            ],
            [
                ['dimension' => 'D', 'label' => 'متمرکز بر نتیجه نهایی'],
                ['dimension' => 'I', 'label' => 'پرشور و ارتباطی'],
                ['dimension' => 'S', 'label' => 'پیوسته و قابل اعتماد'],
                ['dimension' => 'C', 'label' => 'دقیق و تحلیل‌محور'],
            ],
            [
                ['dimension' => 'D', 'label' => 'فرمانده و قاطع'],
                ['dimension' => 'I', 'label' => 'اثرگذار و تعامل‌گرا'],
                ['dimension' => 'S', 'label' => 'صبر و تحمل بالا'],
                ['dimension' => 'C', 'label' => 'محتاط در تصمیم‌گیری'],
            ],
            [
                ['dimension' => 'D', 'label' => 'مطمئن و پیش‌برنده'],
                ['dimension' => 'I', 'label' => 'پرنشاط و گرم'],
                ['dimension' => 'S', 'label' => 'مهربان و مراقب'],
                ['dimension' => 'C', 'label' => 'شیفته کیفیت و نظم'],
            ],
            [
                ['dimension' => 'D', 'label' => 'جسور و پیش‌قدم'],
                ['dimension' => 'I', 'label' => 'مجذوب تعامل اجتماعی'],
                ['dimension' => 'S', 'label' => 'مهربان و آرام'],
                ['dimension' => 'C', 'label' => 'با تمرکز بر جزئیات'],
            ],
            [
                ['dimension' => 'D', 'label' => 'نتیجه‌گرا و جدی'],
                ['dimension' => 'I', 'label' => 'شورانگیز و مثبت'],
                ['dimension' => 'S', 'label' => 'ثابت و همراه'],
                ['dimension' => 'C', 'label' => 'تحلیل‌گر و دقیق'],
            ],
            [
                ['dimension' => 'D', 'label' => 'پرقدرت و بی‌باک'],
                ['dimension' => 'I', 'label' => 'باانرژی و خوش‌رو'],
                ['dimension' => 'S', 'label' => 'آرام و حمایتگر'],
                ['dimension' => 'C', 'label' => 'برنامه‌ریز و منظم'],
            ],
            [
                ['dimension' => 'D', 'label' => 'صریح و مقتدر'],
                ['dimension' => 'I', 'label' => 'سرگرم‌کننده و خوش‌برخورد'],
                ['dimension' => 'S', 'label' => 'دلگرم‌کننده و صبور'],
                ['dimension' => 'C', 'label' => 'دقیق و سیستماتیک'],
            ],
            [
                ['dimension' => 'D', 'label' => 'متمرکز بر دستاورد'],
                ['dimension' => 'I', 'label' => 'نوآور و ارتباطی'],
                ['dimension' => 'S', 'label' => 'پایداری آرامش‌بخش'],
                ['dimension' => 'C', 'label' => 'باوجدان و دقیق'],
            ],
        ];

        $questions = [];

        foreach ($optionGroups as $index => $group) {
            $options = [];
            $optionDimensions = [];

            foreach ($group as $optionIndex => $optionDefinition) {
                $optionValue = chr(65 + $optionIndex);

                $options[] = [
                    'value' => $optionValue,
                    'label' => $optionDefinition['label'],
                    'dimension' => $optionDefinition['dimension'],
                    'score' => [
                        'best' => [
                            'dimension' => $optionDefinition['dimension'],
                            'points' => 1,
                        ],
                        'least' => [
                            'dimension' => $optionDefinition['dimension'],
                            'points' => -1,
                        ],
                    ],
                ];

                $optionDimensions[$optionValue] = $optionDefinition['dimension'];
            }

            $questions[] = [
                'code' => sprintf('DISC-Q%02d', $index + 1),
                'text' => $instruction,
                'type' => 'forced_choice',
                'options' => $options,
                'weight' => 1,
                'metadata' => [
                    'response_mode' => 'best_least',
                    'option_dimensions' => $optionDimensions,
                    'note' => 'برای هر سوال یک گزینه را به عنوان بهترین و یک گزینه را به عنوان ضعیف‌ترین انتخاب کنید.',
                ],
            ];
        }

        return $questions;
    }

    private function generateAnalyticalQuestionBlueprints(): array
    {
        $templates = [
            [
                'text' => 'کدام گزینه ادامهٔ منطقی الگوی عددی ۲، ۶، ۱۲، ۲۰، ... است؟',
                'skill' => 'pattern_recognition',
                'difficulty' => 'medium',
                'answer' => 'B',
                'options' => [
                    'A' => '۲۸',
                    'B' => '۳۰',
                    'C' => '۳۲',
                    'D' => '۳۶',
                ],
                'explanation' => 'اختلاف‌ها ۴، ۶ و ۸ هستند؛ اختلاف بعدی ۱۰ است و عدد بعدی ۳۰ می‌شود.',
            ],
            [
                'text' => 'اگر دو برابر عددی به اضافهٔ ۶ برابر با ۲۲ باشد، آن عدد چیست؟',
                'skill' => 'algebraic_reasoning',
                'difficulty' => 'easy',
                'answer' => 'C',
                'options' => [
                    'A' => '۶',
                    'B' => '۷',
                    'C' => '۸',
                    'D' => '۱۲',
                ],
                'explanation' => '۲x + ۶ = ۲۲، بنابراین ۲x = ۱۶ و در نتیجه x = ۸.',
            ],
            [
                'text' => 'قیمت یک محصول ۲۵۰٬۰۰۰ تومان است و ۱۰٪ تخفیف می‌گیرد. قیمت نهایی چقدر است؟',
                'skill' => 'quantitative_analysis',
                'difficulty' => 'easy',
                'answer' => 'B',
                'options' => [
                    'A' => '۲۰۰٬۰۰۰ تومان',
                    'B' => '۲۲۵٬۰۰۰ تومان',
                    'C' => '۲۳۰٬۰۰۰ تومان',
                    'D' => '۲۳۵٬۰۰۰ تومان',
                ],
                'explanation' => '۱۰٪ از ۲۵۰٬۰۰۰ برابر ۲۵٬۰۰۰ است؛ قیمت نهایی ۲۲۵٬۰۰۰ تومان می‌شود.',
            ],
            [
                'text' => 'خودرویی مسافت ۱۸۰ کیلومتر را در ۳ ساعت طی می‌کند. سرعت میانگین خودرو چقدر است؟',
                'skill' => 'rate_and_speed',
                'difficulty' => 'easy',
                'answer' => 'D',
                'options' => [
                    'A' => '۴۵ کیلومتر بر ساعت',
                    'B' => '۵۰ کیلومتر بر ساعت',
                    'C' => '۵۵ کیلومتر بر ساعت',
                    'D' => '۶۰ کیلومتر بر ساعت',
                ],
                'explanation' => '۱۸۰ تقسیم بر ۳ برابر ۶۰ کیلومتر بر ساعت است.',
            ],
            [
                'text' => 'همهٔ تحلیلگران دقیق هستند. امیر یک تحلیلگر است. کدام نتیجه معتبر است؟',
                'skill' => 'logical_deduction',
                'difficulty' => 'easy',
                'answer' => 'C',
                'options' => [
                    'A' => 'هیچ تحلیلگری دقیق نیست.',
                    'B' => 'ممکن است امیر دقیق نباشد.',
                    'C' => 'امیر قطعاً دقیق است.',
                    'D' => 'هیچ نتیجه‌ای نمی‌توان گرفت.',
                ],
                'explanation' => 'اگر همهٔ تحلیلگران دقیق‌اند و امیر تحلیلگر است، او نیز دقیق است.',
            ],
            [
                'text' => 'میانگین پنج عدد برابر ۱۸ است. اگر عدد ۱۲ حذف شود، میانگین چهار عدد باقی‌مانده چقدر می‌شود؟',
                'skill' => 'data_interpretation',
                'difficulty' => 'medium',
                'answer' => 'C',
                'options' => [
                    'A' => '۱۸',
                    'B' => '۱۸٫۵',
                    'C' => '۱۹٫۵',
                    'D' => '۲۰',
                ],
                'explanation' => 'جمع پنج عدد ۹۰ است؛ با حذف ۱۲ جمع ۷۸ و میانگین جدید ۱۹٫۵ خواهد بود.',
            ],
            [
                'text' => 'دو سکهٔ سالم پرتاب می‌شوند. احتمال آن‌که دست‌کم یک رو به دست آید چقدر است؟',
                'skill' => 'probability',
                'difficulty' => 'easy',
                'answer' => 'C',
                'options' => [
                    'A' => '۱/۴',
                    'B' => '۱/۲',
                    'C' => '۳/۴',
                    'D' => '۱',
                ],
                'explanation' => 'تنها حالت بدون رو شیر-شیر است؛ احتمال آن ۱/۴ است، پس مکمل آن ۳/۴ می‌شود.',
            ],
            [
                'text' => 'در شرکتی با ۲۴۰ کارمند، ۳۵٪ کارکنان در تیم تحلیل داده کار می‌کنند. تعداد این تیم چند نفر است؟',
                'skill' => 'percentage',
                'difficulty' => 'easy',
                'answer' => 'A',
                'options' => [
                    'A' => '۸۴ نفر',
                    'B' => '۷۲ نفر',
                    'C' => '۹۰ نفر',
                    'D' => '۹۶ نفر',
                ],
                'explanation' => '۳۵٪ از ۲۴۰ برابر ۸۴ است.',
            ],
            [
                'text' => 'محیط مستطیلی ۴۸ واحد و طول آن ۱۴ واحد است. عرض مستطیل چقدر است؟',
                'skill' => 'spatial_reasoning',
                'difficulty' => 'medium',
                'answer' => 'D',
                'options' => [
                    'A' => '۸',
                    'B' => '۹',
                    'C' => '۱۲',
                    'D' => '۱۰',
                ],
                'explanation' => 'محیط ۲(طول + عرض) است؛ ۲(۱۴ + عرض) = ۴۸ ⇒ عرض = ۱۰.',
            ],
            [
                'text' => 'اگر ۳( x − ۲ ) = ۱۵ باشد، مقدار x چیست؟',
                'skill' => 'algebraic_reasoning',
                'difficulty' => 'easy',
                'answer' => 'A',
                'options' => [
                    'A' => '۷',
                    'B' => '۹',
                    'C' => '۱۱',
                    'D' => '۱۳',
                ],
                'explanation' => '۳x − ۶ = ۱۵، پس ۳x = ۲۱ و x = ۷.',
            ],
            [
                'text' => 'میانگین فروش سه شعبه ۱۴۰ واحد است. فروش دو شعبهٔ اول ۱۲۰ و ۱۶۰ واحد است. فروش شعبهٔ سوم چقدر است؟',
                'skill' => 'data_interpretation',
                'difficulty' => 'medium',
                'answer' => 'B',
                'options' => [
                    'A' => '۱۲۰ واحد',
                    'B' => '۱۴۰ واحد',
                    'C' => '۱۵۰ واحد',
                    'D' => '۱۸۰ واحد',
                ],
                'explanation' => 'جمع کل ۴۲۰ است؛ با کم‌کردن ۱۲۰ و ۱۶۰ مقدار باقی ۱۴۰ خواهد بود.',
            ],
            [
                'text' => 'عدد بعدی در دنبالهٔ فیبوناچی ۱، ۱، ۲، ۳، ۵، ۸، ؟ کدام است؟',
                'skill' => 'pattern_recognition',
                'difficulty' => 'easy',
                'answer' => 'D',
                'options' => [
                    'A' => '۱۰',
                    'B' => '۱۱',
                    'C' => '۱۲',
                    'D' => '۱۳',
                ],
                'explanation' => 'مجموع دو عدد قبلی عدد بعدی را می‌سازد؛ ۵ + ۸ = ۱۳.',
            ],
            [
                'text' => 'نسبت دانشجویان کارشناسی به کارشناسی ارشد ۳ به ۲ است و تعداد دانشجویان کارشناسی ارشد ۱۵۰ نفر است. تعداد کل دانشجویان چقدر است؟',
                'skill' => 'ratio_and_proportion',
                'difficulty' => 'medium',
                'answer' => 'C',
                'options' => [
                    'A' => '۳۰۰ نفر',
                    'B' => '۳۶۰ نفر',
                    'C' => '۳۷۵ نفر',
                    'D' => '۳۹۰ نفر',
                ],
                'explanation' => 'هر سهم برابر ۷۵ است؛ مجموع ۵ سهم × ۷۵ = ۳۷۵ نفر.',
            ],
            [
                'text' => 'یک کارگر کاری را در ۱۲ روز و کارگر دیگری همان کار را در ۱۸ روز انجام می‌دهد. اگر با هم کار کنند، تکمیل کار چند روز طول می‌کشد؟',
                'skill' => 'work_rate',
                'difficulty' => 'medium',
                'answer' => 'B',
                'options' => [
                    'A' => '۶ روز',
                    'B' => '۷ روز و ۵ ساعت',
                    'C' => '۸ روز',
                    'D' => '۹ روز',
                ],
                'explanation' => 'نرخ ترکیبی ۱/۱۲ + ۱/۱۸ = ۵/۳۶ است؛ زمان برابر ۳۶/۵ ≈ ۷٫۲ روز یعنی حدود ۷ روز و ۵ ساعت.',
            ],
            [
                'text' => 'مجموع دو عدد صحیح متوالی ۳۱ است. مقدار عدد بزرگ‌تر کدام است؟',
                'skill' => 'algebraic_reasoning',
                'difficulty' => 'easy',
                'answer' => 'B',
                'options' => [
                    'A' => '۱۵',
                    'B' => '۱۶',
                    'C' => '۱۷',
                    'D' => '۱۸',
                ],
                'explanation' => 'اگر اعداد n و n+1 باشند، ۲n + ۱ = ۳۱ ⇒ n = ۱۵ و عدد بزرگ‌تر ۱۶ است.',
            ],
            [
                'text' => 'مجموع زوایای داخلی یک هفت‌ضلعی ساده چقدر است؟',
                'skill' => 'geometry_fundamentals',
                'difficulty' => 'medium',
                'answer' => 'B',
                'options' => [
                    'A' => '۷۲۰ درجه',
                    'B' => '۹۰۰ درجه',
                    'C' => '۱۰۸۰ درجه',
                    'D' => '۱۲۶۰ درجه',
                ],
                'explanation' => '(n − ۲) × ۱۸۰ برای n = ۷ مقدار ۹۰۰ درجه را می‌دهد.',
            ],
            [
                'text' => 'احتمال وقوع رخداد A برابر ۰٫۴ و رخداد B برابر ۰٫۳ است و دو رخداد مستقل هستند. احتمال وقوع همزمان آن‌ها چقدر است؟',
                'skill' => 'probability',
                'difficulty' => 'medium',
                'answer' => 'D',
                'options' => [
                    'A' => '۰٫۰۷',
                    'B' => '۰٫۲۴',
                    'C' => '۰٫۵۸',
                    'D' => '۰٫۱۲',
                ],
                'explanation' => 'برای رخدادهای مستقل، حاصل‌ضرب احتمالات ۰٫۴ × ۰٫۳ = ۰٫۱۲ است.',
            ],
            [
                'text' => 'سرمایه‌ای به مبلغ ۱٬۰۰۰٬۰۰۰ تومان با نرخ سود مرکب سالانهٔ ۱۲٪ سرمایه‌گذاری می‌شود. ارزش سرمایه بعد از دو سال چقدر است؟',
                'skill' => 'financial_reasoning',
                'difficulty' => 'medium',
                'answer' => 'C',
                'options' => [
                    'A' => '۱٬۱۲۰٬۰۰۰ تومان',
                    'B' => '۱٬۲۲۴٬۰۰۰ تومان',
                    'C' => '۱٬۲۵۴٬۴۰۰ تومان',
                    'D' => '۱٬۳۲۰٬۰۰۰ تومان',
                ],
                'explanation' => 'ارزش برابر ۱٬۰۰۰٬۰۰۰ × (۱٫۱۲)^۲ = ۱٬۲۵۴٬۴۰۰ تومان است.',
            ],
            [
                'text' => 'میانگین وزنی نمرات سه درس با ضرایب ۲، ۳ و ۵ و نمرات ۱۶، ۱۸ و ۲۰ چقدر است؟',
                'skill' => 'weighted_average',
                'difficulty' => 'medium',
                'answer' => 'B',
                'options' => [
                    'A' => '۱۸٫۲',
                    'B' => '۱۸٫۶',
                    'C' => '۱۸٫۸',
                    'D' => '۱۹',
                ],
                'explanation' => '(۲×۱۶ + ۳×۱۸ + ۵×۲۰) / ۱۰ = ۱۸٫۶.',
            ],
            [
                'text' => 'بودجه‌ای ۴۰۰ میلیون تومانی با نسبت ۲ : ۳ : ۵ بین سه تیم توزیع می‌شود. سهم تیم سوم چقدر است؟',
                'skill' => 'ratio_and_proportion',
                'difficulty' => 'easy',
                'answer' => 'C',
                'options' => [
                    'A' => '۱۲۰ میلیون تومان',
                    'B' => '۱۵۰ میلیون تومان',
                    'C' => '۲۰۰ میلیون تومان',
                    'D' => '۲۵۰ میلیون تومان',
                ],
                'explanation' => 'جمع نسبت‌ها ۱۰ است؛ ۵/۱۰ از ۴۰۰ میلیون برابر ۲۰۰ میلیون تومان است.',
            ],
        ];

        $questions = [];

        foreach ($templates as $index => $template) {
            $options = [];
            foreach ($template['options'] as $value => $label) {
                $isCorrect = $value === $template['answer'];
                $options[] = [
                    'value' => $value,
                    'label' => $label,
                    'score' => [
                        'is_correct' => $isCorrect,
                        'points' => $isCorrect ? 1 : 0,
                    ],
                ];
            }

            $metadata = [
                'skill' => $template['skill'],
                'difficulty' => $template['difficulty'],
            ];

            if (!empty($template['explanation'])) {
                $metadata['explanation'] = $template['explanation'];
            }

            $questions[] = [
                'code' => sprintf('ANALYTICAL-Q%02d', $index + 1),
                'text' => $template['text'],
                'type' => 'single_choice',
                'options' => $options,
                'answer' => $template['answer'],
                'weight' => $template['weight'] ?? 1,
                'metadata' => $metadata,
            ];
        }

        return $questions;
    }

    private function normalizeOptionalAmount($rawValue, array &$errors, string $errorKey, string $errorMessage)
    {
        if ($rawValue === null) {
            return null;
        }

        $value = trim((string) $rawValue);
        if ($value === '') {
            return null;
        }

        $value = UtilityHelper::persianToEnglish($value);

        $currencyTokens = ['تومان', 'ريال', 'ریال', 'rial', 'RIAL', 'toman', 'TOMAN'];
        $value = str_ireplace($currencyTokens, '', $value);

        $value = str_replace(['٫'], '.', $value);
        $value = str_replace(['٬'], '', $value);
        $value = preg_replace('/\s+/u', '', $value);

        if (strpos($value, '.') === false) {
            $commaVariants = [',', '،'];
            $commaCount = 0;
            foreach ($commaVariants as $comma) {
                $commaCount += substr_count($value, $comma);
            }

            if ($commaCount === 1) {
                $value = str_replace('،', ',', $value);
                $parts = explode(',', $value);
                $decimalCandidate = array_pop($parts);
                $integerCandidate = implode('', $parts);

                if ($decimalCandidate !== '' && strlen($decimalCandidate) <= 2) {
                    $value = $integerCandidate . '.' . $decimalCandidate;
                } else {
                    $value = $integerCandidate . $decimalCandidate;
                }
            } else {
                $value = str_replace([',', '،'], '', $value);
            }
        } else {
            $value = str_replace([',', '،'], '', $value);
        }

        if (!preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            if (!isset($errors[$errorKey])) {
                $errors[$errorKey] = $errorMessage;
            }

            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * Ensure organizations table exists in database
     */
    private function ensureOrganizationsTable()
    {
        $pdo = DatabaseHelper::getConnection();
        $tableExists = $pdo->query("SHOW TABLES LIKE 'organizations'")->fetch();

        if (!$tableExists) {
            $createSql = "CREATE TABLE IF NOT EXISTS `organizations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `code` VARCHAR(50) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `latin_name` VARCHAR(255) NOT NULL,
                `subdomain` VARCHAR(150) DEFAULT NULL,
                `organization_code` VARCHAR(100) NOT NULL,
                `username` VARCHAR(150) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `evaluation_unit` VARCHAR(255) DEFAULT NULL,
                `close_washup_after_confirmation` TINYINT(1) NOT NULL DEFAULT 0,
                `enable_region_area` TINYINT(1) NOT NULL DEFAULT 0,
                `allow_competency_notes` TINYINT(1) NOT NULL DEFAULT 0,
                `credit_amount` DECIMAL(15,2) DEFAULT NULL,
                `exam_fee_per_participant` DECIMAL(15,2) DEFAULT NULL,
                `score_range_1` VARCHAR(100) DEFAULT NULL,
                `score_range_2` VARCHAR(100) DEFAULT NULL,
                `score_range_3` VARCHAR(100) DEFAULT NULL,
                `score_range_4` VARCHAR(100) DEFAULT NULL,
                `score_range_5` VARCHAR(100) DEFAULT NULL,
                `logo_path` VARCHAR(255) DEFAULT NULL,
                `report_cover_logo_path` VARCHAR(255) DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_organizations_code` (`code`),
                UNIQUE KEY `uniq_organizations_subdomain` (`subdomain`),
                UNIQUE KEY `uniq_organizations_username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $pdo->exec($createSql);
        } else {
            try {
                $subdomainColumn = $pdo->query("SHOW COLUMNS FROM `organizations` LIKE 'subdomain'")->fetch();
                if ($subdomainColumn && strtoupper($subdomainColumn['Null']) === 'NO') {
                    $pdo->exec("ALTER TABLE `organizations` MODIFY `subdomain` VARCHAR(150) DEFAULT NULL");
                }

                $evaluationColumn = $pdo->query("SHOW COLUMNS FROM `organizations` LIKE 'evaluation_unit'")->fetch();
                if ($evaluationColumn && strtoupper($evaluationColumn['Null']) === 'NO') {
                    $pdo->exec("ALTER TABLE `organizations` MODIFY `evaluation_unit` VARCHAR(255) DEFAULT NULL");
                }

                $creditColumn = $pdo->query("SHOW COLUMNS FROM `organizations` LIKE 'credit_amount'")->fetch();
                if (!$creditColumn) {
                    $pdo->exec("ALTER TABLE `organizations` ADD `credit_amount` DECIMAL(15,2) DEFAULT NULL AFTER `allow_competency_notes`");
                }

                $examFeeColumn = $pdo->query("SHOW COLUMNS FROM `organizations` LIKE 'exam_fee_per_participant'")->fetch();
                if (!$examFeeColumn) {
                    $pdo->exec("ALTER TABLE `organizations` ADD `exam_fee_per_participant` DECIMAL(15,2) DEFAULT NULL AFTER `credit_amount`");
                }
            } catch (PDOException $exception) {
                // Ignore alteration errors silently to avoid breaking flow
            }
        }
    }
}