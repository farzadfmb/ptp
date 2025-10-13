<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'دستگاه‌های اجرایی';
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

$organizationExecutiveUnits = $organizationExecutiveUnits ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .organization-executive-units-table tbody tr {\n        opacity: 1 !important;\n        transform: none !important;\n        visibility: visible !important;\n        display: table-row !important;\n    }\n    .organization-executive-units-table tbody tr td {\n        vertical-align: middle;\n    }\n    .organization-executive-units-table .table-actions {\n        display: inline-flex;\n        align-items: center;\n        gap: 8px;\n        flex-wrap: wrap;\n    }\n    .organization-executive-units-table .table-actions .btn,\n    .organization-executive-units-table .table-actions button {\n        width: 40px;\n        height: 40px;\n        padding: 0;\n        display: inline-flex;\n        align-items: center;\n        justify-content: center;\n        border-radius: 50%;\n    }\n    .organization-executive-units-header .btn-main {\n        min-width: 200px;\n    }\n";

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
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 organization-executive-units-header">
                            <div>
                                <h2 class="mb-6 text-gray-900">دستگاه‌های اجرایی</h2>
                                <p class="text-gray-500 mb-0">لیست دستگاه‌های اجرایی ثبت‌شده را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/executive-units/create'); ?>" class="btn btn-main" title="ایجاد دستگاه اجرایی">
                                    ایجاد دستگاه اجرایی
                                    <span class="visually-hidden">ایجاد دستگاه اجرایی</span>
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
                            <table class="table align-middle mb-0 organization-executive-units-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search">عملیات</th>
                                        <th scope="col">شناسه</th>
                                        <th scope="col" class="text-start">نام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($organizationExecutiveUnits)): ?>
                                        <?php foreach ($organizationExecutiveUnits as $unit): ?>
                                            <tr>
                                                <td style="width: 10%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/executive-units/edit?id=' . urlencode((string) ($unit['id'] ?? ''))); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/executive-units/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این دستگاه اجرایی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($unit['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td style="width: 10%;"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($unit['id'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($unit['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($organizationExecutiveUnits)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    دستگاه اجرایی برای نمایش وجود ندارد. برای ایجاد اولین مورد از دکمه «ایجاد دستگاه اجرایی» استفاده کنید.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
