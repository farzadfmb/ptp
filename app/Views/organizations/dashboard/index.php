<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'داشبورد سازمان';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com'
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$accountSourceRaw = (string)($user['account_source'] ?? '');
$accountSource = $accountSourceRaw !== '' && function_exists('mb_strtolower')
    ? mb_strtolower($accountSourceRaw, 'UTF-8')
    : strtolower($accountSourceRaw);
$isOwnerAccount = ($accountSource === 'organizations');

if ($isOwnerAccount) {
    $additional_css[] = 'public/assets/css/apexcharts.css';
    $additional_js[] = 'public/assets/js/apexcharts.min.js';
}

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;

$formatMoney = $formatMoney ?? function ($value) {
    if ($value === null || $value === '') {
        return 'نامشخص';
    }

    $formatted = number_format((float) $value, 0, '.', ',');
    return UtilityHelper::englishToPersian($formatted) . ' تومان';
};

$creditUsageChart = $creditUsageChart ?? ['used' => 0, 'remaining' => 0, 'total' => 0];
$summaryCards = $summaryCards ?? [];
$monthlyOverview = $monthlyOverview ?? [];
$upcomingEvaluations = $upcomingEvaluations ?? [];
$recentActivities = $recentActivities ?? [];
$quickLinks = $quickLinks ?? [];
$competencyModelShowcase = $competencyModelShowcase ?? [];
$organization = $organization ?? ['name' => 'سازمان نمونه'];
$organizationSubtitle = $organizationSubtitle ?? 'Organization Dashboard';
$organizationCode = $organizationCode ?? 'ORG-000';
$organizationSubdomain = $organizationSubdomain ?? '---';

$creditUsedDisplay = $creditUsageChart['used_display'] ?? $creditUsageChart['used'] ?? null;
$creditRemainingDisplay = $creditUsageChart['remaining_display'] ?? $creditUsageChart['remaining'] ?? null;
$creditParticipantsCompleted = (int) ($creditUsageChart['completed_participations'] ?? 0);

$creditSeriesJson = json_encode([
    (float) ($creditUsageChart['used'] ?? 0),
    (float) ($creditUsageChart['remaining'] ?? 0),
], JSON_UNESCAPED_UNICODE);
$participantLabelsJson = json_encode(array_column($monthlyOverview, 'label'), JSON_UNESCAPED_UNICODE);
$participantSeriesJson = json_encode(array_column($monthlyOverview, 'participants'), JSON_UNESCAPED_UNICODE);
$scoreSeriesJson = json_encode(array_column($monthlyOverview, 'average_score'), JSON_UNESCAPED_UNICODE);

// New charts data coming from controller
$periodicExamsLabelsJson = json_encode($periodicExamsLabels ?? [], JSON_UNESCAPED_UNICODE);
$periodicExamsSeriesJson = json_encode($periodicExamsSeries ?? [], JSON_UNESCAPED_UNICODE);
$monthlyExamsLabelsJson = json_encode($monthlyExamsLabels ?? [], JSON_UNESCAPED_UNICODE);
$monthlyExamsSeriesJson = json_encode($monthlyExamsSeries ?? [], JSON_UNESCAPED_UNICODE);

if ($isOwnerAccount) {
$inline_scripts .= <<<JS
    document.addEventListener('DOMContentLoaded', function () {
    const creditUsageRaw = $creditSeriesJson;
    const creditUsageSeries = Array.isArray(creditUsageRaw) ? creditUsageRaw : [];
        const creditUsageOptions = {
            chart: {
                type: 'donut',
                fontFamily: 'IRANSans, Tahoma, sans-serif',
                toolbar: { show: false },
            },
            labels: ['اعتبار مصرف شده', 'اعتبار باقی مانده'],
            series: creditUsageSeries,
            colors: ['#f97316', '#22c55e'],
            legend: {
                position: 'bottom',
                fontFamily: 'IRANSans, Tahoma, sans-serif',
            },
            dataLabels: {
                formatter: function (val) {
                    return val.toFixed(1) + '%';
                }
            }
        };

        const creditChartContainer = document.querySelector('#credit-usage-chart');
        if (creditChartContainer) {
            const hasCreditData = Array.isArray(creditUsageSeries) && creditUsageSeries.some((value) => Number(value) > 0);

            if (hasCreditData) {
                const creditChart = new ApexCharts(creditChartContainer, creditUsageOptions);
                creditChart.render();
            } else {
                creditChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                creditChartContainer.innerHTML = '<span>داده‌ای برای نمایش نمودار اعتباری وجود ندارد.</span>';
            }
        }

    const participantLabelsRaw = $participantLabelsJson;
    const participantSeriesRaw = $participantSeriesJson;
    const scoreSeriesRaw = $scoreSeriesJson;

    const participantLabels = Array.isArray(participantLabelsRaw) ? participantLabelsRaw : [];
    const participantSeries = Array.isArray(participantSeriesRaw) ? participantSeriesRaw : [];
    const scoreSeries = Array.isArray(scoreSeriesRaw) ? scoreSeriesRaw : [];

        const participantsOptions = {
            chart: {
                type: 'area',
                height: 280,
                fontFamily: 'IRANSans, Tahoma, sans-serif',
                toolbar: { show: false },
            },
            series: [
                { name: 'تعداد شرکت‌کنندگان', data: participantSeries },
                { name: 'میانگین امتیاز', data: scoreSeries }
            ],
            colors: ['#2563eb', '#f59e0b'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: participantLabels },
            tooltip: {
                y: {
                    formatter: function (value, context) {
                        if (context.seriesIndex === 0) {
                            return value + ' نفر';
                        }
                        return value + ' امتیاز';
                    }
                }
            }
        };

        const participantsChartContainer = document.querySelector('#participants-chart');
        if (participantsChartContainer) {
            const participantNumbers = participantSeries.map((value) => Number(value));
            const scoreNumbers = scoreSeries.map((value) => Number(value));
            const hasParticipantData = participantLabels.length > 0 && (
                participantNumbers.some((value) => value > 0) ||
                scoreNumbers.some((value) => value > 0)
            );

            if (hasParticipantData) {
                const participantsChart = new ApexCharts(participantsChartContainer, participantsOptions);
                participantsChart.render();
            } else {
                participantsChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                participantsChartContainer.innerHTML = '<span>داده‌ای برای روند مشارکت کارکنان ثبت نشده است.</span>';
            }
        }

        // Periodic exams (last 6 months)
        const periodicLabelsRaw = $periodicExamsLabelsJson;
        const periodicSeriesRaw = $periodicExamsSeriesJson;
        const periodicLabels = Array.isArray(periodicLabelsRaw) ? periodicLabelsRaw : [];
        const periodicSeries = Array.isArray(periodicSeriesRaw) ? periodicSeriesRaw.map((value) => Number(value)) : [];

        const periodicChartContainer = document.querySelector('#periodic-exams-chart');
        if (periodicChartContainer) {
            if (periodicLabels.length > 0) {
                const options = {
                    chart: { type: 'bar', height: 280, fontFamily: 'IRANSans, Tahoma, sans-serif', toolbar: { show: false } },
                    series: [{ name: 'آزمون‌های دوره‌ای', data: periodicSeries }],
                    colors: ['#10b981'],
                    xaxis: { categories: periodicLabels },
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '40%' } },
                    tooltip: { y: { formatter: (v) => (typeof v === 'number' ? v : Number(v || 0)).toLocaleString('fa-IR') } }
                };
                const chart = new ApexCharts(periodicChartContainer, options);
                chart.render();
            } else {
                periodicChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                periodicChartContainer.innerHTML = '<span>اطلاعاتی برای نمودار آزمون‌های دوره‌ای موجود نیست.</span>';
            }
        }

        // Exams conducted this month (per day)
        const monthlyLabelsRaw = $monthlyExamsLabelsJson;
        const monthlySeriesRaw = $monthlyExamsSeriesJson;
        const monthlyLabels = Array.isArray(monthlyLabelsRaw) ? monthlyLabelsRaw : [];
        const monthlySeries = Array.isArray(monthlySeriesRaw) ? monthlySeriesRaw.map((value) => Number(value)) : [];

        const monthlyChartContainer = document.querySelector('#monthly-exams-chart');
        if (monthlyChartContainer) {
            if (monthlyLabels.length > 0) {
                const options = {
                    chart: { type: 'line', height: 280, fontFamily: 'IRANSans, Tahoma, sans-serif', toolbar: { show: false } },
                    series: [{ name: 'آزمون‌های برگزار شده', data: monthlySeries }],
                    colors: ['#8b5cf6'],
                    stroke: { width: 3, curve: 'smooth' },
                    xaxis: { categories: monthlyLabels },
                    dataLabels: { enabled: false },
                    markers: { size: 4 },
                    tooltip: { y: { formatter: (v) => (typeof v === 'number' ? v : Number(v || 0)).toLocaleString('fa-IR') } }
                };
                const chart = new ApexCharts(monthlyChartContainer, options);
                chart.render();
            } else {
                monthlyChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                monthlyChartContainer.innerHTML = '<span>برای این ماه داده‌ای جهت نمایش نمودار ثبت نشده است.</span>';
            }
        }
    });
JS;
}
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <?php if ($isOwnerAccount): ?>
        <div class="row g-4">
            <!-- KPI: Users/Evaluations summary -->
            <?php if (!empty($summaryCards)): ?>
                <?php foreach (array_slice($summaryCards, 0, 4) as $card): ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-24 h-100">
                            <div class="card-body p-24">
                                <span class="text-gray-500 text-sm d-block mb-8"><?= htmlspecialchars($card['label']); ?></span>
                                <h3 class="mb-0 text-gray-900 fw-bold"><?= $card['value']; ?></h3>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>



            <?php if (!empty($summaryCards)): ?>
                <?php foreach (array_slice($summaryCards, 4) as $card): ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-24 h-100">
                            <div class="card-body p-24">
                                <div class="d-flex justify-content-between align-items-start mb-12">
                                    <span class="text-gray-500 text-sm"><?= htmlspecialchars($card['label']); ?></span>
                                    <?php if (!empty($card['trend'])): ?>
                                        <span class="badge bg-main-50 text-main-600 rounded-pill">
                                            <i class="fas fa-chart-line ms-4"></i>
                                            <?= htmlspecialchars($card['trend']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="mb-12 text-gray-900 fw-bold"><?= $card['value']; ?></h3>
                                <div class="progress rounded-pill bg-gray-100" style="height: 8px;">
                                    <div class="progress-bar bg-main-500" role="progressbar" style="width: <?= min(100, max(0, ($card['percent'] ?? 0))); ?>%;"></div>
                                </div>
                                <div class="d-flex justify-content-between text-gray-400 text-xs mt-10">
                                    <span>ماه جاری</span>
                                    <span>ماه گذشته</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-donut-small ms-10 text-main-500"></i>
                                وضعیت اعتبار سازمان
                            </h4>
                            <span class="badge bg-success-50 text-success-600 rounded-pill">پایداری مطلوب</span>
                        </div>
                        <div id="credit-usage-chart" style="max-width: 100%; height: 280px;"></div>
                        <div class="row mt-16">
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-10">
                                    <span class="w-12 h-12 rounded-circle bg-danger-500 d-inline-block"></span>
                                    <div>
                                        <p class="mb-1 text-gray-500 text-sm">اعتبار مصرف‌شده</p>
                                        <h6 class="mb-0 text-gray-900"><?= $formatMoney($creditUsedDisplay); ?></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-10">
                                    <span class="w-12 h-12 rounded-circle bg-success-500 d-inline-block"></span>
                                    <div>
                                        <p class="mb-1 text-gray-500 text-sm">اعتبار باقی‌مانده</p>
                                        <h6 class="mb-0 text-gray-900"><?= $formatMoney($creditRemainingDisplay); ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($creditParticipantsCompleted > 0): ?>
                            <div class="text-gray-400 text-sm mt-16 text-end">
                                <i class="fas fa-user-check ms-6"></i>
                                <?= UtilityHelper::englishToPersian(number_format($creditParticipantsCompleted, 0)); ?> آزمون تکمیل شده در این دوره محاسبه شده است.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-chart-area ms-10 text-main-500"></i>
                                روند مشارکت کارکنان
                            </h4>
                            <span class="text-gray-400 text-sm">۶ ماه گذشته</span>
                        </div>
                        <div id="participants-chart" style="max-width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>

            <!-- New charts: periodic exams + exams in current month -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-calendar-alt ms-10 text-main-500"></i>
                                آزمون‌های دوره‌ای (۶ ماه)
                            </h4>
                        </div>
                        <div id="periodic-exams-chart" style="max-width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-chart-line ms-10 text-main-500"></i>
                                آزمون‌های برگزار شده در ماه
                            </h4>
                            <span class="text-gray-400 text-sm">روزهای ماه جاری</span>
                        </div>
                        <div id="monthly-exams-chart" style="max-width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-calendar-check ms-10 text-main-500"></i>
                                ارزیابی‌های پیش‌رو
                            </h4>
                            <a href="#" class="btn btn-sm btn-outline-main rounded-pill px-16">تقویم کامل</a>
                        </div>
                        <div class="timeline">
                            <?php foreach ($upcomingEvaluations as $evaluation): ?>
                                <div class="timeline-item mb-16">
                                    <div class="timeline-badge bg-main-500 text-white">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h6 class="mb-0 text-gray-900"><?= htmlspecialchars($evaluation['title']); ?></h6>
                                            <span class="badge bg-main-50 text-main-600 rounded-pill"><?= htmlspecialchars($evaluation['status']); ?></span>
                                        </div>
                                        <p class="mb-0 text-gray-500 text-sm">تاریخ اجرا: <?= htmlspecialchars($evaluation['date']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($upcomingEvaluations)): ?>
                                <div class="text-center py-40 text-gray-400">
                                    ارزیابی برنامه‌ریزی‌شده‌ای وجود ندارد.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-stream ms-10 text-main-500"></i>
                                فعالیت‌های اخیر
                            </h4>
                            <a href="#" class="btn btn-sm btn-outline-gray rounded-pill px-16">مشاهده همه</a>
                        </div>
                        <div class="d-flex flex-column gap-16">
                            <?php foreach ($recentActivities as $activity): ?>
                                <div class="border border-gray-100 rounded-16 p-16 d-flex align-items-center gap-12">
                                    <div class="w-40 h-40 rounded-circle bg-main-50 text-main-600 flex-center">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <h6 class="mb-4 text-gray-900"><?= htmlspecialchars($activity['description']); ?></h6>
                                        <span class="text-gray-400 text-sm"><?= htmlspecialchars($activity['time']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($recentActivities)): ?>
                                <div class="text-center py-40 text-gray-400">
                                    فعالیتی ثبت نشده است.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-layer-group ms-10 text-main-500"></i>
                                گالری مدل‌های شایستگی
                            </h4>
                            <a href="<?= UtilityHelper::baseUrl('organizations/competency-models'); ?>" class="btn btn-sm btn-outline-main rounded-pill px-16">
                                مدیریت مدل‌ها
                            </a>
                        </div>
                        <?php if (!empty($competencyModelShowcase)): ?>
                            <div class="row g-16">
                                <?php foreach ($competencyModelShowcase as $model): ?>
                                    <?php
                                    $rawImagePath = trim((string) ($model['image_path'] ?? ''));
                                    $imageUrl = $rawImagePath;
                                    if (!empty($rawImagePath) && empty($model['is_external'])) {
                                        $normalizedPath = ltrim($rawImagePath, '/');
                                        if (stripos($normalizedPath, 'public/') === 0) {
                                            $publicPath = $normalizedPath;
                                        } else {
                                            $publicPath = 'public/' . $normalizedPath;
                                        }
                                        $imageUrl = UtilityHelper::baseUrl($publicPath);
                                    }

                                    $codeLabel = trim((string) ($model['code'] ?? ''));
                                    $updatedLabel = '';
                                    $updatedRaw = $model['updated_at'] ?? null;
                                    if (!empty($updatedRaw)) {
                                        try {
                                            $updatedDate = new DateTime($updatedRaw, new DateTimeZone('Asia/Tehran'));
                                            $updatedLabel = UtilityHelper::englishToPersian($updatedDate->format('Y/m/d'));
                                        } catch (Exception $exception) {
                                            $updatedLabel = UtilityHelper::englishToPersian(date('Y/m/d', strtotime($updatedRaw)));
                                        }
                                    }
                                    ?>
                                    <div class="col-xl-4 col-md-6">
                                        <div class="h-100 border border-gray-100 rounded-24 p-16 d-flex flex-column gap-12">
                                            <div class="w-100" style="height: 200px; border-radius: 18px; overflow: hidden; background: #f1f5f9;">
                                                <?php if (!empty($imageUrl)): ?>
                                                    <img src="<?= htmlspecialchars($imageUrl); ?>" alt="<?= htmlspecialchars($model['title']); ?>" class="w-100 h-100" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-gray-400">
                                                        تصویری ثبت نشده است.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1 text-end">
                                                <h6 class="mb-6 text-gray-900 fw-bold"><?= htmlspecialchars($model['title']); ?></h6>
                                                <?php if ($codeLabel !== ''): ?>
                                                    <p class="mb-2 text-gray-400 text-sm">کد مدل: <?= htmlspecialchars(UtilityHelper::englishToPersian($codeLabel)); ?></p>
                                                <?php endif; ?>
                                                <?php if ($updatedLabel !== ''): ?>
                                                    <p class="mb-0 text-gray-400 text-xs">آخرین بروزرسانی: <?= htmlspecialchars($updatedLabel); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-gray-400 py-40">
                                تاکنون تصویری برای مدل‌های شایستگی ثبت نشده است.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-th-large ms-10 text-main-500"></i>
                                میانبرهای سریع
                            </h4>
                        </div>
                        <div class="row g-16">
                            <?php foreach ($quickLinks as $link): ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <a href="<?= htmlspecialchars($link['url']); ?>" class="card shadow-sm border-0 rounded-20 h-100 hover-shadow p-20 d-block text-end">
                                        <div class="d-flex justify-content-between align-items-center mb-12">
                                            <span class="w-44 h-44 rounded-circle bg-main-50 text-main-600 flex-center">
                                                <i class="fas fa-arrow-circle-left"></i>
                                            </span>
                                            <i class="fas fa-angle-left text-gray-300"></i>
                                        </div>
                                        <h6 class="text-gray-900 mb-0"><?= htmlspecialchars($link['label']); ?></h6>
                                    </a>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($quickLinks)): ?>
                                <div class="col-12 text-center text-gray-400 py-32">
                                    میانبری تعریف نشده است.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-32 text-center">
                        <h3 class="text-gray-900 fw-bold mb-16">به پنل سازمان خوش آمدید</h3>
                        <p class="text-gray-600 mb-0" style="line-height:1.9;">
                            این بخش شامل آمار، نمودارها و گزارش‌های مدیریتی است و تنها برای مالک سازمان در دسترس می‌باشد.
                            در صورتی که نیاز به مشاهده این اطلاعات دارید، لطفاً با مالک سازمان هماهنگ کنید یا از او بخواهید دسترسی لازم را برای شما فعال کند.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>

