<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

if (!class_exists('PermissionHelper') && file_exists(__DIR__ . '/../../Helpers/PermissionHelper.php')) {
    require_once __DIR__ . '/../../Helpers/PermissionHelper.php';
}

$sidebarSettings = $sidebarSettings ?? null;

if ($sidebarSettings === null) {
    try {
        $sidebarSettings = DatabaseHelper::fetchOne('SELECT site_name, system_logo_path, system_default_avatar_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $e) {
        $sidebarSettings = null;
    }
}

$sidebarSiteName = $sidebarSettings['site_name'] ?? 'پنل سازمانی ادمیت';
$sidebarLogoPath = $sidebarSettings['system_logo_path'] ?? null;
$sidebarDefaultLogo = UtilityHelper::baseUrl('public/assets/images/logo/logo.png');
$sidebarLogoUrl = $sidebarLogoPath
    ? UtilityHelper::baseUrl('public/' . ltrim($sidebarLogoPath, '/'))
    : $sidebarDefaultLogo;
$sidebarDefaultAvatarPath = 'assets/images/thumbs/user-img.png';
$sidebarAvatarPath = $sidebarSettings['system_default_avatar_path'] ?? null;
$sidebarAvatarPath = $sidebarAvatarPath ?: $sidebarDefaultAvatarPath;
$sidebarDefaultAvatarUrl = UtilityHelper::baseUrl('public/' . ltrim($sidebarAvatarPath, '/'));

AuthHelper::startSession();
$currentOrganizationUser = AuthHelper::getUser();

$grantedPermissions = [];
if (class_exists('PermissionHelper')) {
    $rawPermissions = is_array($currentOrganizationUser) ? ($currentOrganizationUser['permissions'] ?? []) : [];
    $grantedPermissions = PermissionHelper::normalizePermissions($rawPermissions);
}

$resolveRoleLabel = static function ($user) {
    if (!is_array($user)) {
        return 'کاربر سازمان';
    }

    $label = trim((string)($user['role_label'] ?? ''));
    if ($label !== '') {
        return $label;
    }

    $label = trim((string)($user['organization_role_name'] ?? ''));
    if ($label !== '') {
        return $label;
    }

    $slug = trim((string)($user['role_slug'] ?? ''));
    $normalized = $slug !== '' ? mb_strtolower($slug, 'UTF-8') : '';

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

    return function_exists('mb_convert_case')
        ? mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8')
        : ucwords($clean);
};

$currentRoleLabel = $resolveRoleLabel($currentOrganizationUser);

$organizationData = is_array($currentOrganizationUser) ? ($currentOrganizationUser['organization'] ?? null) : null;
$organizationId = (int) ($organizationData['id'] ?? ($currentOrganizationUser['organization_id'] ?? 0));
$organizationName = $organizationData['name'] ?? ($currentOrganizationUser['organization_name'] ?? '');
$organizationLogoPath = $organizationData['logo_path'] ?? null;

if ($organizationId > 0) {
    try {
        $organizationRecord = DatabaseHelper::fetchOne(
            'SELECT name, logo_path FROM organizations WHERE id = :id LIMIT 1',
            ['id' => $organizationId]
        );

        if ($organizationRecord) {
            if (!empty($organizationRecord['name'])) {
                $organizationName = $organizationRecord['name'];
            }

            if (!empty($organizationRecord['logo_path'])) {
                $organizationLogoPath = $organizationRecord['logo_path'];
            }
        }
    } catch (Exception $exception) {
        // Silent failure; fall back to defaults
    }
}

$organizationLogoUrl = $organizationLogoPath
    ? UtilityHelper::baseUrl('public/' . ltrim($organizationLogoPath, '/'))
    : $sidebarDefaultAvatarUrl;

$organizationDisplayName = $organizationName !== '' ? $organizationName : 'سازمان من';

$resolveMenuUrl = static function (?string $path): string {
    if ($path === null || $path === '') {
        return 'javascript:void(0)';
    }

    if (preg_match('/^(?:https?:)?\/\//u', $path)) {
        return $path;
    }

    return UtilityHelper::baseUrl($path);
};

$organizationSidebarMenu = [
    [
        'title' => 'داشبورد',
        'icon' => 'home-sharp',
        'route' => 'organizations/dashboard',
        'permission' => 'dashboard_overview_view',
    ],
    [
        'title' => 'مدیریت سازمان',
        'icon' => 'briefcase-sharp',
        'permissions' => ['org_report_settings_manage', 'org_posts_manage', 'org_service_locations_manage'],
        'children' => [
            ['title' => 'تنظیمات گزارشات', 'route' => 'organizations/report-settings', 'permission' => 'org_report_settings_manage'],
            ['title' => 'پست های سازمانی', 'route' => 'organizations/posts', 'permission' => 'org_posts_manage'],
            ['title' => 'محل های خدمت', 'route' => 'organizations/service-locations', 'permission' => 'org_service_locations_manage'],
        ],
    ],
    [
        'title' => 'مدیریت دسترسی ها',
        'icon' => 'shield-checkmark-sharp',
        'permissions' => ['users_manage_roles', 'role_access_matrix_manage', 'executive_units_manage', 'users_manage_users', 'users_manage_user_roles'],
        'children' => [
            ['title' => 'مدیریت نقش‌ها', 'route' => 'organizations/roles', 'permission' => 'users_manage_roles'],
            ['title' => 'ماتریس نقش دسترسی', 'route' => 'organizations/role-access-matrix', 'permission' => 'role_access_matrix_manage'],
            ['title' => 'دستگاه‌های اجرایی', 'route' => 'organizations/executive-units', 'permission' => 'executive_units_manage'],
            ['title' => 'کاربران سازمان', 'route' => 'organizations/users', 'permission' => 'users_manage_users'],
            ['title' => 'ماتریس نقش کاربران', 'route' => 'organizations/users/role-matrix', 'permission' => 'users_manage_user_roles'],
        ],
    ],
    [
        'title' => 'مدیریت گروه های ارزیابی',
        'icon' => 'people-sharp',
        'permissions' => ['evaluation_calendar_manage', 'evaluation_calendar_matrix_manage'],
        'children' => [
            ['title' => 'تقویم ارزیابی', 'route' => 'organizations/evaluation-calendar', 'permission' => 'evaluation_calendar_manage'],
            ['title' => 'ماتریس تقویم ارزشیابی', 'route' => 'organizations/evaluation-calendar/matrix', 'permission' => 'evaluation_calendar_matrix_manage'],
        ],
    ],
    [
        'title' => 'مدیریت ابزار ها',
        'icon' => 'construct-sharp',
        'permissions' => ['tools_manage', 'tools_view', 'tools_mbti_settings_manage', 'tools_disc_settings_manage', 'tools_neo_settings_manage'],
        'children' => [
            ['title' => 'ابزار های ارزیابی', 'route' => 'organizations/evaluation-tools', 'permissions' => ['tools_view', 'tools_manage']],
            ['title' => 'تنظیمات آزمون MBTI', 'route' => 'organizations/tools/mbti-settings', 'permission' => 'tools_mbti_settings_manage'],
            ['title' => 'تنظیمات آزمون DISC', 'route' => 'organizations/tools/disc-settings', 'permission' => 'tools_disc_settings_manage'],
            ['title' => 'تنظیمات آزمون NEO', 'route' => 'organizations/tools/neo-settings', 'permission' => 'tools_neo_settings_manage'],
        ],
    ],
    [
        'title' => 'مدیریت شایستگی',
        'icon' => 'ribbon-sharp',
        'permissions' => ['competency_dimensions_manage', 'competencies_manage', 'competency_model_manage', 'competency_features_manage', 'competency_model_matrix_manage', 'tool_competency_matrix_manage', 'competencies_view'],
        'children' => [
            ['title' => 'ابعاد شایستگی', 'route' => 'organizations/competency-dimensions', 'permission' => 'competency_dimensions_manage'],
            ['title' => 'شایستگی ها', 'route' => 'organizations/competencies', 'permissions' => ['competencies_manage', 'competencies_view']],
            ['title' => 'مدل شایستگی', 'route' => 'organizations/competency-models', 'permission' => 'competency_model_manage'],
            ['title' => 'ویژگی های شایستگی', 'route' => 'organizations/competency-features', 'permission' => 'competency_features_manage'],
            ['title' => 'ماتریس مدل شایستگی', 'route' => 'organizations/competency-models/matrix', 'permission' => 'competency_model_matrix_manage'],
            ['title' => 'ماتریس شایستگی ابزار', 'route' => 'organizations/competency-tools/matrix', 'permission' => 'tool_competency_matrix_manage'],
        ],
    ],
    [
        'title' => 'مدیریت دوره های آموزشی',
        'icon' => 'book-sharp',
        'permissions' => ['courses_manage', 'courses_view'],
        'children' => [
            ['title' => 'برنامه های توسعه فردی', 'route' => 'organizations/development-programs', 'permissions' => ['courses_manage', 'courses_view']],
        ],
    ],
    [
        'title' => 'ثبت نتایج',
        'icon' => 'clipboard-sharp',
        'permissions' => ['results_exam_questionwise', 'results_exam_register', 'results_tool_score_manage', 'results_assessment_register', 'results_washup_register', 'results_excel_report', 'results_resume_selected', 'results_washup_final'],
        'children' => [
            ['title' => 'ثبت نتایج آزمون به تفکیک سوال', 'route' => 'upload_user_tool_qu/index.php', 'permission' => 'results_exam_questionwise'],
            ['title' => 'ثبت نتایج آزمون', 'route' => 'upload_user_tool_qu/upload_user_qu.php', 'permission' => 'results_exam_register'],
            ['title' => 'ثبت امتیاز ابزار', 'route' => 'upload_user_tool_qu/user_tool_value.php', 'permission' => 'results_tool_score_manage'],
            ['title' => 'ارزیابی های فعال', 'route' => 'organizations/active-evaluations', 'permission' => 'results_assessment_register'],
            ['title' => 'Wash-Up', 'route' => 'organizations/wash-up', 'permissions' => ['results_washup_register', 'results_washup_final']],
            ['title' => 'گزارش اکسل', 'route' => 'organizations/reports/excel', 'permission' => 'results_excel_report'],
            ['title' => 'رزومه های منتخب', 'route' => 'resume/index.php', 'permission' => 'results_resume_selected'],
        ],
    ],
    [
        'title' => 'گزارشات',
        'icon' => 'bar-chart-sharp',
        'permissions' => ['reports_self_view', 'reports_final_view', 'reports_dev_program_view', 'reports_settings_manage', 'reports_dashboard_view'],
        'children' => [
            ['title' => 'مشاهده نتایج خود ارزیابی کاربران', 'route' => 'organizations/reports/self-assessment', 'permission' => 'reports_self_view'],
            ['title' => 'گزارش نهایی ارزیابی', 'route' => 'report/UserFinalReport.php', 'permission' => 'reports_final_view'],
            ['title' => 'گزارش برنامه های توسعه فردی', 'route' => 'report/EducationList.php', 'permission' => 'reports_dev_program_view'],
            ['title' => 'تنظیمات گزارش ارزیابی', 'route' => 'report/reportsetting.php', 'permission' => 'reports_settings_manage'],
            ['title' => 'تنظیمات گواهی دوره', 'route' => 'organizations/reports/certificate-settings', 'permission' => 'reports_settings_manage'],
        ],
    ],
];

if (class_exists('PermissionHelper')) {
    $organizationSidebarMenu = PermissionHelper::filterMenuByPermissions($organizationSidebarMenu, $grantedPermissions);
}
?>

<aside class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="<?= htmlspecialchars($sidebarLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" class="logo-icon" alt="لوگوی سازمان">
        </div>
        <div>
            <span class="logo-text mb-0 sidebar-site-title d-block text-truncate" title="<?= htmlspecialchars($sidebarSiteName, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($sidebarSiteName, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
        <div class="toggle-icon ms-auto"><ion-icon name="menu-sharp"></ion-icon></div>
    </div>


    <ul class="metismenu" id="menu">
        <div class="px-3 pt-2 pb-3 text-center organization-profile-block">
        <div class="rounded-circle mx-auto p-2 bg-white shadow-sm org-avatar">
            <img src="<?= htmlspecialchars($organizationLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded-circle h-100 w-100 object-fit-cover" alt="لوگوی سازمان">
        </div>
        <div class="fw-semibold mt-2 text-white org-name" title="<?= htmlspecialchars($organizationDisplayName, ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($organizationDisplayName, ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <?php if ($currentOrganizationUser): ?>
            <div class="text-white-50 small mt-1">
                ورود به عنوان <?= htmlspecialchars($currentOrganizationUser['name'] ?? ($currentOrganizationUser['username'] ?? 'کاربر سازمان'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="text-white-50 small">
                نقش: <?= htmlspecialchars($currentRoleLabel, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php else: ?>
            <div class="text-white-50 small mt-1">کاربر مهمان</div>
        <?php endif; ?>
    </div>
        <?php foreach ($organizationSidebarMenu as $menuItem): ?>
            <?php $children = $menuItem['children'] ?? []; ?>
            <?php $hasChildren = !empty($children); ?>
            <?php $topHref = $hasChildren ? 'javascript:;' : $resolveMenuUrl($menuItem['route'] ?? null); ?>
            <li>
                <a href="<?= htmlspecialchars($topHref, ENT_QUOTES, 'UTF-8'); ?>" <?= $hasChildren ? ' class="has-arrow"' : ''; ?>>
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
                                <a href="<?= htmlspecialchars($childHref, ENT_QUOTES, 'UTF-8'); ?>">
                                    <ion-icon name="ellipse-outline"></ion-icon>
                                    <?= htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>


</aside>