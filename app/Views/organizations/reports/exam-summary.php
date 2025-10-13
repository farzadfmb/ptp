<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات پاسخ‌های آزمون';
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

$summaryStats = $summaryStats ?? [];
$summaryStats = is_array($summaryStats) ? $summaryStats : [];
$questionEntries = $questionEntries ?? [];
$participationHeader = $participationHeader ?? [
    'participation' => [],
    'evaluation' => [],
    'tool' => [],
];
$evaluateeSummary = $evaluateeSummary ?? [
    'id' => 0,
    'name' => 'ارزیابی‌شونده',
];
$breadcrumbs = $breadcrumbs ?? [];

$tableOptions = [
    'paging' => false,
    'ordering' => false,
    'info' => false,
    'responsive' => true,
    'responsiveDesktopMin' => 992,
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= "\n    body {\n        background: #f4f6fb;\n    }\n    .exam-summary-table thead th,\n    .exam-summary-table tbody td {\n        text-align: right;\n        vertical-align: top;\n        direction: rtl;\n    }\n    .exam-summary-table tbody td .badge,\n    .exam-summary-table tbody td span,\n    .exam-summary-table tbody td p,\n    .exam-summary-table tbody td small {\n        direction: rtl;\n        text-align: right;\n    }\n    .exam-summary-table-wrapper {\n        overflow-x: auto;\n        -webkit-overflow-scrolling: touch;\n    }\n";

$formatPersianDateTime = static function ($dateTime, string $fallback = '-') {
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

$formatNumber = static function ($number, string $fallback = '-') {
    if ($number === null || $number === '') {
        return $fallback;
    }

    if (is_numeric($number)) {
        return UtilityHelper::englishToPersian((string) $number);
    }

    return UtilityHelper::englishToPersian((string) $number);
};

$formatPayloadForDisplay = null;
$formatPayloadForDisplay = static function ($payload) use (&$formatPayloadForDisplay) {
    if ($payload === null || $payload === '') {
        return '';
    }

    if (is_string($payload)) {
        $decoded = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $formatPayloadForDisplay($decoded);
        }

        return '<span class="d-block text-gray-600">' . htmlspecialchars(UtilityHelper::englishToPersian($payload), ENT_QUOTES, 'UTF-8') . '</span>';
    }

    if (is_scalar($payload)) {
        return '<span class="d-block text-gray-600">' . htmlspecialchars(UtilityHelper::englishToPersian((string) $payload), ENT_QUOTES, 'UTF-8') . '</span>';
    }

    if (is_array($payload)) {
        if (empty($payload)) {
            return '';
        }

        $items = '';
        foreach ($payload as $key => $value) {
            $label = is_int($key)
                ? UtilityHelper::englishToPersian((string) ($key + 1))
                : htmlspecialchars(UtilityHelper::englishToPersian((string) $key), ENT_QUOTES, 'UTF-8');

            if (is_array($value)) {
                $child = $formatPayloadForDisplay($value);
                $items .= '<li class="mb-2"><span class="fw-semibold text-gray-700">' . $label . ':</span>';
                if ($child !== '') {
                    $items .= '<div class="mt-2 ms-3 payload-nested">' . $child . '</div>';
                }
                $items .= '</li>';
            } else {
                $displayValue = is_string($value)
                    ? UtilityHelper::englishToPersian($value)
                    : UtilityHelper::englishToPersian((string) $value);
                $items .= '<li class="mb-1"><span class="fw-semibold text-gray-700">' . $label . ':</span> <span class="text-gray-600">' . htmlspecialchars($displayValue, ENT_QUOTES, 'UTF-8') . '</span></li>';
            }
        }

        return '<ul class="list-unstyled mb-0 payload-list">' . $items . '</ul>';
    }

    return '';
};

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-12 mb-16">
            <div>
                <h2 class="mb-4 text-gray-900">جزئیات پاسخ‌های آزمون</h2>
                <p class="mb-0 text-gray-500">اطلاعات کامل پاسخ‌ها و امتیازات آزمون برای <?= htmlspecialchars($evaluateeSummary['name'] ?? 'ارزیابی‌شونده', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-8">
                <a href="<?= UtilityHelper::baseUrl('organizations/reports/excel/detail?evaluatee_id=' . urlencode((string) ($participationHeader['participation']['evaluatee_id'] ?? $evaluateeSummary['id'] ?? 0))); ?>" class="btn btn-outline-secondary rounded-pill px-20 d-flex align-items-center gap-8">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    بازگشت به لیست آزمون‌ها
                </a>
            </div>
        </div>

        <?php if (!empty($breadcrumbs)): ?>
            <nav aria-label="breadcrumb" class="mb-20">
                <ol class="breadcrumb mb-0">
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <li class="breadcrumb-item <?= empty($crumb['url']) ? 'active' : ''; ?>">
                            <?php if (!empty($crumb['url'])): ?>
                                <a href="<?= htmlspecialchars($crumb['url'], ENT_QUOTES, 'UTF-8'); ?>" class="text-decoration-none text-main"><?= htmlspecialchars($crumb['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($crumb['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <?php
            $participation = $participationHeader['participation'] ?? [];
            $evaluation = $participationHeader['evaluation'] ?? [];
            $tool = $participationHeader['tool'] ?? [];

            $downloadUrl = $downloadUrl ?? null;

            if ($downloadUrl === null) {
                $downloadParams = [];
                $downloadEvaluateeId = (int) ($participation['evaluatee_id'] ?? ($evaluateeSummary['id'] ?? 0));

                if ($downloadEvaluateeId > 0) {
                    $downloadParams['evaluatee_id'] = $downloadEvaluateeId;

                    $downloadEvaluationId = (int) ($participation['evaluation_id'] ?? ($evaluation['id'] ?? 0));
                    if ($downloadEvaluationId > 0) {
                        $downloadParams['evaluation_id'] = $downloadEvaluationId;
                    }

                    $downloadParticipationId = (int) ($participation['id'] ?? 0);
                    if ($downloadParticipationId > 0) {
                        $downloadParams['participation_id'] = $downloadParticipationId;
                    }

                    $downloadUrl = UtilityHelper::baseUrl('organizations/reports/excel/download' . (!empty($downloadParams) ? '?' . http_build_query($downloadParams) : ''));
                }
            }
        ?>

        <div class="card border-0 shadow-sm rounded-24 mb-24">
            <div class="card-body p-24">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <div class="rounded-20 border border-gray-100 p-16 h-100">
                            <h6 class="text-gray-500 mb-8">ارزیابی‌شونده</h6>
                            <p class="fw-semibold text-gray-900 mb-4"><?= htmlspecialchars($evaluateeSummary['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($evaluateeSummary['evaluation_code'])): ?>
                                <p class="mb-2 text-gray-600">کد پرسنلی: <strong><?= UtilityHelper::englishToPersian($evaluateeSummary['evaluation_code']); ?></strong></p>
                            <?php endif; ?>
                            <?php if (!empty($evaluateeSummary['department'])): ?>
                                <p class="mb-2 text-gray-600">واحد سازمانی: <strong><?= htmlspecialchars($evaluateeSummary['department'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            <?php endif; ?>
                            <?php if (!empty($evaluateeSummary['job_title'])): ?>
                                <p class="mb-2 text-gray-600">سمت: <strong><?= htmlspecialchars($evaluateeSummary['job_title'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            <?php endif; ?>
                            <?php if (!empty($evaluateeSummary['username'])): ?>
                                <p class="mb-0 text-gray-500 small">نام کاربری: <?= htmlspecialchars($evaluateeSummary['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="rounded-20 border border-gray-100 p-16 h-100">
                            <h6 class="text-gray-500 mb-8">اطلاعات ارزیابی</h6>
                            <p class="fw-semibold text-gray-900 mb-4"><?= htmlspecialchars($evaluation['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php
                                $participationDateValue = $participation['participated_at'] ?? $participation['registered_at'] ?? $participation['created_at'] ?? null;
                                $participationIdDisplay = isset($participation['id']) ? UtilityHelper::englishToPersian((string) $participation['id']) : '-';
                            ?>
                            <p class="mb-2 text-gray-600">تاریخ ارزیابی: <strong><?= $formatPersianDateTime($evaluation['evaluation_date'] ?? $participationDateValue ?? null, '-'); ?></strong></p>
                            <p class="mb-0 text-gray-600">شناسه شرکت در آزمون: <strong><?= $participationIdDisplay; ?></strong></p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="rounded-20 border border-gray-100 p-16 h-100">
                            <h6 class="text-gray-500 mb-8">اطلاعات ابزار</h6>
                            <p class="fw-semibold text-gray-900 mb-4"><?= htmlspecialchars($tool['name'] ?? 'ابزار نامشخص', ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mb-2 text-gray-600">نوع سوالات: <strong><?= htmlspecialchars($tool['question_type'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            <?php if (!empty($tool['code'])): ?>
                                <p class="mb-0 text-gray-600">کد ابزار: <strong><?= UtilityHelper::englishToPersian((string) $tool['code']); ?></strong></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
            $totalQuestions = (int) ($summaryStats['total_questions'] ?? 0);
            $answeredQuestions = (int) ($summaryStats['answered'] ?? 0);
            $correctAnswers = (int) ($summaryStats['correct'] ?? 0);
            $incorrectAnswers = (int) ($summaryStats['incorrect'] ?? 0);
            $characterSummary = $summaryStats['character_summary'] ?? [];
        ?>

        <div class="row g-3 mb-24">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="rounded-20 border border-gray-100 p-20 h-100 bg-white shadow-sm">
                    <p class="text-gray-500 mb-4">تعداد سوالات</p>
                    <h3 class="mb-0 fw-semibold text-gray-900"><?= UtilityHelper::englishToPersian((string) $totalQuestions); ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="rounded-20 border border-gray-100 p-20 h-100 bg-white shadow-sm">
                    <p class="text-gray-500 mb-4">سوالات پاسخ داده‌شده</p>
                    <h3 class="mb-0 fw-semibold text-main"><?= UtilityHelper::englishToPersian((string) $answeredQuestions); ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="rounded-20 border border-success-100 p-20 h-100 bg-success-50 shadow-sm">
                    <p class="text-success-600 mb-4">پاسخ‌های صحیح</p>
                    <h3 class="mb-0 fw-semibold text-success-700"><?= UtilityHelper::englishToPersian((string) $correctAnswers); ?></h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="rounded-20 border border-danger-100 p-20 h-100 bg-danger-50 shadow-sm">
                    <p class="text-danger-600 mb-4">پاسخ‌های نادرست</p>
                    <h3 class="mb-0 fw-semibold text-danger-700"><?= UtilityHelper::englishToPersian((string) $incorrectAnswers); ?></h3>
                </div>
            </div>
        </div>

        <?php if (!empty($downloadUrl)): ?>
            <div class="card border-0 shadow-sm rounded-24 mb-24">
                <div class="card-body p-24">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-16">
                        <div class="flex-grow-1">
                            <h5 class="mb-8 text-gray-900">دریافت خروجی اکسل آزمون</h5>
                            <p class="mb-0 text-gray-500">بسته ZIP شامل فایل Excel (.xlsx) با جزئیات کامل همین آزمون و فیلترهای اعمال‌شده است.</p>
                        </div>
                        <a href="<?= htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-success rounded-pill px-24 d-flex align-items-center gap-8">
                            <ion-icon name="archive-outline"></ion-icon>
                            دانلود فایل اکسل آزمون
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($characterSummary)): ?>
            <div class="card border-0 shadow-sm rounded-24 mb-24">
                <div class="card-body p-24">
                    <h5 class="text-gray-900 mb-16">نتایج شخصیت‌شناسی / امتیازات شاخص</h5>
                    <div class="row g-3">
                        <?php foreach ($characterSummary as $summaryItem): ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="rounded-20 border border-gray-100 p-20 h-100 bg-white">
                                    <p class="text-gray-500 mb-6"><?= htmlspecialchars($summaryItem['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h4 class="fw-semibold text-gray-900 mb-6"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($summaryItem['value'] ?? '-')), ENT_QUOTES, 'UTF-8'); ?></h4>
                                    <?php if (!empty($summaryItem['details'])): ?>
                                        <p class="mb-0 text-gray-600 small"><?= htmlspecialchars($summaryItem['details'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-24">
            <div class="card-body p-24">
                <div class="d-flex align-items-center justify-content-between mb-16 flex-wrap gap-12">
                    <h5 class="mb-0 text-gray-900">جدول پاسخ‌ها</h5>
                    <span class="text-gray-500">نمایش خلاصه پاسخ‌های ثبت‌شده به تفکیک سوال</span>
                </div>
                <div class="table-responsive border border-gray-100 rounded-20 exam-summary-table-wrapper">
                    <table class="table align-middle mb-0 js-data-table exam-summary-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="992">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th scope="col" class="text-center">ردیف</th>
                                <th scope="col">سوال</th>
                                <th scope="col">پاسخ انتخاب‌شده</th>
                                <th scope="col" class="text-center">وضعیت / امتیاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($questionEntries)): ?>
                                <?php $rowIndex = 1; ?>
                                <?php foreach ($questionEntries as $entry): ?>
                                    <?php
                                        $isDescriptionOnly = !empty($entry['is_description_only']);
                                        $questionTitle = trim((string) ($entry['title'] ?? ''));
                                        $questionText = trim((string) ($entry['text'] ?? ''));
                                        $questionDescription = trim((string) ($entry['description'] ?? ''));
                                        $answers = $entry['answers'] ?? [];
                                    ?>
                                    <tr class="<?= $isDescriptionOnly ? 'bg-gray-50' : ''; ?>">
                                        <td class="text-center fw-semibold text-gray-700 align-top">
                                            <?= UtilityHelper::englishToPersian((string) $rowIndex); ?>
                                        </td>
                                        <td class="align-top">
                                            <div class="d-flex flex-column gap-6 text-end">
                                                <?php if ($questionTitle !== ''): ?>
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($questionTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                                <?php if ($questionText !== ''): ?>
                                                    <span class="text-gray-600"><?= nl2br(htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8')); ?></span>
                                                <?php endif; ?>
                                                <?php if ($questionDescription !== ''): ?>
                                                    <small class="text-gray-500"><?= nl2br(htmlspecialchars($questionDescription, ENT_QUOTES, 'UTF-8')); ?></small>
                                                <?php endif; ?>
                                                <?php if ($isDescriptionOnly): ?>
                                                    <span class="badge bg-info bg-opacity-10 text-info">سوال توضیحی</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="align-top">
                                            <?php if (!empty($answers)): ?>
                                                <ul class="list-unstyled mb-0 d-flex flex-column gap-8 text-end">
                                                    <?php foreach ($answers as $answer): ?>
                                                        <?php
                                                            $answerCode = $answer['answer_code'] ?? null;
                                                            $answerText = $answer['answer_text'] ?? null;
                                                            $rawPayload = $answer['raw_payload'] ?? null;
                                                            $discBest = $answer['disc_best'] ?? [];
                                                            $discLeast = $answer['disc_least'] ?? [];
                                                        ?>
                                                        <li class="rounded-16 border border-gray-100 p-12 bg-white">
                                                            <?php if ($answerCode !== null || $answerText !== null): ?>
                                                                <p class="mb-4 text-gray-900 fw-semibold">
                                                                    <?php if ($answerCode !== null && $answerCode !== ''): ?>
                                                                        <span class="badge bg-main-50 text-main ms-6">کد <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $answerCode), ENT_QUOTES, 'UTF-8'); ?></span>
                                                                    <?php endif; ?>
                                                                    <?= htmlspecialchars($answerText ?? 'پاسخ ثبت نشده', ENT_QUOTES, 'UTF-8'); ?>
                                                                </p>
                                                            <?php else: ?>
                                                                <p class="mb-4 text-gray-500 fw-semibold">پاسخی ثبت نشده است.</p>
                                                            <?php endif; ?>

                                                            <?php if (!empty($discBest['code']) || !empty($discLeast['code'])): ?>
                                                                <div class="d-flex flex-column gap-4">
                                                                    <?php if (!empty($discBest['code'])): ?>
                                                                        <span class="badge bg-success-50 text-success-700">BEST: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $discBest['code']), ENT_QUOTES, 'UTF-8'); ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($discLeast['code'])): ?>
                                                                        <span class="badge bg-warning-50 text-warning-700">LEAST: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $discLeast['code']), ENT_QUOTES, 'UTF-8'); ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <?php if (!empty($rawPayload)): ?>
                                                                <?php $payloadHtml = $formatPayloadForDisplay($rawPayload); ?>
                                                                <?php if ($payloadHtml !== ''): ?>
                                                                    <details class="mt-6">
                                                                        <summary class="text-main small">مشاهده جزئیات پاسخ</summary>
                                                                        <div class="mt-4 bg-gray-100 rounded-12 p-12 text-gray-700 payload-wrapper">
                                                                            <?= $payloadHtml; ?>
                                                                        </div>
                                                                    </details>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-gray-400">پاسخی ثبت نشده است.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-top text-center">
                                            <?php if (!empty($answers)): ?>
                                                <div class="d-flex flex-column gap-6 align-items-center">
                                                    <?php foreach ($answers as $answer): ?>
                                                        <?php
                                                            $isCorrect = $answer['is_correct'];
                                                            $numericScore = $answer['numeric_score'] ?? null;
                                                            $characterScore = $answer['character_score'] ?? null;
                                                        ?>
                                                        <div class="d-flex flex-column gap-4 align-items-center">
                                                            <?php if ($isCorrect === true): ?>
                                                                <span class="badge bg-success-100 text-success-700">پاسخ صحیح</span>
                                                            <?php elseif ($isCorrect === false): ?>
                                                                <span class="badge bg-danger-100 text-danger-700">پاسخ نادرست</span>
                                                            <?php endif; ?>

                                                            <?php if ($numericScore !== null && $numericScore !== ''): ?>
                                                                <span class="badge bg-primary-subtle text-primary">امتیاز عددی: <?= UtilityHelper::englishToPersian(number_format((float) $numericScore, 2)); ?></span>
                                                            <?php endif; ?>
                                                            <?php if ($characterScore !== null && $characterScore !== ''): ?>
                                                                <span class="badge bg-indigo-50 text-indigo-700">امتیاز کاراکتری: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) $characterScore), ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php $rowIndex++; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-32 text-gray-500">هیچ پاسخی برای نمایش وجود ندارد.</td>
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
