<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

AuthHelper::startSession();
$currentUser = AuthHelper::getUser();

$siteSettings = $siteSettings ?? null;
if ($siteSettings === null) {
    try {
        $siteSettings = DatabaseHelper::fetchOne('SELECT site_name, system_logo_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $exception) {
        $siteSettings = null;
    }
}

$defaultLogo = UtilityHelper::baseUrl('public/assets/images/logo/logo.png');

$systemName = $siteSettings['site_name'] ?? 'پرتال ارزیابی ادمیت';
$systemLogoPath = $siteSettings['system_logo_path'] ?? null;
$systemLogoUrl = $systemLogoPath ? UtilityHelper::baseUrl('public/' . ltrim($systemLogoPath, '/')) : $defaultLogo;

$organizationName = trim((string) ($currentUser['organization_name'] ?? ''));
$organizationLogoPath = trim((string) ($currentUser['organization_logo'] ?? ''));
$organizationId = (int) ($currentUser['organization_id'] ?? 0);

if ($organizationId > 0 && ($organizationName === '' || $organizationLogoPath === '')) {
    try {
        $organizationRecord = DatabaseHelper::fetchOne(
            'SELECT name, logo_path FROM organizations WHERE id = :id LIMIT 1',
            ['id' => $organizationId]
        );

        if ($organizationRecord) {
            if ($organizationName === '' && !empty($organizationRecord['name'])) {
                $organizationName = $organizationRecord['name'];
            }

            if ($organizationLogoPath === '' && !empty($organizationRecord['logo_path'])) {
                $organizationLogoPath = $organizationRecord['logo_path'];
            }
        }
    } catch (Exception $exception) {
        // ignore lookup failure
    }
}

$displayOrganizationName = $organizationName !== '' ? $organizationName : $systemName;
$organizationLogoUrl = $organizationLogoPath !== ''
    ? UtilityHelper::baseUrl('public/' . ltrim($organizationLogoPath, '/'))
    : $systemLogoUrl;

$resolveMenuUrl = static function (?string $path): string {
    if ($path === null || $path === '') {
        return 'javascript:void(0)';
    }

    if (preg_match('/^(?:https?:)?\/\//u', $path)) {
        return $path;
    }

    return UtilityHelper::baseUrl($path);
};

$homeSidebarMenu = [
  
    [
        'title' => 'داشبورد',
        'icon' => 'speedometer-sharp',
        'route' => 'home',
    ],
    [
        'title' => 'آزمون',
        'icon' => 'clipboard-sharp',
        'route' => null,
        'children' => [
            [
                'title' => 'تقویم آموزشی',
                'route' => 'tests/training-calendar',
            ],
            [
                'title' => 'گزارشات',
                'route' => 'tests/reports',
            ],
        ],
    ],
    
];
?>

<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-top-brand text-center py-2 px-3 fw-semibold text-white-75 text-uppercase small">
        <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="sidebar-header">
        <div>
            <img src="<?= htmlspecialchars($systemLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" class="logo-icon" alt="لوگوی سامانه">
        </div>
        <div>
            <span class="logo-text mb-0 sidebar-site-title d-block text-truncate" title="<?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
        <div class="toggle-icon ms-auto"><ion-icon name="menu-sharp"></ion-icon></div>
    </div>

    

    <ul class="metismenu" id="menu">
        <div class="px-3 pt-2 pb-3 text-center organization-profile-block">
        <div class="rounded-circle mx-auto p-2 bg-white shadow-sm org-avatar">
            <img src="<?= htmlspecialchars($organizationLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded-circle h-100 w-100 object-fit-cover" alt="لوگوی سازمان">
        </div>
        <div class="fw-semibold mt-2 text-white org-name" title="<?= htmlspecialchars($displayOrganizationName, ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($displayOrganizationName, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="text-white-50 small mt-1">
            <?= htmlspecialchars(($currentUser['name'] ?? '') !== '' ? $currentUser['name'] : 'کاربر مهمان', ENT_QUOTES, 'UTF-8'); ?>
        </div>
    </div>
        <?php foreach ($homeSidebarMenu as $menuItem): ?>
            <?php
                $children = $menuItem['children'] ?? [];
                $hasChildren = is_array($children) && !empty($children);
                $href = $hasChildren ? 'javascript:void(0)' : $resolveMenuUrl($menuItem['route'] ?? null);
            ?>
            <li>
                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"<?= $hasChildren ? ' class="has-arrow"' : ''; ?>>
                    <div class="parent-icon">
                        <ion-icon name="<?= htmlspecialchars($menuItem['icon'], ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                    </div>
                    <div class="menu-title"><?= htmlspecialchars($menuItem['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                </a>
                <?php if ($hasChildren): ?>
                    <ul>
                        <?php foreach ($children as $child): ?>
                            <?php $childHref = $resolveMenuUrl($child['route'] ?? null); ?>
                            <li>
                                <a href="<?= htmlspecialchars($childHref, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($child['title'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
