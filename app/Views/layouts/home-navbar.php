<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

AuthHelper::startSession();
$navbarUser = $navbarUser ?? AuthHelper::getUser();

$siteName = 'پرتال ارزیابی';
try {
    $record = DatabaseHelper::fetchOne('SELECT site_name FROM system_settings ORDER BY id ASC LIMIT 1');
    if (!empty($record['site_name'])) {
        $siteName = $record['site_name'];
    }
} catch (Exception $exception) {
    // ignore
}

$isAuthenticated = !empty($navbarUser);
$displayName = $isAuthenticated ? trim(($navbarUser['name'] ?? '') ?: (($navbarUser['first_name'] ?? '') . ' ' . ($navbarUser['last_name'] ?? ''))) : '';
if ($isAuthenticated && $displayName === '') {
    $displayName = 'کاربر سیستم';
}
?>

<header class="top-header">
    <nav class="navbar navbar-expand gap-3 align-items-center">
        <div class="mobile-menu-button d-xl-none"><ion-icon name="menu-sharp"></ion-icon></div>

        <div class="d-flex align-items-center gap-2 text-secondary">
            <ion-icon name="planet-sharp" class="fs-5"></ion-icon>
            <span class="fw-medium"><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="top-navbar-links d-none d-lg-flex ms-4">
            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= UtilityHelper::baseUrl('home'); ?>">داشبورد</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        آزمون
                    </a>
                    <ul class="dropdown-menu shadow-sm rounded-16">
                        <li>
                            <a class="dropdown-item" href="<?= UtilityHelper::baseUrl('tests/training-calendar'); ?>">تقویم آموزشی</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= UtilityHelper::baseUrl('tests/reports'); ?>">گزارشات</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="top-navbar-right ms-auto">
            <ul class="navbar-nav align-items-center">
                <?php if ($isAuthenticated): ?>
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="<?= UtilityHelper::baseUrl('profile'); ?>">
                            <ion-icon name="person-circle-outline"></ion-icon>
                            <span class="ms-2"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= UtilityHelper::baseUrl('user/logout'); ?>">
                            خروج
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= UtilityHelper::baseUrl('user/login'); ?>">
                            ورود کاربران
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-sm btn-main rounded-pill" href="<?= UtilityHelper::baseUrl('user/register'); ?>">
                            ثبت نام
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?= UtilityHelper::baseUrl('organizations/login'); ?>">
                            ورود سازمانی
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
