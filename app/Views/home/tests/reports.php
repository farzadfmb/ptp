<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'گزارشات';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$reports = isset($reports) && is_array($reports) ? $reports : [];
$summaryStats = isset($summaryStats) && is_array($summaryStats) ? $summaryStats : ['total' => 0, 'completed' => 0, 'certificates' => 0];

$inline_styles .= <<<'CSS'
.reports-card {
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 8px 24px rgba(15,23,42,0.08);
}
.reports-summary-chip {
    border-radius: 14px;
    padding: 14px 18px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    height: 100%;
}
.reports-summary-chip .label {
    font-size: 0.9rem;
    color: #64748b;
}
.reports-summary-chip .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}
.reports-status-badge {
    font-size: 0.82rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
}
.reports-table {
    min-width: 900px;
}
CSS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
?>
<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card reports-card border-0">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h1 class="h4 mb-2">گزارشات</h1>
                            <p class="mb-0 text-secondary">وضعیت ارزیابی‌های شما و دسترسی به گواهی‌ها را در این بخش دنبال کنید.</p>
                        </div>
                        <div class="text-end small text-secondary">
                            <span>تاریخ بروزرسانی:</span>
                            <span><?= htmlspecialchars(UtilityHelper::getTodayDate(), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="reports-summary-chip d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="label">کل ارزیابی‌های اختصاص داده شده</div>
                                        <div class="value"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($summaryStats['total'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2">فعال</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="reports-summary-chip d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="label">ارزیابی‌های تکمیل‌شده</div>
                                        <div class="value text-success"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($summaryStats['completed'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <span class="badge bg-success-subtle text-success px-3 py-2">تکمیل شده</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="reports-summary-chip d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="label">گواهی‌های آماده دریافت</div>
                                        <div class="value text-info"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($summaryStats['certificates'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <span class="badge bg-info-subtle text-info px-3 py-2">قابل دریافت</span>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle reports-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:5%">#</th>
                                        <th style="width:28%">عنوان ارزیابی</th>
                                        <th style="width:12%">تاریخ</th>
                                        <th style="width:18%">پیشرفت آزمون‌ها</th>
                                        <th style="width:18%">وضعیت نمایش</th>
                                        <th style="width:19%">گواهی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($reports)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-secondary">هنوز ارزیابی فعالی برای شما ثبت نشده است.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reports as $index => $item):
                                        $completedCount = (int)($item['completed_tools_count'] ?? 0);
                                        $totalCount = (int)($item['total_tools_count'] ?? 0);
                                        $hasTools = $totalCount > 0;
                                        $allToolsCompleted = !empty($item['all_tools_completed']);
                                        $washupDone = !empty($item['washup_completed']);
                                        $isVisible = !empty($item['is_visible']);
                                        $hasCertificate = !empty($item['has_certificate_access']);
                                        $certificateUrl = (string)($item['certificate_url'] ?? '');
                                        $completedNames = is_array($item['completed_tool_names'] ?? null) ? implode('، ', $item['completed_tool_names']) : '';
                                        $incompleteNames = is_array($item['incomplete_tool_names'] ?? null) ? implode('، ', $item['incomplete_tool_names']) : '';

                                        $progressLabel = '—';
                                        if ($hasTools) {
                                            $progressLabel = UtilityHelper::englishToPersian((string)$completedCount) . ' / ' . UtilityHelper::englishToPersian((string)$totalCount);
                                        }

                                        $statusClass = 'bg-secondary-subtle text-secondary';
                                        $statusText = 'در انتظار شروع';

                                        if ($hasCertificate) {
                                            $statusClass = 'bg-success-subtle text-success';
                                            $statusText = 'گواهی آماده دریافت';
                                        } elseif (!$isVisible) {
                                            $statusClass = 'bg-warning-subtle text-warning';
                                            $statusText = 'در انتظار تایید مدیر';
                                        } elseif (!$washupDone) {
                                            $statusClass = 'bg-warning-subtle text-warning';
                                            $statusText = 'در انتظار تکمیل WashUp';
                                        } elseif ($allToolsCompleted) {
                                            $statusClass = 'bg-success-subtle text-success';
                                            $statusText = 'آزمون‌ها تکمیل شده است';
                                        } elseif ($completedCount > 0) {
                                            $statusClass = 'bg-primary-subtle text-primary';
                                            $statusText = 'در حال تکمیل';
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-center fw-semibold"><?= htmlspecialchars(UtilityHelper::englishToPersian((string)($index + 1)), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark mb-1"><?= htmlspecialchars($item['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?></div>
                                            <div class="text-secondary small">مدل: <?= htmlspecialchars($item['model'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($item['evaluation_date'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="mb-1 fw-semibold">پیشرفت: <?= htmlspecialchars($progressLabel, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php if ($completedNames !== ''): ?>
                                                <div class="text-success small">تکمیل شده: <?= htmlspecialchars($completedNames, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                            <?php if ($incompleteNames !== ''): ?>
                                                <div class="text-danger small">باقی‌مانده: <?= htmlspecialchars($incompleteNames, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                            <?php if ($completedNames === '' && $incompleteNames === '' && !$hasTools): ?>
                                                <div class="text-muted small">برای این ارزیابی آزمونی تعریف نشده است.</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="reports-status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($hasCertificate && $certificateUrl !== ''): ?>
                                                <a href="<?= htmlspecialchars($certificateUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-success rounded-pill px-3">مشاهده گواهی</a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>در دسترس نیست</button>
                                                <?php if (!$isVisible): ?>
                                                    <div class="text-muted small mt-1">منتظر تایید مدیر سازمان.</div>
                                                <?php elseif (!$washupDone): ?>
                                                    <div class="text-muted small mt-1">پس از ثبت نتایج WashUp فعال می‌شود.</div>
                                                <?php elseif (!$allToolsCompleted): ?>
                                                    <div class="text-muted small mt-1">لطفاً تمامی آزمون‌ها را تکمیل کنید.</div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info rounded-16 mt-4" role="alert">
                            <div class="fw-semibold mb-1">راهنما</div>
                            <div class="small mb-0">برای دریافت گواهی لازم است تمام آزمون‌های برنامه را تکمیل کرده باشید، مرحله WashUp توسط مدیر شما نهایی شده باشد و دسترسی مشاهده گواهی برایتان فعال شده باشد.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
