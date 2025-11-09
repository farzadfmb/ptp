<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../../Helpers/autoload.php';
}

$title = $title ?? 'نتیجه آزمون‌های دوره';
$user = $user ?? [];
$courses = $courses ?? [];
$selectedCourse = $selectedCourse ?? null;
$selectedCourseId = $selectedCourseId ?? 0;
$attempts = $attempts ?? [];
$pendingParticipants = $pendingParticipants ?? [];
$courseSummary = $courseSummary ?? ['enrolled' => 0, 'attempted' => 0, 'completed' => 0, 'pending' => 0];
$searchTerm = $searchTerm ?? '';
$baseListUrl = $baseListUrl ?? UtilityHelper::baseUrl('organizations/courses/exam-results');
$detailUrl = $detailUrl ?? UtilityHelper::baseUrl('organizations/courses/exam-results/view');
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
        $formatted = $date->format('Y/m/d H:i');
        return UtilityHelper::englishToPersian($formatted);
    } catch (Exception $exception) {
        return UtilityHelper::englishToPersian((string) $value);
    }
};

?>

<style>
    .exam-results-hero {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        border-radius: 24px;
        padding: 36px;
        color: #ffffff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .exam-results-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 55%);
        pointer-events: none;
    }

    .exam-results-hero h2,
    .exam-results-hero p {
        position: relative;
        z-index: 1;
    }

    .exam-results-hero h2 {
        margin-bottom: 8px;
        font-weight: 700;
    }

    .exam-results-hero .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
    }

    .hero-meta-badge {
        background: rgba(255, 255, 255, 0.16);
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .exam-results-filter {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }

    .exam-results-filter .form-label {
        font-weight: 600;
        color: #1e293b;
    }

    .exam-results-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .exam-results-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
    }

    .exam-results-card .card-title {
        font-size: 14px;
        color: #475569;
        margin: 0;
    }

    .exam-results-card .card-value {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .attempts-table-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .attempts-table-wrapper h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
    }

    .table thead tr {
        background: #f8fafc;
    }

    .table thead th {
        border-bottom: none;
        font-weight: 600;
        color: #475569;
    }

    .table tbody tr {
        vertical-align: middle;
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
    .badge-soft-warning { background: rgba(245, 158, 11, 0.14); color: #b45309; }
    .badge-soft-info { background: rgba(14, 165, 233, 0.12); color: #0c4a6e; }

    .empty-state-card {
        background: #ffffff;
        border: 2px dashed #cbd5f5;
        border-radius: 18px;
        padding: 48px 20px;
        text-align: center;
        color: #475569;
    }

    .pending-list-wrapper {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 20px;
    }

    .pending-list-wrapper h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 16px;
    }

    .pending-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 12px;
    }

    .pending-item:last-child {
        border-bottom: none;
    }

    .pending-item .info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .pending-item .info span {
        font-size: 13px;
        color: #475569;
    }

    .pending-item .meta {
        font-size: 12px;
        color: #64748b;
    }

    .btn-soft {
        border-radius: 12px;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-soft-primary { background: rgba(59, 130, 246, 0.12); color: #1d4ed8; border: none; }
    .btn-soft-danger { background: rgba(239, 68, 68, 0.12); color: #b91c1c; border: none; }

    .btn-soft-primary:hover { background: rgba(59, 130, 246, 0.18); color: #1d4ed8; }
    .btn-soft-danger:hover { background: rgba(239, 68, 68, 0.18); color: #b91c1c; }

    @media (max-width: 768px) {
        .exam-results-filter {
            flex-direction: column;
            align-items: stretch;
        }

        .pending-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .pending-item .actions {
            width: 100%;
        }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="exam-results-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2>نتیجه آزمون‌های دوره</h2>
                    <p class="mb-0">گزارش وضعیت آزمون‌ کاربران برای دوره‌های سازمان</p>
                </div>
                <?php if ($selectedCourse && !empty($selectedCourse['exam_tool_name'])): ?>
                    <div class="hero-meta">
                        <span class="hero-meta-badge">
                            <ion-icon name="school-outline"></ion-icon>
                            <?= htmlspecialchars($selectedCourse['exam_tool_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <?php if (!empty($selectedCourse['exam_tool_code'])): ?>
                            <span class="hero-meta-badge">
                                <ion-icon name="barcode-outline"></ion-icon>
                                <?= htmlspecialchars($selectedCourse['exam_tool_code'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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

        <?php if (empty($courses)): ?>
            <div class="empty-state-card">
                <ion-icon name="book-outline" style="font-size: 64px; color: #94a3b8; margin-bottom: 12px;"></ion-icon>
                <h4 style="font-weight: 700; margin-bottom: 8px;">هیچ دوره‌ای برای نمایش وجود ندارد</h4>
                <p class="mb-3">ابتدا یک دوره ایجاد کرده و آزمونی را برای آن تعیین کنید تا نتایج در این بخش نمایش داده شوند.</p>
                <a href="<?= UtilityHelper::baseUrl('organizations/courses'); ?>" class="btn btn-primary rounded-pill px-4">مدیریت دوره‌ها</a>
            </div>
        <?php else: ?>
            <form id="examResultsFilter" class="exam-results-filter" method="get" action="<?= htmlspecialchars($baseListUrl, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="flex-fill" style="min-width: 220px;">
                    <label for="courseSelect" class="form-label">انتخاب دوره</label>
                    <select name="course_id" id="courseSelect" class="form-select">
                        <?php foreach ($courses as $course): ?>
                            <?php $courseId = (int) ($course['id'] ?? 0); ?>
                            <option value="<?= $courseId; ?>" <?= $courseId === $selectedCourseId ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($course['title'] !== '' ? $course['title'] : 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-fill" style="min-width: 220px;">
                    <label for="searchInput" class="form-label">جستجوی کاربر</label>
                    <input type="text" id="searchInput" name="search" value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" placeholder="نام، ایمیل یا کد ارزیابی...">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">اعمال فیلتر</button>
                    <button type="button" id="resetFilters" class="btn btn-outline-secondary">بازنشانی</button>
                </div>
            </form>

            <div class="exam-results-stats">
                <div class="exam-results-card">
                    <p class="card-title">کل ثبت‌نام‌شده‌ها</p>
                    <p class="card-value"><?= UtilityHelper::englishToPersian((string) ($courseSummary['enrolled'] ?? 0)); ?></p>
                </div>
                <div class="exam-results-card">
                    <p class="card-title">آزمون تکمیل شده</p>
                    <p class="card-value"><?= UtilityHelper::englishToPersian((string) ($courseSummary['completed'] ?? 0)); ?></p>
                </div>
                <div class="exam-results-card">
                    <p class="card-title">کل محاولات ذخیره شده</p>
                    <p class="card-value"><?= UtilityHelper::englishToPersian((string) ($courseSummary['attempted'] ?? 0)); ?></p>
                </div>
                <div class="exam-results-card">
                    <p class="card-title">کاربران منتظر آزمون</p>
                    <p class="card-value"><?= UtilityHelper::englishToPersian((string) ($courseSummary['pending'] ?? 0)); ?></p>
                </div>
            </div>

            <div class="attempts-table-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">نتیجه آزمون کاربران</h3>
                    <?php if ($selectedCourse && !empty($selectedCourse['title'])): ?>
                        <span class="badge-soft badge-soft-info">
                            <ion-icon name="book-outline"></ion-icon>
                            <?= htmlspecialchars($selectedCourse['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($attempts)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">کاربر</th>
                                    <th scope="col">کد ارزیابی</th>
                                    <th scope="col">پرسش‌های پاسخ داده شده</th>
                                    <th scope="col">تاریخ تکمیل</th>
                                    <th scope="col">نوع آزمون</th>
                                    <th scope="col" class="text-end">اقدامات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attempts as $attempt): ?>
                                    <?php
                                        $attemptId = (int) ($attempt['id'] ?? 0);
                                        $answered = (int) ($attempt['answered_questions'] ?? 0);
                                        $total = (int) ($attempt['total_questions'] ?? 0);
                                        $completedLabel = $attempt['is_completed'] ? 'badge-soft-success' : 'badge-soft-warning';
                                        $questionBadge = $attempt['is_disc'] ? 'DISC' : ($attempt['question_type'] ?: 'استاندارد');
                                        $fullName = trim((string) ($attempt['name'] ?? ''));
                                        if ($fullName === '') {
                                            $fullName = 'کاربر بدون نام';
                                        }
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php if (!empty($attempt['email'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($attempt['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($attempt['evaluation_code'] !== '' ? UtilityHelper::englishToPersian((string) $attempt['evaluation_code']) : '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge-soft <?= $completedLabel; ?>">
                                                <?= UtilityHelper::englishToPersian((string) $answered); ?> از <?= UtilityHelper::englishToPersian((string) ($total > 0 ? $total : $answered)); ?>
                                            </span>
                                        </td>
                                        <td><?= $formatDateTime($attempt['completed_at'] ?? $attempt['created_at'] ?? null); ?></td>
                                        <td>
                                            <span class="badge-soft badge-soft-info" style="text-transform: uppercase;">
                                                <?= htmlspecialchars($questionBadge, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a class="btn-soft btn-soft-primary" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8'); ?>?attempt_id=<?= $attemptId; ?>">
                                                    <ion-icon name="eye-outline"></ion-icon>
                                                    مشاهده
                                                </a>
                                                <form method="post" action="<?= htmlspecialchars($resetActionUrl, ENT_QUOTES, 'UTF-8'); ?>" class="reset-attempt-form" data-user-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="attempt_id" value="<?= $attemptId; ?>">
                                                    <button type="submit" class="btn-soft btn-soft-danger">
                                                        <ion-icon name="refresh-outline"></ion-icon>
                                                        بازنشانی
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state-card" style="padding: 32px 20px; border-style: solid;">
                        <ion-icon name="checkmark-done-outline" style="font-size: 48px; color: #94a3b8; margin-bottom: 10px;"></ion-icon>
                        <p class="mb-0">هیچ نتیجه‌ای مطابق فیلترهای فعلی یافت نشد.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pending-list-wrapper">
                <h3 class="d-flex align-items-center gap-2 mb-0">
                    <ion-icon name="time-outline"></ion-icon>
                    کاربران منتظر شرکت در آزمون
                </h3>

                <?php if (!empty($pendingParticipants)): ?>
                    <div class="mt-3">
                        <?php foreach ($pendingParticipants as $participant): ?>
                            <div class="pending-item">
                                <div class="info">
                                    <strong><?= htmlspecialchars($participant['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <div class="meta">
                                        <?php $metaParts = []; ?>
                                        <?php if (!empty($participant['email'])) { $metaParts[] = htmlspecialchars($participant['email'], ENT_QUOTES, 'UTF-8'); } ?>
                                        <?php if (!empty($participant['evaluation_code'])) { $metaParts[] = 'کد: ' . UtilityHelper::englishToPersian((string) $participant['evaluation_code']); } ?>
                                        <?= !empty($metaParts) ? implode(' · ', $metaParts) : '—'; ?>
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size: 12px;">
                                    از <?= $formatDateTime($participant['enrolled_at'] ?? null); ?> در دوره ثبت‌نام شده است
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0 mt-3">همه کاربران ثبت‌نام شده آزمون خود را تکمیل کرده‌اند.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('courseSelect');
        const resetButton = document.getElementById('resetFilters');
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('examResultsFilter');

        if (courseSelect && filterForm) {
            courseSelect.addEventListener('change', function () {
                filterForm.submit();
            });
        }

        if (resetButton && filterForm) {
            resetButton.addEventListener('click', function () {
                if (courseSelect) {
                    courseSelect.selectedIndex = 0;
                }
                if (searchInput) {
                    searchInput.value = '';
                }
                filterForm.submit();
            });
        }

        document.querySelectorAll('.reset-attempt-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const userName = form.getAttribute('data-user-name') || 'این کاربر';
                const confirmation = confirm('آیا از بازنشانی نتیجه آزمون ' + userName + ' اطمینان دارید؟\nپس از بازنشانی، کاربر می‌تواند دوباره در آزمون شرکت کند.');
                if (!confirmation) {
                    event.preventDefault();
                }
            });
        });
    });
</script>

