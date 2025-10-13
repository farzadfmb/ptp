<?php

class SupperAdminAuthController
{
    public function showLogin()
    {
        AuthHelper::startSession();

        if (AuthHelper::isLoggedIn()) {
            UtilityHelper::redirect(UtilityHelper::baseUrl('supperadmin/dashboard'));
        }

        $validationErrors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);

        $authError = flash('error');
        $authSuccess = flash('success');

        include __DIR__ . '/../Views/supperAdmin/login/sign-in.php';
    }

    public function handleLogin()
    {
        AuthHelper::startSession();

        $redirectUrl = UtilityHelper::baseUrl('supperadmin/login');
        $dashboardUrl = UtilityHelper::baseUrl('supperadmin/dashboard');

        $token = $_POST['_token'] ?? '';
        if (!AuthHelper::verifyCsrfToken($token)) {
            ResponseHelper::flashError('توکن امنیتی نامعتبر است. لطفاً مجدداً تلاش کنید.');
            UtilityHelper::redirect($redirectUrl);
        }

        $identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember_me']) ? '1' : '0';

        $_SESSION['old_input'] = [
            'email' => $identifier,
            'remember_me' => $remember,
        ];

        $validationErrors = [];

        if ($identifier === '') {
            $validationErrors['email'] = 'وارد کردن ایمیل الزامی است.';
        } elseif (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
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

        $identifier = mb_strtolower($identifier);

        try {
            $user = DatabaseHelper::fetchOne(
                'SELECT u.*, r.slug AS role_slug, r.scope_type FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE LOWER(u.email) = :email LIMIT 1',
                ['email' => $identifier]
            );
        } catch (Exception $exception) {
            ResponseHelper::flashError('در هنگام بررسی اطلاعات ورود خطایی رخ داد.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (!$user) {
            ResponseHelper::flashError('کاربری با این مشخصات یافت نشد.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (!password_verify($password, $user['password'])) {
            ResponseHelper::flashError('اطلاعات ورود نادرست است.');
            UtilityHelper::redirect($redirectUrl);
        }

        if (($user['status'] ?? 'inactive') !== 'active') {
            ResponseHelper::flashError('حساب کاربری شما غیرفعال است.');
            UtilityHelper::redirect($redirectUrl);
        }

        $roleSlug = $user['role_slug'] ?? '';
        $scopeType = $user['scope_type'] ?? '';

        if ($roleSlug !== 'super-admin' && $scopeType !== 'superadmin') {
            ResponseHelper::flashError('دسترسی سوپر ادمین برای این حساب فعال نیست.');
            UtilityHelper::redirect($redirectUrl);
        }

        AuthHelper::login([
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'national_code' => $user['national_code'],
            'role' => 'supperadmin',
            'role_slug' => $roleSlug,
            'scope_type' => $scopeType,
            'organization_id' => $user['organization_id'],
            'status' => $user['status'],
        ]);

        try {
            DatabaseHelper::update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $user['id']]);
        } catch (Exception $exception) {
            // Silent failure for last login update
        }

        unset($_SESSION['old_input'], $_SESSION['validation_errors']);

        ResponseHelper::flashSuccess('ورود با موفقیت انجام شد.');
        UtilityHelper::redirect($dashboardUrl);
    }
}
