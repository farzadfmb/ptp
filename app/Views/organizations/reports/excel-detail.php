<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات آزمون‌های تکمیل‌شده';
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

$rows = $rows ?? [];
$evaluateeSummary = $evaluateeSummary ?? ['id' => 0, 'name' => ''];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$tableOptions = [
    'paging' => true,
    'pageLength' => 20,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 992,
    'order' => [[0, 'asc']],
    'columnDefs' => [
        ['targets' => 4, 'orderable' => false, 'searchable' => false],
        ['targets' => 3, 'orderable' => false],
        ['targets' => '_all', 'className' => 'all'],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body {\n        background: #f4f6fb;\n    }\n    .excel-report-detail-table tbody tr td {\n        vertical-align: middle;\n        white-space: nowrap;\n    }\n    .excel-report-detail-header {\n        border-radius: 20px;\n        border: 1px solid rgba(148, 163, 184, 0.12);\n        background: linear-gradient(135deg, rgba(139, 92, 246, 0.08), rgba(14, 165, 233, 0.08));\n    }\n";

$formatPersianDateTime = static function ($dateTime, string $fallback = '-'): string {
    if (empty($dateTime)) {
        return $fallback;
    }

    try {
        $dt = new DateTime($dateTime, new DateTimeZone('Asia/Tehran'));
    } catch (Exception $exception) {
        try {
            $dt = new DateTime($dateTime);
        } catch (Exception $innerException) {
            return $fallback;
        }
        $dt->setTimezone(new DateTimeZone('Asia/Tehran'));
    }

    if (class_exists('IntlDateFormatter')) {
        $formatter = new IntlDateFormatter(
            'fa_IR',
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            'Asia/Tehran',
            IntlDateFormatter::TRADITIONAL,
            'yyyy/MM/dd HH:mm'
        );

        if ($formatter !== false) {
            $formatted = $formatter->format($dt);
            if ($formatted !== false) {
                return UtilityHelper::englishToPersian($formatted);
            }
        }
    }

    return UtilityHelper::englishToPersian($dt->format('Y/m/d H:i'));
};

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="card border-0 shadow-sm excel-report-detail-header mb-24">
            <div class="card-body p-24 d-flex flex-wrap justify-content-between align-items-center gap-16">
                <div>
                    <h2 class="mb-8 text-gray-900">گزارش آزمون‌های تکمیل‌شده</h2>
                    <p class="mb-2 text-gray-600">ارزیابی‌شونده: <strong><?= htmlspecialchars($evaluateeSummary['name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    <?php if (!empty($evaluateeSummary['evaluation_code'])): ?>
                        <p class="mb-0 text-gray-500">کد ارزیابی: <strong><?= UtilityHelper::englishToPersian($evaluateeSummary['evaluation_code']); ?></strong></p>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-10">
                    <a href="<?= UtilityHelper::baseUrl('organizations/reports/excel'); ?>" class="btn btn-outline-secondary rounded-pill px-24 d-flex align-items-center gap-8">
                        <ion-icon name="arrow-back-outline"></ion-icon>
                        بازگشت به گزارش اکسل
                    </a>
                </div>
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

        <div class="card border-0 shadow-sm rounded-24">
            <div class="card-body p-24">
                <h4 class="mb-20 text-gray-900">آزمون‌های تکمیل‌شده</h4>
                <div class="table-responsive border border-gray-100 rounded-20">
                    <table class="table mb-0 align-middle excel-report-detail-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="992">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th scope="col" class="text-center">ردیف</th>
                                <th scope="col">عنوان ارزیابی و ابزار</th>
                                <th scope="col">تاریخ ثبت</th>
                                <th scope="col">گزارش</th>
                                <th scope="col">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $row): ?>
                                    <?php
                                        $index = UtilityHelper::englishToPersian((string) ($row['index'] ?? 0));
                                        $evaluationTitle = trim((string) ($row['evaluation_title'] ?? ''));
                                        $toolTitle = trim((string) ($row['tool_title'] ?? ''));
                                        $registeredAt = $formatPersianDateTime($row['registered_at'] ?? null);
                                        $reportLink = $row['report_link'] ?? null;
                                        $operationLink = $row['operation_link'] ?? null;
                                        $retakeUrl = $row['retake_url'] ?? null;
                                        $participationId = (int) ($row['participation_id'] ?? 0);
                                        $summaryLink = null;
                                        if ($participationId > 0) {
                                            $summaryLink = UtilityHelper::baseUrl('organizations/reports/exam-summary?participation_id=' . urlencode((string) $participationId));
                                            $evaluateeId = (int) ($evaluateeSummary['id'] ?? 0);
                                            if ($evaluateeId > 0) {
                                                $summaryLink .= '&evaluatee_id=' . urlencode((string) $evaluateeId);
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-gray-700"><?= $index; ?></td>
                                        <td>
                                            <div class="d-flex flex-column text-end">
                                                <span class="fw-semibold text-gray-900"><?= htmlspecialchars($evaluationTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <small class="text-gray-500">ابزار: <?= htmlspecialchars($toolTitle, ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </td>
                                        <td><?= $registeredAt; ?></td>
                                        <td>
                                            <?php if ($summaryLink): ?>
                                                <a href="<?= htmlspecialchars($summaryLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-main rounded-pill px-16">مشاهده گزارش</a>
                                            <?php elseif ($reportLink): ?>
                                                <a href="<?= htmlspecialchars($reportLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-main rounded-pill px-16" target="_blank" rel="noopener">مشاهده گزارش</a>
                                            <?php else: ?>
                                                <span class="text-gray-400">ناموجود</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-8 justify-content-end">
                                                <?php if ($retakeUrl && $participationId > 0): ?>
                                                    <form method="post" action="<?= htmlspecialchars($retakeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="d-inline" onsubmit="return confirm('آیا از حذف آزمون و امکان شرکت مجدد اطمینان دارید؟');">
                                                        <input type="hidden" name="participation_id" value="<?= htmlspecialchars((string) $participationId, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="evaluatee_id" value="<?= htmlspecialchars((string) ($evaluateeSummary['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger rounded-pill px-16 d-flex align-items-center gap-8">
                                                            <ion-icon name="refresh-outline"></ion-icon>
                                                            دوباره آزمون بده
                                                        </button>
                                                    </form>
                                                <?php elseif ($operationLink): ?>
                                                    <a href="<?= htmlspecialchars($operationLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-main rounded-pill px-16" target="_blank" rel="noopener">جزئیات ابزار</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-32 text-gray-500">آزمون تکمیل‌شده‌ای برای نمایش وجود ندارد.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
