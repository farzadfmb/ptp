<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'تقویم آموزشی';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';


// Only keep the correct heredoc CSS assignment
$inline_styles .= <<<'CSS'
.training-calendar-card {
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15,23,42,0.08);
}
.calendar-table {
    min-width: 900px;
}
.calendar-table thead th {
    font-weight: 600;
    color: #0f172a;
    background: #f1f5f9;
    border-bottom: none;
}
.calendar-table tbody td {
    color: #334155;
}
.status-badge {
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
}
.summary-chip {
    border-radius: 14px;
    padding: 14px 18px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}
.summary-chip .label {
    font-size: 0.9rem;
    color: #64748b;
}
.summary-chip .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}
.exam-list-title {
    font-size: 0.9rem;
    margin-bottom: 4px;
}
.exam-list {
    font-size: 0.85rem;
    line-height: 1.7;
}
.exam-list.muted {
    color: #94a3b8;
}
CSS;

        AuthHelper::startSession();
        $user = AuthHelper::getUser();
        $navbarUser = $user;

        $calendarItems = $calendarItems ?? [];
        if (!is_array($calendarItems)) {
            $calendarItems = [];
        }

        $summaryCounts = [
            'total' => count($calendarItems),
            'participated' => 0,
            'not_participated' => 0,
            'completed' => 0,
        ];
        foreach ($calendarItems as $item) {
            if (!empty($item['has_participated'])) $summaryCounts['participated']++;
            if (!empty($item['all_tools_completed'])) $summaryCounts['completed']++;
        }
        $summaryCounts['not_participated'] = max(0, $summaryCounts['total'] - $summaryCounts['participated']);

        $parsePersianEvaluationDate = static function (string $input): ?DateTimeImmutable {
            $normalized = str_replace('/', '-', UtilityHelper::persianToEnglish(trim($input)));
            if ($normalized === '') {
                return null;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
                return null;
            }

            $timezone = new DateTimeZone('Asia/Tehran');
            $year = (int) substr($normalized, 0, 4);

            if ($year >= 1300 && $year <= 1700 && class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    'fa_IR@calendar=persian',
                    IntlDateFormatter::NONE,
                    IntlDateFormatter::NONE,
                    'Asia/Tehran',
                    IntlDateFormatter::TRADITIONAL,
                    'yyyy-MM-dd'
                );

                if ($formatter !== false) {
                    $timestamp = $formatter->parse($normalized);
                    if ($timestamp !== false) {
                        return (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone);
                    }
                }
            }

            try {
                return new DateTimeImmutable($normalized, $timezone);
            } catch (Exception $exception) {
                return null;
            }
        };

        try {
            $todayGregorian = new DateTimeImmutable('today', new DateTimeZone('Asia/Tehran'));
        } catch (Exception $exception) {
            $todayGregorian = null;
        }

        include __DIR__ . '/../../layouts/home-header.php';
        include __DIR__ . '/../../layouts/home-sidebar.php';
        ?>
        <?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>
        <div class="page-content-wrapper">
            <div class="page-content">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card training-calendar-card border-0">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div>
                                    <h1 class="h4 mb-2">تقویم آموزشی</h1>
                                    <p class="mb-0 text-secondary">در این صفحه وضعیت ارزیابی‌ها و آزمون‌های اختصاص داده شده را مشاهده و مدیریت کنید.</p>
                                </div>
                                <div class="text-end small text-secondary">
                                    <span>تاریخ بروزرسانی:</span>
                                    <span><?= htmlspecialchars(UtilityHelper::getTodayDate(), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-4">
                                        <div class="summary-chip h-100 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="label">کل ارزیابی‌ها</div>
                                                <div class="value"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $summaryCounts['total']), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary px-3 py-2">فعال</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="summary-chip h-100 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="label">دارای فعالیت</div>
                                                <div class="value text-success"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $summaryCounts['participated']), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <span class="badge bg-success-subtle text-success px-3 py-2">شرکت کرده</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="summary-chip h-100 d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="label">تکمیل شده</div>
                                                <div class="value text-success"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) $summaryCounts['completed']), ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                            <span class="badge bg-success text-white px-3 py-2">تمام شده</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle calendar-table">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:5%">#</th>
                                                <th style="width:25%">عنوان ارزیابی</th>
                                                <th style="width:13%">تاریخ</th>
                                                <th style="width:22%">آزمون‌های تکمیل‌شده</th>
                                                <th style="width:22%">آزمون‌های باقی‌مانده</th>
                                                <th style="width:13%">وضعیت / اقدام</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (empty($calendarItems)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-secondary">
                                                    هیچ ارزیابی‌ای برای شما تعریف نشده است.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($calendarItems as $index => $item):
                                                $completedToolNames = is_array($item['completed_tool_names'] ?? null) ? $item['completed_tool_names'] : [];
                                                $incompleteToolNames = is_array($item['incomplete_tool_names'] ?? null) ? $item['incomplete_tool_names'] : [];
                                                $completedLabel = !empty($completedToolNames) ? implode('، ', $completedToolNames) : '—';
                                                $remainingLabel = !empty($incompleteToolNames) ? implode('، ', $incompleteToolNames) : '—';
                                                $allToolsCompleted = !empty($item['all_tools_completed']);
                                                $startExamUrl = $item['start_exam_url'] ?? null;

                                                $statusLabel = 'نامشخص';
                                                $statusClass = 'bg-secondary-subtle text-secondary';
                                                $statusCode = $item['status_code'] ?? 'scheduled';

                                                $evaluationDateRaw = trim((string) ($item['evaluation_date'] ?? ''));
                                                $evaluationDateObject = $parsePersianEvaluationDate($evaluationDateRaw);

                                                $relativeDaysDifference = null;
                                                $isEvaluationPast = false;
                                                $isEvaluationGraceWindow = false;
                                                $isEvaluationExpired = false;
                                                $isEvaluationTodayOrFuture = false;

                                                if ($evaluationDateObject && $todayGregorian) {
                                                    $interval = $evaluationDateObject->diff($todayGregorian);
                                                    $relativeDaysDifference = (int) $interval->format('%r%a');

                                                    if ($relativeDaysDifference > 0) {
                                                        $isEvaluationPast = true;
                                                    }

                                                    if ($relativeDaysDifference >= 3) {
                                                        $isEvaluationExpired = true;
                                                    }

                                                    if ($relativeDaysDifference <= 0) {
                                                        $isEvaluationTodayOrFuture = true;
                                                    } elseif ($relativeDaysDifference > 0 && $relativeDaysDifference < 3) {
                                                        $isEvaluationGraceWindow = true;
                                                    }
                                                }

                                                if ($isEvaluationExpired) {
                                                    $statusLabel = 'تاریخ آزمون گذشته';
                                                    $statusClass = 'bg-danger-subtle text-danger';
                                                } elseif ($isEvaluationTodayOrFuture || $isEvaluationGraceWindow) {
                                                    $statusLabel = 'آماده شروع';
                                                    $statusClass = 'bg-success-subtle text-success';
                                                } else {
                                                    $statusLabel = $item['status_label'] ?? 'نامشخص';
                                                    $statusClass = $item['status_class'] ?? 'bg-secondary-subtle text-secondary';
                                                }

                                                $canShowStartButton = false;
                                                if (($isEvaluationTodayOrFuture || $isEvaluationGraceWindow) && !empty($startExamUrl) && !empty($item['has_exam_tools']) && !$allToolsCompleted) {
                                                    $canShowStartButton = true;
                                                }
                                            ?>
                                            <tr>
                                                <td class="text-center fw-semibold"><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($index + 1)), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="fw-semibold text-dark mb-1"><?= htmlspecialchars($item['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="text-secondary small">مدل: <?= htmlspecialchars($item['model'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($item['evaluation_date'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="exam-list-title text-success fw-semibold">تکمیل شده</div>
                                                    <div class="exam-list <?= $completedLabel === '—' ? 'muted' : 'text-success'; ?>">
                                                        <?= htmlspecialchars($completedLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="small text-muted mt-1">تعداد: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($item['completed_tools_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td>
                                                    <div class="exam-list-title text-danger fw-semibold">باقی‌مانده</div>
                                                    <div class="exam-list <?= $remainingLabel === '—' ? 'muted' : 'text-danger'; ?>">
                                                        <?= htmlspecialchars($remainingLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                    <div class="small text-muted mt-1">تعداد: <?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($item['incomplete_tools_count'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($allToolsCompleted): ?>
                                                        <div class="text-success small mt-2">تمام آزمون‌ها تکمیل شده است.</div>
                                                    <?php elseif ($canShowStartButton && !empty($item['can_start_exam'])): ?>
                                                        <a href="<?= htmlspecialchars($startExamUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-primary rounded-pill mt-2">
                                                            <?= $statusCode === 'in_progress' ? 'ادامه آزمون' : 'شروع آزمون'; ?>
                                                        </a>
                                                    <?php elseif ($isEvaluationExpired): ?>
                                                        <div class="text-danger small mt-2">این ارزیابی مربوط به تاریخ گذشته است.</div>
                                                    <?php elseif (empty($item['has_exam_tools'])): ?>
                                                        <div class="text-muted small mt-2">برای این ارزیابی هنوز آزمونی تعریف نشده است.</div>
                                                    <?php else: ?>
                                                        <div class="text-muted small mt-2">آزمونی برای انجام باقی نمانده است.</div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
