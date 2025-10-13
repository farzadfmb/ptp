<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'مدیریت محل خدمت';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
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

$organizationServiceLocations = $organizationServiceLocations ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .organization-service-locations-table tbody tr {\n        opacity: 1 !important;\n        transform: none !important;\n        visibility: visible !important;\n        display: table-row !important;\n    }\n    .organization-service-locations-table tbody tr td {\n        vertical-align: middle;\n    }\n    .organization-service-locations-table .table-actions {\n        display: inline-flex;\n        align-items: center;\n        gap: 8px;\n        flex-wrap: wrap;\n    }\n    .organization-service-locations-table .table-actions .btn,\n    .organization-service-locations-table .table-actions button {\n        width: 40px;\n        height: 40px;\n        padding: 0;\n        display: inline-flex;\n        align-items: center;\n        justify-content: center;\n        border-radius: 50%;\n    }\n    .organization-service-locations-header .btn-main {\n        min-width: 180px;\n    }\n";

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
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 organization-service-locations-header">
                            <div>
                                <h2 class="mb-6 text-gray-900">مدیریت محل خدمت</h2>
                                <p class="text-gray-500 mb-0">لیست محل‌های خدمت ثبت‌شده را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/service-locations/create'); ?>" class="btn btn-main" title="ایجاد محل خدمت">
                                    ایجاد محل خدمت
                                    <span class="visually-hidden">ایجاد محل خدمت</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive rounded-16 border border-gray-100" style="direction: rtl;">
                            <table class="table align-middle mb-0 organization-service-locations-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search">عملیات</th>
                                        <th scope="col">شناسه</th>
                                        <th scope="col">کد</th>
                                        <th scope="col">نام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($organizationServiceLocations)): ?>
                                        <?php foreach ($organizationServiceLocations as $location): ?>
                                            <tr>
                                                <td style="width: 10%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/service-locations/edit?id=' . urlencode((string) ($location['id'] ?? ''))); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/service-locations/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این محل خدمت اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($location['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($location['id'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($location['code'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($location['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($organizationServiceLocations)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    محلی برای نمایش وجود ندارد. برای ایجاد اولین مورد از دکمه «ایجاد محل خدمت» استفاده کنید.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
