<?php

require_once __DIR__ . '/../Helpers/autoload.php';

class DeploymentController
{
    public function showMigrationAssistant(): void
    {
        AuthHelper::startSession();

        $appConfig = $this->loadConfig('app');
        $dbConfig = $this->loadConfig('database');

        $formData = $_SESSION['migration_form'] ?? [];
        $validationErrors = $_SESSION['migration_errors'] ?? [];
        unset($_SESSION['migration_form'], $_SESSION['migration_errors']);

        $flashSuccess = flash('success');
        $flashError = flash('error');

        $environmentInfo = [
            'base_url_detected' => UtilityHelper::baseUrl(),
            'app_url_configured' => $appConfig['url'] ?? null,
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'project_root' => dirname(__DIR__, 2),
            'public_path' => dirname(__DIR__, 2) . '/public',
            'is_localhost' => UtilityHelper::isLocalhost(),
        ];

        $viewData = [
            'appConfig' => $appConfig,
            'dbConfig' => $dbConfig,
            'environmentInfo' => $environmentInfo,
            'formData' => $formData,
            'validationErrors' => $validationErrors,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'updateUrl' => UtilityHelper::baseUrl('deployment/migration-assistant'),
        ];

        include __DIR__ . '/../Views/system/migration-assistant.php';
    }

    public function updateMigrationAssistant(): void
    {
        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('deployment/migration-assistant');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $input = [
            'app_url' => trim((string)($_POST['app_url'] ?? '')),
            'db_host' => trim((string)($_POST['db_host'] ?? '')),
            'db_name' => trim((string)($_POST['db_name'] ?? '')),
            'db_user' => trim((string)($_POST['db_user'] ?? '')),
            'db_password' => (string)($_POST['db_password'] ?? ''),
        ];

        $_SESSION['migration_form'] = $input;

        $errors = [];

        if ($input['app_url'] !== '' && !filter_var($input['app_url'], FILTER_VALIDATE_URL)) {
            $errors['app_url'] = 'آدرس سایت معتبر نیست. لطفاً با https:// یا http:// وارد کنید.';
        }

        if ($input['db_host'] === '') {
            $errors['db_host'] = 'آدرس سرور پایگاه داده الزامی است.';
        }

        if ($input['db_name'] === '') {
            $errors['db_name'] = 'نام پایگاه داده الزامی است.';
        }

        if ($input['db_user'] === '') {
            $errors['db_user'] = 'نام کاربری پایگاه داده الزامی است.';
        }

        if (!empty($errors)) {
            $_SESSION['migration_errors'] = $errors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را برطرف کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $appConfig = $this->loadConfig('app');
        $dbConfig = $this->loadConfig('database');

        $appConfig['url'] = $input['app_url'] !== '' ? $input['app_url'] : null;
        $dbConfig['host'] = $input['db_host'];
        $dbConfig['database'] = $input['db_name'];
        $dbConfig['username'] = $input['db_user'];
    $newPassword = $input['db_password'] === '' ? ($dbConfig['password'] ?? '') : $input['db_password'];
    $dbConfig['password'] = $newPassword;

        $appWriteResult = $this->writeAppConfig($appConfig);
        $dbWriteResult = $this->writeDatabaseConfig($dbConfig);

        if (!$appWriteResult || !$dbWriteResult) {
            ResponseHelper::flashError('ذخیره تنظیمات با خطا مواجه شد. لطفاً سطح دسترسی فایل‌های config را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        unset($_SESSION['migration_form']);
        ResponseHelper::flashSuccess('تنظیمات با موفقیت بروزرسانی شد. لطفاً پس از انتقال روی هاست، این صفحه را حذف یا غیرفعال کنید.');
        UtilityHelper::redirect($redirectUrl);
    }

    private function loadConfig(string $name): array
    {
        $path = dirname(__DIR__, 2) . '/config/' . $name . '.php';
        if (!file_exists($path)) {
            return [];
        }

        $config = include $path;
        return is_array($config) ? $config : [];
    }

    private function writeAppConfig(array $config): bool
    {
        $path = dirname(__DIR__, 2) . '/config/app.php';

        $appName = addslashes($config['name'] ?? 'PTP Application');
        $appUrl = $config['url'];
        $timezone = addslashes($config['timezone'] ?? 'Asia/Tehran');
        $locale = addslashes($config['locale'] ?? 'fa');
        $encryptionKey = addslashes($config['encryption_key'] ?? 'your-secret-encryption-key-32-chars-here!!');
        $debug = !empty($config['debug']);

        $urlLiteral = $appUrl !== null && $appUrl !== '' ? "'" . addslashes($appUrl) . "'" : 'null';
        $debugLiteral = $debug ? 'true' : 'false';

        $content = <<<'PHP'
<?php

return [
    'name' => '%s',
    'url' => %s,
    'timezone' => '%s',
    'locale' => '%s',
    'encryption_key' => '%s',
    'debug' => %s
];
PHP;
        $content = sprintf($content, $appName, $urlLiteral, $timezone, $locale, $encryptionKey, $debugLiteral);

        return $this->writeFile($path, $content);
    }

    private function writeDatabaseConfig(array $config): bool
    {
        $path = dirname(__DIR__, 2) . '/config/database.php';

        $host = addslashes($config['host'] ?? 'localhost');
        $database = addslashes($config['database'] ?? '');
        $username = addslashes($config['username'] ?? '');
        $password = addslashes($config['password'] ?? '');
        $charset = addslashes($config['charset'] ?? 'utf8mb4');
        $collation = addslashes($config['collation'] ?? 'utf8mb4_unicode_ci');

        $content = <<<'PHP'
<?php

return [
    'host' => '%s',
    'database' => '%s',
    'username' => '%s',
    'password' => '%s',
    'charset' => '%s',
    'collation' => '%s',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
PHP;
        $content = sprintf($content, $host, $database, $username, $password, $charset, $collation);

        return $this->writeFile($path, $content);
    }

    private function writeFile(string $path, string $contents): bool
    {
        try {
            return (bool)file_put_contents($path, $contents);
        } catch (Exception $exception) {
            return false;
        }
    }
}
