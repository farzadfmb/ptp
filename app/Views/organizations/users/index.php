<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'کاربران سازمان';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com'
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$additional_css[] = 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css';
$additional_css[] = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$tableOptions = [
    'paging' => true,
    'pageLength' => 10,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 768,
    'scrollX' => true,
    'order' => [[12, 'desc']],
    'columnDefs' => [
        ['targets' => '_all', 'className' => 'all'],
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$organizationUsers = $organizationUsers ?? [];
$search = $search ?? '';
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$fallbackNotice = $fallbackNotice ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .organization-users-table tbody tr {\n        opacity: 1 !important;\n        transform: none !important;\n        visibility: visible !important;\n        display: table-row !important;\n    }\n    .organization-users-table tbody tr td {\n        vertical-align: middle;\n        white-space: nowrap;\n    }\n    .organization-users-table thead th {\n        white-space: nowrap;\n    }\n    .organization-users-table .badge {\n        font-size: 12px;\n        padding: 6px 12px;\n        border-radius: 999px;\n    }\n    .organization-users-actions {\n        display: flex;\n        justify-content: flex-end;\n        gap: 8px;\n        flex-wrap: wrap;\n    }\n    .organization-users-actions form {\n        display: inline-flex;\n        align-items: center;\n    }\n    .organization-users-actions .btn {\n        width: 36px;\n        height: 36px;\n        padding: 0;\n        display: inline-flex;\n        align-items: center;\n        justify-content: center;\n    }\n    .organization-users-actions ion-icon {\n        font-size: 18px;\n    }\n    .organization-users-table-wrapper {\n        overflow-x: auto;\n        -webkit-overflow-scrolling: touch;\n    }\n";

$booleanBadge = static function ($value, string $trueLabel = 'بله', string $falseLabel = 'خیر'): string {
    $isTrue = (int) $value === 1;
    $class = $isTrue ? 'badge bg-success-100 text-success-700' : 'badge bg-gray-100 text-gray-600';
    $label = $isTrue ? $trueLabel : $falseLabel;

    return sprintf('<span class="%s">%s</span>', $class, htmlspecialchars($label, ENT_QUOTES, 'UTF-8'));
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
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="">
                                <h2 class="mb-6 text-gray-900">لیست کاربران سازمان</h2>
                                <p class="text-gray-500 mb-0">کاربران ثبت‌شده برای سازمان را مشاهده، مدیریت و جستجو کنید.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-10">
                                <a href="<?= UtilityHelper::baseUrl('organizations/users/import'); ?>" class="btn btn-outline-main rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="cloud-upload-outline"></ion-icon>
                                    بارگذاری اکسل
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/users/create'); ?>" class="btn btn-main rounded-pill px-24 d-flex align-items-center gap-8">
                                    <ion-icon name="person-add-outline"></ion-icon>
                                    ایجاد کاربر
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16  d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16  d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                       

                        <?php if (!empty($fallbackNotice)): ?>
                           <div class="alert alert-warning rounded-16  d-flex align-items-center gap-12 mb-24" role="alert">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($fallbackNotice, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive organization-users-table-wrapper rounded-16 border border-gray-100">
                            <table class="table align-middle mb-0  organization-users-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search">عملیات</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">نام خانوادگی</th>
                                        <th scope="col">جنسیت</th>
                                        <th scope="col">کد ارزیابی</th>
                                        <th scope="col">ادمین سیستم</th>
                                        <th scope="col">مدیر</th>
                                        <th scope="col">ارزیابی‌شونده</th>
                                        <th scope="col">ارزیاب</th>
                                        <th scope="col">فعال</th>
                                        <th scope="col">استان</th>
                                        <th scope="col">شهر</th>
                                        <th scope="col">تاریخ ایجاد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($organizationUsers)): ?>
                                        <?php foreach ($organizationUsers as $organizationUser): ?>
                                            <tr>
                                                <td>
                                                    <div class="organization-users-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/users/edit?id=' . urlencode((string) ($organizationUser['id'] ?? ''))); ?>" class="btn btn-sm btn-main rounded-pill d-flex align-items-center justify-content-center" title="ویرایش" aria-label="ویرایش کاربر">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/users/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این کاربر اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars((string) ($organizationUser['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill d-flex align-items-center justify-content-center" title="حذف" aria-label="حذف کاربر">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($organizationUser['first_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($organizationUser['last_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php
                                                    $gender = strtolower((string) ($organizationUser['gender'] ?? ''));
                                                    $genderMap = [
                                                        'male' => 'مرد',
                                                        'female' => 'زن',
                                                        'other' => 'سایر',
                                                    ];
                                                    echo htmlspecialchars($genderMap[$gender] ?? ($organizationUser['gender'] ?? '-'), ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($organizationUser['evaluation_code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                           
                                                <td><?= $booleanBadge($organizationUser['is_system_admin'] ?? 0); ?></td>
                                                <td><?= $booleanBadge($organizationUser['is_manager'] ?? 0); ?></td>
                                                <td><?= $booleanBadge($organizationUser['is_evaluee'] ?? 0); ?></td>
                                                <td><?= $booleanBadge($organizationUser['is_evaluator'] ?? 0); ?></td>
                                                <td><?= $booleanBadge($organizationUser['is_active'] ?? 0, 'فعال', 'غیرفعال'); ?></td>
                                                <td><?= htmlspecialchars($organizationUser['province'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($organizationUser['city'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php
                                                    $createdAt = $organizationUser['created_at'] ?? null;
                                                    if ($createdAt) {
                                                        $formattedDate = date('Y/m/d H:i', strtotime($createdAt));
                                                        echo UtilityHelper::englishToPersian($formattedDate);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

              
                        <?php if (empty($organizationUsers)): ?>
                           <div class="alert alert-info mt-24 rounded-16  d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="people-circle-outline"></ion-icon>
                                <span>کاربری برای نمایش وجود ندارد. می‌توانید با دکمه «ایجاد کاربر» یک کاربر جدید اضافه کنید.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>