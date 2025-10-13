<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'تقویم ارزشیابی';
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

$calendarMeta = $calendarMeta ?? [
    'display_year' => (int) date('Y'),
    'display_month' => (int) date('n'),
    'month_label' => '—',
    'current_year' => (int) date('Y'),
    'current_month' => (int) date('n'),
    'current_day' => (int) date('j'),
    'prev_year' => (int) date('Y'),
    'prev_month' => (int) date('n'),
    'next_year' => (int) date('Y'),
    'next_month' => (int) date('n'),
];

$weeks = $weeks ?? [];
$weekDays = $weekDays ?? ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
$calendarEvaluationRows = $calendarEvaluationRows ?? [];

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-calendar-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        padding: 24px;
    }
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .calendar-header-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
    }
    .calendar-nav {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .calendar-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 14px;
        min-height: 44px;
    }
    .calendar-nav-btn ion-icon {
        font-size: 18px;
    }
    .calendar-nav-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1d4ed8;
    }
    .calendar-nav-current {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #1f2937;
        font-weight: 700;
        border-radius: 999px;
        padding: 8px 20px;
        min-height: 44px;
        font-size: 0.95rem;
    }
    .calendar-wrapper {
        margin-top: 24px;
    }
    .calendar-weekdays,
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 12px;
    }
    .calendar-weekdays {
        margin-bottom: 12px;
    }
    .calendar-weekday {
        text-align: center;
        font-weight: 600;
        color: #6b7280;
        letter-spacing: 0.02em;
    }
    .calendar-day {
        min-height: 120px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }
    .calendar-day.is-empty {
        background-color: transparent;
        border-color: transparent;
    }
    .calendar-day.has-events {
        border-color: rgba(59, 130, 246, 0.4);
        box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.35);
    }
    .calendar-day.is-today {
        border-color: rgba(34, 197, 94, 0.6);
        box-shadow: inset 0 0 0 2px rgba(34, 197, 94, 0.4);
    }
    .calendar-day.is-focused {
        border-color: rgba(99, 102, 241, 0.75);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18);
        transform: translateY(-2px);
    }
    .calendar-day-number {
        font-weight: 700;
        font-size: 1.125rem;
        color: #111827;
    }
    .calendar-day-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: rgba(59, 130, 246, 0.1);
        color: #1d4ed8;
    }
    .calendar-day-events {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .calendar-day-events li {
        font-size: 0.82rem;
        color: #374151;
        background-color: #f3f4f6;
        border-radius: 12px;
        padding: 6px 10px;
        line-height: 1.4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .calendar-empty-message {
        font-size: 0.85rem;
        color: #9ca3af;
        margin-top: auto;
    }
    .evaluation-table .btn {
        border-radius: 999px;
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    @media (max-width: 575.98px) {
        .calendar-nav {
            justify-content: center;
        }
        .calendar-nav-btn {
            padding: 6px 10px;
        }
        .calendar-nav-label {
            font-size: 0.78rem;
        }
        .calendar-nav-current {
            font-size: 0.85rem;
            padding: 6px 16px;
        }
    }
CSS;

$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        const calendar = document.querySelector('.evaluation-calendar');
        if (!calendar) {
            return;
        }

        const currentYear = parseInt(calendar.dataset.currentYear || '0', 10);
        const currentMonth = parseInt(calendar.dataset.currentMonth || '0', 10);

        const dayCells = Array.prototype.slice.call(calendar.querySelectorAll('[data-day-key]'));
        const dayMap = {};
        dayCells.forEach(function (cell) {
            const key = cell.getAttribute('data-day-key');
            if (key) {
                dayMap[key] = cell;
            }
        });

        const focusButtons = document.querySelectorAll('.js-focus-day');
        focusButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetYear = parseInt(button.dataset.year || '0', 10);
                const targetMonth = parseInt(button.dataset.month || '0', 10);
                const targetKey = button.dataset.dayKey || '';

                if (targetYear && targetMonth && (targetYear !== currentYear || targetMonth !== currentMonth)) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('year', targetYear.toString());
                    url.searchParams.set('month', targetMonth.toString());
                    window.location.href = url.toString();
                    return;
                }

                const targetCell = dayMap[targetKey];
                if (!targetCell) {
                    return;
                }

                dayCells.forEach(function (cell) {
                    cell.classList.remove('is-focused');
                });

                targetCell.classList.add('is-focused');
                targetCell.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    });
SCRIPT;

$inline_scripts .= "\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<?php
$calendarYear = (int) ($calendarMeta['display_year'] ?? 0);
$calendarMonth = (int) ($calendarMeta['display_month'] ?? 0);
$prevYear = (int) ($calendarMeta['prev_year'] ?? $calendarYear);
$prevMonth = (int) ($calendarMeta['prev_month'] ?? $calendarMonth);
$nextYear = (int) ($calendarMeta['next_year'] ?? $calendarYear);
$nextMonth = (int) ($calendarMeta['next_month'] ?? $calendarMonth);
$monthLabel = trim((string) ($calendarMeta['month_label'] ?? '')); 
$currentYear = (int) ($calendarMeta['current_year'] ?? $calendarYear);
$currentMonth = (int) ($calendarMeta['current_month'] ?? $calendarMonth);
$currentDay = (int) ($calendarMeta['current_day'] ?? 0);

$prevLink = UtilityHelper::baseUrl('organizations/evaluation-calendar?year=' . urlencode((string) $prevYear) . '&month=' . urlencode((string) $prevMonth));
$nextLink = UtilityHelper::baseUrl('organizations/evaluation-calendar?year=' . urlencode((string) $nextYear) . '&month=' . urlencode((string) $nextMonth));
$monthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
$formatMonthYear = static function (int $year, int $month) use ($monthNames): string {
    if ($month < 1 || $month > 12) {
        return UtilityHelper::englishToPersian(sprintf('%04d/%02d', $year, max(1, min(12, $month))));
    }

    $name = $monthNames[$month - 1] ?? '';
    $yearFa = UtilityHelper::englishToPersian((string) $year);

    return $name !== '' ? trim($name . ' ' . $yearFa) : UtilityHelper::englishToPersian(sprintf('%04d/%02d', $year, $month));
};
$currentMonthLabel = $monthLabel !== '' ? $monthLabel : $formatMonthYear($calendarYear, $calendarMonth);
$prevMonthLabel = $formatMonthYear($prevYear, $prevMonth);
$nextMonthLabel = $formatMonthYear($nextYear, $nextMonth);
$evaluationTableOptions = [
    'paging' => true,
    'pageLength' => 10,
    'lengthChange' => false,
    'dom' => "<'row align-items-center mb-3'<'col-lg-6 col-md-6 col-sm-12 text-start text-md-start'f>><'row'<'col-12'tr>><'row align-items-center mt-3'<'col-md-6 text-start text-md-start'i><'col-md-6 text-start text-md-start text-md-end'p>>",
];
$evaluationTableOptionsAttr = htmlspecialchars(json_encode($evaluationTableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="evaluation-calendar-card evaluation-calendar" data-current-year="<?= htmlspecialchars((string) $calendarYear, ENT_QUOTES, 'UTF-8'); ?>" data-current-month="<?= htmlspecialchars((string) $calendarMonth, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="calendar-header">
                        <div>
                            <h2 class="calendar-header-title">تقویم ارزشیابی سازمان</h2>
                            <p class="text-gray-500 mb-0">برنامه‌های ارزیابی باز و در حال برنامه‌ریزی سازمان را در یک نگاه مشاهده کنید.</p>
                        </div>
                        <div class="calendar-nav" role="navigation" aria-label="جابه‌جایی بین ماه‌ها">
                            <a href="<?= htmlspecialchars($prevLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-gray calendar-nav-btn" title="ماه قبل: <?= htmlspecialchars($prevMonthLabel, ENT_QUOTES, 'UTF-8'); ?>" aria-label="ماه قبل: <?= htmlspecialchars($prevMonthLabel, ENT_QUOTES, 'UTF-8'); ?>">
                                <ion-icon name="chevron-back-outline"></ion-icon>
                                <span class="calendar-nav-label"><?= htmlspecialchars($prevMonthLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                            <div class="calendar-nav-current" aria-live="polite">
                                <?= htmlspecialchars($currentMonthLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <a href="<?= htmlspecialchars($nextLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-gray calendar-nav-btn" title="ماه بعد: <?= htmlspecialchars($nextMonthLabel, ENT_QUOTES, 'UTF-8'); ?>" aria-label="ماه بعد: <?= htmlspecialchars($nextMonthLabel, ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="calendar-nav-label"><?= htmlspecialchars($nextMonthLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                <ion-icon name="chevron-forward-outline"></ion-icon>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-12 mt-3">
                        <span class="badge bg-main-soft text-main fw-semibold px-3 py-2">
                            <?= htmlspecialchars($currentMonthLabel !== '' ? $currentMonthLabel : 'بدون تاریخ', ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <span class="text-sm text-gray-500">
                            امروز: <?= UtilityHelper::englishToPersian(sprintf('%04d/%02d/%02d', $currentYear, $currentMonth, $currentDay)); ?>
                        </span>
                    </div>

                    <div class="calendar-wrapper mt-4">
                        <div class="calendar-weekdays">
                            <?php foreach ($weekDays as $weekday): ?>
                                <div class="calendar-weekday"><?= htmlspecialchars($weekday, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="calendar-grid mt-2">
                            <?php foreach ($weeks as $week): ?>
                                <?php foreach ($week as $cell): ?>
                                    <?php if ($cell === null): ?>
                                        <div class="calendar-day is-empty"></div>
                                    <?php else: ?>
                                        <?php
                                            $classes = ['calendar-day'];
                                            if (!empty($cell['events_count'])) {
                                                $classes[] = 'has-events';
                                            }
                                            if (!empty($cell['is_today'])) {
                                                $classes[] = 'is-today';
                                            }
                                            $classAttr = implode(' ', $classes);
                                            $dayKeyRaw = (string) ($cell['date_key'] ?? '');
                                            $dayKey = htmlspecialchars($dayKeyRaw, ENT_QUOTES, 'UTF-8');
                                            $dayLabel = htmlspecialchars($cell['day_label'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $matrixDateLink = UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix?date=' . urlencode($dayKeyRaw));
                                            $dayTitleText = $dayKeyRaw !== ''
                                                ? UtilityHelper::englishToPersian(str_replace('-', '/', $dayKeyRaw))
                                                : 'بدون تاریخ';
                                            $dayTitle = htmlspecialchars('نمایش ارزیابی‌های تاریخ ' . $dayTitleText, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <?php
                                            $events = $cell['events'] ?? [];
                                            $eventsCount = (int) ($cell['events_count'] ?? count($events));
                                            $eventTitles = [];
                                            foreach ($events as $eventItem) {
                                                $eventTitles[] = trim((string) ($eventItem['title'] ?? 'بدون عنوان'));
                                            }

                                            $dayTooltip = $dayTitle;
                                            if (!empty($eventTitles)) {
                                                $tooltipLines = array_merge([
                                                    htmlspecialchars_decode($dayTitle, ENT_QUOTES),
                                                ], $eventTitles);
                                                $dayTooltip = htmlspecialchars(implode("\n", $tooltipLines), ENT_QUOTES, 'UTF-8');
                                            }
                                        ?>
                                        <a class="<?= $classAttr; ?>" data-day-key="<?= $dayKey; ?>" href="<?= htmlspecialchars($matrixDateLink, ENT_QUOTES, 'UTF-8'); ?>" title="<?= $dayTooltip; ?>" aria-label="<?= $dayTitle; ?>">
                                            <div class="calendar-day-number"><?= $dayLabel; ?></div>
                                            <?php if ($eventsCount > 0): ?>
                                                <div class="calendar-day-count">
                                                    <?= UtilityHelper::englishToPersian((string) $eventsCount); ?> ارزیابی
                                                </div>
                                                <ul class="calendar-day-events">
                                                    <?php foreach (array_slice($events, 0, 2) as $eventItem): ?>
                                                        <?php $eventTitle = trim((string) ($eventItem['title'] ?? 'بدون عنوان')); ?>
                                                        <li title="<?= htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars(mb_strimwidth($eventTitle, 0, 36, '...'), ENT_QUOTES, 'UTF-8'); ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                    <?php if ($eventsCount > 2): ?>
                                                        <li title="<?= htmlspecialchars(implode('، ', array_slice($eventTitles, 2)), ENT_QUOTES, 'UTF-8'); ?>">...</li>
                                                    <?php endif; ?>
                                                </ul>
                                            <?php else: ?>
                                                <div class="calendar-empty-message">بدون برنامه</div>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24 evaluation-table">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h3 class="mb-6 text-gray-900">همه ارزیابی‌های ثبت‌شده</h3>
                                <p class="text-gray-500 mb-0">در این جدول می‌توانید تمام ارزیابی‌های ایجادشده را مشاهده و مدیریت کنید.</p>
                            </div>
                        </div>

                        <div class="table-responsive rounded-16 border border-gray-100">
                            <table class="table align-middle mb-0 js-data-table " data-datatable-options="<?= $evaluationTableOptionsAttr; ?>">
                                <thead class="bg-gray-100 text-gray-700">
                                    <tr>
                                        <th scope="col" class="text-start">عنوان ارزیابی</th>
                                        <th scope="col" class="text-start">تاریخ ارزیابی</th>
                                        <th scope="col" style="width: 45%;" class="no-sort no-search text-start">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($calendarEvaluationRows)): ?>
                                        <?php foreach ($calendarEvaluationRows as $evaluation): ?>
                                            <?php
                                                $evaluationIdRaw = (int) ($evaluation['id'] ?? 0);
                                                $evaluationIdAttr = htmlspecialchars((string) $evaluationIdRaw, ENT_QUOTES, 'UTF-8');
                                                $titleDisplay = htmlspecialchars($evaluation['title'] ?? '-', ENT_QUOTES, 'UTF-8');
                                                $dateDisplay = htmlspecialchars($evaluation['persian_date_display'] ?? '-', ENT_QUOTES, 'UTF-8');
                                                $yearAttr = htmlspecialchars((string) ($evaluation['persian_year'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                $monthAttr = htmlspecialchars((string) ($evaluation['persian_month'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                $dayAttr = htmlspecialchars((string) ($evaluation['persian_day'] ?? ''), ENT_QUOTES, 'UTF-8');
                                                $dateKey = htmlspecialchars($evaluation['persian_date_key'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $hasDate = $evaluation['persian_year'] !== null && $evaluation['persian_month'] !== null && $evaluation['persian_day'] !== null;
                                                $editLink = UtilityHelper::baseUrl('organizations/evaluation-calendar/edit?id=' . $evaluationIdRaw);
                                                $matrixManageLink = UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix/manage?id=' . $evaluationIdRaw);
                                            ?>
                                            <tr>
                                                <td><?= $titleDisplay; ?></td>
                                                <td><?= $dateDisplay; ?></td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php if ($hasDate): ?>
                                                            <button type="button" class="btn btn-outline-main js-focus-day" data-year="<?= $yearAttr; ?>" data-month="<?= $monthAttr; ?>" data-day="<?= $dayAttr; ?>" data-day-key="<?= $dateKey; ?>">
                                                                <ion-icon name="calendar-outline"></ion-icon>
                                                                نمایش در تقویم
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline-main" disabled>
                                                                <ion-icon name="calendar-outline"></ion-icon>
                                                                بدون تاریخ
                                                            </button>
                                                        <?php endif; ?>
                                                        <a href="<?= htmlspecialchars($editLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-warning">
                                                            <ion-icon name="create-outline"></ion-icon>
                                                            ویرایش
                                                        </a>
                                                        <a href="<?= htmlspecialchars($matrixManageLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">
                                                            <ion-icon name="grid-outline"></ion-icon>
                                                            ماتریس ارزیابی
                                                        </a>
                                                        <form action="<?= htmlspecialchars(UtilityHelper::baseUrl('organizations/evaluation-calendar/delete'), ENT_QUOTES, 'UTF-8'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('آیا از حذف ارزیابی «<?= $titleDisplay; ?>» اطمینان دارید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= $evaluationIdAttr; ?>">
                                                            <button type="submit" class="btn btn-outline-danger">
                                                                <ion-icon name="trash-outline"></ion-icon>
                                                                حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-24 text-gray-500">ارزیابی‌ای برای نمایش وجود ندارد.</td>
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
    </div>
</div>
