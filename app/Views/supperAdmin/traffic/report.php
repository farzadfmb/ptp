<?php
$title = 'گزارش ترافیک';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم'];
$additional_js = [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

$summary = $trafficData['summary'] ?? [];
$activeSessions = $trafficData['active_sessions'] ?? [];
$topPages = $trafficData['top_pages_today'] ?? [];
$deviceBreakdown = $trafficData['device_breakdown'] ?? [];
$browserBreakdown = $trafficData['browser_breakdown'] ?? [];
$osBreakdown = $trafficData['os_breakdown'] ?? [];
$topReferers = $trafficData['top_referers'] ?? [];
$topUsers = $trafficData['top_users'] ?? [];
$recentEvents = $trafficData['recent_events'] ?? [];
$activityTrend = $trafficData['activity_trend'] ?? [];

$toPersian = static function ($value) {
    if (!class_exists('UtilityHelper')) {
        return $value;
    }

    return UtilityHelper::englishToPersian((string) $value);
};

$formatDuration = static function (int $seconds) use ($toPersian): string {
    if ($seconds <= 0) {
        return 'کمتر از یک دقیقه';
    }

    $minutes = intdiv($seconds, 60);
    $remainingSeconds = $seconds % 60;

    if ($minutes === 0) {
        return $toPersian($remainingSeconds) . ' ثانیه';
    }

    if ($minutes < 60) {
        if ($remainingSeconds === 0) {
            return $toPersian($minutes) . ' دقیقه';
        }
        return $toPersian($minutes) . ' دقیقه و ' . $toPersian($remainingSeconds) . ' ثانیه';
    }

    $hours = intdiv($minutes, 60);
    $minutes = $minutes % 60;
    $parts = [$toPersian($hours) . ' ساعت'];
    if ($minutes > 0) {
        $parts[] = $toPersian($minutes) . ' دقیقه';
    }
    if ($remainingSeconds > 0 && empty($parts)) {
        $parts[] = $toPersian($remainingSeconds) . ' ثانیه';
    }

    return implode(' و ', $parts);
};

$deviceLabels = [
    'mobile' => 'موبایل',
    'tablet' => 'تبلت',
    'desktop' => 'دسکتاپ',
    'bot' => 'ربات/خزشگر',
    'نامشخص' => 'نامشخص',
];

$trafficInitialJson = json_encode(
    $trafficData,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-12 text-end">
                        <div>
                            <h3 class="mb-4">گزارش ترافیک زنده</h3>
                            <p class="text-gray-600 mb-0">در این بخش می‌توانید وضعیت لحظه‌ای بازدیدکنندگان سامانه، نشست‌های فعال، صفحات پر بازدید و جزئیات دستگاه‌ها را مشاهده کنید.</p>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="text-gray-600">پنجره زمانی فعال: <?= $toPersian($activeWindowMinutes); ?> دقیقه گذشته</span>
                            <small class="text-gray-500">آخرین به‌روزرسانی: <span id="traffic-last-updated"><?= $toPersian(date('Y-m-d H:i')); ?></span></small>
                            <form class="d-flex align-items-center mt-8 gap-8" method="get">
                                <label for="window" class="text-gray-600">بازه زمانی</label>
                                <select name="window" id="window" class="form-select w-auto">
                                    <?php foreach ([5, 10, 15, 30] as $option): ?>
                                        <option value="<?= $option; ?>" <?= $option === (int) $activeWindowMinutes ? 'selected' : ''; ?>><?= $toPersian($option); ?> دقیقه</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-main rounded-pill px-16">اعمال</button>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/traffic-report'); ?>" class="btn btn-light rounded-pill px-16">بازنشانی</a>
                            </form>
                            <div class="d-flex flex-wrap align-items-center justify-content-end mt-3 gap-2">
                                <button type="button" id="traffic-sound-toggle" class="btn btn-danger text-white rounded-pill px-16">قطع صدا</button>
                                <button type="button" id="traffic-sound-test" class="btn btn-primary text-white rounded-pill px-16">تست صدا</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-12">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-end">
                                <span class="text-gray-500 d-block mb-4">بازدیدکنندگان آنلاین</span>
                                <h2 class="mb-0 text-main-600"><span id="traffic-summary-online-total"><?= $toPersian($summary['online_total'] ?? 0); ?></span></h2>
                                <small class="text-gray-500">کاربران وارد شده: <span id="traffic-summary-online-logged"><?= $toPersian($summary['online_logged_in'] ?? 0); ?></span> | مهمان: <span id="traffic-summary-online-guests"><?= $toPersian($summary['online_guests'] ?? 0); ?></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-end">
                                <span class="text-gray-500 d-block mb-4">بازدید در ۱۵ دقیقه اخیر</span>
                                <h2 class="mb-0 text-indigo-600"><span id="traffic-summary-views-15"><?= $toPersian($summary['views_last_15_minutes'] ?? 0); ?></span></h2>
                                <small class="text-gray-500">بازدید در یک ساعت اخیر: <span id="traffic-summary-views-60"><?= $toPersian($summary['views_last_hour'] ?? 0); ?></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-end">
                                <span class="text-gray-500 d-block mb-4">بازدید امروز</span>
                                <h2 class="mb-0 text-success-600"><span id="traffic-summary-views-today"><?= $toPersian($summary['page_views_today'] ?? 0); ?></span></h2>
                                <small class="text-gray-500">بازدیدکنندگان یکتا امروز: <span id="traffic-summary-unique-today"><?= $toPersian($summary['unique_today'] ?? 0); ?></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-end">
                                <span class="text-gray-500 d-block mb-4">میانگین زمان نشست</span>
                                <?php $avgDuration = (int) ($summary['avg_session_duration'] ?? 0); ?>
                                <h2 class="mb-0 text-danger-600"><span id="traffic-summary-avg-duration"><?= htmlspecialchars($formatDuration($avgDuration), ENT_QUOTES, 'UTF-8'); ?></span></h2>
                                <small class="text-gray-500">میانگین درخواست هر نشست: <span id="traffic-summary-avg-requests"><?= $toPersian($summary['avg_requests_per_session'] ?? 0); ?></span></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">روند روزانه بازدیدکنندگان یکتا</h5>
                        <span class="text-gray-500 small">نمایش ۱۴ روز گذشته</span>
                    </div>
                    <div class="card-body" style="min-height: 260px;">
                        <canvas id="traffic-daily-trend" height="240" aria-label="نمودار روند بازدیدکنندگان یکتا"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">نشست‌های فعال</h5>
                        <span class="badge bg-main-50 text-main-600"><span id="traffic-active-session-count"><?= $toPersian(count($activeSessions)); ?></span> نشست فعال</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle text-end mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>کاربر</th>
                                    <th>صفحه فعلی</th>
                                    <th>دستگاه و مرورگر</th>
                                    <th>نشست</th>
                                    <th>آخرین فعالیت</th>
                                    <th>آی‌پی</th>
                                </tr>
                            </thead>
                            <tbody id="traffic-sessions-body">
                                <?php if (empty($activeSessions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-32 text-gray-500">هیچ نشست فعالی در بازه انتخاب‌شده یافت نشد.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activeSessions as $session): ?>
                                        <?php
                                        $deviceKey = $session['device_type'] ?? 'نامشخص';
                                        $deviceLabel = $deviceLabels[$deviceKey] ?? ($deviceKey ?: 'نامشخص');
                                        $isLoggedIn = $session['is_logged_in'] ? 'کاربر وارد شده' : 'مهمان';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900">
                                                        <?= htmlspecialchars($session['user_name'] ?? '---', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <small class="text-gray-500">
                                                        <?= $session['is_logged_in'] ? 'شناسه: ' . $toPersian($session['user_id'] ?? '---') : 'مهمان'; ?>
                                                        <?php if (!empty($session['role'])): ?>
                                                            | نقش: <?= htmlspecialchars($session['role'], ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900" dir="ltr">
                                                        <?= htmlspecialchars($session['last_path'] ?? '/', ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <?php if (!empty($session['referer'])): ?>
                                                        <small class="text-gray-500" dir="ltr">ارجاع از: <?= htmlspecialchars($session['referer'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($deviceLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500">مرورگر: <?= htmlspecialchars($session['browser'] ?? '---', ENT_QUOTES, 'UTF-8'); ?> | سیستم‌عامل: <?= htmlspecialchars($session['os'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge rounded-pill <?= $session['is_logged_in'] ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-800'; ?>"><?= htmlspecialchars($isLoggedIn, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500">مدت زمان: <?= htmlspecialchars($formatDuration((int) $session['session_duration_seconds']), ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <small class="text-gray-500">تعداد درخواست: <?= $toPersian($session['requests_count'] ?? 0); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($toPersian($session['last_activity_relative'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($session['last_activity'])): ?>
                                                        <small class="text-gray-500"><?= $toPersian(date('Y-m-d H:i', strtotime($session['last_activity']))); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td dir="ltr">
                                                <span class="text-gray-700"><?= htmlspecialchars($session['ip'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">دستگاه‌ها</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($deviceBreakdown)); ?> نوع</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($deviceBreakdown)): ?>
                            <p class="text-gray-500 text-end mb-0">اطلاعاتی برای نمایش وجود ندارد.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-12">
                                <?php foreach ($deviceBreakdown as $item): ?>
                                    <?php
                                    $labelKey = $item['label'] ?? 'نامشخص';
                                    $label = $deviceLabels[$labelKey] ?? $labelKey;
                                    ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <span class="fw-semibold text-gray-900"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="text-gray-600"><?= $toPersian($item['total'] ?? 0); ?> نفر (<?= $toPersian($item['percentage'] ?? 0); ?>٪)</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-main-500" role="progressbar" style="width: <?= min(100, max(0, (float) ($item['percentage'] ?? 0))); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">مرورگرها</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($browserBreakdown)); ?> نوع</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($browserBreakdown)): ?>
                            <p class="text-gray-500 text-end mb-0">اطلاعاتی برای نمایش وجود ندارد.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-12">
                                <?php foreach ($browserBreakdown as $item): ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <span class="fw-semibold text-gray-900"><?= htmlspecialchars($item['label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="text-gray-600"><?= $toPersian($item['total'] ?? 0); ?> نفر (<?= $toPersian($item['percentage'] ?? 0); ?>٪)</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-indigo-500" role="progressbar" style="width: <?= min(100, max(0, (float) ($item['percentage'] ?? 0))); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">سیستم‌عامل‌ها</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($osBreakdown)); ?> نوع</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($osBreakdown)): ?>
                            <p class="text-gray-500 text-end mb-0">اطلاعاتی برای نمایش وجود ندارد.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-12">
                                <?php foreach ($osBreakdown as $item): ?>
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <span class="fw-semibold text-gray-900"><?= htmlspecialchars($item['label'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="text-gray-600"><?= $toPersian($item['total'] ?? 0); ?> نفر (<?= $toPersian($item['percentage'] ?? 0); ?>٪)</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success-500" role="progressbar" style="width: <?= min(100, max(0, (float) ($item['percentage'] ?? 0))); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">صفحات برتر امروز</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($topPages)); ?> مسیر</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topPages)): ?>
                            <p class="text-gray-500 text-end mb-0">هنوز داده‌ای ثبت نشده است.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topPages as $page): ?>
                                    <div class="list-group-item py-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-end">
                                                <strong class="d-block text-gray-900" dir="ltr"><?= htmlspecialchars($page['path'] ?? '/', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small class="text-gray-500">بازدید: <?= $toPersian($page['views'] ?? 0); ?></small>
                                            </div>
                                            <span class="badge bg-main-50 text-main-600"><?= $toPersian($page['percentage'] ?? 0); ?>٪</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">کاربران پر تعامل (۲۴ ساعت اخیر)</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($topUsers)); ?> نفر</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topUsers)): ?>
                            <p class="text-gray-500 text-end mb-0">هنوز کاربر فعالی شناسایی نشده است.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topUsers as $userRow): ?>
                                    <div class="list-group-item py-12 d-flex justify-content-between align-items-center">
                                        <div class="text-end">
                                            <strong class="d-block text-gray-900"><?= htmlspecialchars($userRow['user_name'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small class="text-gray-500">شناسه: <?= $toPersian($userRow['user_id'] ?? 0); ?><?= !empty($userRow['role']) ? ' | نقش: ' . htmlspecialchars($userRow['role'], ENT_QUOTES, 'UTF-8') : ''; ?></small>
                                        </div>
                                        <span class="badge bg-indigo-50 text-indigo-700">بازدید: <?= $toPersian($userRow['views'] ?? 0); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">ارجاع‌دهندگان برتر (۲۴ ساعت اخیر)</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($topReferers)); ?> منبع</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($topReferers)): ?>
                            <p class="text-gray-500 text-end mb-0">هیچ ارجاع‌دهنده معتبری ثبت نشده است.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topReferers as $referer): ?>
                                    <div class="list-group-item py-12 d-flex justify-content-between align-items-center">
                                        <span class="text-gray-900" dir="ltr"><?= htmlspecialchars($referer['referer'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="badge bg-success-50 text-success-700"><?= $toPersian($referer['total'] ?? 0); ?> بازدید</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">روند فعالیت (۶۰ دقیقه اخیر)</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($activityTrend)); ?> بازه</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activityTrend)): ?>
                            <p class="text-gray-500 text-end mb-0">هنوز داده‌ای ثبت نشده است.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle text-end mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>بازه زمانی</th>
                                            <th>بازدید</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activityTrend as $row): ?>
                                            <tr>
                                                <td><?= $toPersian(date('H:i', strtotime($row['bucket']))); ?></td>
                                                <td><?= $toPersian($row['total'] ?? 0); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">آخرین رویدادهای ثبت‌شده</h5>
                        <span class="badge bg-gray-100 text-gray-700"><?= $toPersian(count($recentEvents)); ?> رویداد اخیر</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle text-end mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>زمان</th>
                                    <th>کاربر</th>
                                    <th>مسیر</th>
                                    <th>دستگاه</th>
                                    <th>ارجاع</th>
                                    <th>آی‌پی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentEvents)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-32 text-gray-500">هنوز رویدادی ثبت نشده است.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentEvents as $event): ?>
                                        <?php
                                        $deviceKey = $event['device_type'] ?? 'نامشخص';
                                        $deviceLabel = $deviceLabels[$deviceKey] ?? ($deviceKey ?: 'نامشخص');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($toPersian($event['created_at_relative'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($event['created_at'])): ?>
                                                        <small class="text-gray-500"><?= $toPersian(date('Y-m-d H:i', strtotime($event['created_at']))); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($event['user_name'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if (!empty($event['role'])): ?>
                                                        <small class="text-gray-500">نقش: <?= htmlspecialchars($event['role'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td dir="ltr">
                                                <span class="text-gray-900"><?= htmlspecialchars($event['path'] ?? '/', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= htmlspecialchars($deviceLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <small class="text-gray-500">مرورگر: <?= htmlspecialchars($event['browser'] ?? '---', ENT_QUOTES, 'UTF-8'); ?> | سیستم‌عامل: <?= htmlspecialchars($event['os'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></small>
                                                </div>
                                            </td>
                                            <td dir="ltr">
                                                <span class="text-gray-600"><?= htmlspecialchars($event['referer'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td dir="ltr">
                                                <span class="text-gray-700"><?= htmlspecialchars($event['ip'] ?? '---', ENT_QUOTES, 'UTF-8'); ?></span>
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

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.fetch) {
        return;
    }

    const trafficInitialData = <?= $trafficInitialJson ?: 'null'; ?> || {};
    const trafficWindowMinutes = <?= (int) $activeWindowMinutes; ?>;
    const trafficDataEndpoint = '<?= UtilityHelper::baseUrl('supperadmin/traffic-report/live'); ?>';
    const deviceLabels = <?= json_encode($deviceLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const refreshInterval = 8000;

    const elements = {
        onlineTotal: document.getElementById('traffic-summary-online-total'),
        onlineLogged: document.getElementById('traffic-summary-online-logged'),
        onlineGuests: document.getElementById('traffic-summary-online-guests'),
        views15: document.getElementById('traffic-summary-views-15'),
        views60: document.getElementById('traffic-summary-views-60'),
        viewsToday: document.getElementById('traffic-summary-views-today'),
        uniqueToday: document.getElementById('traffic-summary-unique-today'),
        avgDuration: document.getElementById('traffic-summary-avg-duration'),
        avgRequests: document.getElementById('traffic-summary-avg-requests'),
        sessionsBody: document.getElementById('traffic-sessions-body'),
        sessionsCount: document.getElementById('traffic-active-session-count'),
        lastUpdated: document.getElementById('traffic-last-updated')
    };

    const dailyTrendCanvas = document.getElementById('traffic-daily-trend');
    let dailyTrendChart = null;

    const soundControls = {
        toggle: document.getElementById('traffic-sound-toggle'),
        test: document.getElementById('traffic-sound-test'),
    };

    const numberMap = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    function englishToPersian(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value).replace(/[0-9]/g, function (digit) {
            return numberMap[Number(digit)] || digit;
        });
    }

    function setText(node, value) {
        if (!node) {
            return;
        }
        node.textContent = englishToPersian(value === undefined ? '' : value);
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value).replace(/[&<>"']/g, function (char) {
            switch (char) {
                case '&': return '&amp;';
                case '<': return '&lt;';
                case '>': return '&gt;';
                case '"': return '&quot;';
                case '\'': return '&#39;';
                default: return char;
            }
        });
    }

    function formatDurationLabel(totalSeconds) {
        const seconds = Math.max(0, parseInt(totalSeconds, 10) || 0);
        if (seconds <= 0) {
            return 'کمتر از یک دقیقه';
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        if (minutes === 0) {
            return `${remainingSeconds} ثانیه`;
        }

        if (minutes < 60) {
            if (remainingSeconds === 0) {
                return `${minutes} دقیقه`;
            }
            return `${minutes} دقیقه و ${remainingSeconds} ثانیه`;
        }

        const hours = Math.floor(minutes / 60);
        const finalMinutes = minutes % 60;
        const parts = [`${hours} ساعت`];
        if (finalMinutes > 0) {
            parts.push(`${finalMinutes} دقیقه`);
        }
        return parts.join(' و ');
    }

    function formatAbsoluteDate(value) {
        if (!value) {
            return '';
        }

        const normalized = String(value).replace(' ', 'T');
        const date = new Date(normalized);
        if (Number.isNaN(date.getTime())) {
            return englishToPersian(value);
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return englishToPersian(`${year}-${month}-${day} ${hours}:${minutes}`);
    }

    function renderSummary(summary) {
        const onlineTotal = Number((summary && summary.online_total) || 0);
        setText(elements.onlineTotal, onlineTotal);
        setText(elements.onlineLogged, summary ? summary.online_logged_in || 0 : 0);
        setText(elements.onlineGuests, summary ? summary.online_guests || 0 : 0);
        setText(elements.views15, summary ? summary.views_last_15_minutes || 0 : 0);
        setText(elements.views60, summary ? summary.views_last_hour || 0 : 0);
        setText(elements.viewsToday, summary ? summary.page_views_today || 0 : 0);
        setText(elements.uniqueToday, summary ? summary.unique_today || 0 : 0);

        if (elements.avgDuration) {
            const label = formatDurationLabel(summary ? summary.avg_session_duration || 0 : 0);
            elements.avgDuration.textContent = englishToPersian(label);
        }

        setText(elements.avgRequests, summary ? summary.avg_requests_per_session || 0 : 0);

        return onlineTotal;
    }

    function renderSessions(sessions) {
        if (!elements.sessionsBody) {
            return 0;
        }

        if (!Array.isArray(sessions) || sessions.length === 0) {
            elements.sessionsBody.innerHTML = '<tr><td colspan="6" class="text-center py-32 text-gray-500">هیچ نشست فعالی در بازه انتخاب‌شده یافت نشد.</td></tr>';
            setText(elements.sessionsCount, 0);
            return 0;
        }

        const rows = sessions.map(function (session) {
            const deviceKey = session && session.device_type ? session.device_type : 'نامشخص';
            const deviceLabel = deviceLabels[deviceKey] || deviceKey || 'نامشخص';
            const isLoggedIn = session && session.is_logged_in ? true : false;
            const badgeClass = isLoggedIn ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-800';
            const userIdValue = session && session.user_id !== undefined && session.user_id !== null && session.user_id !== ''
                ? session.user_id
                : '---';
            const identity = isLoggedIn
                ? 'شناسه: ' + englishToPersian(userIdValue)
                : 'مهمان';
            const roleInfo = session && session.role ? ' | نقش: ' + escapeHtml(session.role) : '';
            const referer = session && session.referer ? escapeHtml(session.referer) : '';
            const lastRelative = session && session.last_activity_relative ? englishToPersian(session.last_activity_relative) : '-';
            const lastAbsolute = session && session.last_activity ? formatAbsoluteDate(session.last_activity) : '';
            const durationLabel = session && typeof session.session_duration_label === 'string'
                ? englishToPersian(session.session_duration_label)
                : englishToPersian(formatDurationLabel(session && session.session_duration_seconds ? session.session_duration_seconds : 0));
            const requestsCount = session && session.requests_count ? session.requests_count : 0;

            return '<tr>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-semibold text-gray-900">' + escapeHtml(session && session.user_name ? session.user_name : '---') + '</span>' +
                        '<small class="text-gray-500">' + identity + roleInfo + '</small>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-semibold text-gray-900" dir="ltr">' + escapeHtml((session && session.last_path) || '/') + '</span>' +
                        (referer ? '<small class="text-gray-500" dir="ltr">ارجاع از: ' + referer + '</small>' : '') +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-semibold text-gray-900">' + escapeHtml(deviceLabel) + '</span>' +
                        '<small class="text-gray-500">مرورگر: ' + escapeHtml(session && session.browser ? session.browser : '---') + ' | سیستم‌عامل: ' + escapeHtml(session && session.os ? session.os : '---') + '</small>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="badge rounded-pill ' + badgeClass + '">' + (isLoggedIn ? 'کاربر وارد شده' : 'مهمان') + '</span>' +
                        '<small class="text-gray-500">مدت زمان: ' + durationLabel + '</small>' +
                        '<small class="text-gray-500">تعداد درخواست: ' + englishToPersian(requestsCount) + '</small>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<div class="d-flex flex-column">' +
                        '<span class="fw-semibold text-gray-900">' + lastRelative + '</span>' +
                        (lastAbsolute ? '<small class="text-gray-500">' + lastAbsolute + '</small>' : '') +
                    '</div>' +
                '</td>' +
                '<td dir="ltr">' +
                    '<span class="text-gray-700">' + escapeHtml(session && session.ip ? session.ip : '---') + '</span>' +
                '</td>' +
            '</tr>';
        }).join('');

        elements.sessionsBody.innerHTML = rows;
        setText(elements.sessionsCount, sessions.length);
        return sessions.length;
    }

    function renderDailyTrend(trend) {
        if (!dailyTrendCanvas || typeof Chart === 'undefined') {
            return;
        }

        const safeTrend = Array.isArray(trend) ? trend : [];
        const labels = [];
        const values = [];

        safeTrend.forEach(function (row) {
            const dateValue = row && row.date ? String(row.date) : '';
            labels.push(dateValue);
            values.push(Number((row && row.unique_visitors) || 0));
        });

        if (labels.length === 0) {
            const today = new Date();
            const fallbackDate = today.toISOString().slice(0, 10);
            labels.push(fallbackDate);
            values.push(0);
        }

        if (dailyTrendChart) {
            dailyTrendChart.data.labels = labels;
            dailyTrendChart.data.datasets[0].data = values;
            dailyTrendChart.update();
            return;
        }

        dailyTrendChart = new Chart(dailyTrendCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'بازدیدکنندگان یکتا',
                        data: values,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#4f46e5',
                        pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                const item = tooltipItems && tooltipItems.length ? tooltipItems[0] : null;
                                const label = item && item.label ? item.label : '';
                                return englishToPersian(label);
                            },
                            label: function (context) {
                                const value = context && context.parsed && context.parsed.y ? context.parsed.y : 0;
                                return 'بازدیدکننده یکتا: ' + englishToPersian(value);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 8,
                            callback: function (value, index) {
                                const label = this && typeof this.getLabelForValue === 'function'
                                    ? this.getLabelForValue(value)
                                    : (labels[index] || '');
                                return englishToPersian(label);
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: function (value) {
                                return englishToPersian(value);
                            }
                        }
                    }
                }
            }
        });
    }

    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    const audioStorageKey = 'trafficAudioMuted';
    let audioContext = null;
    let audioEnabled = !!AudioContextClass;
    let audioMuted = false;

    if (audioEnabled) {
        try {
            const stored = window.localStorage ? window.localStorage.getItem(audioStorageKey) : null;
            audioMuted = stored === '1';
        } catch (error) {
            audioMuted = false;
        }
    }

    function persistAudioPreference() {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(audioStorageKey, audioMuted ? '1' : '0');
            }
        } catch (error) {
            // ignore storage errors silently
        }
    }

    function updateAudioControlsState() {
        if (!soundControls.toggle) {
            return;
        }

        if (!audioEnabled) {
            soundControls.toggle.textContent = 'صدا پشتیبانی نمی‌شود';
            soundControls.toggle.disabled = true;
            soundControls.toggle.classList.remove('btn-danger', 'btn-success');
            soundControls.toggle.classList.add('btn-secondary');
            if (soundControls.test) {
                soundControls.test.disabled = true;
            }
            return;
        }

        soundControls.toggle.disabled = false;
        soundControls.toggle.textContent = audioMuted ? 'فعال‌سازی صدا' : 'قطع صدا';
        soundControls.toggle.classList.remove('btn-secondary');
        soundControls.toggle.classList.toggle('btn-danger', !audioMuted);
        soundControls.toggle.classList.toggle('btn-success', audioMuted);

        if (soundControls.test) {
            soundControls.test.disabled = false;
        }
    }

    function detachAudioPrimers() {
        document.removeEventListener('click', primeAudio);
        document.removeEventListener('keydown', primeAudio);
        document.removeEventListener('touchstart', primeAudio);
    }

    function primeAudio() {
        if (!audioEnabled) {
            return;
        }

        try {
            if (!audioContext) {
                audioContext = new AudioContextClass();
            }

            if (audioContext.state === 'suspended') {
                audioContext.resume().then(function () {
                    if (audioContext.state === 'running') {
                        detachAudioPrimers();
                    }
                }).catch(function (error) {
                    console.debug('traffic notification audio resume blocked', error);
                });
            } else if (audioContext.state === 'running') {
                detachAudioPrimers();
            }
        } catch (error) {
            console.error('traffic notification audio init failed', error);
            audioEnabled = false;
            detachAudioPrimers();
            updateAudioControlsState();
        }
    }

    if (audioEnabled) {
        document.addEventListener('click', primeAudio);
        document.addEventListener('keydown', primeAudio);
        document.addEventListener('touchstart', primeAudio, { passive: true });
    }

    function playNotification(options) {
        if (!audioEnabled) {
            return;
        }

        const force = options && options.force === true;

        if (!force && audioMuted) {
            return;
        }

        primeAudio();

        if (!audioContext || audioContext.state !== 'running') {
            return;
        }

        try {
            const start = audioContext.currentTime;
            const tones = [
                { frequency: 880, duration: 0.28, type: 'square', offset: 0, gain: 0.55 },
                { frequency: 1320, duration: 0.26, type: 'sawtooth', offset: 0.22, gain: 0.45 },
                { frequency: 1760, duration: 0.22, type: 'triangle', offset: 0.44, gain: 0.38 }
            ];

            tones.forEach(function (tone) {
                const osc = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                osc.type = tone.type || 'sine';
                osc.frequency.setValueAtTime(tone.frequency, start + tone.offset);

                const peakGain = Math.min(Math.max(tone.gain || 0.45, 0.05), 0.95);
                gainNode.gain.setValueAtTime(0.0, start + tone.offset);
                gainNode.gain.linearRampToValueAtTime(peakGain, start + tone.offset + 0.03);
                gainNode.gain.linearRampToValueAtTime(0.0001, start + tone.offset + tone.duration);

                osc.connect(gainNode);
                gainNode.connect(audioContext.destination);

                osc.start(start + tone.offset);
                osc.stop(start + tone.offset + tone.duration + 0.04);
            });
        } catch (error) {
            console.error('traffic notification sound failed', error);
            audioEnabled = false;
            detachAudioPrimers();
            updateAudioControlsState();
        }
    }

    if (soundControls.toggle) {
        soundControls.toggle.addEventListener('click', function () {
            if (!audioEnabled) {
                return;
            }

            audioMuted = !audioMuted;
            persistAudioPreference();

            if (!audioMuted) {
                primeAudio();
            }

            updateAudioControlsState();
        });
    }

    if (soundControls.test) {
        soundControls.test.addEventListener('click', function () {
            if (!audioEnabled) {
                return;
            }

            primeAudio();
            playNotification({ force: true });
        });
    }

    updateAudioControlsState();

    let lastOnlineTotal = 0;
    if (trafficInitialData && trafficInitialData.summary && typeof trafficInitialData.summary.online_total !== 'undefined') {
        lastOnlineTotal = Number(trafficInitialData.summary.online_total) || 0;
    }

    lastOnlineTotal = renderSummary(trafficInitialData.summary || {});
    renderSessions(trafficInitialData.active_sessions || []);
    renderDailyTrend(trafficInitialData.daily_unique_trend || []);

    let refreshTimer = null;
    let isFetching = false;

    function scheduleNext() {
        if (refreshTimer) {
            window.clearTimeout(refreshTimer);
        }
        refreshTimer = window.setTimeout(fetchAndUpdate, refreshInterval);
    }

    function updateLastUpdatedLabel(value) {
        if (!elements.lastUpdated) {
            return;
        }
        elements.lastUpdated.textContent = formatAbsoluteDate(value) || englishToPersian(value || '');
    }

    async function fetchAndUpdate() {
        if (isFetching) {
            if (!document.hidden) {
                scheduleNext();
            }
            return;
        }

        isFetching = true;
        try {
            const url = trafficDataEndpoint + '?window=' + trafficWindowMinutes + '&_t=' + Date.now();
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const payload = await response.json();
            if (!payload || payload.success === false) {
                throw new Error('Invalid response');
            }

            const data = payload.data || {};
            const summary = data.summary || {};
            const sessions = data.active_sessions || [];

            const currentOnline = renderSummary(summary);
            renderSessions(sessions);
            renderDailyTrend(data.daily_unique_trend || []);

            if (typeof payload.generated_at === 'string') {
                updateLastUpdatedLabel(payload.generated_at);
            }

            if (currentOnline > lastOnlineTotal) {
                playNotification();
            }

            lastOnlineTotal = currentOnline;
        } catch (error) {
            console.error('live traffic update failed', error);
        } finally {
            isFetching = false;
            if (!document.hidden) {
                scheduleNext();
            } else {
                refreshTimer = null;
            }
        }
    }

    scheduleNext();

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (refreshTimer) {
                window.clearTimeout(refreshTimer);
                refreshTimer = null;
            }
        } else if (!refreshTimer) {
            fetchAndUpdate();
        }
    });
});
</script>
