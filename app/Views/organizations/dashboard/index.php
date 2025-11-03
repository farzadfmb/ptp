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

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .hover-shadow {\n        transition: all 0.3s ease;\n    }\n    .hover-shadow:hover {\n        transform: translateY(-4px);\n        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;\n    }\n    .transition {\n        transition: all 0.3s ease;\n    }\n    .flex-center {\n        display: flex;\n        align-items: center;\n        justify-content: center;\n    }\n";

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
$weakCompetencyChart = $weakCompetencyChart ?? ['labels' => [], 'series' => []];
$strongCompetencyChart = $strongCompetencyChart ?? ['labels' => [], 'series' => []];

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
$weakCompetencyLabelsJson = json_encode($weakCompetencyChart['labels'], JSON_UNESCAPED_UNICODE);
$weakCompetencySeriesJson = json_encode($weakCompetencyChart['series'], JSON_UNESCAPED_UNICODE);
$strongCompetencyLabelsJson = json_encode($strongCompetencyChart['labels'], JSON_UNESCAPED_UNICODE);
$strongCompetencySeriesJson = json_encode($strongCompetencyChart['series'], JSON_UNESCAPED_UNICODE);

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

        // Competency performance charts
        const weakLabelsRaw = $weakCompetencyLabelsJson;
        const weakSeriesRaw = $weakCompetencySeriesJson;
        const strongLabelsRaw = $strongCompetencyLabelsJson;
        const strongSeriesRaw = $strongCompetencySeriesJson;

        const weakLabels = Array.isArray(weakLabelsRaw) ? weakLabelsRaw : [];
        const weakSeries = Array.isArray(weakSeriesRaw) ? weakSeriesRaw.map((value) => Number(value)) : [];
        const strongLabels = Array.isArray(strongLabelsRaw) ? strongLabelsRaw : [];
        const strongSeries = Array.isArray(strongSeriesRaw) ? strongSeriesRaw.map((value) => Number(value)) : [];

        const weakChartContainer = document.querySelector('#weak-competency-chart');
        if (weakChartContainer) {
            if (weakLabels.length > 0 && weakSeries.some((value) => !Number.isNaN(value))) {
                const options = {
                    chart: { type: 'bar', height: 280, fontFamily: 'IRANSans, Tahoma, sans-serif', toolbar: { show: false } },
                    series: [{ name: 'میانگین امتیاز', data: weakSeries }],
                    colors: ['#ef4444'],
                    xaxis: { categories: weakLabels, labels: { rotate: -45 } },
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                    tooltip: {
                        y: {
                            formatter: (value) => {
                                const num = typeof value === 'number' ? value : Number(value || 0);
                                return num.toFixed(1) + ' امتیاز';
                            }
                        }
                    }
                };
                const chart = new ApexCharts(weakChartContainer, options);
                chart.render();
            } else {
                weakChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                weakChartContainer.innerHTML = '<span>شایستگی با میانگین کمتر از ۶۰ برای نمایش موجود نیست.</span>';
            }
        }

        const strongChartContainer = document.querySelector('#strong-competency-chart');
        if (strongChartContainer) {
            if (strongLabels.length > 0 && strongSeries.some((value) => !Number.isNaN(value))) {
                const options = {
                    chart: { type: 'bar', height: 280, fontFamily: 'IRANSans, Tahoma, sans-serif', toolbar: { show: false } },
                    series: [{ name: 'میانگین امتیاز', data: strongSeries }],
                    colors: ['#22c55e'],
                    xaxis: { categories: strongLabels, labels: { rotate: -45 } },
                    dataLabels: { enabled: false },
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                    tooltip: {
                        y: {
                            formatter: (value) => {
                                const num = typeof value === 'number' ? value : Number(value || 0);
                                return num.toFixed(1) + ' امتیاز';
                            }
                        }
                    }
                };
                const chart = new ApexCharts(strongChartContainer, options);
                chart.render();
            } else {
                strongChartContainer.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-gray-400');
                strongChartContainer.innerHTML = '<span>شایستگی با میانگین بالای ۶۰ برای نمایش موجود نیست.</span>';
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
            <!-- Statistics Cards -->
            <?php if (!empty($summaryCards)): ?>
                <?php foreach ($summaryCards as $card): ?>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm rounded-24 h-100 hover-shadow transition">
                            <div class="card-body p-24">
                                <div class="d-flex align-items-start justify-content-between mb-16">
                                    <div class="w-56 h-56 rounded-circle bg-main-50 text-main-600 flex-center">
                                        <i class="fas fa-<?= htmlspecialchars($card['icon'] ?? 'chart-bar'); ?> fs-4"></i>
                                    </div>
                                    <span class="badge bg-success-50 text-success-600 rounded-pill px-12 py-6">فعال</span>
                                </div>
                                <h6 class="text-gray-500 mb-8 fw-normal"><?= htmlspecialchars($card['label']); ?></h6>
                                <h2 class="mb-0 text-gray-900 fw-bold"><?= $card['value']; ?></h2>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Charts: Periodic Exams (6 months) -->
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

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-thermometer-quarter ms-10 text-danger"></i>
                                شایستگی‌های نیازمند تقویت 
                            </h4>
                            <span class="text-gray-400 text-sm">بر اساس امتیاز توافقی</span>
                        </div>
                        <div id="weak-competency-chart" style="max-width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-16">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-trophy ms-10 text-success"></i>
                                شایستگی‌های برتر
                            </h4>
                            <span class="text-gray-400 text-sm">بر اساس امتیاز توافقی</span>
                        </div>
                        <div id="strong-competency-chart" style="max-width: 100%; height: 280px;"></div>
                    </div>
                </div>
            </div>

            <!-- Competency Models Showcase -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex justify-content-between align-items-center mb-20">
                            <h4 class="mb-0 text-gray-900">
                                <i class="fas fa-layer-group ms-10 text-main-500"></i>
                                تصاویر مدل‌های شایستگی
                            </h4>
                            <a href="<?= UtilityHelper::baseUrl('organizations/competency-models'); ?>" class="btn btn-sm btn-main rounded-pill px-20">
                                <i class="fas fa-cog ms-6"></i>
                                مدیریت مدل‌ها
                            </a>
                        </div>
                        <?php if (!empty($competencyModelShowcase)): ?>
                            <div class="row g-20">
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
                                    <div class="col-xl-4 col-lg-6">
                                        <div class="card border-0 shadow-sm rounded-20 h-100 hover-shadow transition">
                                            <div class="position-relative overflow-hidden d-flex align-items-center justify-content-center" style="height: 240px; border-radius: 20px 20px 0 0; background: #f8fafc;">
                                                <?php if (!empty($imageUrl)): ?>
                                                    <img src="<?= htmlspecialchars($imageUrl); ?>" 
                                                         alt="<?= htmlspecialchars($model['title']); ?>" 
                                                         class="w-100 h-100" 
                                                         style="object-fit: contain; object-position: center;">
                                                <?php else: ?>
                                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        <i class="fas fa-image text-white mb-12" style="font-size: 48px; opacity: 0.5;"></i>
                                                        <span class="text-white opacity-75">تصویری ثبت نشده</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body p-20">
                                                <h5 class="mb-12 text-gray-900 fw-bold"><?= htmlspecialchars($model['title']); ?></h5>
                                                <div class="d-flex align-items-center justify-content-between text-sm">
                                                    <?php if ($codeLabel !== ''): ?>
                                                        <span class="badge bg-main-50 text-main-600 rounded-pill px-12 py-6">
                                                            <i class="fas fa-hashtag ms-4"></i>
                                                            <?= htmlspecialchars(UtilityHelper::englishToPersian($codeLabel)); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($updatedLabel !== ''): ?>
                                                        <span class="text-gray-400 text-xs">
                                                            <i class="fas fa-calendar-alt ms-4"></i>
                                                            <?= htmlspecialchars($updatedLabel); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-64">
                                <div class="mb-20">
                                    <i class="fas fa-layer-group text-gray-300" style="font-size: 64px;"></i>
                                </div>
                                <h5 class="text-gray-500 mb-12">هنوز مدل شایستگی‌ای ایجاد نشده است</h5>
                                <p class="text-gray-400 mb-20">برای شروع، اولین مدل شایستگی خود را ایجاد کنید.</p>
                                <a href="<?= UtilityHelper::baseUrl('organizations/competency-models'); ?>" class="btn btn-main rounded-pill px-24">
                                    <i class="fas fa-plus ms-6"></i>
                                    ایجاد مدل جدید
                                </a>
                            </div>
                        <?php endif; ?>
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

