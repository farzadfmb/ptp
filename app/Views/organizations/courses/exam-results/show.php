<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات نتیجه آزمون دوره';
$user = $user ?? [];
$attemptDetails = $attemptDetails ?? [];
$answers = $answers ?? [];
$baseListUrl = $baseListUrl ?? UtilityHelper::baseUrl('organizations/courses/exam-results');
$resetActionUrl = $resetActionUrl ?? UtilityHelper::baseUrl('organizations/courses/exam-results/reset');
$csrfToken = $csrfToken ?? AuthHelper::generateCsrfToken();
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
include __DIR__ . '/../../../layouts/organization-navbar.php';

$formatDateTime = static function (?string $value): string {
    if ($value === null || trim($value) === '') {
        return '—';
    }

    try {
        $date = new DateTime($value, new DateTimeZone('Asia/Tehran'));
        return UtilityHelper::englishToPersian($date->format('Y/m/d H:i'));
    } catch (Exception $exception) {
        return UtilityHelper::englishToPersian((string) $value);
    }
};

$fullName = trim((string) (($attemptDetails['user_first_name'] ?? '') . ' ' . ($attemptDetails['user_last_name'] ?? '')));
if ($fullName === '') {
    $fullName = 'کاربر بدون نام';
}

$courseTitle = trim((string) ($attemptDetails['course_title'] ?? 'بدون عنوان'));
$courseCode = trim((string) ($attemptDetails['course_code'] ?? ''));
$toolName = trim((string) ($attemptDetails['tool_name'] ?? ''));
$toolCode = trim((string) ($attemptDetails['tool_code'] ?? ''));
$questionType = trim((string) ($attemptDetails['question_type'] ?? ($attemptDetails['is_disc'] ?? false ? 'DISC' : 'استاندارد')));
$answerSummary = $answerSummary ?? [
    'total_questions' => count($answers),
    'answered_questions' => $attemptDetails['answered_questions'] ?? count($answers),
    'evaluated_questions' => 0,
    'correct_count' => 0,
    'incorrect_count' => 0,
    'unanswered_evaluated' => 0,
    'overall_unanswered' => max(0, (count($answers) - ($attemptDetails['answered_questions'] ?? 0))),
    'score_total' => 0,
    'score_possible' => 0,
    'score_percentage' => null,
    'score_out_of_100' => null,
    'has_evaluation' => false,
];

$formatNumber = static function ($value, int $decimals = 0): string {
    if ($value === null) {
        return '—';
    }

    $numeric = (float) $value;
    $formatted = $decimals > 0
        ? number_format($numeric, $decimals, '.', '')
        : (string) (is_int($value) ? $value : (int) round($numeric));

    if ($decimals > 0) {
        $formatted = rtrim(rtrim($formatted, '0'), '.');
    }

    if ($formatted === '') {
        $formatted = '0';
    }

    return UtilityHelper::englishToPersian($formatted);
};

$totalQuestions = (int) ($answerSummary['total_questions'] ?? ($attemptDetails['total_questions'] ?? count($answers)));
$answeredQuestions = (int) ($answerSummary['answered_questions'] ?? ($attemptDetails['answered_questions'] ?? count($answers)));
$overallUnanswered = (int) ($answerSummary['overall_unanswered'] ?? max(0, $totalQuestions - $answeredQuestions));
$evaluatedQuestions = (int) ($answerSummary['evaluated_questions'] ?? 0);
$correctCount = (int) ($answerSummary['correct_count'] ?? 0);
$incorrectCount = (int) ($answerSummary['incorrect_count'] ?? 0);
$unansweredEvaluated = (int) ($answerSummary['unanswered_evaluated'] ?? 0);
$scoreOutOfHundred = $answerSummary['score_out_of_100'] ?? null;
$scoreTotalRaw = $answerSummary['score_total'] ?? null;
$scorePossibleRaw = $answerSummary['score_possible'] ?? null;
$hasEvaluation = !empty($answerSummary['has_evaluation']);

$scoreTotalDisplay = null;
$scorePossibleDisplay = null;
if ($scorePossibleRaw !== null && $scorePossibleRaw > 0) {
    $scoreTotalDisplay = $formatNumber($scoreTotalRaw, 2);
    $scorePossibleDisplay = $formatNumber($scorePossibleRaw, 2);
}
?>

<style>
    .exam-detail-hero {
        background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
        border-radius: 24px;
        padding: 34px;
        color: #ffffff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .exam-detail-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 55%);
        pointer-events: none;
    }

    .exam-detail-hero h2,
    .exam-detail-hero p {
        position: relative;
        z-index: 1;
    }

    .exam-detail-hero h2 {
        font-size: 24px;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .exam-detail-hero .meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .exam-badge {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-soft-success { background: rgba(16, 185, 129, 0.12); color: #047857; }
    .badge-soft-info { background: rgba(14, 165, 233, 0.12); color: #0c4a6e; }
    .badge-soft-danger { background: rgba(239, 68, 68, 0.12); color: #b91c1c; }
    .badge-soft-neutral { background: rgba(148, 163, 184, 0.16); color: #475569; }

    .exam-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .exam-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .exam-summary-card.highlight-card {
        border-color: #6366f1;
        box-shadow: 0 12px 28px rgba(79, 70, 229, 0.12);
    }

    .exam-summary-card.highlight-card .value {
        color: #3730a3;
    }

    .exam-summary-card .label {
        font-size: 13px;
        color: #475569;
    }

    .exam-summary-card .value {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }

    .answers-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
    }

    .answers-wrapper h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 16px;
        color: #1e293b;
    }

    .answer-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
    }

    .answer-card h4 {
        font-size: 16px;
        margin-bottom: 6px;
        color: #0f172a;
    }

    .answer-card-badges {
        display: flex;
        flex-direction: column;
        gap: 8px;
        align-items: flex-end;
    }

    .answer-card .question-meta {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .answer-card .answer-content {
        background: #f8fafc;
        border-radius: 12px;
        padding: 12px;
        color: #334155;
        font-size: 14px;
    }

    .answer-card .answer-content strong {
        color: #0f172a;
    }

    .answer-content-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .disc-answer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }

    .detail-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .btn-rounded {
        border-radius: 14px;
        padding: 10px 18px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .text-muted-small {
        font-size: 12px;
        color: #94a3b8;
    }

    .answer-correct-list {
        margin-top: 6px;
        padding-right: 18px;
        list-style: disc;
        direction: rtl;
        text-align: right;
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="exam-detail-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mb-0">گزارش ثبت شده از آزمون دوره «<?= htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8'); ?>»</p>
                </div>
                <div class="meta">
                    <?php if ($toolName !== ''): ?>
                        <span class="exam-badge">
                            <ion-icon name="school-outline"></ion-icon>
                            <?= htmlspecialchars($toolName, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($toolCode !== ''): ?>
                        <span class="exam-badge">
                            <ion-icon name="barcode-outline"></ion-icon>
                            <?= htmlspecialchars($toolCode, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($courseCode !== ''): ?>
                        <span class="exam-badge">
                            <ion-icon name="folder-open-outline"></ion-icon>
                            <?= htmlspecialchars($courseCode, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success rounded-16 d-flex align-items-center gap-2 mb-3" role="alert">
                <ion-icon name="checkmark-circle-outline" style="font-size: 22px;"></ion-icon>
                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-2 mb-3" role="alert">
                <ion-icon name="alert-circle-outline" style="font-size: 22px;"></ion-icon>
                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <div class="detail-actions">
            <a href="<?= htmlspecialchars($baseListUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-light btn-rounded">
                <ion-icon name="arrow-back-outline"></ion-icon>
                بازگشت به فهرست
            </a>
            <form id="resetAttemptForm" method="post" action="<?= htmlspecialchars($resetActionUrl, ENT_QUOTES, 'UTF-8'); ?>" class="d-inline">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="attempt_id" value="<?= (int) ($attemptDetails['id'] ?? 0); ?>">
                <button type="submit" class="btn btn-danger btn-rounded">
                    <ion-icon name="refresh-outline"></ion-icon>
                    بازنشانی نتیجه آزمون
                </button>
            </form>
        </div>

        <div class="exam-summary-grid">
            <div class="exam-summary-card">
                <span class="label">تاریخ تکمیل</span>
                <span class="value"><?= $formatDateTime($attemptDetails['completed_at'] ?? $attemptDetails['created_at'] ?? null); ?></span>
            </div>
            <div class="exam-summary-card">
                <span class="label">پرسش‌های پاسخ‌داده شده</span>
                <span class="value"><?= UtilityHelper::englishToPersian((string) $answeredQuestions); ?> از <?= UtilityHelper::englishToPersian((string) ($totalQuestions > 0 ? $totalQuestions : $answeredQuestions)); ?></span>
                <?php if ($overallUnanswered > 0): ?>
                    <span class="text-muted-small">بدون پاسخ: <?= UtilityHelper::englishToPersian((string) $overallUnanswered); ?></span>
                <?php endif; ?>
            </div>
            <div class="exam-summary-card highlight-card">
                <span class="label">امتیاز کسب‌شده (از ۱۰۰)</span>
                <span class="value"><?= $scoreOutOfHundred !== null ? $formatNumber($scoreOutOfHundred, 1) : '—'; ?></span>
                <?php if ($scoreTotalDisplay !== null && $scorePossibleDisplay !== null): ?>
                    <span class="text-muted-small">امتیاز خام: <?= $scoreTotalDisplay; ?> از <?= $scorePossibleDisplay; ?></span>
                <?php elseif (!$hasEvaluation): ?>
                    <span class="text-muted-small">امکان محاسبه امتیاز در دسترس نیست.</span>
                <?php endif; ?>
            </div>
            <div class="exam-summary-card">
                <span class="label">پاسخ‌های صحیح</span>
                <span class="value"><?= UtilityHelper::englishToPersian((string) $correctCount); ?></span>
                <?php if ($hasEvaluation): ?>
                    <span class="text-muted-small">از <?= UtilityHelper::englishToPersian((string) $evaluatedQuestions); ?> سوال ارزیابی‌شده</span>
                <?php endif; ?>
            </div>
            <div class="exam-summary-card">
                <span class="label">پاسخ‌های نادرست</span>
                <span class="value"><?= UtilityHelper::englishToPersian((string) $incorrectCount); ?></span>
                <?php if ($hasEvaluation): ?>
                    <span class="text-muted-small">بی‌پاسخ: <?= UtilityHelper::englishToPersian((string) $unansweredEvaluated); ?></span>
                <?php endif; ?>
            </div>
            <div class="exam-summary-card">
                <span class="label">سوالات بدون پاسخ</span>
                <span class="value"><?= UtilityHelper::englishToPersian((string) $overallUnanswered); ?></span>
                <span class="text-muted-small">کل سوالات: <?= UtilityHelper::englishToPersian((string) $totalQuestions); ?></span>
            </div>
            <div class="exam-summary-card">
                <span class="label">نوع آزمون</span>
                <span class="value" style="text-transform: uppercase;">
                    <?= htmlspecialchars($questionType !== '' ? $questionType : ($attemptDetails['is_disc'] ?? false ? 'DISC' : 'STANDARD'), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </div>
            <div class="exam-summary-card">
                <span class="label">وضعیت ثبت‌نام</span>
                <span class="value" style="font-size: 18px;">
                    <?php if (!empty($attemptDetails['enrolled_at'])): ?>
                        ثبت‌نام <?= $formatDateTime($attemptDetails['enrolled_at']); ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </span>
                <?php if (!empty($attemptDetails['enrollment_completed_at'])): ?>
                    <span class="text-muted-small">تاریخ اتمام دوره: <?= $formatDateTime($attemptDetails['enrollment_completed_at']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="answers-wrapper">
            <h3 class="d-flex align-items-center gap-2">
                <ion-icon name="list-circle-outline"></ion-icon>
                پاسخ‌های ثبت شده
                <span class="text-muted" style="font-size: 14px; font-weight: 400;">(<?= UtilityHelper::englishToPersian((string) count($answers)); ?> مورد)</span>
            </h3>

            <?php if (!empty($answers)): ?>
                <?php foreach ($answers as $index => $answer): ?>
                    <div class="answer-card">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h4 class="mb-1">سؤال <?= UtilityHelper::englishToPersian((string) ($index + 1)); ?><?= !empty($answer['question_title']) ? ' — ' . htmlspecialchars($answer['question_title'], ENT_QUOTES, 'UTF-8') : ''; ?></h4>
                                <?php if (!empty($answer['question_text'])): ?>
                                    <div class="question-meta"><?= htmlspecialchars($answer['question_text'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="answer-card-badges">
                                <?php if (($answer['is_correct'] ?? null) === true): ?>
                                    <span class="badge-soft badge-soft-success">پاسخ صحیح</span>
                                <?php elseif (($answer['is_correct'] ?? null) === false): ?>
                                    <span class="badge-soft badge-soft-danger">پاسخ نادرست</span>
                                <?php elseif (($answer['is_evaluated'] ?? false) && empty($answer['answer_id'])): ?>
                                    <span class="badge-soft badge-soft-neutral">بدون پاسخ</span>
                                <?php endif; ?>
                                <?php if (!empty($answer['is_description_only'])): ?>
                                    <span class="badge-soft badge-soft-info">فقط توضیح</span>
                                <?php elseif (!empty($answer['requires_answer'])): ?>
                                    <span class="badge-soft badge-soft-neutral">پاسخ الزامی</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($answer['question_description'])): ?>
                            <div class="text-muted-small mb-2" style="line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($answer['question_description'], ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($attemptDetails['is_disc'])): ?>
                            <div class="disc-answer-grid">
                                <div class="answer-content">
                                    <strong>بیشترین انطباق:</strong><br>
                                    <?php if (!empty($answer['disc_best_answer_text'])): ?>
                                        <?= htmlspecialchars($answer['disc_best_answer_text'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($answer['disc_best_answer_code'])): ?>
                                            <span class="text-muted-small">(<?= htmlspecialchars($answer['disc_best_answer_code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        بدون انتخاب
                                    <?php endif; ?>
                                </div>
                                <div class="answer-content">
                                    <strong>کمترین انطباق:</strong><br>
                                    <?php if (!empty($answer['disc_least_answer_text'])): ?>
                                        <?= htmlspecialchars($answer['disc_least_answer_text'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($answer['disc_least_answer_code'])): ?>
                                            <span class="text-muted-small">(<?= htmlspecialchars($answer['disc_least_answer_code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        بدون انتخاب
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="answer-content">
                                <div class="answer-content-header">
                                    <strong>پاسخ انتخاب شده:</strong>
                                    <?php if (($answer['is_correct'] ?? null) === true): ?>
                                        <span class="badge-soft badge-soft-success">صحیح</span>
                                    <?php elseif (($answer['is_correct'] ?? null) === false): ?>
                                        <span class="badge-soft badge-soft-danger">نادرست</span>
                                    <?php elseif (($answer['is_evaluated'] ?? false) && empty($answer['answer_id'])): ?>
                                        <span class="badge-soft badge-soft-neutral">بدون پاسخ</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1">
                                    <?php if (!empty($answer['answer_text'])): ?>
                                        <?= htmlspecialchars($answer['answer_text'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($answer['answer_code'])): ?>
                                            <span class="text-muted-small">(<?= htmlspecialchars($answer['answer_code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        پاسخی ثبت نشده است.
                                    <?php endif; ?>
                                </div>
                                <?php if (($answer['selected_score'] ?? null) !== null && ($answer['question_score_weight'] ?? null) !== null): ?>
                                    <div class="text-muted-small mt-2">
                                        امتیاز دریافت شده: <?= $formatNumber($answer['selected_score'], 2); ?>
                                        از <?= $formatNumber($answer['question_score_weight'], 2); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (((($answer['is_correct'] ?? null) === false) || (($answer['is_correct'] ?? null) === null && ($answer['is_evaluated'] ?? false))) && !empty($answer['correct_answers'])): ?>
                                    <div class="text-muted-small mt-2">
                                        پاسخ صحیح:
                                        <ul class="answer-correct-list mb-0">
                                            <?php foreach ($answer['correct_answers'] as $correctOption): ?>
                                                <li>
                                                    <?= htmlspecialchars($correctOption['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php if (!empty($correctOption['code'])): ?>
                                                        <span class="text-muted-small">(<?= htmlspecialchars($correctOption['code'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-5">پاسخی برای نمایش وجود ندارد.</div>
            <?php endif; ?>
        </div>

    <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('resetAttemptForm');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            const confirmation = confirm('آیا از بازنشانی نتیجه آزمون "<?= addslashes($fullName); ?>" اطمینان دارید؟\nپس از بازنشانی، کاربر می‌تواند دوباره در آزمون شرکت کند.');
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });
</script>
