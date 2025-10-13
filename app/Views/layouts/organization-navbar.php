<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

if (!isset($navbarUser)) {
    AuthHelper::startSession();
    $navbarUser = AuthHelper::getUser();
}

$navbarSettings = $navbarSettings ?? null;
if ($navbarSettings === null) {
    try {
        $navbarSettings = DatabaseHelper::fetchOne('SELECT site_name, system_default_avatar_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $exception) {
        $navbarSettings = null;
    }
}

$defaultAvatarPath = 'assets/images/thumbs/user-img.png';
$systemDefaultAvatarPath = $navbarSettings['system_default_avatar_path'] ?? null;
$systemDefaultAvatarPath = $systemDefaultAvatarPath ?: $defaultAvatarPath;

$userAvatarPath = $navbarUser['avatar_path'] ?? null;
$resolvedAvatarPath = trim($userAvatarPath ?: $systemDefaultAvatarPath);
if ($resolvedAvatarPath === '') {
    $resolvedAvatarPath = $defaultAvatarPath;
}

if (preg_match('/^https?:\/\//i', $resolvedAvatarPath)) {
    $navbarAvatarUrl = $resolvedAvatarPath;
} else {
    $relativeAvatarPath = ltrim($resolvedAvatarPath, '/');
    if (strpos($relativeAvatarPath, 'public/') === 0) {
        $navbarAvatarUrl = UtilityHelper::baseUrl($relativeAvatarPath);
    } else {
        $navbarAvatarUrl = UtilityHelper::baseUrl('public/' . $relativeAvatarPath);
    }
}

$navbarFullName = trim(($navbarUser['name'] ?? '') !== '' ? $navbarUser['name'] : trim(($navbarUser['first_name'] ?? '') . ' ' . ($navbarUser['last_name'] ?? '')));
if ($navbarFullName === '') {
    $navbarFullName = 'مدیر سازمان';
}

$navbarEmail = $navbarUser['email'] ?? 'organization@example.com';
$navbarRoleLabel = 'کاربر سازمان';
if (is_array($navbarUser)) {
    $roleLabelCandidate = trim((string)($navbarUser['role_label'] ?? ''));
    $roleNameCandidate = trim((string)($navbarUser['organization_role_name'] ?? ''));
    $roleSlugCandidate = trim((string)($navbarUser['role_slug'] ?? ''));

    if ($roleLabelCandidate !== '') {
        $navbarRoleLabel = $roleLabelCandidate;
    } elseif ($roleNameCandidate !== '') {
        $navbarRoleLabel = $roleNameCandidate;
    } elseif ($roleSlugCandidate !== '') {
        $normalizedRoleSlug = mb_strtolower($roleSlugCandidate, 'UTF-8');
        $roleMapping = [
            'organization-owner' => 'مالک سازمان',
            'organization-admin' => 'مدیر سازمان',
            'organization-manager' => 'مدیر سازمان',
            'organization-user' => 'کاربر سازمان',
            'organization-operator' => 'اپراتور سازمان',
            'organization-supervisor' => 'ناظر سازمان',
        ];

        if (isset($roleMapping[$normalizedRoleSlug])) {
            $navbarRoleLabel = $roleMapping[$normalizedRoleSlug];
        }
    }
}

$profileUrl = UtilityHelper::baseUrl('organizations/profile');
$currentDate = UtilityHelper::getTodayDate();
?>

<header class="top-header">
    <nav class="navbar navbar-expand gap-3 align-items-center">
        <div class="mobile-menu-button d-xl-none"><ion-icon name="menu-sharp"></ion-icon></div>

        <form class="searchbar d-none d-md-flex">
            <div class="position-absolute top-50 translate-middle-y search-icon ms-3"><ion-icon name="search-sharp"></ion-icon></div>
            <input class="form-control" type="text" placeholder="جستجو کنید...">
            <div class="position-absolute top-50 translate-middle-y search-close-icon"><ion-icon name="close-sharp"></ion-icon></div>
        </form>

        <div class="d-none d-md-flex align-items-center gap-2 text-secondary">
            <ion-icon name="calendar-clear-outline" class="fs-5"></ion-icon>
            <span class="fw-medium"><?= htmlspecialchars($currentDate, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="top-navbar-right ms-auto">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item mobile-search-button d-md-none">
                    <a class="nav-link" href="javascript:;">
                        <ion-icon name="search-sharp"></ion-icon>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link dark-mode-icon" href="javascript:;" data-bs-toggle="tooltip" data-bs-placement="bottom" title="تغییر تم">
                        <div class="mode-icon">
                            <ion-icon name="moon-sharp"></ion-icon>
                        </div>
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-large">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                        <div class="position-relative">
                            <span class="notify-badge">0</span>
                            <ion-icon name="notifications-sharp"></ion-icon>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:;">
                            <div class="msg-header">
                                <p class="msg-header-title">اعلان‌ها</p>
                                <p class="msg-header-clear ms-auto">علامت‌گذاری به عنوان خوانده شده</p>
                            </div>
                        </a>
                        <div class="header-notifications-list p-3 text-center text-muted small">
                            در حال حاضر اعلان فعالی موجود نیست.
                        </div>
                        <a href="javascript:;">
                            <div class="text-center msg-footer">مشاهده همه اعلان‌ها</div>
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link user-profile-link d-flex align-items-center gap-3" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>" title="مشاهده پروفایل کاربری">
                        <div class="user-setting">
                            <img src="<?= htmlspecialchars($navbarAvatarUrl, ENT_QUOTES, 'UTF-8'); ?>" class="user-img" alt="<?= htmlspecialchars($navbarFullName, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="d-none d-lg-flex flex-column align-items-end">
                            <span class="fw-semibold text-gray-900"><?= htmlspecialchars($navbarFullName, ENT_QUOTES, 'UTF-8'); ?></span>
                            <small class="text-secondary"><?= htmlspecialchars($navbarRoleLabel, ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                        <ion-icon name="person-circle-outline" class="fs-4 text-main-500 d-none d-lg-block"></ion-icon>
                    </a>
                </li>
                <li class="nav-item dropdown dropdown-user-menu">
                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="javascript:;" data-bs-toggle="dropdown">
                        <ion-icon name="ellipsis-vertical" class="fs-4"></ion-icon>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="px-3 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= htmlspecialchars($navbarAvatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($navbarFullName, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-circle" width="54" height="54">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($navbarFullName, ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <small class="text-secondary d-block mb-1"><?= htmlspecialchars($navbarEmail, ENT_QUOTES, 'UTF-8'); ?></small>
                                        <small class="badge bg-main-50 text-main-600 rounded-pill">نقش: <?= htmlspecialchars($navbarRoleLabel, ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="d-flex align-items-center">
                                    <ion-icon name="person-outline"></ion-icon>
                                    <div class="ms-3"><span>مشاهده پروفایل</span></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= UtilityHelper::baseUrl('organizations/settings'); ?>">
                                <div class="d-flex align-items-center">
                                    <ion-icon name="settings-outline"></ion-icon>
                                    <div class="ms-3"><span>تنظیمات</span></div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= UtilityHelper::baseUrl('organizations/logout'); ?>">
                                <div class="d-flex align-items-center">
                                    <ion-icon name="log-out-outline"></ion-icon>
                                    <div class="ms-3"><span>خروج</span></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
