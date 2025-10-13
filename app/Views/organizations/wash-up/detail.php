<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات Wash-Up';
$user = $user ?? (class_exists('AuthHelper') && AuthHelper::getUser() ? AuthHelper::getUser() : null);
$detailMode = isset($detailMode) && $detailMode === 'final' ? 'final' : 'washup';

$evaluationSummary = $evaluationSummary ?? [
    'id' => 0,
    'title' => 'ارزیابی',
    'date_display' => '—',
    'general_model_label' => '',
    'specific_model_label' => '',
    'schedule_title' => '',
];
$evaluateeSummary = $evaluateeSummary ?? [
    'id' => 0,
    'label' => 'ارزیابی‌شونده',
];
$scorerSummary = $scorerSummary ?? [];
$toolHeaders = $toolHeaders ?? [];
$toolSummaries = $toolSummaries ?? [];
if (empty($toolHeaders) && !empty($toolSummaries) && is_array($toolSummaries)) {
    $toolHeaders = array_values($toolSummaries);
}
$competencySummaries = $competencySummaries ?? [];
$detailStats = $detailStats ?? [
    'total_scores' => 0,
    'average_score' => null,
    'tools_total' => 0,
    'tools_with_scores' => 0,
    'competencies_total' => 0,
];
$visibilityContext = $visibilityContext ?? [
    'role_label' => 'کاربر سازمان',
    'user_display' => 'کاربر سازمان',
    'message' => '',
    'can_view_all' => false,
    'is_evaluator' => false,
    'is_evaluatee' => false,
];

$pageMessages = $pageMessages ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;
$toolsWithoutScores = $toolsWithoutScores ?? [];
$finalRecommendation = $finalRecommendation ?? null;
$agreedScoresAction = $agreedScoresAction ?? UtilityHelper::baseUrl('organizations/wash-up/agreed-scores');
$canEditAgreedScores = $canEditAgreedScores ?? false;

if (!function_exists('washup_escape_html')) {
    function washup_escape_html($value, string $default = '—'): string
    {
        if (is_array($value)) {
            $value = implode('، ', array_filter(array_map(static function ($item) {
                return is_scalar($item) ? (string) $item : '';
            }, $value)));
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $value = $default;
            }
        }

        $value = trim((string) $value);
        if ($value === '') {
            $value = $default;
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$backLink = $backLink ?? UtilityHelper::baseUrl('organizations/wash-up');
$listLink = $listLink ?? $backLink;
$washUpLink = $washUpLink ?? UtilityHelper::currentUrl();
$finalLink = $finalLink ?? UtilityHelper::currentUrl();
$lastUpdatedDisplay = $lastUpdatedDisplay ?? null;
$lastUpdatedAgo = $lastUpdatedAgo ?? null;
$canFinalize = $canFinalize ?? false;

$modeLabel = $detailMode === 'final' ? 'توصیه نهایی' : 'Wash-Up';
$secondaryModeLabel = $detailMode === 'final' ? 'Wash-Up' : 'توصیه نهایی';
$secondaryModeLink = $detailMode === 'final' ? $washUpLink : $finalLink;
$secondaryModeIcon = $detailMode === 'final' ? 'document-text-outline' : 'checkmark-done-outline';

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<'CSS'
    body {
        background: #f4f6fb;
    }
    .washup-card {
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .washup-header-card {
        position: relative;
        overflow: hidden;
    }
    .washup-header-card::after {
        content: '';
        position: absolute;
        inset-inline-end: -60px;
        inset-block-start: -60px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle at center, rgba(59, 130, 246, 0.28), transparent 70%);
        z-index: 0;
    }
    .washup-header-card > * {
        position: relative;
        z-index: 1;
    }
    .detail-meta-list {
        font-size: 0.85rem;
        color: #475569;
        gap: 12px;
    }
    .detail-meta-list ion-icon {
        color: #2563eb;
        font-size: 1.1rem;
    }
    .summary-stat-card {
        border-radius: 20px;
        background: #f8fafc;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #64748b;
        min-height: 120px;
    }
    .summary-stat-card .label {
        font-weight: 600;
        margin-bottom: 6px;
    }
    .summary-stat-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .summary-stat-card .icon {
        font-size: 2.4rem;
        color: rgba(59, 130, 246, 0.75);
    }
    .competency-table thead th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 600;
        border: none;
    }
    .competency-table tbody td {
        vertical-align: middle;
        border-color: #e2e8f0;
    }
    .competency-cell .title {
        font-weight: 600;
        color: #0f172a;
    }
    .competency-cell .meta {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        color: #64748b;
        font-size: 0.85rem;
    }
    .competency-cell .badge {
        background: #e2e8f0;
        color: #0f172a;
        border-radius: 999px;
        padding: 3px 10px;
    }
    .competency-stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .competency-stat .stat-label {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .competency-stat .stat-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
    }
    .empty-state {
        background: #f8fafc;
        border: 1px dashed #cbd5f5;
        border-radius: 16px;
        padding: 32px;
        text-align: center;
        color: #64748b;
    }
    .action-buttons .btn {
        border-radius: 999px;
    }
    .scorer-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
        font-size: 0.85rem;
    }
    .scorer-badge ion-icon {
        font-size: 1rem;
    }
    .final-recommendation-card {
        border: 1px solid rgba(37, 99, 235, 0.16);
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.02));
    }
    .final-recommendation-card .meta {
        color: #475569;
        font-size: 0.85rem;
    }
    .final-recommendation-content {
        display: grid;
        gap: 18px;
        margin-top: 18px;
    }
    .recommendation-section {
        background: rgba(255, 255, 255, 0.72);
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid rgba(148, 163, 184, 0.24);
    }
    .recommendation-section .section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1d4ed8;
        margin-bottom: 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .recommendation-section .section-body {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #0f172a;
        white-space: pre-wrap;
    }
    .recommendation-empty {
        border: 1px dashed rgba(37, 99, 235, 0.28);
        border-radius: 16px;
        padding: 20px;
        background: rgba(37, 99, 235, 0.04);
        color: #1d4ed8;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tool-chip {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 12px 16px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tool-chip .tool-name {
        font-weight: 600;
        color: #1e293b;
    }
    .tool-chip .tool-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.85rem;
        color: #64748b;
    }
    .tool-chip .mini-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(37, 99, 235, 0.12);
        color: #1d4ed8;
    }
    .evaluator-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .evaluator-chip {
        border-radius: 16px;
        background: rgba(15, 23, 42, 0.04);
        padding: 10px 14px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        font-size: 0.88rem;
        color: #1e293b;
    }
    .evaluator-chip .name {
        font-weight: 600;
    }
    .evaluator-chip .score {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fff;
        border-radius: 999px;
        padding: 3px 10px;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.4);
    }
    .evaluator-chip .score .label {
        color: #64748b;
        font-size: 0.78rem;
    }
    .evaluator-summary-card {
        border: 1px solid #dbeafe;
        background: #eff6ff;
        border-radius: 20px;
        padding: 20px 24px;
    }
    .evaluator-summary-card h5 {
        font-size: 1rem;
        color: #1d4ed8;
        margin-bottom: 16px;
        font-weight: 700;
    }
    .evaluator-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
    }
    .evaluator-summary-grid .evaluator-chip {
        background: #fff;
    }
    .agreed-score-input {
        width: 120px;
        max-width: 100%;
        padding: 8px 12px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: #fff;
        font-size: 0.95rem;
        text-align: center;
        color: #0f172a;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .agreed-score-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    @media (max-width: 992px) {
        .summary-stat-card {
            flex-direction: row;
        }
    }
    @media (max-width: 768px) {
        .detail-meta-list {
            flex-direction: column;
            align-items: flex-start;
        }
        .tool-table thead {
            display: none;
        }
        .tool-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px;
            background: #f9fbff;
        }
        .tool-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            border: none;
            padding: 6px 0;
        }
        .tool-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
        }
        .competency-stat {
            align-items: flex-start;
        }
    }
CSS;

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-24">
            <div class="col-12">
                <div class="card washup-card washup-header-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-18">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-8 mb-12">
                                    <a href="<?= htmlspecialchars($backLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm rounded-pill d-inline-flex align-items-center gap-6">
                                        <ion-icon name="arrow-back-outline"></ion-icon>
                                        بازگشت
                                    </a>
                                    <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
                                        حالت فعلی: <?= htmlspecialchars($modeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                                <h2 class="mb-6 text-gray-900 fw-bold">
                                    <?= htmlspecialchars($evaluationSummary['title'] ?? 'ارزیابی', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <?php if (!empty($evaluationSummary['schedule_title'])): ?>
                                    <p class="mb-10 text-gray-600">
                                        <?= htmlspecialchars($evaluationSummary['schedule_title'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($visibilityContext['message'])): ?>
                                    <p class="mb-16 text-gray-500 small">
                                        <?= htmlspecialchars($visibilityContext['message'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                <?php endif; ?>
                                <ul class="detail-meta-list list-unstyled d-flex flex-wrap align-items-center mb-0">
                                    <li class="d-flex align-items-center gap-8">
                                        <ion-icon name="calendar-outline"></ion-icon>
                                        <span>تاریخ: <?= htmlspecialchars($evaluationSummary['date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </li>
                                    <?php if (!empty($evaluateeSummary['label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="person-circle-outline"></ion-icon>
                                            <span>ارزیابی‌شونده: <?= htmlspecialchars($evaluateeSummary['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($visibilityContext['role_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="shield-checkmark-outline"></ion-icon>
                                            <span>نقش شما: <?= htmlspecialchars($visibilityContext['role_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($evaluationSummary['general_model_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="layers-outline"></ion-icon>
                                            <span>مدل عمومی: <?= htmlspecialchars($evaluationSummary['general_model_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($evaluationSummary['specific_model_label'])): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="grid-outline"></ion-icon>
                                            <span>مدل اختصاصی: <?= htmlspecialchars($evaluationSummary['specific_model_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (!empty($lastUpdatedDisplay)): ?>
                                        <li class="d-flex align-items-center gap-8">
                                            <ion-icon name="time-outline"></ion-icon>
                                            <span>
                                                آخرین بروزرسانی: <?= htmlspecialchars($lastUpdatedDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php if (!empty($lastUpdatedAgo)): ?>
                                                    (<?= htmlspecialchars($lastUpdatedAgo, ENT_QUOTES, 'UTF-8'); ?>)
                                                <?php endif; ?>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="action-buttons d-flex flex-column flex-sm-row flex-lg-column align-items-stretch gap-8">
                                <a href="<?= htmlspecialchars($secondaryModeLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary d-inline-flex align-items-center gap-6">
                                    <ion-icon name="<?= htmlspecialchars($secondaryModeIcon, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                                    <?= htmlspecialchars($secondaryModeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php if ($canFinalize): ?>
                                    <a href="<?= htmlspecialchars($finalLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary d-inline-flex align-items-center gap-6">
                                        <ion-icon name="checkmark-done-outline"></ion-icon>
                                        ثبت / ویرایش توصیه نهایی
                                    </a>
                                <?php endif; ?>
                                <a href="<?= htmlspecialchars($listLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light border d-inline-flex align-items-center gap-6">
                                    <ion-icon name="list-outline"></ion-icon>
                                    بازگشت به لیست
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($visibilityContext['user_display'])): ?>
                            <div class="mt-16 text-gray-500 small">
                                مشاهده‌کننده: <?= washup_escape_html($visibilityContext['user_display'], 'کاربر سازمان'); ?>
                                <?php if (!empty($visibilityContext['can_view_all'])): ?>
                                    <span class="ms-8 badge bg-success-50 text-success-700 rounded-pill px-12 py-6">دسترسی کامل</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($scorerSummary) && is_array($scorerSummary)): ?>
                            <div class="d-flex flex-wrap align-items-center gap-8 mt-20">
                                <?php foreach ($scorerSummary as $scorer): ?>
                                    <span class="scorer-badge">
                                        <ion-icon name="person-outline"></ion-icon>
                                        <?= washup_escape_html($scorer, 'ارزیاب'); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage) || !empty($errorMessage) || !empty($warningMessage) || !empty($infoMessage)): ?>
                <div class="col-12">
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($successMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($errorMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($warningMessage)): ?>
                        <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="warning-outline"></ion-icon>
                            <span><?= washup_escape_html($warningMessage); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($infoMessage)): ?>
                        <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= washup_escape_html($infoMessage); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($pageMessages)): ?>
                <div class="col-12">
                    <?php foreach ($pageMessages as $message): ?>
                        <?php
                            $type = $message['type'] ?? 'info';
                            $text = $message['text'] ?? '';
                            $alertClass = 'alert-info';
                            if ($type === 'warning') {
                                $alertClass = 'alert-warning';
                            } elseif ($type === 'success') {
                                $alertClass = 'alert-success';
                            } elseif ($type === 'danger' || $type === 'error') {
                                $alertClass = 'alert-danger';
                            }
                        ?>
                        <div class="alert <?= washup_escape_html($alertClass, 'alert-info'); ?> rounded-16" role="alert">
                            <?= washup_escape_html($text); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($toolsWithoutScores)): ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="list-outline"></ion-icon>
                        <span>
                            برای برخی ابزارها هنوز امتیازی ثبت نشده است:
                            <?php
                                $toolNames = array_map(static function ($tool) {
                                    return $tool['name'] ?? '';
                                }, $toolsWithoutScores);
                                $toolNames = array_filter($toolNames);
                            ?>
                            <?= washup_escape_html(implode('، ', $toolNames)); ?>
                        </span>
                    </div>
                </div>
            <?php endif; ?>

            <?php
                $statCards = [
                    [
                        'label' => 'کل امتیازها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['total_scores'] ?? 0)),
                        'icon' => 'stats-chart-outline',
                    ],
                    [
                        'label' => 'میانگین کلی',
                        'value' => $detailStats['average_score'] !== null ? UtilityHelper::englishToPersian((string) $detailStats['average_score']) : '—',
                        'icon' => 'speedometer-outline',
                    ],
                    [
                        'label' => 'تعداد ابزارها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['tools_total'] ?? 0)),
                        'icon' => 'construct-outline',
                    ],
                    [
                        'label' => 'ابزارهای دارای امتیاز',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['tools_with_scores'] ?? 0)),
                        'icon' => 'checkmark-done-outline',
                    ],
                    [
                        'label' => 'شایستگی‌ها',
                        'value' => UtilityHelper::englishToPersian((string) ($detailStats['competencies_total'] ?? 0)),
                        'icon' => 'sparkles-outline',
                    ],
                ];
            ?>
            <?php foreach ($statCards as $card): ?>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="summary-stat-card shadow-sm">
                        <div>
                            <div class="label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                        <div class="icon">
                            <ion-icon name="<?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($detailMode === 'final'): ?>
                <div class="col-12">
                    <div class="card washup-card shadow-sm final-recommendation-card">
                        <div class="card-body p-24">
                            <?php $hasFinalMeta = is_array($finalRecommendation) && (!empty($finalRecommendation['updated_at_display']) || !empty($finalRecommendation['updated_by'])); ?>
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-16">
                                <div>
                                    <h4 class="mb-4 text-gray-900 fw-semibold d-flex align-items-center gap-8">
                                        <ion-icon name="ribbon-outline"></ion-icon>
                                        توصیه نهایی برای <?= washup_escape_html($evaluateeSummary['label'] ?? 'ارزیابی‌شونده'); ?>
                                    </h4>
                                    <?php if ($hasFinalMeta): ?>
                                        <div class="meta d-flex flex-wrap gap-12">
                                            <?php if (!empty($finalRecommendation['updated_at_display'])): ?>
                                                <span class="d-inline-flex align-items-center gap-6">
                                                    <ion-icon name="time-outline"></ion-icon>
                                                    آخرین بروزرسانی: <?= washup_escape_html($finalRecommendation['updated_at_display']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($finalRecommendation['updated_by'])): ?>
                                                <span class="d-inline-flex align-items-center gap-6">
                                                    <ion-icon name="person-circle-outline"></ion-icon>
                                                    ثبت‌کننده: <?= washup_escape_html($finalRecommendation['updated_by']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($canFinalize): ?>
                                    <a href="<?= htmlspecialchars($finalLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary rounded-pill d-inline-flex align-items-center gap-6">
                                        <ion-icon name="create-outline"></ion-icon>
                                        ویرایش توصیه
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="final-recommendation-content">
                                <?php if ($finalRecommendation): ?>
                                    <div class="recommendation-section">
                                        <div class="section-title">
                                            <ion-icon name="chatbubble-ellipses-outline"></ion-icon>
                                            خلاصه و توصیه نهایی
                                        </div>
                                        <div class="section-body">
                                            <?= nl2br(washup_escape_html($finalRecommendation['recommendation_text'] ?? '', '—')); ?>
                                        </div>
                                    </div>
                                    <div class="recommendation-section">
                                        <div class="section-title">
                                            <ion-icon name="trending-up-outline"></ion-icon>
                                            پیشنهادهای توسعه
                                        </div>
                                        <div class="section-body">
                                            <?= nl2br(washup_escape_html($finalRecommendation['development_text'] ?? '', '—')); ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="recommendation-empty">
                                        <ion-icon name="information-circle-outline"></ion-icon>
                                        <div>
                                            هنوز توصیه نهایی برای این ارزیابی‌شونده ثبت نشده است. از دکمه «ثبت / ویرایش توصیه نهایی» برای ثبت جمع‌بندی استفاده کنید.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="card washup-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
                            <div>
                                <h4 class="mb-2 text-gray-900 fw-semibold">خلاصه ابزارهای ارزیابی</h4>
                                <div class="text-gray-500 small">وضعیت هر ابزار و میزان مشارکت ارزیاب‌ها</div>
                            </div>
                        </div>

                        <?php if (!empty($toolHeaders)): ?>
                            <div class="table-responsive">
                                <table class="table tool-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>عنوان ابزار</th>
                                            <th>میانگین امتیاز</th>
                                            <th>تعداد امتیاز</th>
                                            <th>شایستگی‌های پوشش داده‌شده</th>
                                            <th>ارزیاب‌ها</th>
                                            <th>آخرین بروزرسانی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($toolHeaders as $tool): ?>
                                            <tr>
                                                <td data-label="عنوان ابزار">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($tool['name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($tool['question_type'])): ?>
                                                        <div class="text-xs text-gray-500 mt-4">نوع سوال: <?= htmlspecialchars($tool['question_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="میانگین امتیاز">
                                                    <span class="pill-badge info">
                                                        <?= htmlspecialchars($tool['average'] !== null ? UtilityHelper::englishToPersian((string) $tool['average']) : '—', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="تعداد امتیاز">
                                                    <span class="pill-badge success">
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($tool['scores_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="شایستگی‌های پوشش داده‌شده">
                                                    <span class="tool-badge">
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($tool['competency_total'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> شایستگی
                                                    </span>
                                                </td>
                                                <td data-label="ارزیاب‌ها">
                                                    <?php if (!empty($tool['scorers'])): ?>
                                                        <div class="d-flex flex-wrap gap-6">
                                                            <?php foreach ($tool['scorers'] as $scorer): ?>
                                                                <span class="scorer-badge">
                                                                    <ion-icon name="person-outline"></ion-icon>
                                                                    <?= htmlspecialchars($scorer, ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">اطلاعاتی موجود نیست</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="آخرین بروزرسانی">
                                                    <?= htmlspecialchars($tool['last_updated_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <ion-icon name="pulse-outline" class="fs-3 mb-8"></ion-icon>
                                <p class="mb-0">هیچ ابزار فعالی برای نمایش وجود ندارد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <h4 class="mb-16 text-gray-900 fw-semibold">شایستگی‌ها و جزئیات امتیازدهی</h4>
            </div>

            <?php $globalEvaluatorStats = []; ?>
            <?php if (!empty($competencySummaries)): ?>
                <div class="col-12">
                    <?php if ($canEditAgreedScores): ?>
                        <form action="<?= htmlspecialchars($agreedScoresAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="agreed-score-form">
                    <?php endif; ?>
                        <div class="card washup-card shadow-sm">
                            <div class="card-body p-24">
                                <?php if ($canEditAgreedScores): ?>
                                    <input type="hidden" name="evaluation_id" value="<?= htmlspecialchars((string) ($evaluationSummary['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="evaluatee_id" value="<?= htmlspecialchars((string) ($evaluateeSummary['id'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <div class="table-responsive">
                                <table class="table competency-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>شایستگی</th>
                                            <th>ابزارهای دارای امتیاز</th>
                                            <th>جزئیات ارزیاب‌ها</th>
                                            <th>جمع امتیاز مصداق‌ها</th>
                                            <th>میانگین امتیاز</th>
                                            <th>تعداد امتیاز</th>
                                            <th>امتیاز کل ارزیاب‌ها</th>
                                            <th>امتیاز توافقی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($competencySummaries as $competency): ?>
                                            <?php
                                                $examplesCount = (int) ($competency['examples_count'] ?? 0);
                                                $overallCount = (int) ($competency['overall_count'] ?? 0);
                                                $countBase = $examplesCount > 0 ? $examplesCount : $overallCount;
                                                $sumBase = $examplesCount > 0 ? ($competency['examples_sum'] ?? 0) : ($competency['overall_sum'] ?? 0);
                                                $avgBase = $examplesCount > 0 ? ($competency['examples_average'] ?? null) : ($competency['overall_average'] ?? null);
                                                $hasScores = $countBase > 0;
                                                $sumDisplay = $hasScores ? UtilityHelper::englishToPersian(number_format((float) $sumBase, 2)) : '۰.۰۰';
                                                $avgDisplay = $hasScores && $avgBase !== null ? UtilityHelper::englishToPersian(number_format((float) $avgBase, 2)) : '—';
                                                $countDisplay = UtilityHelper::englishToPersian((string) $countBase);
                                                $toolCells = $competency['tool_cells'] ?? [];
                                                $toolsWithScores = array_values(array_filter($toolCells, static function (array $cell): bool {
                                                    $count = (int) ($cell['count'] ?? 0);
                                                    $hasTotal = array_key_exists('total', $cell) && $cell['total'] !== null;
                                                    $hasAverage = array_key_exists('average', $cell) && $cell['average'] !== null;
                                                    return $count > 0 || $hasTotal || $hasAverage;
                                                }));
                                                if (empty($toolsWithScores) && !empty($toolCells)) {
                                                    $toolsWithScores = $toolCells;
                                                }

                                                $evaluatorStats = [];
                                                if (!empty($competency['examples']) && is_array($competency['examples'])) {
                                                    foreach ($competency['examples'] as $exampleEntry) {
                                                        $scores = $exampleEntry['scores'] ?? [];
                                                        if (!is_array($scores)) {
                                                            continue;
                                                        }
                                                        foreach ($scores as $scoreEntry) {
                                                            $label = trim((string) ($scoreEntry['scorer_label'] ?? ''));
                                                            if ($label === '') {
                                                                $label = 'ارزیاب نامشخص';
                                                            }
                                                            if (!isset($scoreEntry['score'])) {
                                                                continue;
                                                            }
                                                            $scoreValue = (float) $scoreEntry['score'];
                                                            if (!isset($evaluatorStats[$label])) {
                                                                $evaluatorStats[$label] = [
                                                                    'total' => 0.0,
                                                                    'count' => 0,
                                                                ];
                                                            }
                                                            $evaluatorStats[$label]['total'] += $scoreValue;
                                                            $evaluatorStats[$label]['count']++;
                                                        }
                                                    }
                                                }

                                                if (empty($evaluatorStats)) {
                                                    foreach ($toolCells as $cell) {
                                                        $scorerNames = $cell['scorers'] ?? [];
                                                        if (!is_array($scorerNames)) {
                                                            continue;
                                                        }
                                                        foreach ($scorerNames as $scorerName) {
                                                            $label = trim((string) $scorerName);
                                                            if ($label === '') {
                                                                continue;
                                                            }
                                                            if (!isset($evaluatorStats[$label])) {
                                                                $evaluatorStats[$label] = [
                                                                    'total' => null,
                                                                    'count' => 0,
                                                                ];
                                                            }
                                                        }
                                                    }
                                                }

                                                $evaluatorTotalSum = 0.0;
                                                $hasEvaluatorTotals = false;
                                                foreach ($evaluatorStats as $stat) {
                                                    if ($stat['total'] !== null) {
                                                        $hasEvaluatorTotals = true;
                                                        $evaluatorTotalSum += $stat['total'];
                                                    }
                                                }
                                                if ($hasEvaluatorTotals) {
                                                    $evaluatorTotalDisplay = UtilityHelper::englishToPersian(number_format($evaluatorTotalSum, 2));
                                                } else {
                                                    $evaluatorTotalDisplay = '—';
                                                }

                                                foreach ($evaluatorStats as $label => $stat) {
                                                    if (!isset($globalEvaluatorStats[$label])) {
                                                        $globalEvaluatorStats[$label] = [
                                                            'total' => 0.0,
                                                            'count' => 0,
                                                            'has_value' => false,
                                                        ];
                                                    }
                                                    if ($stat['total'] !== null) {
                                                        $globalEvaluatorStats[$label]['total'] += $stat['total'];
                                                        $globalEvaluatorStats[$label]['count'] += $stat['count'];
                                                        $globalEvaluatorStats[$label]['has_value'] = true;
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td class="competency-cell">
                                                    <span class="title">
                                                        <?= htmlspecialchars($competency['title'] ?? 'شایستگی', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <div class="meta">
                                                        <?php if (!empty($competency['dimension'])): ?>
                                                            <span class="badge">
                                                                <?= htmlspecialchars($competency['dimension'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($competency['code'])): ?>
                                                            <span class="badge">
                                                                کد: <?= htmlspecialchars($competency['code'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($toolsWithScores)): ?>
                                                        <div class="d-flex flex-column gap-12">
                                                            <?php foreach ($toolsWithScores as $toolCell): ?>
                                                                <div class="tool-chip">
                                                                    <div class="tool-name">
                                                                        <?= washup_escape_html($toolCell['tool_name'] ?? 'ابزار', 'ابزار'); ?>
                                                                    </div>
                                                                    <div class="tool-meta">
                                                                        <?php if (array_key_exists('total', $toolCell) && $toolCell['total'] !== null): ?>
                                                                            <span>جمع: <?= washup_escape_html(UtilityHelper::englishToPersian(number_format((float) $toolCell['total'], 2))); ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if (array_key_exists('average', $toolCell) && $toolCell['average'] !== null): ?>
                                                                            <span>میانگین: <?= washup_escape_html(UtilityHelper::englishToPersian(number_format((float) $toolCell['average'], 2))); ?></span>
                                                                        <?php endif; ?>
                                                                        <?php if (!empty($toolCell['count'])): ?>
                                                                            <span>تعداد امتیاز: <?= washup_escape_html(UtilityHelper::englishToPersian((string) $toolCell['count']), '۰'); ?></span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php if (!empty($toolCell['scorers'])): ?>
                                                                        <div class="d-flex flex-wrap gap-6">
                                                                            <?php foreach ($toolCell['scorers'] as $scorerName): ?>
                                                                                <span class="mini-badge">
                                                                                    <ion-icon name="person-outline"></ion-icon>
                                                                                    <?= washup_escape_html($scorerName, 'ارزیاب'); ?>
                                                                                </span>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">ابزاری برای این شایستگی امتیازدهی نشده است.</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($evaluatorStats)): ?>
                                                        <div class="evaluator-list">
                                                            <?php foreach ($evaluatorStats as $label => $stat): ?>
                                                                <?php
                                                                    $totalValue = $stat['total'];
                                                                    $countValue = $stat['count'];
                                                                    $averageValue = ($totalValue !== null && $countValue > 0)
                                                                        ? round($totalValue / max($countValue, 1), 2)
                                                                        : null;
                                                                    $totalDisplay = $totalValue !== null
                                                                        ? UtilityHelper::englishToPersian(number_format((float) $totalValue, 2))
                                                                        : '—';
                                                                    $averageDisplay = $averageValue !== null
                                                                        ? UtilityHelper::englishToPersian(number_format((float) $averageValue, 2))
                                                                        : '—';
                                                                    $countDisplayEvaluator = $countValue > 0
                                                                        ? UtilityHelper::englishToPersian((string) $countValue)
                                                                        : '۰';
                                                                ?>
                                                                <div class="evaluator-chip">
                                                                    <span class="name">
                                                                        <ion-icon name="person-circle-outline"></ion-icon>
                                                                        <?= washup_escape_html($label, 'ارزیاب'); ?>
                                                                    </span>
                                                                    <span class="score">
                                                                        <span class="label">جمع</span>
                                                                        <span><?= washup_escape_html($totalDisplay); ?></span>
                                                                    </span>
                                                                    <span class="score">
                                                                        <span class="label">میانگین</span>
                                                                        <span><?= washup_escape_html($averageDisplay); ?></span>
                                                                    </span>
                                                                    <span class="score">
                                                                        <span class="label">تعداد</span>
                                                                        <span><?= washup_escape_html($countDisplayEvaluator, '۰'); ?></span>
                                                                    </span>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">اطلاعاتی برای ارزیاب‌ها ثبت نشده است.</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="competency-stat">
                                                        <span class="stat-label">جمع امتیاز</span>
                                                        <span class="stat-value">
                                                            <?= htmlspecialchars($sumDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="competency-stat">
                                                        <span class="stat-label">میانگین امتیاز</span>
                                                        <span class="stat-value">
                                                            <?= htmlspecialchars($avgDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="competency-stat">
                                                        <span class="stat-label">تعداد امتیاز</span>
                                                        <span class="stat-value">
                                                            <?= htmlspecialchars($countDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="competency-stat">
                                                        <span class="stat-label">مجموع ارزیاب‌ها</span>
                                                        <span class="stat-value">
                                                            <?= htmlspecialchars($evaluatorTotalDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                        $agreedScoreName = 'agreed_scores[' . (int) ($competency['id'] ?? 0) . ']';
                                                        $agreedScoreValue = '';
                                                        $storedAgreedScore = $competency['agreed_score'] ?? null;
                                                        if ($storedAgreedScore !== null) {
                                                            $agreedScoreValue = number_format((float) $storedAgreedScore, 2, '.', '');
                                                        } elseif ($avgBase !== null) {
                                                            $agreedScoreValue = number_format((float) $avgBase, 2, '.', '');
                                                        }
                                                    ?>
                                                    <div class="competency-stat">
                                                        <span class="stat-label">امتیاز توافقی</span>
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            class="agreed-score-input"
                                                            name="<?= htmlspecialchars($agreedScoreName, ENT_QUOTES, 'UTF-8'); ?>"
                                                            value="<?= htmlspecialchars($agreedScoreValue, ENT_QUOTES, 'UTF-8'); ?>"
                                                            placeholder="—"
                                                            inputmode="decimal"
                                                            dir="ltr"
                                                            <?= $canEditAgreedScores ? '' : ' readonly'; ?>
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                                <?php if ($canEditAgreedScores): ?>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-24 gap-12">
                                        <div class="text-muted small">
                                            در صورت نیاز می‌توانید امتیاز توافقی هر شایستگی را به‌صورت دستی تنظیم کرده و سپس ذخیره کنید.
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-24">
                                            <ion-icon name="save-outline"></ion-icon>
                                            ذخیره امتیازهای توافقی
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php if ($canEditAgreedScores): ?>
                        </form>
                    <?php endif; ?>
                </div>
                <?php
                    $summaryEvaluators = array_filter($globalEvaluatorStats, static function (array $stat): bool {
                        return !empty($stat['has_value']) && ($stat['total'] ?? 0) !== 0;
                    });
                ?>
                <?php if (!empty($summaryEvaluators)): ?>
                    <div class="col-12">
                        <div class="evaluator-summary-card shadow-sm">
                            <h5>خلاصه امتیازات ارزیاب‌ها</h5>
                            <div class="evaluator-summary-grid">
                                <?php foreach ($summaryEvaluators as $label => $stat): ?>
                                    <?php
                                        $total = (float) ($stat['total'] ?? 0);
                                        $count = (int) ($stat['count'] ?? 0);
                                        $avg = $count > 0 ? round($total / max($count, 1), 2) : null;
                                        $totalDisplay = UtilityHelper::englishToPersian(number_format($total, 2));
                                        $avgDisplay = $avg !== null ? UtilityHelper::englishToPersian(number_format($avg, 2)) : '—';
                                        $countDisplay = UtilityHelper::englishToPersian((string) $count);
                                    ?>
                                    <div class="evaluator-chip">
                                        <span class="name">
                                            <ion-icon name="person-circle-outline"></ion-icon>
                                            <?= washup_escape_html($label, 'ارزیاب'); ?>
                                        </span>
                                        <span class="score">
                                            <span class="label">جمع</span>
                                            <span><?= washup_escape_html($totalDisplay); ?></span>
                                        </span>
                                        <span class="score">
                                            <span class="label">میانگین</span>
                                            <span><?= washup_escape_html($avgDisplay); ?></span>
                                        </span>
                                        <span class="score">
                                            <span class="label">تعداد</span>
                                            <span><?= washup_escape_html($countDisplay, '۰'); ?></span>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <ion-icon name="time-outline" class="fs-3 mb-8"></ion-icon>
                        <p class="mb-0">داده‌ای برای نمایش جزئیات Wash-Up یافت نشد. پس از ثبت امتیازها، این بخش تکمیل خواهد شد.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>

<script>
    (function () {
        function normalizePersianNumber(value) {
            if (typeof value !== 'string' || value === '') {
                return value;
            }

            var persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            var arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

            var normalized = '';
            for (var i = 0; i < value.length; i++) {
                var char = value.charAt(i);
                var indexPersian = persianDigits.indexOf(char);
                if (indexPersian !== -1) {
                    normalized += indexPersian.toString();
                    continue;
                }
                var indexArabic = arabicDigits.indexOf(char);
                if (indexArabic !== -1) {
                    normalized += indexArabic.toString();
                    continue;
                }
                normalized += char;
            }

            return normalized;
        }

        function handleAgreedScoreInput(event) {
            var target = event.target;
            if (!target || target.value === '') {
                return;
            }

            var normalized = normalizePersianNumber(target.value);
            if (normalized !== target.value) {
                var selectionStart = target.selectionStart;
                target.value = normalized;
                if (typeof selectionStart === 'number') {
                    target.setSelectionRange(selectionStart, selectionStart);
                }
            }
        }

        var agreedInputs = document.querySelectorAll('.agreed-score-input');
        if (agreedInputs && agreedInputs.length > 0) {
            agreedInputs.forEach(function (input) {
                input.addEventListener('input', handleAgreedScoreInput);
                input.addEventListener('change', function (event) {
                    var normalized = normalizePersianNumber(event.target.value);
                    if (normalized !== event.target.value) {
                        event.target.value = normalized;
                    }
                });
            });
        }
    })();
</script>
