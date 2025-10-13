<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'مدل‌های شایستگی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$competencyModels = $competencyModels ?? [];
$modelCompetencyCounts = $modelCompetencyCounts ?? [];
$scoringTypeOptions = $scoringTypeOptions ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

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
    'order' => [[2, 'asc']],
    'columnDefs' => [
        ['targets' => '_all', 'className' => 'all'],
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
        ['targets' => 4, 'orderable' => false, 'searchable' => false],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-models-table tbody tr td {
        vertical-align: middle;
    }
    .competency-models-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .competency-models-table .table-actions .btn,
    .competency-models-table .table-actions button {
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .competency-models-table .table-actions ion-icon {
        font-size: 18px;
    }
    .competency-models-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .model-image-overlay {
        position: fixed;
        inset: 0;
        background: rgba(29, 33, 48, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1055;
        padding: 24px;
    }
    .model-image-overlay.d-none {
        display: none !important;
    }
    .model-image-content {
        position: relative;
        max-width: min(720px, 95vw);
        max-height: 85vh;
        background: #fff;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .model-image-content img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 12px;
    }
    .model-image-close {
        align-self: flex-end;
        border: none;
        background: transparent;
        color: #111827;
        font-size: 22px;
        cursor: pointer;
    }
    .avatar-thumbnail {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }
CSS;

$inline_scripts .= <<<'JS'
    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('modelImageOverlay');
        if (!overlay) {
            return;
        }

        const overlayImage = overlay.querySelector('img');
        const closeButtons = overlay.querySelectorAll('[data-close]');

        document.querySelectorAll('[data-model-image]').forEach(function (button) {
            button.addEventListener('click', function () {
                const imageUrl = this.getAttribute('data-model-image');
                if (!imageUrl) {
                    return;
                }
                overlayImage.setAttribute('src', imageUrl);
                overlay.classList.remove('d-none');
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                overlay.classList.add('d-none');
                overlayImage.removeAttribute('src');
            });
        });

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                overlay.classList.add('d-none');
                overlayImage.removeAttribute('src');
            }
        });
    });
JS;

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
                                <h2 class="mb-6 text-gray-900">مدل‌های شایستگی سازمان</h2>
                                <p class="text-gray-500 mb-0">مدل‌های تعریف‌شده را مشاهده، ویرایش و مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-models/create'); ?>" class="btn btn-main" title="افزودن مدل شایستگی">
                                    افزودن مدل شایستگی
                                    <span class="visually-hidden">افزودن مدل شایستگی جدید</span>
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

                        <div class="table-responsive competency-models-table-wrapper rounded-16 border border-gray-100" style="direction: rtl;">
                            <table class="table align-middle mb-0 competency-models-table js-data-table mt-5" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search nowrap">عملیات</th>
                                        <th scope="col">نوع امتیازدهی</th>
                                        <th scope="col">کد مدل</th>
                                        <th scope="col">نام مدل</th>
                                        <th scope="col">عکس</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($competencyModels)): ?>
                                        <?php foreach ($competencyModels as $model): ?>
                                            <?php
                                                $modelId = (string) ($model['id'] ?? '');
                                                $code = trim((string) ($model['code'] ?? '-'));
                                                $titleText = trim((string) ($model['title'] ?? '-'));
                                                $scoringTypeKey = trim((string) ($model['scoring_type'] ?? ''));
                                                $scoringLabel = $scoringTypeOptions[$scoringTypeKey] ?? '—';
                                                $reportSettingTitle = trim((string) ($model['report_setting_title'] ?? ''));
                                                $reportLevel = trim((string) ($model['report_level'] ?? ''));
                                                $imagePath = trim((string) ($model['image_path'] ?? ''));
                                                $imageUrl = $imagePath !== '' ? UtilityHelper::baseUrl('public/' . ltrim($imagePath, '/')) : '';
                                                $competencyCount = (int) ($modelCompetencyCounts[(int) ($model['id'] ?? 0)] ?? 0);
                                            ?>
                                            <tr>
                                                <td class="nowrap" style="width: 12%;">
                                                    <div class="table-actions">
                                                        <a href="<?= UtilityHelper::baseUrl('organizations/competency-models/edit?id=' . urlencode($modelId)); ?>" class="btn btn-sm btn-outline-main" title="ویرایش">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/competency-models/delete'); ?>" method="post" onsubmit="return confirm('آیا از حذف این مدل شایستگی اطمینان دارید؟');" class="d-inline-flex">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($modelId, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف</span>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="نمایش نمودار" data-model-image="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" <?= $imageUrl === '' ? 'disabled' : ''; ?>>
                                                            <ion-icon name="image-outline"></ion-icon>
                                                            <span class="visually-hidden">نمایش تصویر مدل</span>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($scoringLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="fw-semibold text-gray-900"><?= htmlspecialchars($titleText, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="text-gray-500 small d-flex flex-wrap gap-8 mt-4">
                                                        <?php if ($reportLevel !== ''): ?>
                                                            <span class="badge rounded-pill bg-light text-gray-700">گزارش: <?= htmlspecialchars($reportLevel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ($reportSettingTitle !== ''): ?>
                                                            <span class="badge rounded-pill bg-light text-gray-700">تنظیم گزارش: <?= htmlspecialchars($reportSettingTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                        <span class="badge rounded-pill bg-main-100 text-main-600">شایستگی‌ها: <?= htmlspecialchars((string) $competencyCount, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($imageUrl !== ''): ?>
                                                        <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="تصویر مدل" class="avatar-thumbnail">
                                                    <?php else: ?>
                                                        <span class="text-gray-400">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (empty($competencyModels)): ?>
                                <div class="text-center py-32 text-gray-500">
                                    مدلی برای نمایش وجود ندارد. برای ایجاد اولین مدل از دکمه «افزودن مدل شایستگی» استفاده کنید.
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

<div id="modelImageOverlay" class="model-image-overlay d-none" role="dialog" aria-modal="true">
    <div class="model-image-content">
        <button type="button" class="model-image-close" aria-label="بستن" data-close>&times;</button>
        <img src="" alt="تصویر مدل شایستگی">
        <button type="button" class="btn btn-outline-gray rounded-pill align-self-end" data-close>بستن</button>
    </div>
</div>
