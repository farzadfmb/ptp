<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'نتایج خود ارزیابی کاربران';
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
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js';
$additional_js[] = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js';
$additional_js[] = 'https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$selfAssessmentRows = $selfAssessmentRows ?? [];
$selfAssessmentSummary = $selfAssessmentSummary ?? ['total_records' => 0, 'filtered_records' => 0, 'average_score' => null];
$evaluationOptions = $evaluationOptions ?? [];
$selectedEvaluationId = $selectedEvaluationId ?? 0;
$searchQuery = $searchQuery ?? '';
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$totalRecords = (int) ($selfAssessmentSummary['total_records'] ?? count($selfAssessmentRows));
$filteredRecords = (int) ($selfAssessmentSummary['filtered_records'] ?? count($selfAssessmentRows));
$averageScoreSummary = $selfAssessmentSummary['average_score'] ?? null;
$averageScoreDisplay = $averageScoreSummary !== null
    ? UtilityHelper::englishToPersian(number_format((float) $averageScoreSummary, 2))
    : '—';

$tableOptions = [
    'paging' => true,
    'pageLength' => 25,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 992,
    'dom' => "B<'row align-items-center mb-3'<'col-lg-6 col-md-6 col-sm-12 text-start text-md-start'l><'col-lg-6 col-md-6 col-sm-12 text-start text-md-end'f>><'row'<'col-12'tr>><'row align-items-center mt-3'<'col-md-6 col-sm-12 text-start text-md-start'i><'col-md-6 col-sm-12 text-start text-md-end'p>>",
    'buttons' => [
        [
            'extend' => 'excelHtml5',
            'text' => 'خروجی اکسل',
            'className' => 'btn btn-success rounded-pill px-20 ms-8',
            'title' => 'نتایج خود ارزیابی کاربران',
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
    'order' => [[1, 'desc']],
    'columnDefs' => [
        ['targets' => 0, 'orderable' => false, 'searchable' => false],
        ['targets' => '_all', 'className' => 'all'],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body {\n        background: #f4f6fb;\n    }\n    .self-assessment-summary .card {\n        border-radius: 20px;\n        border: 1px solid rgba(148, 163, 184, 0.18);\n        box-shadow: 0 22px 32px rgba(15, 23, 42, 0.05);\n    }\n    .self-assessment-table tbody tr td {\n        vertical-align: middle;\n        white-space: nowrap;\n    }\n    .self-assessment-stat {\n        display: inline-flex;\n        flex-direction: column;\n        align-items: flex-end;\n        gap: 4px;\n    }\n    .self-assessment-result-tag {\n        display: inline-flex;\n        align-items: center;\n        gap: 8px;\n        border-radius: 999px;\n        padding: 8px 16px;\n        background: rgba(22, 163, 74, 0.12);\n        color: #166534;\n        font-weight: 600;\n    }\n    .self-assessment-result-tag ion-icon {\n        font-size: 1.1rem;\n    }\n    .self-assessment-result-meta {\n        font-size: 0.75rem;\n        color: #64748b;\n    }\n    .self-assessment-filter-form .form-control,\n    .self-assessment-filter-form .form-select {\n        border-radius: 999px;\n    }\n";

$formatNumber = static function ($value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    return UtilityHelper::englishToPersian(number_format((float) $value, 2));
};

$formatInteger = static function ($value): string {
    return UtilityHelper::englishToPersian((string) max(0, (int) $value));
};

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4 mb-24 self-assessment-summary">
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">تعداد کل رکوردها</p>
                        <h3 class="mb-0 text-gray-900 fw-bold"><?= UtilityHelper::englishToPersian((string) $totalRecords); ?></h3>
                        <p class="text-gray-400 text-xs mt-8 mb-0">نمایش داده شده: <?= UtilityHelper::englishToPersian((string) $filteredRecords); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">میانگین امتیاز خود ارزیابی</p>
                        <h3 class="mb-0 text-success-600 fw-bold"><?= $averageScoreDisplay; ?></h3>
                        <p class="text-gray-400 text-xs mt-8 mb-0">میانگین میانگین‌های ثبت شده</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-12">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-gray-500 mb-6">راهنما</p>
                        <p class="text-gray-600 mb-0 text-sm">در این لیست تنها ارزیابی‌شوندگانی قرار دارند که آزمون خود ارزیابی را کامل کرده‌اند؛ می‌توانید با فیلتر برنامه ارزیابی یا جستجوی متن آزاد نتایج را دقیق‌تر کنید.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-24">
            <div class="card-body p-24">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                    <div>
                        <h2 class="mb-6 text-gray-900">نتایج خود ارزیابی کاربران</h2>
                        <p class="text-gray-500 mb-0">فقط برنامه‌هایی نمایش داده می‌شوند که آزمون خود ارزیابی توسط ارزیابی‌شونده به‌طور کامل تکمیل شده است.</p>
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

                <form method="get" class="self-assessment-filter-form mb-24">
                    <div class="row g-16 align-items-end">
                        <div class="col-xl-5 col-md-6">
                            <label class="form-label text-gray-600">جستجو</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="نام، نام خانوادگی، کد ملی یا برنامه ارزیابی">
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <label class="form-label text-gray-600">برنامه ارزیابی</label>
                            <select name="evaluation_id" class="form-select">
                                <?php foreach ($evaluationOptions as $option): ?>
                                    <?php
                                        $value = (int) ($option['value'] ?? 0);
                                        $label = (string) ($option['label'] ?? '');
                                    ?>
                                    <option value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= (int) $selectedEvaluationId === $value ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-3 col-md-2 d-flex align-items-center justify-content-start justify-content-md-end gap-8">
                            <button type="submit" class="btn btn-main rounded-pill px-24">اعمال فیلتر</button>
                            <a href="<?= UtilityHelper::baseUrl('organizations/reports/self-assessment'); ?>" class="btn btn-outline-secondary rounded-pill px-24">پاک‌سازی</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive border border-gray-100 rounded-20">
                    <table class="table align-middle mb-0 self-assessment-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="992">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th scope="col" class="text-center">#</th>
                                <th scope="col">نتایج آزمون</th>
                                <th scope="col">نام کاربری</th>
                                <th scope="col">کد ملی</th>
                                <th scope="col">نام</th>
                                <th scope="col">نام خانوادگی</th>
                                <th scope="col">مدل شایستگی</th>
                                <th scope="col">برنامه ارزیابی</th>
                                <th scope="col">تاریخ ارزیابی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($selfAssessmentRows)): ?>
                                <?php foreach ($selfAssessmentRows as $index => $row): ?>
                                    <?php
                                        $rowIndex = UtilityHelper::englishToPersian((string) ($index + 1));
                                        $averageScore = $row['average_score'] ?? null;
                                        $averageScoreLabel = $averageScore !== null ? $formatNumber($averageScore) : '—';
                                        $scoreCountLabel = $formatInteger($row['score_count'] ?? 0);
                                        $lastScoredDisplay = $row['last_scored_at_display'] ?? '—';
                                        $username = $row['username'] !== '' ? $row['username'] : '-';
                                        $nationalCode = $row['national_code'] !== '' ? $row['national_code'] : '-';
                                        $firstName = $row['first_name'] !== '' ? $row['first_name'] : '-';
                                        $lastName = $row['last_name'] !== '' ? $row['last_name'] : '-';
                                        $competencyModel = $row['competency_model'] !== '' ? $row['competency_model'] : '—';
                                        $evaluationTitle = $row['evaluation_title'] !== '' ? $row['evaluation_title'] : 'بدون عنوان';
                                        $evaluationDateDisplay = $row['evaluation_date_display'] ?? '—';
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-gray-600"><?= $rowIndex; ?></td>
                                        <td>
                                            <?php
                                                $canView = !empty($row['exam_results_viewable']);
                                                $viewLabel = 'مشاهده';
                                                $btnClass = $canView ? 'btn btn-primary rounded-pill px-16' : 'btn btn-outline-secondary rounded-pill px-16 disabled';
                                                $href = $canView ? '#' : 'javascript:void(0)';
                                                $evaluateeId = (int) ($row['evaluatee_id'] ?? 0);
                                                $evaluationId = (int) ($row['evaluation_id'] ?? 0);
                                                if ($canView && $evaluateeId > 0 && $evaluationId > 0) {
                                                    $href = UtilityHelper::baseUrl('organizations/reports/self-assessment/certificate?evaluation_id=' . urlencode((string) $evaluationId) . '&evaluatee_id=' . urlencode((string) $evaluateeId));
                                                }
                                            ?>
                                            <a href="<?= $href; ?>" class="<?= $btnClass; ?>" tabindex="<?= $canView ? '0' : '-1'; ?>" aria-disabled="<?= $canView ? 'false' : 'true'; ?>"><?= $viewLabel; ?></a>
                                        </td>
                                        <td><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($nationalCode, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($competencyModel, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($evaluationTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($evaluationDateDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($selfAssessmentRows)): ?>
                    <div class="alert alert-info rounded-16 d-flex align-items-center gap-12 mt-24" role="alert">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        <span>هنوز هیچ آزمون خود ارزیابی به‌طور کامل توسط ارزیابی‌شوندگان تکمیل نشده است یا شرایط فیلتر شما نتیجه‌ای نداشت.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
