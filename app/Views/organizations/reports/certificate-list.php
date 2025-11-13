<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'لیست گواهی‌ها';
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

$certificateRows = isset($certificateRows) && is_array($certificateRows) ? $certificateRows : [];
$certificateSummary = isset($certificateSummary) && is_array($certificateSummary) ? $certificateSummary : [];
$evaluationOptions = isset($evaluationOptions) && is_array($evaluationOptions) ? $evaluationOptions : [];
$selectedEvaluationId = (int) ($selectedEvaluationId ?? 0);
$searchQuery = (string) ($searchQuery ?? '');
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$tableOptions = [
    'paging' => true,
    'pageLength' => 25,
    'lengthChange' => true,
    'responsive' => true,
    'order' => [[4, 'desc']],
];
$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body { background: #f4f6fb; }\n    .certificate-summary-card { border-radius: 20px; border: 1px solid rgba(148,163,184,0.18); box-shadow: 0 22px 32px rgba(15,23,42,0.05); }\n    .certificate-filters .form-select, .certificate-filters .form-control { border-radius: 999px; }\n    .certificate-table-wrapper { overflow-x: auto; }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="card border-0 shadow-sm rounded-24 mb-24">
            <div class="card-body p-24">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                    <div>
                        <h2 class="mb-6 text-gray-900">لیست گواهی‌ها</h2>
                        <p class="text-gray-500 mb-0">کاربران واجد شرایط دریافت گواهی بر اساس تکمیل آزمون‌ها و نهایی شدن امتیازات WashUp.</p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-8">
                   
                        <a href="<?= UtilityHelper::baseUrl('organizations/reports/self-assessment'); ?>" class="btn btn-outline-secondary rounded-pill px-20 d-flex align-items-center gap-8">
                            <ion-icon name="analytics-outline"></ion-icon>
                            نتایج خودارزیابی
                        </a>
                    </div>
                </div>

                <?php foreach ([['type' => 'success', 'message' => $successMessage], ['type' => 'error', 'message' => $errorMessage], ['type' => 'warning', 'message' => $warningMessage], ['type' => 'info', 'message' => $infoMessage]] as $alert): ?>
                    <?php if (!empty($alert['message'])): ?>
                        <div class="alert alert-<?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?> rounded-16 d-flex align-items-center gap-12 mb-16" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars((string) $alert['message'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php
                    $totalCertificates = (int) ($certificateSummary['total'] ?? 0);
                    $totalEvaluations = (int) ($certificateSummary['evaluations'] ?? 0);
                    $averageScoreAll = $certificateSummary['average_score'] ?? null;
                    $totalAgreedScores = (int) ($certificateSummary['agreed_scores'] ?? 0);

                    $avgScoreDisplay = ($averageScoreAll !== null && $averageScoreAll !== '')
                        ? UtilityHelper::englishToPersian(number_format((float) $averageScoreAll, 2))
                        : '—';
                ?>

                <div class="row g-3 mb-24">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="certificate-summary-card bg-white p-20 h-100">
                            <p class="text-gray-500 mb-6">تعداد گواهی‌های واجد شرایط</p>
                            <h3 class="mb-0 fw-semibold text-gray-900"><?= UtilityHelper::englishToPersian((string) $totalCertificates); ?></h3>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="certificate-summary-card bg-white p-20 h-100">
                            <p class="text-gray-500 mb-6">تعداد برنامه‌های ارزیابی</p>
                            <h3 class="mb-0 fw-semibold text-gray-900"><?= UtilityHelper::englishToPersian((string) $totalEvaluations); ?></h3>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="certificate-summary-card bg-white p-20 h-100">
                            <p class="text-gray-500 mb-6">میانگین امتیاز نهایی</p>
                            <h3 class="mb-0 fw-semibold text-main"><?= $avgScoreDisplay; ?></h3>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="certificate-summary-card bg-white p-20 h-100">
                            <p class="text-gray-500 mb-6">تعداد امتیازات ثبت‌شده</p>
                            <h3 class="mb-0 fw-semibold text-gray-900"><?= UtilityHelper::englishToPersian((string) $totalAgreedScores); ?></h3>
                        </div>
                    </div>
                </div>

                <form method="get" action="<?= UtilityHelper::baseUrl('organizations/reports/certificate-list'); ?>" class="certificate-filters mb-16">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-4">
                            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="جستجو نام/نام‌کاربری/کد ملی/عنوان ارزیابی..." />
                        </div>
                        <div class="col-12 col-md-4">
                            <select name="evaluation_id" class="form-select">
                                <?php foreach ($evaluationOptions as $option): ?>
                                    <?php
                                        $optionValue = (int) ($option['value'] ?? 0);
                                        $optionLabel = (string) ($option['label'] ?? '');
                                    ?>
                                    <option value="<?= $optionValue; ?>" <?= $optionValue === $selectedEvaluationId ? 'selected' : ''; ?>><?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 text-start text-md-end">
                            <button type="submit" class="btn btn-main rounded-pill px-20">اعمال فیلتر</button>
                        </div>
                    </div>
                </form>

                <div class="certificate-table-wrapper">
                    <table class="table table-striped align-middle w-100 js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>">
                        <thead>
                            <tr>
                                <th>نام و نام خانوادگی</th>
                                <th>نام‌کاربری</th>
                                <th>کد ملی</th>
                                <th>عنوان ارزیابی</th>
                                <th>تاریخ ارزیابی</th>
                                <th>مدل شایستگی</th>
                                <th>میانگین امتیاز</th>
                                <th>تکمیل آزمون</th>
                                <th>آخرین بروزرسانی WashUp</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($certificateRows)): ?>
                                <?php foreach ($certificateRows as $row): ?>
                                    <?php
                                        $evaluationId = (int) ($row['evaluation_id'] ?? 0);
                                        $evaluateeId = (int) ($row['evaluatee_id'] ?? 0);
                                        $fullName = trim((string) ($row['full_name'] ?? ''));
                                        if ($fullName === '') {
                                            $firstName = (string) ($row['first_name'] ?? '');
                                            $lastName = (string) ($row['last_name'] ?? '');
                                            $fullName = trim($firstName . ' ' . $lastName);
                                        }
                                        if ($fullName === '') {
                                            $fullName = (string) ($row['username'] ?? '---');
                                        }
                                        $avgScore = $row['average_score'] ?? null;
                                        $avgDisplay = ($avgScore !== null && $avgScore !== '')
                                            ? UtilityHelper::englishToPersian(number_format((float) $avgScore, 2))
                                            : '—';
                                        $certificateUrl = ($evaluationId > 0 && $evaluateeId > 0)
                                            ? UtilityHelper::baseUrl('organizations/reports/certificate-view?evaluation_id=' . urlencode((string) $evaluationId) . '&evaluatee_id=' . urlencode((string) $evaluateeId))
                                            : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['username'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($row['national_code'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['evaluation_title'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['evaluation_date_display'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['competency_model'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= $avgDisplay; ?></td>
                                        <td><?= htmlspecialchars((string) ($row['exam_completed_at_display'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string) ($row['washup_updated_display'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <?php if ($certificateUrl !== ''): ?>
                                                <a href="<?= htmlspecialchars($certificateUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-success btn-sm rounded-pill px-16 d-inline-flex align-items-center gap-6" target="_blank">
                                                    <ion-icon name="ribbon-outline"></ion-icon>
                                                    مشاهده گواهی
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-16">رکوردی مطابق با فیلترهای فعلی یافت نشد.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
