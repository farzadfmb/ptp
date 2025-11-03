<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'گزارش‌های Wash-Up';
$user = $user ?? (class_exists('AuthHelper') && AuthHelper::getUser() ? AuthHelper::getUser() : null);
$washUpRows = $washUpRows ?? [];
$summaryMetrics = $summaryMetrics ?? [
    'rows' => 0,
    'evaluations' => 0,
    'evaluatees' => 0,
    'completed' => 0,
    'in_progress' => 0,
    'pending' => 0,
    'with_scores' => 0,
    'completion_rate' => 0,
];
$visibilityContext = $visibilityContext ?? [
    'mode' => 'limited',
    'message' => '',
    'role_label' => 'کاربر سازمان',
    'user_display' => 'کاربر سازمان',
    'can_view_all' => false,
    'is_evaluator' => false,
    'is_evaluatee' => false,
];
$pageMessages = $pageMessages ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;
$canFinalize = $canFinalize ?? false;

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$statusClassMap = [
    'success' => 'badge-soft-success',
    'info' => 'badge-soft-info',
    'warning' => 'badge-soft-warning',
    'danger' => 'badge-soft-danger',
    'secondary' => 'badge-soft-secondary',
];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .washup-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .washup-hero {
        position: relative;
        overflow: hidden;
    }
    .washup-hero::before {
        content: '';
        position: absolute;
        inset-inline-start: -140px;
        inset-block-start: -140px;
        width: 280px;
        height: 280px;
        background: radial-gradient(circle at center, rgba(96, 165, 250, 0.2), transparent 70%);
        z-index: 0;
    }
    .washup-hero > * {
        position: relative;
        z-index: 1;
    }
    .summary-card {
        border-radius: 20px;
        background: #f8fafc;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        height: 100%;
        border: 1px solid rgba(226, 232, 240, 0.6);
    }
    .summary-card .summary-label {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 6px;
    }
    .summary-card .summary-value {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }
    .summary-card .summary-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: rgba(96, 165, 250, 0.16);
        color: #2563eb;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .washup-table thead th {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }
    .washup-table tbody td {
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
    }
    .washup-table tbody tr:hover {
        background: #f9fbff;
    }
    .badge-soft-warning {
        background: rgba(250, 204, 21, 0.18);
        color: #92400e;
    }
    .badge-soft-info {
        background: rgba(59, 130, 246, 0.18);
        color: #1d4ed8;
    }
    .badge-soft-success {
        background: rgba(34, 197, 94, 0.18);
        color: #15803d;
    }
    .badge-soft-danger {
        background: rgba(248, 113, 113, 0.18);
        color: #b91c1c;
    }
    .badge-soft-secondary {
        background: rgba(148, 163, 184, 0.24);
        color: #475569;
    }
    .progress-wrapper {
        width: 160px;
        max-width: 100%;
    }
    .progress {
        height: 8px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }
    .progress-bar {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
    }
    .badge-pill {
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .empty-state {
        border-radius: 18px;
        border: 1px dashed rgba(148, 163, 184, 0.4);
        padding: 32px;
        background: #fbfdff;
        text-align: center;
        color: #64748b;
    }
    .action-buttons .btn {
        border-radius: 999px;
        font-size: 12px;
        padding-inline: 16px;
    }
    .washup-meta {
        gap: 12px;
    }
    @media (max-width: 992px) {
        .summary-card {
            flex-direction: row;
            align-items: flex-start;
        }
        .summary-card .summary-value {
            font-size: 20px;
        }
    }
    @media (max-width: 768px) {
        .washup-table thead {
            display: none;
        }
        .washup-table tbody tr {
            display: block;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .washup-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border: none;
            padding: 6px 0;
        }
        .washup-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
        }
        .progress-wrapper {
            width: 100%;
        }
        .action-buttons {
            flex-direction: column;
            align-items: stretch;
        }
    }
CSS;

$summaryCards = [
    [
        'label' => 'کل ردیف‌ها',
        'value' => $summaryMetrics['rows'] ?? 0,
        'icon' => 'list-outline',
    ],
    [
        'label' => 'ارزیابی‌ها',
        'value' => $summaryMetrics['evaluations'] ?? 0,
        'icon' => 'layers-outline',
    ],
    [
        'label' => 'ارزیابی‌شوندگان',
        'value' => $summaryMetrics['evaluatees'] ?? 0,
        'icon' => 'people-outline',
    ],
    [
        'label' => 'تکمیل شده',
        'value' => $summaryMetrics['completed'] ?? 0,
        'icon' => 'checkmark-circle-outline',
    ],
    [
        'label' => 'در حال تکمیل',
        'value' => $summaryMetrics['in_progress'] ?? 0,
        'icon' => 'time-outline',
    ],
    [
        'label' => 'در انتظار امتیاز',
        'value' => $summaryMetrics['pending'] ?? 0,
        'icon' => 'alert-circle-outline',
    ],
];

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../layouts/organization-navbar.php';
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card washup-card washup-hero shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-18">
                            <div>
                                <h2 class="mb-10 text-gray-900 fw-bold">گزارش‌های Wash-Up</h2>
                                <p class="mb-0 text-gray-600">
                                    <?= htmlspecialchars($visibilityContext['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-8 align-items-center washup-meta">
                                <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
                                    نقش: <?= htmlspecialchars($visibilityContext['role_label'] ?? 'کاربر سازمان', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php if (!empty($visibilityContext['user_display'])): ?>
                                    <span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-8">
                                        <?= htmlspecialchars($visibilityContext['user_display'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($visibilityContext['can_view_all'])): ?>
                                    <span class="badge bg-success-50 text-success-700 rounded-pill px-16 py-8">دسترسی کامل</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage) || !empty($errorMessage) || !empty($warningMessage) || !empty($infoMessage)): ?>
                <div class="col-12">
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($warningMessage)): ?>
                        <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="warning-outline"></ion-icon>
                            <span><?= htmlspecialchars($warningMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($infoMessage)): ?>
                        <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                            <ion-icon name="information-circle-outline"></ion-icon>
                            <span><?= htmlspecialchars($infoMessage, ENT_QUOTES, 'UTF-8'); ?></span>
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
                        <div class="alert <?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8'); ?> rounded-16" role="alert">
                            <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($summaryCards as $card): ?>
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="summary-card shadow-sm">
                        <div>
                            <div class="summary-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="summary-value">
                                <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($card['value'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        <div class="summary-icon">
                            <ion-icon name="<?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="col-12">
                <div class="card washup-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
                            <div>
                                <h4 class="mb-2 text-gray-900 fw-semibold">لیست ارزیابی‌شوندگان برای Wash-Up</h4>
                                <div class="text-gray-500 small">
                                    نرخ تکمیل: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($summaryMetrics['completion_rate'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>٪
                                    | ردیف‌های دارای امتیاز: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($summaryMetrics['with_scores'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($washUpRows)): ?>
                            <div class="table-responsive">
                                <table class="table washup-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ارزیابی</th>
                                            <th>ارزیابی‌شونده</th>
                                            <th>ارزیاب‌ها</th>
                                            <th class="text-center">پیشرفت ابزارها</th>
                                            <th class="text-center">وضعیت</th>
                                            <th>آخرین بروزرسانی</th>
                                            <th class="text-center">اقدامات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($washUpRows as $row): ?>
                                            <?php
                                                $toolsCount = (int) ($row['tools_count'] ?? 0);
                                                $toolsScored = (int) ($row['tools_scored'] ?? 0);
                                                $progress = $toolsCount > 0 ? min(100, max(0, (int) round(($toolsScored / max($toolsCount, 1)) * 100))) : ($row['scores_recorded'] ?? 0 > 0 ? 100 : 0);
                                                $statusVariant = $statusClassMap[$row['status_variant'] ?? 'secondary'] ?? 'badge-soft-secondary';
                                                $statusLabel = $row['status_label'] ?? 'نامشخص';
                                                $evaluateeLabel = $row['evaluatee_label'] ?? '';
                                                $evaluationTitle = $row['evaluation_title'] ?? '';
                                                $scheduleTitle = $row['schedule_title'] ?? '';
                                                $lastUpdatedDisplay = $row['last_updated_display'] ?? '—';
                                                $lastUpdatedAgo = $row['last_updated_ago'] ?? null;
                                                $washupLink = $row['washup_link'] ?? '#';
                                                $finalLink = $row['final_link'] ?? '#';
                                                $legacyWashupLink = $washupLink;
                                                if ($legacyWashupLink !== '#') {
                                                    $hasQuery = strpos($legacyWashupLink, '?') !== false;
                                                    $separator = $hasQuery ? '&' : '?';
                                                    if (strpos($legacyWashupLink, 'layout=') === false) {
                                                        $legacyWashupLink .= $separator . 'layout=legacy';
                                                    }
                                                }
                                            ?>
                                            <tr>
                                                <td data-label="ارزیابی">
                                                    <div class="fw-semibold text-gray-900 mb-4">
                                                        <?= htmlspecialchars($evaluationTitle !== '' ? $evaluationTitle : 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="d-flex flex-column gap-4 text-xs text-gray-500">
                                                        <span><ion-icon name="calendar-outline"></ion-icon> <?= htmlspecialchars($row['evaluation_date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php if ($scheduleTitle !== ''): ?>
                                                            <span><ion-icon name="albums-outline"></ion-icon> <?= htmlspecialchars($scheduleTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="ارزیابی‌شونده">
                                                    <div class="fw-semibold text-gray-900">
                                                        <?= htmlspecialchars($evaluateeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-4">
                                                        شناسه: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($row['evaluatee_id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </td>
                                                <td data-label="ارزیاب‌ها">
                                                    <?php if (!empty($row['evaluator_labels'])): ?>
                                                        <div class="d-flex flex-column gap-6">
                                                            <?php foreach ($row['evaluator_labels'] as $label): ?>
                                                                <span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-6">
                                                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="پیشرفت ابزارها" class="text-center">
                                                    <div class="d-inline-flex flex-column align-items-center gap-6">
                                                        <div class="progress-wrapper">
                                                            <div class="progress">
                                                                <div class="progress-bar" role="progressbar" style="width: <?= (int) $progress; ?>%" aria-valuenow="<?= (int) $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $toolsScored), ENT_QUOTES, 'UTF-8'); ?> / <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $toolsCount), ENT_QUOTES, 'UTF-8'); ?> ابزار
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            امتیازها: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($row['scores_recorded'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="وضعیت" class="text-center">
                                                    <span class="badge <?= htmlspecialchars($statusVariant, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <div class="text-xs text-gray-500 mt-6">
                                                        ارزیاب‌ها: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($row['scorers_involved'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                </td>
                                                <td data-label="آخرین بروزرسانی">
                                                    <div class="fw-semibold text-gray-900">
                                                        <?= htmlspecialchars($lastUpdatedDisplay, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <?php if ($lastUpdatedAgo): ?>
                                                        <div class="text-xs text-gray-500 mt-2">
                                                            <?= htmlspecialchars($lastUpdatedAgo, ENT_QUOTES, 'UTF-8'); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="اقدامات">
                                                    <div class="d-flex action-buttons flex-column flex-md-row gap-8 justify-content-center">
                                                        <a href="<?= htmlspecialchars($legacyWashupLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-main btn-sm d-inline-flex align-items-center gap-6">
                                                            <ion-icon name="document-text-outline"></ion-icon>
                                                            Wash-Up
                                                        </a>
                                                        <a href="<?= htmlspecialchars($finalLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-main btn-sm d-inline-flex align-items-center gap-6 <?= $canFinalize ? '' : 'disabled'; ?>">
                                                            <ion-icon name="checkmark-done-outline"></ion-icon>
                                                            ثبت نهایی
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <ion-icon name="time-outline" class="fs-3 mb-8"></ion-icon>
                                <p class="mb-0">در حال حاضر داده‌ای برای Wash-Up وجود ندارد. پس از ثبت امتیاز ابزارها، این بخش تکمیل خواهد شد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
