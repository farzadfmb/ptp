<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'گزارش اکسل ارزیابی‌شوندگان';
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
$additional_css[] = 'https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js';
$additional_js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$excelReportRows = $excelReportRows ?? [];
$excelReportSummary = $excelReportSummary ?? [];
$excelStatusMeta = $excelStatusMeta ?? [];
$statusOptions = $statusOptions ?? [];
$statusFilter = $statusFilter ?? 'all';
$search = $search ?? '';
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$totalEvaluatees = (int) ($excelReportSummary['total'] ?? count($excelReportRows));
$completedEvaluatees = (int) ($excelReportSummary['completed'] ?? 0);
$inProgressEvaluatees = (int) ($excelReportSummary['in_progress'] ?? 0);
$pendingEvaluatees = (int) ($excelReportSummary['pending'] ?? 0);
$noExamEvaluatees = (int) ($excelReportSummary['no_exam'] ?? 0);
$completionRate = $totalEvaluatees > 0 ? (int) round(($completedEvaluatees / $totalEvaluatees) * 100) : 0;
$totalAssignedExams = (int) ($excelReportSummary['assigned_exams_total'] ?? 0);
$totalCompletedExams = (int) ($excelReportSummary['completed_exams_total'] ?? 0);
$incompleteExams = max(0, $totalAssignedExams - $totalCompletedExams);

$tableOptions = [
    'paging' => true,
    'pageLength' => 25,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 992,
    'scrollX' => true,
    'dom' => "B<'row align-items-center mb-3'<'col-lg-6 col-md-6 col-sm-12 text-start text-md-start'l><'col-lg-6 col-md-6 col-sm-12 text-start text-md-end'f>><'row'<'col-12'tr>><'row align-items-center mt-3'<'col-md-6 col-sm-12 text-start text-md-start'i><'col-md-6 col-sm-12 text-start text-md-end'p>>",
    'buttons' => [
        [
            'extend' => 'excelHtml5',
            'text' => 'دریافت اکسل',
            'className' => 'btn btn-success rounded-pill px-20 ms-8',
            'title' => 'گزارش ارزیابی‌شوندگان',
            'exportOptions' => [
                'columns' => ':visible',
            ],
        ],
        [
            'extend' => 'print',
            'text' => 'چاپ',
            'className' => 'btn btn-outline-secondary rounded-pill px-20',
        ],
    ],
    'order' => [[3, 'asc']],
    'columnDefs' => [
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
        ['targets' => '_all', 'className' => 'all'],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body {\n        background: #f4f6fb;\n    }\n    .excel-report-table tbody tr td {\n        vertical-align: middle;\n        white-space: nowrap;\n    }\n    .excel-report-filters .form-select,\n    .excel-report-filters .form-control {\n        border-radius: 999px;\n    }\n    .excel-report-summary .card {\n        border-radius: 20px;\n        border: 1px solid rgba(148, 163, 184, 0.18);\n        box-shadow: 0 22px 32px rgba(15, 23, 42, 0.05);\n    }\n    .excel-report-table-wrapper {\n        overflow-x: auto;\n    }\n    .excel-report-status-badge {\n        border-radius: 999px;\n        padding: 6px 16px;\n        font-size: 0.875rem;\n        font-weight: 600;\n        display: inline-flex;\n        align-items: center;\n        gap: 6px;\n        transition: all 0.2s ease;\n        color: inherit !important;\n    }\n    .excel-report-status-link {\n        text-decoration: none;\n    }\n    .excel-report-status-link:hover .excel-report-status-badge {\n        transform: translateY(-1px);\n        box-shadow: 0 12px 20px rgba(56, 189, 248, 0.18);\n    }\n    .excel-report-status-button {\n        display: inline-flex;\n        align-items: center;\n        gap: 8px;\n        border-radius: 999px;\n        font-weight: 600;\n        font-size: 0.875rem;\n        padding: 8px 18px;\n        transition: all 0.2s ease;\n    }\n    .excel-report-status-button ion-icon {\n        font-size: 1.1rem;\n    }\n    .excel-report-status-button:hover {\n        transform: translateY(-1px);\n        box-shadow: 0 12px 20px rgba(15, 23, 42, 0.12);\n        text-decoration: none;\n    }\n";

$inline_styles .= "\n    .excel-report-status-button {\n        border: 1px solid rgba(148, 163, 184, 0.28);\n        background-color: #ffffff;\n        color: #1f2937;\n    }\n    .excel-report-status-button--completed {\n        background-color: rgba(22, 163, 74, 0.12);\n        border-color: rgba(22, 163, 74, 0.35);\n        color: #166534;\n    }\n    .excel-report-status-button--completed:hover {\n        box-shadow: 0 16px 24px rgba(22, 163, 74, 0.22);\n    }\n    .excel-report-status-button--in_progress {\n        background-color: rgba(234, 179, 8, 0.18);\n        border-color: rgba(234, 179, 8, 0.35);\n        color: #b45309;\n    }\n    .excel-report-status-button--in_progress:hover {\n        box-shadow: 0 16px 24px rgba(234, 179, 8, 0.25);\n    }\n    .excel-report-status-button--pending {\n        background-color: rgba(56, 189, 248, 0.12);\n        border-color: rgba(56, 189, 248, 0.28);\n        color: #0369a1;\n    }\n    .excel-report-status-button--pending:hover {\n        box-shadow: 0 16px 24px rgba(56, 189, 248, 0.2);\n    }\n    .excel-report-status-button--no_exam {\n        background-color: rgba(148, 163, 184, 0.16);\n        border-color: rgba(148, 163, 184, 0.35);\n        color: #475569;\n    }\n    .excel-report-status-button--no_exam:hover {\n        box-shadow: 0 16px 24px rgba(148, 163, 184, 0.2);\n    }\n";

$inline_styles .= "\n    .excel-report-summary .card .card-body {\n        padding: 16px 18px;\n    }\n    .excel-report-header-card .card-body {\n        padding: 18px 20px;\n    }\n";

$formatPersianDateTime = static function ($dateTime): string {
    if (empty($dateTime)) {
        return '-';
    }

    try {
        $dt = new DateTime($dateTime, new DateTimeZone('Asia/Tehran'));
    } catch (Exception $exception) {
        try {
            $dt = new DateTime($dateTime);
        } catch (Exception $innerException) {
            return '-';
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
        <div class="row g-4 mb-24 excel-report-summary">
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-12">
                            <div>
                                <p class="text-gray-500 mb-6">کل ارزیابی‌شوندگان</p>
                                <h3 class="mb-0 text-gray-900 fw-bold"><?= UtilityHelper::englishToPersian((string) $totalEvaluatees); ?></h3>
                            </div>
                            <span class="badge bg-main-50 text-main-600 rounded-pill">٪<?= UtilityHelper::englishToPersian((string) $completionRate); ?> تکمیل</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">آزمون‌های تکمیل‌شده</p>
                        <h4 class="mb-0 text-success-600 fw-semibold"><?= UtilityHelper::englishToPersian((string) $totalCompletedExams); ?></h4>
                        <p class="text-gray-400 text-xs mt-8 mb-0">معادل <?= UtilityHelper::englishToPersian((string) $completedEvaluatees); ?> ارزیابی‌شونده</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">آزمون‌های در جریان</p>
                        <h4 class="mb-0 text-warning-600 fw-semibold"><?= UtilityHelper::englishToPersian((string) $incompleteExams); ?></h4>
                        <p class="text-gray-400 text-xs mt-8 mb-0">برای <?= UtilityHelper::englishToPersian((string) ($inProgressEvaluatees + $pendingEvaluatees)); ?> ارزیابی‌شونده</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">تخصیص آزمون‌ها</p>
                        <h4 class="mb-0 text-gray-600 fw-semibold"><?= UtilityHelper::englishToPersian((string) $totalAssignedExams); ?></h4>
                        <p class="text-gray-400 text-xs mt-8 mb-0">بدون آزمون: <?= UtilityHelper::englishToPersian((string) $noExamEvaluatees); ?> نفر</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-24 excel-report-header-card">
            <div class="card-body p-24">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                    <div>
                        <h2 class="mb-6 text-gray-900">گزارش اکسل ارزیابی‌شوندگان</h2>
                        <p class="text-gray-500 mb-0">نمایش وضعیت شرکت در آزمون برای ارزیابی‌شوندگان سازمان و دریافت بسته ZIP شامل اکسل جداگانه برای هر آزمون تکمیل‌شده.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-8 text-start text-md-end">
                        <a href="<?= UtilityHelper::baseUrl('organizations/users/import'); ?>" class="btn btn-outline-main rounded-pill px-24 d-flex align-items-center gap-8">
                            <ion-icon name="download-outline"></ion-icon>
                            بارگذاری اکسل جدید
                        </a>
                        <small class="text-gray-500">- بسته ZIP شامل فایل‌های Excel (.xlsx) برای هر آزمون از صفحه جزئیات ارزیابی‌شونده قابل دریافت است.</small>
                    </div>
                </div>

                <?php foreach ([['type' => 'success', 'message' => $successMessage], ['type' => 'error', 'message' => $errorMessage], ['type' => 'warning', 'message' => $warningMessage], ['type' => 'info', 'message' => $infoMessage]] as $alert): ?>
                    <?php if (!empty($alert['message'])): ?>
                        <div class="alert alert-<?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?> rounded-16 d-flex align-items-center gap-12 mb-20" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars((string) $alert['message'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form method="get" class="excel-report-filters mb-24">
                    <div class="row g-16 align-items-center">
                        <div class="col-xl-5 col-md-6">
                            <label class="form-label text-gray-600">جستجو</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="نام، نام خانوادگی، کد ارزیابی یا کد ملی">
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <label class="form-label text-gray-600">وضعیت آزمون</label>
                            <select name="exam_status" class="form-select">
                                <?php foreach ($statusOptions as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?= $statusFilter === $value ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-2 d-flex align-items-end justify-content-start justify-content-md-end gap-8">
                            <button type="submit" class="btn btn-main rounded-pill px-24">اعمال فیلتر</button>
                            <a href="<?= UtilityHelper::baseUrl('organizations/reports/excel'); ?>" class="btn btn-outline-secondary rounded-pill px-24">پاک‌سازی</a>
                        </div>
                    </div>
                </form>

                <div class="excel-report-table-wrapper border border-gray-100 rounded-20">
                    <table class="table align-middle mb-0 excel-report-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="992">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th scope="col" class="no-sort no-search text-center">#</th>
                                <th scope="col">وضعیت آزمون</th>
                                <th scope="col">نام کاربری</th>
                                <th scope="col">نام و نام خانوادگی</th>
                                <th scope="col">جنسیت</th>
                                <th scope="col">کد ارزیابی</th>
                                <th scope="col">استان</th>
                                <th scope="col">شهر</th>
                                <th scope="col">تعداد آزمون</th>
                                <th scope="col">تکمیل شده</th>
                                <th scope="col">آخرین تکمیل</th>
                                <th scope="col">تاریخ ایجاد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($excelReportRows)): ?>
                                <?php foreach ($excelReportRows as $index => $row): ?>
                                    <?php
                                        $rowIndex = UtilityHelper::englishToPersian((string) ($index + 1));
                                        $statusMeta = $row['exam_status_meta'] ?? ['label' => 'نامشخص', 'badge_class' => 'badge bg-secondary'];
                                        $statusKeyValue = (string) ($row['exam_status_key'] ?? '');
                                        $detailUrl = $row['exam_detail_url'] ?? null;
                                        $statusBadgeClass = trim(($statusMeta['badge_class'] ?? 'badge bg-secondary') . ' excel-report-status-badge');
                                        $statusButtonClass = 'excel-report-status-button';
                                        if ($statusKeyValue !== '') {
                                            $statusModifier = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', $statusKeyValue));
                                            $statusButtonClass .= ' excel-report-status-button--' . $statusModifier;
                                        }
                                        $fullName = trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? '')) ?: '-';
                                        $gender = strtolower((string) ($row['gender'] ?? ''));
                                        $genderMap = [
                                            'male' => 'مرد',
                                            'female' => 'زن',
                                            'other' => 'سایر',
                                        ];
                                        $genderLabel = $genderMap[$gender] ?? ($row['gender'] ?? '-');
                                        $evaluationCode = $row['evaluation_code'] ?? '-';
                                        $province = $row['province'] ?? '-';
                                        $city = $row['city'] ?? '-';
                                        $username = $row['username'] ?? '-';
                                        $assignedExams = UtilityHelper::englishToPersian((string) ($row['exam_total_assigned'] ?? 0));
                                        $recordedExams = UtilityHelper::englishToPersian((string) ($row['exam_total_participations'] ?? 0));
                                        $completedExams = UtilityHelper::englishToPersian((string) ($row['exam_completed_participations'] ?? 0));
                                        $pendingExams = UtilityHelper::englishToPersian((string) ($row['exam_pending_participations'] ?? 0));

                                        $lastCompletedFormatted = $formatPersianDateTime($row['exam_last_completed_at'] ?? null);
                                        $createdFormatted = $formatPersianDateTime($row['created_at'] ?? null);
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-gray-600"><?= $rowIndex; ?></td>
                                        <td>
                                            <?php if (!empty($detailUrl)): ?>
                                                <a href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>" class="<?= htmlspecialchars($statusButtonClass, ENT_QUOTES, 'UTF-8'); ?>" title="نمایش گزارش آزمون‌های تکمیل‌شده">
                                                    <ion-icon name="open-outline"></ion-icon>
                                                    <span><?= htmlspecialchars($statusMeta['label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?></span>
                                                </a>
                                            <?php else: ?>
                                                <span class="<?= htmlspecialchars($statusBadgeClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars($statusMeta['label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($genderLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($evaluationCode, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($province !== '' ? $province : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($city !== '' ? $city : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="d-flex flex-column text-end">
                                                <span class="fw-semibold text-gray-900"><?= $assignedExams; ?></span>
                                                <small class="text-gray-400">ثبت شده: <?= $recordedExams; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column text-end">
                                                <span class="fw-semibold text-success-600"><?= $completedExams; ?></span>
                                                <small class="text-gray-400">باقیمانده: <?= $pendingExams; ?></small>
                                            </div>
                                        </td>
                                        <td><?= $lastCompletedFormatted; ?></td>
                                        <td><?= $createdFormatted; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($excelReportRows)): ?>
                    <div class="alert alert-info rounded-16 d-flex align-items-center gap-12 mt-24" role="alert">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        <span>هیچ ارزیابی‌شونده‌ای با شرایط فعلی یافت نشد. فیلترها را تغییر دهید یا کاربران جدید اضافه کنید.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
