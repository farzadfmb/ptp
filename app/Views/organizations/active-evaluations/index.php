<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ارزیابی‌های فعال';
$user = $user ?? (class_exists('AuthHelper') && AuthHelper::getUser() ? AuthHelper::getUser() : null);
$activeEvaluations = $activeEvaluations ?? [];
$timelineEntries = $timelineEntries ?? [];
$summaryMetrics = $summaryMetrics ?? [
    'total' => 0,
    'open' => 0,
    'upcoming' => 0,
    'evaluatees' => 0,
    'evaluators' => 0,
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

$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$variantClassMap = [
    'success' => 'badge-soft-success',
    'info' => 'badge-soft-info',
    'primary' => 'badge-soft-primary',
    'danger' => 'badge-soft-danger',
    'secondary' => 'badge-soft-secondary',
];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .active-evaluations-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .active-evaluations-hero {
        position: relative;
        overflow: hidden;
    }
    .active-evaluations-hero::before {
        content: '';
        position: absolute;
        inset-inline-start: -140px;
        inset-block-start: -140px;
        width: 260px;
        height: 260px;
        background: radial-gradient(circle at center, rgba(99, 102, 241, 0.18), transparent 70%);
        z-index: 0;
    }
    .active-evaluations-hero > * {
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
        background: rgba(79, 70, 229, 0.12);
        color: #4f46e5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .active-timeline {
        position: relative;
        padding-inline-end: 28px;
        border-inline-end: 2px dashed rgba(148, 163, 184, 0.35);
    }
    .active-timeline::before {
        content: '';
        position: absolute;
        inset-inline-end: -1px;
        inset-block-start: 0;
        width: 2px;
        height: 100%;
        background: linear-gradient(180deg, rgba(99, 102, 241, 0.15), rgba(99, 102, 241, 0));
    }
    .active-timeline-item {
        position: relative;
        padding-block: 14px;
        padding-inline-end: 12px;
    }
    .active-timeline-item::before {
        content: '';
        position: absolute;
        inset-inline-end: -7px;
        inset-block-start: 20px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #6366f1;
        border: 4px solid #ffffff;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
    }
    .active-timeline-item.upcoming::before {
        background: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25);
    }
    .active-timeline-item.past::before {
        background: #94a3b8;
        box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.2);
    }
    .active-timeline-item:last-child {
        padding-block-end: 0;
    }
    .badge-soft-success {
        background: rgba(34, 197, 94, 0.14);
        color: #15803d;
    }
    .badge-soft-info {
        background: rgba(59, 130, 246, 0.14);
        color: #1d4ed8;
    }
    .badge-soft-primary {
        background: rgba(99, 102, 241, 0.14);
        color: #4338ca;
    }
    .badge-soft-danger {
        background: rgba(248, 113, 113, 0.18);
        color: #b91c1c;
    }
    .badge-soft-secondary {
        background: rgba(148, 163, 184, 0.22);
        color: #475569;
    }
    .pill-badge {
        background: rgba(99, 102, 241, 0.12);
        color: #3730a3;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        line-height: 1.4;
    }
    .pill-badge-info {
        background: rgba(14, 165, 233, 0.14);
        color: #0e7490;
    }
    .pill-badge-muted {
        background: rgba(148, 163, 184, 0.18);
        color: #475569;
    }
    .pill-badge-tool {
        background: rgba(250, 204, 21, 0.16);
        color: #92400e;
    }
    .active-evaluations-table thead th {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }
    .active-evaluations-table tbody td {
        vertical-align: top;
        border-top: 1px solid #f1f5f9;
    }
    .active-evaluations-table tbody tr:hover {
        background: #f9fbff;
    }
    .empty-state {
        border-radius: 18px;
        border: 1px dashed rgba(148, 163, 184, 0.4);
        padding: 24px;
        background: #fbfdff;
        text-align: center;
        color: #64748b;
    }
    @media (max-width: 768px) {
        .summary-card {
            flex-direction: row;
            align-items: flex-start;
        }
        .summary-card .summary-value {
            font-size: 20px;
        }
        .active-timeline {
            padding-inline-end: 18px;
        }
        .active-evaluations-table thead {
            display: none;
        }
        .active-evaluations-table tbody tr {
            display: block;
            padding: 16px 0;
        }
        .active-evaluations-table tbody td {
            display: block;
            padding: 8px 0;
            border: none;
        }
        .active-evaluations-table tbody td:before {
            content: attr(data-label);
            display: block;
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 4px;
        }
    }
CSS;

$summaryCards = [
    [
        'label' => 'کل ارزیابی‌ها',
        'value' => $summaryMetrics['total'] ?? 0,
        'icon' => 'calendar-outline',
    ],
    [
        'label' => 'ارزیابی‌های فعال',
        'value' => $summaryMetrics['open'] ?? 0,
        'icon' => 'flash-outline',
    ],
    [
        'label' => 'جلسات پیش‌رو',
        'value' => $summaryMetrics['upcoming'] ?? 0,
        'icon' => 'hourglass-outline',
    ],
    [
        'label' => 'تعداد ارزیابی‌شوندگان',
        'value' => $summaryMetrics['evaluatees'] ?? 0,
        'icon' => 'people-outline',
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
                <div class="card active-evaluations-card active-evaluations-hero shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16">
                            <div>
                                <h2 class="mb-6 text-gray-900 fw-bold">ارزیابی‌های فعال</h2>
                                <p class="mb-0 text-gray-600">
                                    <?= htmlspecialchars($visibilityContext['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <div class="d-flex flex-wrap gap-8 align-items-center">
                                <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">
                                    نقش: <?= htmlspecialchars($visibilityContext['role_label'] ?? 'کاربر سازمان', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php if (!empty($visibilityContext['user_display'])): ?>
                                    <span class="badge bg-secondary-50 text-secondary-600 rounded-pill px-16 py-8">
                                        <?= htmlspecialchars($visibilityContext['user_display'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($visibilityContext['can_view_all'])): ?>
                                    <span class="badge bg-success-50 text-success-700 rounded-pill px-16 py-8">
                                        دسترسی کامل
                                    </span>
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

            <?php foreach ($summaryCards as $card): ?>
                <div class="col-12 col-sm-6 col-lg-3">
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
                <div class="card active-evaluations-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
                            <h4 class="mb-0 text-gray-900 fw-semibold">خط زمانی ارزیابی‌ها</h4>
                            <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-calendar'); ?>" class="btn btn-outline-main rounded-pill px-20 d-inline-flex align-items-center gap-8">
                                <ion-icon name="calendar-outline"></ion-icon>
                                تقویم ارزشیابی
                            </a>
                        </div>
                        <?php if (!empty($timelineEntries)): ?>
                            <div class="active-timeline">
                                <?php foreach ($timelineEntries as $entry): ?>
                                    <?php $timelineClass = !empty($entry['is_upcoming']) ? 'upcoming' : 'past'; ?>
                                    <?php $statusClass = $variantClassMap[$entry['status_variant'] ?? 'secondary'] ?? 'badge-soft-secondary'; ?>
                                    <div class="active-timeline-item <?= htmlspecialchars($timelineClass, ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12">
                                            <div>
                                                <div class="text-sm text-gray-500 mb-4">
                                                    <?= htmlspecialchars($entry['date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <h6 class="mb-6 text-gray-900 fw-semibold">
                                                    <?= htmlspecialchars($entry['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                                                </h6>
                                                <div class="d-flex flex-wrap gap-10 text-xs text-gray-500">
                                                    <span class="d-inline-flex align-items-center gap-4">
                                                        <ion-icon name="people-outline"></ion-icon>
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($entry['evaluatees_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> نفر
                                                    </span>
                                                    <span class="d-inline-flex align-items-center gap-4">
                                                        <ion-icon name="person-outline"></ion-icon>
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($entry['evaluators_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> ارزیاب
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($entry['status_label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <ion-icon name="time-outline" class="fs-3 mb-8"></ion-icon>
                                <p class="mb-0">هنوز ارزیابی فعالی برای نمایش در خط زمانی وجود ندارد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card active-evaluations-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-20 gap-12">
                            <h4 class="mb-0 text-gray-900 fw-semibold">فهرست ارزیابی‌های فعال</h4>
                            <div class="d-flex flex-wrap gap-8">
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix'); ?>" class="btn btn-outline-secondary rounded-pill px-18 d-inline-flex align-items-center gap-8">
                                    <ion-icon name="grid-outline"></ion-icon>
                                    ماتریس ارزیابی
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($activeEvaluations)): ?>
                            <div class="table-responsive">
                                <table class="table active-evaluations-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">عنوان ارزیابی</th>
                                            <th scope="col">تاریخ اجرا</th>
                                            <th scope="col">وضعیت</th>
                                            <th scope="col">ارزیابان</th>
                                            <th scope="col">ارزیابی‌شونده‌ها</th>
                                            <th scope="col">ابزارها</th>
                                            <th scope="col">اقدامات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeEvaluations as $evaluation): ?>
                                            <?php
                                                $evaluatees = $evaluation['evaluatees'] ?? [];
                                                $evaluators = $evaluation['evaluators'] ?? [];
                                                $tools = $evaluation['tools'] ?? [];
                                                $collapseId = 'evaluatees-' . ($evaluation['id'] ?? uniqid());
                                                $extraEvaluators = max(0, ($evaluation['evaluators_count'] ?? count($evaluators)) - 3);
                                                $extraTools = max(0, ($evaluation['tools_count'] ?? count($tools)) - 3);
                                                $statusClass = $variantClassMap[$evaluation['status_variant'] ?? 'secondary'] ?? 'badge-soft-secondary';
                                            ?>
                                            <tr>
                                                <td data-label="عنوان ارزیابی">
                                                    <div class="fw-semibold text-gray-900 mb-4">
                                                        <?= htmlspecialchars($evaluation['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <?php if (!empty($evaluation['schedule_title'])): ?>
                                                        <div class="text-xs text-gray-500">برنامه مرتبط: <?= htmlspecialchars($evaluation['schedule_title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="تاریخ اجرا">
                                                    <div class="d-flex flex-column gap-8">
                                                        <span class="fw-semibold text-gray-900">
                                                            <?= htmlspecialchars($evaluation['date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                        <?php if (!empty($evaluation['calendar_link'])): ?>
                                                            <a href="<?= htmlspecialchars($evaluation['calendar_link'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-main rounded-pill px-14 d-inline-flex align-items-center gap-6">
                                                                <ion-icon name="calendar-outline"></ion-icon>
                                                                تقویم
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="وضعیت">
                                                    <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?= htmlspecialchars($evaluation['status_label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                </td>
                                                <td data-label="ارزیابان">
                                                    <div class="d-flex flex-wrap gap-6">
                                                        <?php if (!empty($evaluators)): ?>
                                                            <?php foreach (array_slice($evaluators, 0, 3) as $evaluator): ?>
                                                                <span class="pill-badge">
                                                                    <?= htmlspecialchars($evaluator['label'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                            <?php if ($extraEvaluators > 0): ?>
                                                                <span class="pill-badge pill-badge-muted">
                                                                    +<?= htmlspecialchars(UtilityHelper::englishToPersian((string) $extraEvaluators), ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-xs text-muted">ثبت نشده</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="ارزیابی‌شونده‌ها">
                                                    <button class="btn btn-sm btn-outline-main rounded-pill px-16 d-inline-flex align-items-center gap-6" type="button" data-bs-toggle="collapse" data-bs-target="#<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>" aria-expanded="false">
                                                        <ion-icon name="people-outline"></ion-icon>
                                                        <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($evaluation['evaluatees_count'] ?? count($evaluatees))), ENT_QUOTES, 'UTF-8'); ?> نفر
                                                    </button>
                                                    <div class="collapse mt-3" id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <div class="d-flex flex-wrap gap-6">
                                                            <?php if (!empty($evaluatees)): ?>
                                                                <?php foreach ($evaluatees as $evaluatee): ?>
                                                                    <?php
                                                                        $evaluateeLabel = $evaluatee['label'] ?? '—';
                                                                        $evaluateeLink = $evaluatee['link'] ?? ($evaluation['score_link'] ?? '#');
                                                                    ?>
                                                                    <a href="<?= htmlspecialchars($evaluateeLink, ENT_QUOTES, 'UTF-8'); ?>" class="pill-badge pill-badge-info text-decoration-none">
                                                                        <?= htmlspecialchars($evaluateeLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <span class="text-xs text-muted">فهرست خالی است.</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="ابزارها">
                                                    <div class="d-flex flex-wrap gap-6">
                                                        <?php if (!empty($tools)): ?>
                                                            <?php foreach (array_slice($tools, 0, 3) as $tool): ?>
                                                                <span class="pill-badge pill-badge-tool">
                                                                    <?= htmlspecialchars($tool['tool_name'] ?? 'ابزار', ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                            <?php if ($extraTools > 0): ?>
                                                                <span class="pill-badge pill-badge-muted">
                                                                    +<?= htmlspecialchars(UtilityHelper::englishToPersian((string) $extraTools), ENT_QUOTES, 'UTF-8'); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-xs text-muted">ابزاری اختصاص داده نشده است.</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="اقدامات">
                                                    <div class="d-flex flex-wrap gap-8">
                                                        <a href="<?= htmlspecialchars($evaluation['score_link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-main rounded-pill px-16 d-inline-flex align-items-center gap-6">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            ارزیابی
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
                                <ion-icon name="analytics-outline" class="fs-3 mb-8"></ion-icon>
                                <p class="mb-3">هیچ ارزیابی فعالی برای شما ثبت نشده است.</p>
                                <a href="<?= UtilityHelper::baseUrl('organizations/evaluation-calendar/create'); ?>" class="btn btn-main rounded-pill px-20 d-inline-flex align-items-center gap-8">
                                    <ion-icon name="add-circle-outline"></ion-icon>
                                    ایجاد ارزیابی جدید
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
