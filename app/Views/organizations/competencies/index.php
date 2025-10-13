<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'شایستگی‌ها';
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

$competencies = $competencies ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competencies-table tbody tr td {
        vertical-align: middle;
    }
    .competencies-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .competencies-table .table-actions .btn,
    .competencies-table .table-actions button {
        width: 40px;
        height: 40px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .competencies-table .table-actions ion-icon {
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
                                <h2 class="mb-6 text-gray-900">شایستگی‌های سازمان</h2>
                                <p class="text-gray-500 mb-0">لیست شایستگی‌های تعریف‌شده را مشاهده و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competencies/create'); ?>" class="btn btn-main" title="افزودن شایستگی">
                                    افزودن شایستگی
                                    <span class="visually-hidden">افزودن شایستگی</span>
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
                            <table class="table align-middle mb-0 competencies-table js-data-table mt-5">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap">عملیات</th>
                                        <th scope="col">تعریف مصداق</th>
                                        <th scope="col">کد شایستگی</th>
                                        <th scope="col">بعد شایستگی</th>
                                        <th scope="col">عنوان شایستگی</th>
                                        <th scope="col">تعریف شایستگی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($competencies)): ?>
                                        <?php foreach ($competencies as $competency): ?>
                                            <?php
                                                $competencyId = (string) ($competency['id'] ?? '');
                                                $code = trim((string) ($competency['code'] ?? '-'));
                                                $titleText = trim((string) ($competency['title'] ?? '-'));
                                                $definitionText = trim((string) ($competency['definition'] ?? ''));
                                                $dimensionName = trim((string) ($competency['dimension_name'] ?? '—'));

                                                $definitionDisplay = $definitionText !== ''
                                                    ? htmlspecialchars(mb_strimwidth($definitionText, 0, 120, '…'), ENT_QUOTES, 'UTF-8')
                                                    : '<span class="text-gray-400">—</span>';
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 10%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/competencies/edit?id=' . urlencode($competencyId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/competencies/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این شایستگی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($competencyId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="<?= UtilityHelper::baseUrl('organizations/competencies/examples?competency_id=' . urlencode($competencyId)); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="مدیریت مصداق‌ها">
                                                        مصداق
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($dimensionName !== '' ? $dimensionName : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $definitionDisplay; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($competencies)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    شایستگی‌ای برای نمایش وجود ندارد. برای ایجاد اولین شایستگی از دکمه «افزودن شایستگی» استفاده کنید.
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
