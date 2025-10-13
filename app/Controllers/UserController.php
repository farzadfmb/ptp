<?php

require_once __DIR__ . '/../Helpers/autoload.php';

class UserController
{
    public function showLogin(): void
    {
        AuthHelper::startSession();

        $currentUser = AuthHelper::getUser();
        if ($this->isUserAuthenticated($currentUser)) {
            UtilityHelper::redirect(UtilityHelper::baseUrl('home'));
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $authError = flash('error');
        $authSuccess = flash('success');
        $generalSettings = $this->getGeneralSettings();
        $title = 'ورود کاربران سامانه';

        include __DIR__ . '/../Views/home/login/index.php';
    }

    public function handleLogin(): void
    {
        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('user/login');
        $dashboardUrl = UtilityHelper::baseUrl('home');

        $token = (string)($_POST['_token'] ?? '');
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $identifier = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = isset($_POST['remember_me']) ? '1' : '';

        $_SESSION['old_input'] = [
            'email' => $identifier,
            'remember_me' => $remember,
        ];

        $validationErrors = [];

        if ($identifier === '') {
            $validationErrors['email'] = 'وارد کردن ایمیل یا نام کاربری الزامی است.';
        }

        $isEmail = strpos($identifier, '@') !== false;

        if ($isEmail && $identifier !== '' && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $validationErrors['email'] = 'فرمت ایمیل صحیح نیست.';
        }

        if ($password === '') {
            $validationErrors['password'] = 'رمز عبور را وارد کنید.';
        }

        if (!empty($validationErrors)) {
            $_SESSION['validation_errors'] = $validationErrors;
            ResponseHelper::flashError('لطفاً خطاهای فرم را بررسی کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ($identifier !== '') {
            $normalizedIdentifier = function_exists('mb_strtolower')
                ? mb_strtolower($identifier, 'UTF-8')
                : strtolower($identifier);
        } else {
            $normalizedIdentifier = '';
        }

        $organizationUser = $this->fetchOrganizationUser($normalizedIdentifier, $isEmail);

        if (!$organizationUser) {
            ResponseHelper::flashError('اطلاعات ورود نادرست است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (!password_verify($password, (string)($organizationUser['password_hash'] ?? ''))) {
            ResponseHelper::flashError('اطلاعات ورود نادرست است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ((int)($organizationUser['is_active'] ?? 0) !== 1) {
            ResponseHelper::flashError('حساب کاربری شما غیرفعال است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (isset($organizationUser['organization_active_lookup']) && (int)$organizationUser['organization_active_lookup'] === 0) {
            ResponseHelper::flashError('سازمان شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.');
            UtilityHelper::redirect($redirectUrl);
        }

        AuthHelper::login($this->buildSessionPayload($organizationUser));

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        ResponseHelper::flashSuccess('ورود با موفقیت انجام شد.');
        UtilityHelper::redirect($dashboardUrl);
    }

    public function logout(): void
    {
        AuthHelper::logout();
        AuthHelper::startSession();
        ResponseHelper::flashSuccess('خروج با موفقیت انجام شد.');
        UtilityHelper::redirect(UtilityHelper::baseUrl('user/login'));
    }

    public function showRegister(): void
    {
        ResponseHelper::flashError('ثبت‌نام کاربران به‌زودی راه‌اندازی خواهد شد.');
        UtilityHelper::redirect(UtilityHelper::baseUrl('user/login'));
    }

    public function handleRegister(): void
    {
        ResponseHelper::flashError('در حال حاضر امکان ثبت‌نام وجود ندارد.');
        UtilityHelper::redirect(UtilityHelper::baseUrl('user/login'));
    }

    public function showForgotPassword(): void
    {
        ResponseHelper::flashSuccess('برای بازیابی رمز عبور با پشتیبانی سامانه تماس بگیرید.');
        UtilityHelper::redirect(UtilityHelper::baseUrl('user/login'));
    }

    private function fetchOrganizationUser(string $identifier, bool $isEmail): ?array
    {
        if ($identifier === '') {
            return null;
        }

        $condition = $isEmail
            ? 'ou.email IS NOT NULL AND LOWER(ou.email) = :identifier'
            : 'LOWER(ou.username) = :identifier';

        $sql = <<<SQL
SELECT
    ou.*,
    o.name AS organization_name_lookup,
    o.code AS organization_code_lookup,
    o.subdomain AS organization_subdomain_lookup,
    o.is_active AS organization_active_lookup
FROM organization_users ou
LEFT JOIN organizations o ON ou.organization_id = o.id
WHERE {$condition}
LIMIT 1
SQL;

        try {
            $result = DatabaseHelper::fetchOne($sql, ['identifier' => $identifier]);
        } catch (Exception $exception) {
            return null;
        }

        if ($result === false) {
            return null;
        }

        return is_array($result) ? $result : null;
    }

    private function buildSessionPayload(array $user): array
    {
        $status = ((int)($user['is_active'] ?? 0) === 1) ? 'active' : 'inactive';
        $organizationId = $user['organization_id'] ?? null;
        $organizationName = $this->resolveOrganizationName($user);
        $sessionId = isset($user['id']) ? 'orguser-' . $user['id'] : uniqid('orguser-', true);

        return [
            'id' => $sessionId,
            'organization_user_id' => $user['id'] ?? null,
            'user_id' => $user['user_id'] ?? null,
            'first_name' => $user['first_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'name' => $this->resolveUserName($user),
            'email' => $user['email'] ?? null,
            'username' => $user['username'] ?? null,
            'mobile' => $user['mobile'] ?? null,
            'national_code' => $user['national_code'] ?? null,
            'role' => 'organization_user',
            'role_slug' => 'organization-user',
            'status' => $status,
            'account_source' => 'organization_users',
            'organization_id' => $organizationId,
            'organization_name' => $organizationName,
            'organization_role_id' => $user['organization_role_id'] ?? null,
            'organization_user_flags' => [
                'is_manager' => (int)($user['is_manager'] ?? 0),
                'is_evaluator' => (int)($user['is_evaluator'] ?? 0),
                'is_evaluee' => (int)($user['is_evaluee'] ?? 0),
                'is_system_admin' => (int)($user['is_system_admin'] ?? 0),
            ],
            'evaluation_code' => $user['evaluation_code'] ?? null,
            'personnel_code' => $user['personnel_code'] ?? null,
            'organization' => [
                'id' => $organizationId,
                'name' => $organizationName,
                'code' => $user['organization_code_lookup'] ?? null,
                'subdomain' => $user['organization_subdomain_lookup'] ?? null,
            ],
        ];
    }

    private function resolveOrganizationName(array $user): ?string
    {
        $explicitName = trim((string)($user['organization_name'] ?? ''));
        if ($explicitName !== '') {
            return $explicitName;
        }

        $lookupName = trim((string)($user['organization_name_lookup'] ?? ''));
        return $lookupName !== '' ? $lookupName : null;
    }

    private function getGeneralSettings(): ?array
    {
        try {
            return DatabaseHelper::fetchOne(
                'SELECT site_name, system_logo_path FROM system_settings ORDER BY id ASC LIMIT 1'
            );
        } catch (Exception $exception) {
            return null;
        }
    }

    private function isUserAuthenticated($user): bool
    {
        if (!is_array($user) || empty($user)) {
            return false;
        }

        $role = $user['role'] ?? '';
        $status = $user['status'] ?? '';

        return $role !== '' && $status === 'active';
    }

    private function resolveUserName(array $user): string
    {
        $firstName = trim((string)($user['first_name'] ?? ''));
        $lastName = trim((string)($user['last_name'] ?? ''));
        $name = trim($firstName . ' ' . $lastName);

        if ($name !== '') {
            return $name;
        }

        $username = (string)($user['username'] ?? '');
        if ($username !== '') {
            return $username;
        }

        $email = (string)($user['email'] ?? '');
        return $email !== '' ? $email : 'کاربر سامانه';
    }
}
