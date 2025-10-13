<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'پروفایل کاربری سازمان';
$user = $user ?? (class_exists('AuthHelper') ? AuthHelper::getUser() : null);
$organization = $organization ?? [];
$roleLabel = $roleLabel ?? 'کاربر سازمان';
$permissions = isset($permissions) && is_array($permissions) ? $permissions : [];
$permissionsCount = isset($permissionsCount) ? (int) $permissionsCount : count($permissions);
$accountSourceLabel = $accountSourceLabel ?? 'کاربر سازمان';
$accountSource = $accountSource ?? ($user['account_source'] ?? 'organizations');
$organizationUserId = $organizationUserId ?? ($user['organization_user_id'] ?? null);
$userFlags = isset($userFlags) && is_array($userFlags) ? $userFlags : [];
$contactDetails = isset($contactDetails) && is_array($contactDetails) ? $contactDetails : [];
$profileMeta = isset($profileMeta) && is_array($profileMeta) ? $profileMeta : [];

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$avatarUrl = null;
if (is_array($user)) {
    $avatarPath = $user['avatar_path'] ?? null;
    if ($avatarPath) {
        if (preg_match('/^https?:\/\//i', $avatarPath)) {
            $avatarUrl = $avatarPath;
        } else {
            $relative = ltrim($avatarPath, '/');
            $avatarUrl = UtilityHelper::baseUrl('public/' . $relative);
        }
    }
}

if (!$avatarUrl) {
    $avatarUrl = UtilityHelper::baseUrl('public/assets/images/thumbs/user-img.png');
}

$fullName = is_array($user)
    ? trim(($user['name'] ?? '') !== '' ? $user['name'] : trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')))
    : '';

if ($fullName === '') {
    $fullName = 'کاربر سازمان';
}

$organizationName = $organization['name'] ?? 'سازمان من';
$organizationCode = $organization['code'] ?? null;
$organizationSubdomain = $organization['subdomain'] ?? null;

$formatNullable = static function ($value) {
    if ($value === null || $value === '') {
        return '<span class="text-muted">نامشخص</span>';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$formatDateTime = static function ($value) {
    if ($value === null || $value === '') {
        return '<span class="text-muted">-</span>';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '<span class="text-muted">-</span>';
    }

    return htmlspecialchars(UtilityHelper::englishToPersian(date('Y/m/d H:i', $timestamp)), ENT_QUOTES, 'UTF-8');
};

$permissionsPreview = array_slice($permissions, 0, 6);
$remainingPermissions = max(0, $permissionsCount - count($permissionsPreview));

$badgeClassForFlag = static function ($key, $value) {
    if ((int) $value !== 1) {
        return '';
    }

    $mapping = [
        'is_manager' => 'bg-main-100 text-main-700',
        'is_evaluator' => 'bg-success-100 text-success-700',
        'is_evaluee' => 'bg-warning-100 text-warning-800',
        'is_system_admin' => 'bg-danger-100 text-danger-700',
    ];

    return $mapping[$key] ?? 'bg-gray-100 text-gray-700';
};

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap gap-16 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-16">
                                <div class="rounded-circle overflow-hidden border border-gray-100" style="width: 88px; height: 88px;">
                                    <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="آواتار کاربر" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div>
                                    <h2 class="mb-6 text-gray-900 fs-4 fw-bold">
                                        <?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>
                                    </h2>
                                    <div class="d-flex flex-wrap gap-8 align-items-center">
                                        <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">نقش: <?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-8">نوع حساب: <?= htmlspecialchars($accountSourceLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($organizationUserId): ?>
                                            <span class="badge bg-info-50 text-info-700 rounded-pill px-16 py-8">شناسه کاربر: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $organizationUserId), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-10">
                                <a href="<?= UtilityHelper::baseUrl('organizations/dashboard'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="home-outline"></ion-icon>
                                    داشبورد
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/settings'); ?>" class="btn btn-main rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    تنظیمات سازمان
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <h4 class="mb-16 text-gray-900 d-flex align-items-center gap-8">
                            <ion-icon name="id-card-outline" class="text-main-500"></ion-icon>
                            اطلاعات کاربری
                        </h4>
                        <div class="row g-12">
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">نام کامل</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">نام کاربری</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($contactDetails['username'] ?? null); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">ایمیل</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($contactDetails['email'] ?? null); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">شماره تماس</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($contactDetails['mobile'] ?? null); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">کد ملی</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($contactDetails['national_code'] ?? null); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">تاریخ ایجاد حساب</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatDateTime($profileMeta['created_at'] ?? null); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">آخرین ورود</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatDateTime($profileMeta['last_login_at'] ?? null); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <h4 class="mb-16 text-gray-900 d-flex align-items-center gap-8">
                            <ion-icon name="business-outline" class="text-main-500"></ion-icon>
                            اطلاعات سازمانی
                        </h4>
                        <div class="row g-12">
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">نام سازمان</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">کد سازمان</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($organizationCode); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">ساب‌دومین</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4"><?= $formatNullable($organizationSubdomain); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-gray-500 text-sm">تعداد دسترسی‌ها</span>
                                <p class="mb-0 fw-semibold text-gray-900 mt-4">
                                    <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $permissionsCount), ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="text-sm text-gray-500">مجوز</span>
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($userFlags)): ?>
                            <hr class="my-20">
                            <h6 class="text-gray-800 mb-12">نقش‌های تکمیلی</h6>
                            <div class="d-flex flex-wrap gap-8">
                                <?php foreach ($userFlags as $flagKey => $flagValue): ?>
                                    <?php if ((int) $flagValue === 1): ?>
                                        <?php
                                            $labelMap = [
                                                'is_manager' => 'مدیر ارزیابی',
                                                'is_evaluator' => 'ارزیاب',
                                                'is_evaluee' => 'ارزیابی‌شونده',
                                                'is_system_admin' => 'مدیر سامانه',
                                            ];
                                            $label = $labelMap[$flagKey] ?? $flagKey;
                                            $badgeClass = $badgeClassForFlag($flagKey, $flagValue);
                                        ?>
                                        <span class="badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?> rounded-pill px-16 py-8">
                                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-20">
                            <h4 class="mb-0 text-gray-900 d-flex align-items-center gap-8">
                                <ion-icon name="shield-checkmark-outline" class="text-main-500"></ion-icon>
                                سطوح دسترسی فعال
                            </h4>
                            <a href="<?= UtilityHelper::baseUrl('organizations/role-access-matrix'); ?>" class="btn btn-outline-main rounded-pill px-20 d-flex align-items-center gap-8">
                                <ion-icon name="settings-outline"></ion-icon>
                                مدیریت دسترسی‌ها
                            </a>
                        </div>
                        <?php if (!empty($permissionsPreview)): ?>
                            <div class="d-flex flex-wrap gap-10">
                                <?php foreach ($permissionsPreview as $permission): ?>
                                    <span class="badge bg-gray-100 text-gray-700 rounded-pill px-16 py-8">
                                        <?= htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if ($remainingPermissions > 0): ?>
                                    <span class="badge bg-main-100 text-main-700 rounded-pill px-16 py-8">
                                        +<?= htmlspecialchars(UtilityHelper::englishToPersian((string) $remainingPermissions), ENT_QUOTES, 'UTF-8'); ?> مجوز دیگر
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info rounded-20 mb-0" role="alert">
                                هنوز دسترسی فعالی برای این حساب ثبت نشده است. لطفاً با مدیر سازمان خود در تماس باشید.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
