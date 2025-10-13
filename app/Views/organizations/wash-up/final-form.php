<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ثبت توصیه نهایی Wash-Up';
$user = $user ?? (class_exists('AuthHelper') && AuthHelper::getUser() ? AuthHelper::getUser() : null);
$evaluationSummary = $evaluationSummary ?? [
    'id' => 0,
    'title' => 'ارزیابی',
    'schedule_title' => '',
    'general_model_label' => '',
    'specific_model_label' => '',
];
$evaluateeSummary = $evaluateeSummary ?? [
    'id' => 0,
    'label' => 'ارزیابی‌شونده',
];
$formData = $formData ?? [
    'recommendation_text' => '',
    'development_text' => '',
];
$formAction = $formAction ?? UtilityHelper::currentUrl();
$cancelLink = $cancelLink ?? UtilityHelper::baseUrl('organizations/wash-up');
$finalRecommendation = $finalRecommendation ?? null;

$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;

$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

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

$inline_styles .= <<<'CSS'
    body {
        background: #f1f5fb;
    }
    .recommendation-wrapper {
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.28);
        background: #ffffff;
    }
    .page-heading-card {
        border-radius: 24px;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(37, 99, 235, 0.04));
        border: 1px solid rgba(59, 130, 246, 0.18);
    }
    .meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.78rem;
        color: #1d4ed8;
        background: rgba(59, 130, 246, 0.16);
    }
    .recommendation-form textarea {
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.4);
        padding: 16px;
        font-size: 0.95rem;
        min-height: 160px;
        resize: vertical;
    }
    .recommendation-form label {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .form-helper-text {
        font-size: 0.8rem;
        color: #64748b;
    }
    .last-update-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: #475569;
        background: rgba(148, 163, 184, 0.2);
        border-radius: 999px;
        padding: 6px 14px;
    }
    .form-actions .btn {
        border-radius: 999px;
        padding-inline: 24px;
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
                <div class="card page-heading-card shadow-sm">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-18">
                            <div class="d-flex flex-column gap-10">
                                <div class="d-flex flex-wrap align-items-center gap-8">
                                    <a href="<?= htmlspecialchars($cancelLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-sm rounded-pill d-inline-flex align-items-center gap-6">
                                        <ion-icon name="arrow-back-outline"></ion-icon>
                                        بازگشت به گزارش
                                    </a>
                                    <span class="meta-pill">
                                        <ion-icon name="ribbon-outline"></ion-icon>
                                        ثبت توصیه نهایی
                                    </span>
                                </div>
                                <h2 class="text-gray-900 fw-bold mb-6">
                                    <?= washup_escape_html($evaluationSummary['title'] ?? 'ارزیابی'); ?>
                                </h2>
                                <div class="d-flex flex-wrap gap-12 text-sm text-gray-600">
                                    <span class="d-inline-flex align-items-center gap-6">
                                        <ion-icon name="person-circle-outline"></ion-icon>
                                        ارزیابی‌شونده: <?= washup_escape_html($evaluateeSummary['label'] ?? ''); ?>
                                    </span>
                                    <?php if (!empty($evaluationSummary['schedule_title'])): ?>
                                        <span class="d-inline-flex align-items-center gap-6">
                                            <ion-icon name="calendar-outline"></ion-icon>
                                            برنامه: <?= washup_escape_html($evaluationSummary['schedule_title']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($finalRecommendation['updated_at_display'])): ?>
                                        <span class="last-update-badge">
                                            <ion-icon name="time-outline"></ion-icon>
                                            آخرین بروزرسانی: <?= washup_escape_html($finalRecommendation['updated_at_display']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($successMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <span><?= washup_escape_html($successMessage); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="alert-circle-outline"></ion-icon>
                        <span><?= washup_escape_html($errorMessage); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($warningMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="warning-outline"></ion-icon>
                        <span><?= washup_escape_html($warningMessage); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($infoMessage)): ?>
                <div class="col-12">
                    <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        <span><?= washup_escape_html($infoMessage); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="card recommendation-wrapper shadow-sm">
                    <div class="card-body p-24">
                        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="recommendation-form d-flex flex-column gap-24">
                            <input type="hidden" name="evaluation_id" value="<?= htmlspecialchars((string) ($evaluationSummary['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="evaluatee_id" value="<?= htmlspecialchars((string) ($evaluateeSummary['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                            <div>
                                <label for="recommendation_text" class="form-label">جمع‌بندی و توصیه نهایی</label>
                                <textarea id="recommendation_text" name="recommendation_text" class="form-control" placeholder="توصیه نهایی برای این ارزیابی‌شونده را با جزئیات بیان کنید."><?= washup_escape_html($formData['recommendation_text'] ?? '', ''); ?></textarea>
                                <div class="form-helper-text mt-8">
                                    می‌توانید جمع‌بندی نهایی، تصمیم‌ها و پیشنهادهای کلیدی را در این بخش وارد کنید.
                                </div>
                            </div>

                            <div>
                                <label for="development_text" class="form-label">پیشنهادهای توسعه و گام‌های بعدی (اختیاری)</label>
                                <textarea id="development_text" name="development_text" class="form-control" placeholder="پیشنهادهای توسعه فردی یا سازمانی، برنامه‌های آموزشی یا اقدامات تکمیلی."><?= washup_escape_html($formData['development_text'] ?? '', ''); ?></textarea>
                                <div class="form-helper-text mt-8">
                                    این بخش می‌تواند شامل پیشنهادهای توسعه‌ای، دوره‌های آموزشی یا مسیرهای رشد برای ارزیابی‌شونده باشد.
                                </div>
                            </div>

                            <div class="form-actions d-flex flex-wrap gap-12">
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-6">
                                    <ion-icon name="save-outline"></ion-icon>
                                    ذخیره توصیه نهایی
                                </button>
                                <a href="<?= htmlspecialchars($cancelLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light border d-inline-flex align-items-center gap-6">
                                    <ion-icon name="arrow-back-outline"></ion-icon>
                                    بازگشت به گزارش Wash-Up
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
