<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ابعاد شایستگی';
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

$competencyDimensions = $competencyDimensions ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-dimensions-table tbody tr td {
        vertical-align: middle;
    }
    .competency-dimensions-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .competency-dimensions-table .table-actions .btn,
    .competency-dimensions-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .competency-dimensions-table .table-actions ion-icon {
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
                                <h2 class="mb-6 text-gray-900">ابعاد شایستگی</h2>
                                <p class="text-gray-500 mb-0">لیست ابعاد شایستگی سازمان را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-dimensions/create'); ?>" class="btn btn-main" title="افزودن بعد شایستگی">
                                    افزودن بعد شایستگی
                                    <span class="visually-hidden">افزودن بعد شایستگی</span>
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
                            <table class="table align-middle mb-0 competency-dimensions-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap">عملیات</th>
                                        <th scope="col">نام</th>
                                        <th scope="col">توضیحات</th>
                                        <th scope="col">عمومی می باشد؟</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($competencyDimensions)): ?>
                                        <?php foreach ($competencyDimensions as $dimension): ?>
                                            <?php
                                                $dimensionId = (string) ($dimension['id'] ?? '');
                                                $dimensionName = trim((string) ($dimension['name'] ?? '-'));
                                                $dimensionDescription = trim((string) ($dimension['description'] ?? ''));
                                                $isPublic = (int) ($dimension['is_public'] ?? 0) === 1;
                                                $descriptionDisplay = $dimensionDescription !== ''
                                                    ? htmlspecialchars(mb_strimwidth($dimensionDescription, 0, 120, '…'), ENT_QUOTES, 'UTF-8')
                                                    : '<span class="text-gray-400">—</span>';
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 10%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/competency-dimensions/edit?id=' . urlencode($dimensionId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-dimensions/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این بعد شایستگی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($dimensionId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($dimensionName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $descriptionDisplay; ?></td>
                                                <td>
                                                    <?php if ($isPublic): ?>
                                                        <span class="badge bg-success-subtle text-success fw-semibold px-3 py-2">بله</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-gray-200 text-gray-600 fw-semibold px-3 py-2">خیر</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($competencyDimensions)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    بعد شایستگی‌ای برای نمایش وجود ندارد. برای ایجاد اولین بعد از دکمه «افزودن بعد شایستگی» استفاده کنید.
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
