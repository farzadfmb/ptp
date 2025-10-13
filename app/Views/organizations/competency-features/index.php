<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویژگی‌های شایستگی';
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

$competencyFeatures = $competencyFeatures ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-features-table tbody tr td {
        vertical-align: middle;
    }
    .competency-features-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .competency-features-table .table-actions .btn,
    .competency-features-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .competency-features-table .table-actions ion-icon {
        font-size: 18px;
    }
CSS;

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
                            <div>
                                <h2 class="mb-6 text-gray-900">ویژگی‌های شایستگی</h2>
                                <p class="text-gray-500 mb-0">لیست ویژگی‌های مرتبط با شایستگی‌های سازمان را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-features/create'); ?>" class="btn btn-main" title="افزودن ویژگی شایستگی">
                                    افزودن ویژگی جدید
                                    <span class="visually-hidden">افزودن ویژگی شایستگی جدید</span>
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
                            <table class="table align-middle mb-0 competency-features-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap">عملیات</th>
                                        <th scope="col">کد ویژگی</th>
                                        <th scope="col">نوع</th>
                                        <th scope="col">شایستگی</th>
                                        <th scope="col">توضیحات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($competencyFeatures)): ?>
                                        <?php foreach ($competencyFeatures as $feature): ?>
                                            <?php
                                                $featureId = (string) ($feature['id'] ?? '');
                                                $code = trim((string) ($feature['code'] ?? '-'));
                                                $typeKey = trim((string) ($feature['type'] ?? ''));
                                                $typeLabel = $typeKey !== '' ? $typeKey : '—';
                                                $competencyTitle = trim((string) ($feature['competency_title'] ?? ''));
                                                $competencyCode = trim((string) ($feature['competency_code'] ?? ''));
                                                $competencyDisplay = '';
                                                if ($competencyTitle !== '' && $competencyCode !== '') {
                                                    $competencyDisplay = $competencyCode . ' - ' . $competencyTitle;
                                                } elseif ($competencyTitle !== '') {
                                                    $competencyDisplay = $competencyTitle;
                                                } elseif ($competencyCode !== '') {
                                                    $competencyDisplay = $competencyCode;
                                                } else {
                                                    $competencyDisplay = '—';
                                                }
                                                $descriptionText = trim((string) ($feature['description'] ?? ''));
                                                $descriptionDisplay = $descriptionText !== ''
                                                    ? htmlspecialchars(mb_strimwidth($descriptionText, 0, 120, '…'), ENT_QUOTES, 'UTF-8')
                                                    : '<span class="text-gray-400">—</span>';
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 12%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/competency-features/edit?id=' . urlencode($featureId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-features/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این ویژگی شایستگی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($featureId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($competencyDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $descriptionDisplay; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($competencyFeatures)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    ویژگی شایستگی‌ای برای نمایش وجود ندارد. برای ثبت اولین ویژگی از دکمه «افزودن ویژگی جدید» استفاده کنید.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
