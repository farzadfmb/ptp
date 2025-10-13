<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'ماتریس تقویم ارزشیابی';
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

$evaluations = $evaluations ?? [];
$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');
$filterMeta = $filterMeta ?? ['date_key' => null, 'display' => null];
$filteredDateKey = $filterMeta['date_key'] ?? null;
$filteredDateDisplay = $filterMeta['display'] ?? null;
$filteredDateDisplaySafe = null;
if (!empty($filteredDateKey)) {
    if (is_string($filteredDateDisplay) && trim($filteredDateDisplay) !== '') {
        $filteredDateDisplaySafe = trim($filteredDateDisplay);
    } else {
        $filteredDateDisplaySafe = UtilityHelper::englishToPersian(str_replace('-', '/', (string) $filteredDateKey));
    }
}
$createEvaluationLink = UtilityHelper::baseUrl('organizations/evaluation-calendar/create' . (!empty($filteredDateKey) ? '?date=' . urlencode($filteredDateKey) : ''));

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-matrix-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .evaluation-matrix-header h2 {
        font-size: 22px;
        font-weight: 600;
    }
    .evaluation-matrix-header p {
        color: #475467;
        margin-bottom: 0;
    }
    .evaluation-matrix-table thead th,
    .evaluation-matrix-table tbody td {
        white-space: nowrap;
        vertical-align: middle;
    }
    .evaluation-matrix-table tbody tr {
        opacity: 1 !important;
        visibility: visible !important;
        transform: none !important;
        display: table-row !important;
    }
    .evaluation-matrix-table .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .evaluation-matrix-table .table-actions .btn,
    .evaluation-matrix-table .table-actions button {
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .evaluation-matrix-table .table-actions ion-icon {
        font-size: 18px;
    }
    .evaluation-matrix-tools-wrapper {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .evaluation-matrix-tools-wrapper .btn-matrix-manage {
        border-radius: 14px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .matrix-filter-alert {
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid rgba(59, 130, 246, 0.18);
    }
    .matrix-filter-alert .filter-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }
    .matrix-filter-alert .filter-meta ion-icon {
        font-size: 20px;
    }
    .badge-role {
        background: #f5f3ff;
        color: #5b21b6;
        font-size: 12px;
        border-radius: 999px;
        padding: 4px 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .evaluation-meta {
        font-size: 12px;
        color: #6b7280;
    }
    .evaluation-matrix-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
CSS;

$tableOptions = [
    'paging' => true,
    'pageLength' => 10,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 768,
    'scrollX' => true,
    'order' => [[1, 'desc']],
    'columnDefs' => [
        ['targets' => '_all', 'className' => 'all'],
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
        ['targets' => 5, 'orderable' => false],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card evaluation-matrix-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 evaluation-matrix-header">
                            <div>
                                <h2 class="mb-6 text-gray-900">ماتریس تقویم ارزشیابی</h2>
                                <p>برنامه‌های ارزیابی ثبت‌شده را مدیریت کنید و ابزارهای اختصاص‌یافته را مشاهده نمایید.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-12">
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-calendar'); ?>" class="btn btn-outline-main d-inline-flex align-items-center gap-6">
                                    <ion-icon name="calendar-outline"></ion-icon>
                                    تقویم ارزشیابی
                                </a>
                                <a href="<?= htmlspecialchars($createEvaluationLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-main d-inline-flex align-items-center gap-6">
                                    <ion-icon name="add-circle-outline"></ion-icon>
                                    افزودن ارزیابی
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($filteredDateKey)): ?>
                            <?php $matrixBaseLink = UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix'); ?>
                            <div class="matrix-filter-alert mb-24" role="status" aria-live="polite">
                                <div class="filter-meta">
                                    <ion-icon name="funnel-outline"></ion-icon>
                                    <span>نمایش ارزیابی‌های تاریخ <?= htmlspecialchars($filteredDateDisplaySafe, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <a href="<?= htmlspecialchars($matrixBaseLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-main">
                                    حذف فیلتر
                                </a>
                            </div>
                        <?php endif; ?>

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

                        <div class="table-responsive evaluation-matrix-table-wrapper rounded-16 border border-gray-100 mt-3">
                            <table class="table align-middle mb-0 evaluation-matrix-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="no-sort no-search">عملیات</th>
                                        <th scope="col">تاریخ</th>
                                        <th scope="col">عنوان</th>
                                        <th scope="col">مدل ارزیابی عمومی</th>
                                        <th scope="col">مدل ارزیابی اختصاصی</th>
                                        <th scope="col" class="no-sort">ماتریس تقویم ارزیابی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($evaluations)): ?>
                                        <?php foreach ($evaluations as $evaluation): ?>
                                            <?php
                                                $evaluationId = (int) ($evaluation['id'] ?? 0);
                                                $evaluationIdAttr = htmlspecialchars((string) $evaluationId, ENT_QUOTES, 'UTF-8');
                                                $titleValue = htmlspecialchars($evaluation['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8');
                                                $dateDisplay = htmlspecialchars($evaluation['date_display'] ?? '—', ENT_QUOTES, 'UTF-8');
                                                $generalModelLabel = trim((string) ($evaluation['general_model_label'] ?? ''));
                                                if ($generalModelLabel === '') {
                                                    $generalModelLabel = trim((string) ($evaluation['general_model'] ?? ''));
                                                }
                                                $generalModelRaw = trim((string) ($evaluation['general_model_raw'] ?? ''));
                                                $generalModelValueForDisplay = $generalModelLabel !== '' ? $generalModelLabel : $generalModelRaw;
                                                $generalModelDisplay = $generalModelValueForDisplay !== '' ? htmlspecialchars($generalModelValueForDisplay, ENT_QUOTES, 'UTF-8') : '—';

                                                $specificModelLabel = trim((string) ($evaluation['specific_model_label'] ?? ''));
                                                if ($specificModelLabel === '') {
                                                    $specificModelLabel = trim((string) ($evaluation['specific_model'] ?? ''));
                                                }
                                                $specificModelRaw = trim((string) ($evaluation['specific_model_raw'] ?? ''));
                                                $specificModelValueForDisplay = $specificModelLabel !== '' ? $specificModelLabel : $specificModelRaw;
                                                $specificModelDisplay = $specificModelValueForDisplay !== '' ? htmlspecialchars($specificModelValueForDisplay, ENT_QUOTES, 'UTF-8') : '—';
                                                $calendarLink = htmlspecialchars($evaluation['calendar_link'] ?? UtilityHelper::baseUrl('organizations/evaluation-calendar'), ENT_QUOTES, 'UTF-8');
                                                $editLink = htmlspecialchars(UtilityHelper::baseUrl('organizations/evaluation-calendar/edit?id=' . $evaluationIdAttr), ENT_QUOTES, 'UTF-8');
                                                $matrixManageLink = htmlspecialchars(UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix/manage?id=' . $evaluationIdAttr), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="table-actions">
                                                        <a href="<?= $editLink; ?>" class="btn btn-sm btn-outline-main" title="ویرایش ارزیابی">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            <span class="visually-hidden">ویرایش ارزیابی</span>
                                                        </a>
                                                        <a href="<?= $calendarLink; ?>" class="btn btn-sm btn-outline-secondary" title="مشاهده در تقویم">
                                                            <ion-icon name="calendar-clear-outline"></ion-icon>
                                                            <span class="visually-hidden">نمایش در تقویم</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('organizations/evaluation-calendar/delete'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('آیا از حذف ارزیابی «<?= $titleValue; ?>» اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= $evaluationIdAttr; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف ارزیابی">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                <span class="visually-hidden">حذف ارزیابی</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= $dateDisplay; ?></td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="fw-semibold text-gray-900"><?= $titleValue; ?></span>
                                                        <div class="d-flex flex-wrap gap-6">
                                                            <?php if (!empty($evaluation['evaluators'])): ?>
                                                                <span class="badge-role" title="ارزیاب‌ها">
                                                                    <ion-icon name="people-outline"></ion-icon>
                                                                    <?= UtilityHelper::englishToPersian((string) count($evaluation['evaluators'])); ?> ارزیاب
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if (!empty($evaluation['evaluatees'])): ?>
                                                                <span class="badge-role" title="ارزیاب‌شونده‌ها">
                                                                    <ion-icon name="person-circle-outline"></ion-icon>
                                                                    <?= UtilityHelper::englishToPersian((string) count($evaluation['evaluatees'])); ?> ارزیاب‌شونده
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= $generalModelDisplay; ?></td>
                                                <td><?= $specificModelDisplay; ?></td>
                                                <td>
                                                    <div class="evaluation-matrix-tools-wrapper">
                                                        <a href="<?= $matrixManageLink; ?>" class="btn btn-sm btn-outline-main btn-matrix-manage">
                                                            <ion-icon name="grid-outline"></ion-icon>
                                                            <span>ماتریس تقویم</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="py-32 text-center text-gray-500">
                                                <?php if (!empty($filteredDateKey)): ?>
                                                    در تاریخ <?= htmlspecialchars($filteredDateDisplaySafe ?? '', ENT_QUOTES, 'UTF-8'); ?> ارزیابی‌ای ثبت نشده است.
                                                <?php else: ?>
                                                    هنوز ارزیابی‌ای ثبت نشده است. برای شروع روی دکمه «افزودن ارزیابی» کلیک کنید.
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
