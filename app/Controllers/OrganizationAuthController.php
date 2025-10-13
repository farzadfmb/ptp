<?php

require_once __DIR__ . '/../Helpers/autoload.php';
if (!class_exists('PermissionHelper') && file_exists(__DIR__ . '/../Helpers/PermissionHelper.php')) {
    require_once __DIR__ . '/../Helpers/PermissionHelper.php';
}

class OrganizationAuthController
{
    public function showLogin(): void
    {
        AuthHelper::startSession();

        $currentUser = AuthHelper::getUser();
        if ($this->isOrganizationAccount($currentUser)) {
            UtilityHelper::redirect(UtilityHelper::baseUrl('organizations/dashboard'));
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $authError = flash('error');
        $authSuccess = flash('success');

        $generalSettings = $this->getGeneralSettings();

        include __DIR__ . '/../Views/organizations/login/sign-in.php';
    }

    public function handleLogin(): void
    {
        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('organizations/login');
        $dashboardUrl = UtilityHelper::baseUrl('organizations/dashboard');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً دوباره تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $identifier = trim($_POST['email'] ?? '');
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

        if ($isEmail && !filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
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

        $normalizedIdentifier = function_exists('mb_strtolower')
            ? mb_strtolower($identifier, 'UTF-8')
            : strtolower($identifier);

        $organizationAccount = $this->fetchOrganizationByUsername($normalizedIdentifier);

        if ($organizationAccount) {
            $this->authenticateOrganizationAccount($organizationAccount, $password, $redirectUrl, $dashboardUrl);
            return;
        }

        $organizationUser = $this->fetchOrganizationPortalUser($normalizedIdentifier, $isEmail);

        if (!$organizationUser) {
            ResponseHelper::flashError('کاربری با این مشخصات یافت نشد.');
            UtilityHelper::redirect($redirectUrl);
        }

        $this->authenticateOrganizationUser($organizationUser, $password, $redirectUrl, $dashboardUrl);
    }

    public function logout(): void
    {
        AuthHelper::logout();
        AuthHelper::startSession();
        ResponseHelper::flashSuccess('خروج با موفقیت انجام شد.');
        UtilityHelper::redirect(UtilityHelper::baseUrl('organizations/login'));
    }

    private function authenticateOrganizationUser(array $organizationUser, string $password, string $redirectUrl, string $dashboardUrl): void
    {
        $organizationId = (int)($organizationUser['organization_id'] ?? 0);

        if ($organizationId <= 0) {
            ResponseHelper::flashError('سازمان مرتبط با حساب شما یافت نشد.');
            UtilityHelper::redirect($redirectUrl);
        }

        $passwordHash = (string)($organizationUser['password_hash'] ?? '');
        if ($passwordHash === '' || !password_verify($password, $passwordHash)) {
            ResponseHelper::flashError('اطلاعات ورود نادرست است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ((int)($organizationUser['is_active'] ?? 0) !== 1) {
            ResponseHelper::flashError('حساب کاربری شما غیرفعال است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ((int)($organizationUser['organization_active_lookup'] ?? 1) === 0) {
            ResponseHelper::flashError('سازمان شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $organization = $this->fetchOrganization($organizationId);
        if (!$organization) {
            $organization = [
                'id' => $organizationId,
                'name' => $organizationUser['organization_name_lookup'] ?? null,
                'latin_name' => $organizationUser['organization_latin_name_lookup'] ?? null,
                'code' => $organizationUser['organization_code_lookup'] ?? null,
                'subdomain' => $organizationUser['organization_subdomain_lookup'] ?? null,
            ];
        }

        $isSystemAdmin = (int)($organizationUser['is_system_admin'] ?? 0) === 1;
        $roleId = isset($organizationUser['organization_role_id']) ? (int)$organizationUser['organization_role_id'] : null;
        $permissions = ['dashboard_overview_view'];
        if (class_exists('PermissionHelper')) {
            $permissions = call_user_func(
                ['PermissionHelper', 'fetchPermissionsForOrganizationUser'],
                $organizationId,
                $roleId,
                $isSystemAdmin
            );
        }
        $roleSlug = $this->resolveOrganizationRoleSlug($organizationUser, $isSystemAdmin);
        $roleLabel = $this->resolveOrganizationRoleLabel($organizationUser, $roleSlug, $isSystemAdmin);

        $fullName = trim(($organizationUser['first_name'] ?? '') . ' ' . ($organizationUser['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $organizationUser['username'] ?? 'کاربر سازمان';
        }

        AuthHelper::login([
            'id' => 'orguser-' . ($organizationUser['id'] ?? uniqid('', true)),
            'organization_user_id' => $organizationUser['id'] ?? null,
            'first_name' => $organizationUser['first_name'] ?? null,
            'last_name' => $organizationUser['last_name'] ?? null,
            'name' => $fullName,
            'email' => $organizationUser['email'] ?? null,
            'mobile' => $organizationUser['mobile'] ?? null,
            'national_code' => $organizationUser['national_code'] ?? null,
            'username' => $organizationUser['username'] ?? null,
            'role' => 'organization',
            'role_slug' => $roleSlug,
            'role_label' => $roleLabel,
            'scope_type' => 'organization',
            'organization_id' => $organization['id'] ?? $organizationId,
            'organization_name' => $organization['name'] ?? $organizationUser['organization_name_lookup'] ?? null,
            'organization' => [
                'id' => $organization['id'] ?? $organizationId,
                'name' => $organization['name'] ?? $organizationUser['organization_name_lookup'] ?? null,
                'latin_name' => $organization['latin_name'] ?? $organizationUser['organization_latin_name_lookup'] ?? null,
                'code' => $organization['code'] ?? $organizationUser['organization_code_lookup'] ?? null,
                'subdomain' => $organization['subdomain'] ?? $organizationUser['organization_subdomain_lookup'] ?? null,
            ],
            'status' => ((int)($organizationUser['is_active'] ?? 0) === 1) ? 'active' : 'inactive',
            'account_source' => 'organization_users',
            'permissions' => $permissions,
            'organization_role_id' => $roleId,
            'organization_role_name' => $organizationUser['organization_role_name_lookup'] ?? $roleLabel,
            'organization_user_flags' => [
                'is_manager' => (int)($organizationUser['is_manager'] ?? 0),
                'is_evaluator' => (int)($organizationUser['is_evaluator'] ?? 0),
                'is_evaluee' => (int)($organizationUser['is_evaluee'] ?? 0),
                'is_system_admin' => $isSystemAdmin ? 1 : 0,
            ],
        ]);

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        ResponseHelper::flashSuccess('ورود با موفقیت انجام شد.');
        UtilityHelper::redirect($dashboardUrl);
    }

    private function fetchOrganizationPortalUser(string $identifier, bool $isEmail): ?array
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
    o.latin_name AS organization_latin_name_lookup,
    o.code AS organization_code_lookup,
    o.subdomain AS organization_subdomain_lookup,
    o.is_active AS organization_active_lookup,
    r.name AS organization_role_name_lookup
FROM organization_users ou
LEFT JOIN organizations o ON ou.organization_id = o.id
LEFT JOIN organization_roles r ON ou.organization_role_id = r.id
WHERE {$condition}
LIMIT 1
SQL;

        try {
            return DatabaseHelper::fetchOne($sql, ['identifier' => $identifier]);
        } catch (Exception $exception) {
            return null;
        }
    }

    private function authenticateOrganizationAccount(array $organizationAccount, string $password, string $redirectUrl, string $dashboardUrl): void
    {
        if (!password_verify($password, (string)($organizationAccount['password'] ?? ''))) {
            ResponseHelper::flashError('اطلاعات ورود نادرست است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if ((int)($organizationAccount['is_active'] ?? 0) === 0) {
            ResponseHelper::flashError('سازمان شما غیرفعال شده است. لطفاً با پشتیبانی تماس بگیرید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $organization = $this->fetchOrganization((int)$organizationAccount['id']);
        if (!$organization) {
            $organization = $organizationAccount;
        }

        AuthHelper::login([
            'id' => 'org-' . $organizationAccount['id'],
            'first_name' => null,
            'last_name' => null,
            'name' => $organizationAccount['name'],
            'email' => null,
            'mobile' => null,
            'national_code' => null,
            'role' => 'organization',
            'role_slug' => 'organization-owner',
            'role_label' => 'مالک سازمان',
            'scope_type' => 'organization',
            'organization_id' => $organizationAccount['id'],
            'organization_name' => $organizationAccount['name'],
            'organization' => [
                'id' => $organization['id'] ?? $organizationAccount['id'],
                'name' => $organization['name'] ?? $organizationAccount['name'],
                'latin_name' => $organization['latin_name'] ?? $organizationAccount['latin_name'],
                'code' => $organization['code'] ?? $organizationAccount['code'],
                'subdomain' => $organization['subdomain'] ?? $organizationAccount['subdomain'],
            ],
            'status' => 'active',
            'account_source' => 'organizations',
            'username' => $organizationAccount['username'],
            'permissions' => class_exists('PermissionHelper')
                ? call_user_func(['PermissionHelper', 'fetchPermissionsForOrganizationUser'], (int)$organizationAccount['id'], null, true)
                : ['dashboard_overview_view'],
            'organization_role_name' => 'مالک سازمان',
        ]);

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        ResponseHelper::flashSuccess('ورود با موفقیت انجام شد.');
        UtilityHelper::redirect($dashboardUrl);
    }

    private function isOrganizationAccount($user): bool
    {
        if (!is_array($user)) {
            return false;
        }

        $scopeType = $user['scope_type'] ?? '';
        $roleSlug = $user['role_slug'] ?? '';
        $organizationId = $user['organization_id'] ?? null;

        if ($this->isOrganizationScope($scopeType, $roleSlug) && $organizationId) {
            return true;
        }

        return false;
    }

    private function isOrganizationScope(?string $scopeType, ?string $roleSlug): bool
    {
        $scopeType = (string) $scopeType;
        $roleSlug = (string) $roleSlug;

        $normalizedScope = $scopeType !== '' ? mb_strtolower($scopeType, 'UTF-8') : '';
        $normalizedSlug = $roleSlug !== '' ? mb_strtolower($roleSlug, 'UTF-8') : '';

        if ($normalizedScope === 'organization') {
            return true;
        }

        if ($normalizedSlug === '') {
            return false;
        }

        $sanitizedSlug = str_replace(['_', '.', ' '], '-', $normalizedSlug);

        $allowedSlugs = [
            'organization-admin',
            'organization-manager',
            'organization-user',
            'organization-operator',
            'organization-supervisor',
            'organization-owner',
        ];

        if (in_array($sanitizedSlug, $allowedSlugs, true)) {
            return true;
        }

        return mb_strpos($normalizedSlug, 'organization') !== false;
    }

    private function fetchOrganization(int $organizationId): ?array
    {
        try {
            return DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE id = :id LIMIT 1', ['id' => $organizationId]);
        } catch (Exception $exception) {
            return null;
        }
    }

    private function resolveOrganizationRoleSlug(array $organizationUser, bool $isSystemAdmin): string
    {
        if ($isSystemAdmin) {
            return 'organization-admin';
        }

        $roleName = trim((string)($organizationUser['organization_role_name_lookup'] ?? ''));
        if ($roleName !== '') {
            $slug = UtilityHelper::slugify('organization-' . $roleName);
            if ($slug !== '') {
                return $slug;
            }
        }

        return 'organization-user';
    }

    private function resolveOrganizationRoleLabel(array $organizationUser, string $roleSlug, bool $isSystemAdmin): string
    {
        if ($isSystemAdmin) {
            return 'مدیر سامانه سازمان';
        }

        $roleName = trim((string)($organizationUser['organization_role_name_lookup'] ?? ''));
        if ($roleName !== '') {
            return $roleName;
        }

        return $this->humanizeOrganizationRoleSlug($roleSlug);
    }

    private function humanizeOrganizationRoleSlug(string $roleSlug): string
    {
        $normalized = trim(mb_strtolower((string)$roleSlug, 'UTF-8'));

        $mapping = [
            'organization-owner' => 'مالک سازمان',
            'organization-admin' => 'مدیر سازمان',
            'organization-manager' => 'مدیر سازمان',
            'organization-user' => 'کاربر سازمان',
            'organization-operator' => 'اپراتور سازمان',
            'organization-supervisor' => 'ناظر سازمان',
        ];

        if ($normalized !== '' && isset($mapping[$normalized])) {
            return $mapping[$normalized];
        }

        if ($normalized === '') {
            return 'کاربر سازمان';
        }

        $clean = preg_replace('/^organization[-_\s]?/u', '', $normalized);
        $clean = str_replace(['-', '_'], ' ', (string)$clean);
        $clean = trim($clean);

        if ($clean === '') {
            return 'کاربر سازمان';
        }

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords($clean);
    }

    private function fetchOrganizationByUsername(string $username): ?array
    {
        if ($username === '') {
            return null;
        }

        try {
            $record = DatabaseHelper::fetchOne('SELECT * FROM organizations WHERE LOWER(username) = :username LIMIT 1', ['username' => $username]);

            if (!is_array($record) || empty($record)) {
                return null;
            }

            return $record;
        } catch (Exception $exception) {
            return null;
        }
    }

    private function getGeneralSettings(): ?array
    {
        try {
            return DatabaseHelper::fetchOne('SELECT site_name, system_logo_path FROM system_settings ORDER BY id ASC LIMIT 1');
        } catch (Exception $exception) {
            return null;
        }
    }
}
