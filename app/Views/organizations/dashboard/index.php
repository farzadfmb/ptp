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

$additional_css[] = 'public/assets/css/apexcharts.css';
$additional_js[] = 'public/assets/js/apexcharts.min.js';

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

$creditUsageChart = $creditUsageChart ?? ['used' => 0, 'remaining' => 0];
$summaryCards = $summaryCards ?? [];
$monthlyOverview = $monthlyOverview ?? [];
$upcomingEvaluations = $upcomingEvaluations ?? [];
$recentActivities = $recentActivities ?? [];
$quickLinks = $quickLinks ?? [];
$organization = $organization ?? ['name' => 'سازمان نمونه'];
$organizationSubtitle = $organizationSubtitle ?? 'Organization Dashboard';
$organizationCode = $organizationCode ?? 'ORG-000';
$organizationSubdomain = $organizationSubdomain ?? '---';

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

$inline_scripts .= <<<JS
    document.addEventListener('DOMContentLoaded', function () {
        const creditUsageSeries = $creditSeriesJson;
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

        if (document.querySelector('#credit-usage-chart')) {
            const creditChart = new ApexCharts(document.querySelector('#credit-usage-chart'), creditUsageOptions);
            creditChart.render();
        }

        const participantLabels = $participantLabelsJson;
        const participantSeries = $participantSeriesJson;
        const scoreSeries = $scoreSeriesJson;

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

        if (document.querySelector('#participants-chart')) {
            const participantsChart = new ApexCharts(document.querySelector('#participants-chart'), participantsOptions);
            participantsChart.render();
        }

        // Periodic exams (last 6 months)
        const periodicLabels = $periodicExamsLabelsJson;
        const periodicSeries = $periodicExamsSeriesJson;
        if (document.querySelector('#periodic-exams-chart')) {
            const options = {
                chart: { type: 'bar', height: 280, fontFamily: 'IRANSans, Tahoma, sans-serif', toolbar: { show: false } },
                series: [{ name: 'آزمون‌های دوره‌ای', data: periodicSeries }],
                colors: ['#10b981'],
                xaxis: { categories: periodicLabels },
                dataLabels: { enabled: false },
                plotOptions: { bar: { borderRadius: 6, columnWidth: '40%' } },
                tooltip: { y: { formatter: (v) => (typeof v === 'number' ? v : Number(v || 0)).toLocaleString('fa-IR') } }
            };
            const chart = new ApexCharts(document.querySelector('#periodic-exams-chart'), options);
            chart.render();
        }

        // Exams conducted this month (per day)
        const monthlyLabels = $monthlyExamsLabelsJson;
        const monthlySeries = $monthlyExamsSeriesJson;
        if (document.querySelector('#monthly-exams-chart')) {
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
            const chart = new ApexCharts(document.querySelector('#monthly-exams-chart'), options);
            chart.render();
        }
    });
JS;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
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

            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-24">
                            <div class="d-flex align-items-center gap-16">
                                <div class="d-flex flex-wrap gap-12">
                                    <a href="<?= UtilityHelper::baseUrl('supperadmin/organizations/edit?id=' . ($organization['id'] ?? 0)); ?>" class="btn btn-outline-main rounded-pill px-20 d-flex align-items-center gap-8">
                                        <i class="fas fa-edit"></i>
                                        مدیریت سازمان
                                    </a>
                                    <a href="<?= UtilityHelper::baseUrl('user/logout'); ?>" class="btn btn-danger rounded-pill px-20 d-flex align-items-center gap-8">
                                        <i class="fas fa-sign-out-alt"></i>
                                        خروج
                                    </a>
                                </div>
                                <div class="text-end">
                                    <h2 class="mb-4 text-gray-900">داشبورد سازمانی - <?= htmlspecialchars($organization['name']); ?></h2>
                                    <p class="mb-0 text-gray-500">
                                        کد سازمان: <span class="fw-semibold text-gray-700"><?= UtilityHelper::englishToPersian($organizationCode); ?></span>
                                        <span class="mx-12">|</span>
                                        ساب‌دومین: <span class="fw-semibold text-gray-700"><?= UtilityHelper::englishToPersian($organizationSubdomain); ?></span>
                                    </p>
                                </div>
                                <div class="w-72 h-72 rounded-20 bg-main-100 text-main-600 flex-center fs-2 fw-bold">
                                    <?= UtilityHelper::englishToPersian(mb_substr($organization['name'] ?? 'سازمان', 0, 1)); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                        <h6 class="mb-0 text-gray-900"><?= $formatMoney($creditUsageChart['used'] ?? 0); ?></h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-10">
                                    <span class="w-12 h-12 rounded-circle bg-success-500 d-inline-block"></span>
                                    <div>
                                        <p class="mb-1 text-gray-500 text-sm">اعتبار باقی‌مانده</p>
                                        <h6 class="mb-0 text-gray-900"><?= $formatMoney($creditUsageChart['remaining'] ?? 0); ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>

