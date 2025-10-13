<?php
// Set page specific variables
$title = 'داشبورد ادمین - صفحه اصلی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];

// Add dashboard-specific JavaScript
$additional_js = ['public/assets/js/dashboard-charts.js'];

// Include header and sidebar
include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

$metrics = [
    'totalOrganizations' => 128,
    'newOrganizationsThisMonth' => 12,
    'totalUsers' => 4580,
    'activeUsers' => 4120,
    'inactiveUsers' => 460,
    'totalExamsHeld' => 1820,
    'examsToday' => 18,
    'examsThisMonth' => 245,
    'totalRevenue' => 874500000,
    'revenueGrowth' => 18,
];

$activePercentage = $metrics['totalUsers'] > 0 ? round(($metrics['activeUsers'] / $metrics['totalUsers']) * 100) : 0;
$inactivePercentage = max(0, 100 - $activePercentage);

$creditUsage = [
    ['label' => 'سازمان آلفا', 'value' => 14800000],
    ['label' => 'گروه برنا', 'value' => 11200000],
    ['label' => 'شرکت نوین داده', 'value' => 9300000],
    ['label' => 'هلدینگ پارس آوا', 'value' => 7600000],
    ['label' => 'فناوری آسمان', 'value' => 5400000],
];

$creditUsageColors = ['#3D7FF9', '#27CFA7', '#FA902F', '#6142FF', '#FF6B6B'];

$topOrganizations = [
    ['name' => 'سازمان آلفا', 'industry' => 'فناوری', 'exams' => 128, 'credit' => 15800000],
    ['name' => 'گروه برنا', 'industry' => 'آموزش', 'exams' => 115, 'credit' => 12400000],
    ['name' => 'شرکت نوین داده', 'industry' => 'داده و تحلیل', 'exams' => 104, 'credit' => 9700000],
    ['name' => 'هلدینگ پارس آوا', 'industry' => 'صنعتی', 'exams' => 96, 'credit' => 8600000],
    ['name' => 'فناوری آسمان', 'industry' => 'نرم‌افزار', 'exams' => 88, 'credit' => 7200000],
    ['name' => 'رسالت پژوهان', 'industry' => 'آموزش عالی', 'exams' => 81, 'credit' => 6100000],
    ['name' => 'راهکاران توسعه', 'industry' => 'مشاوره', 'exams' => 74, 'credit' => 5200000],
    ['name' => 'سلامت نگار', 'industry' => 'سلامت', 'exams' => 69, 'credit' => 4800000],
    ['name' => 'اندیشه سبز', 'industry' => 'منابع انسانی', 'exams' => 63, 'credit' => 4400000],
    ['name' => 'موسسه افق', 'industry' => 'آموزش', 'exams' => 58, 'credit' => 3900000],
];

$latestTransactions = [
    ['organization' => 'سازمان آلفا', 'amount' => 6500000, 'reference' => 'TRX-98423', 'date' => '2025-09-29 14:20', 'status' => 'settled'],
    ['organization' => 'گروه برنا', 'amount' => 4200000, 'reference' => 'TRX-98411', 'date' => '2025-09-29 10:05', 'status' => 'pending'],
    ['organization' => 'شرکت نوین داده', 'amount' => 3100000, 'reference' => 'TRX-98388', 'date' => '2025-09-28 18:42', 'status' => 'settled'],
    ['organization' => 'هلدینگ پارس آوا', 'amount' => 2700000, 'reference' => 'TRX-98354', 'date' => '2025-09-28 12:17', 'status' => 'failed'],
    ['organization' => 'فناوری آسمان', 'amount' => 1950000, 'reference' => 'TRX-98321', 'date' => '2025-09-27 09:30', 'status' => 'settled'],
];

$latestTickets = [
    ['subject' => 'مشکل اتصال به آزمون منطقه ۳', 'organization' => 'مدارس آتیه', 'priority' => 'high', 'opened_at' => '2025-09-30 09:05', 'status' => 'در انتظار پاسخ'],
    ['subject' => 'درخواست افزودن نقش جدید', 'organization' => 'راهکاران توسعه', 'priority' => 'medium', 'opened_at' => '2025-09-29 21:40', 'status' => 'در حال بررسی'],
    ['subject' => 'خطا در گزارش مالی ماهانه', 'organization' => 'سازمان آلفا', 'priority' => 'high', 'opened_at' => '2025-09-29 17:15', 'status' => 'پاسخ داده شد'],
    ['subject' => 'عدم نمایش پیامک تایید', 'organization' => 'فناوری آسمان', 'priority' => 'low', 'opened_at' => '2025-09-28 11:32', 'status' => 'در انتظار پاسخ'],
];

$userActivityLogs = [
    ['user' => 'لیلا محسنی', 'role' => 'مدیر سازمان آلفا', 'action' => 'ورود موفق به پنل', 'time' => '2025-09-30 08:55', 'icon' => 'fa-sign-in-alt'],
    ['user' => 'رضا قاسمی', 'role' => 'کارشناس پشتیبانی', 'action' => 'حذف آزمون تکراری', 'time' => '2025-09-30 08:12', 'icon' => 'fa-trash-restore'],
    ['user' => 'سمانه رفیعی', 'role' => 'مدیر گروه برنا', 'action' => 'ایجاد آزمون جدید ریاضی', 'time' => '2025-09-29 23:10', 'icon' => 'fa-plus-circle'],
    ['user' => 'امیررضا جعفری', 'role' => 'ادمین سیستم', 'action' => 'تغییر سطح دسترسی کاربر', 'time' => '2025-09-29 22:04', 'icon' => 'fa-user-shield'],
    ['user' => 'مهسا پاکدل', 'role' => 'کارشناس مالی', 'action' => 'تایید تراکنش سازمان فناوری آسمان', 'time' => '2025-09-29 19:47', 'icon' => 'fa-check-circle'],
];

$creditUsageData = htmlspecialchars(json_encode($creditUsage, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$creditUsageColorsData = htmlspecialchars(json_encode($creditUsageColors, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$formatNumber = function ($number) {
    return UtilityHelper::englishToPersian(number_format($number));
};

$formatCurrency = function ($amount) use ($formatNumber) {
    return $formatNumber($amount) . ' تومان';
};

$formatDateTime = function ($dateTime) {
    return UtilityHelper::englishToPersian(date('H:i Y/m/d', strtotime($dateTime)));
};
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <!-- Metric Widgets -->
            <div class="col-12">
                <div class="row gy-4">
                    <div class="col-xxl-3 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="flex-between gap-12 align-items-start">
                                    <span class="flex-shrink-0 w-48 h-48 flex-center rounded-circle bg-main-600 text-white text-2xl"><i class="fas fa-building"></i></span>
                                    <span class="badge bg-success-50 text-success-600 rounded-pill fw-semibold">
                                        <?= UtilityHelper::englishToPersian('+' . $metrics['newOrganizationsThisMonth']); ?> این ماه
                                    </span>
                                </div>
                                <h4 class="mt-20 mb-8"><?= $formatNumber($metrics['totalOrganizations']); ?></h4>
                                <p class="text-gray-600 mb-16">تعداد کل سازمان‌های فعال</p>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/organizations?filter=new'); ?>" class="text-13 fw-semibold text-main-600 d-inline-flex align-items-center gap-4">
                                    مشاهده سازمان‌های جدید
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="flex-between gap-12 align-items-start">
                                    <span class="flex-shrink-0 w-48 h-48 flex-center rounded-circle bg-main-two-600 text-white text-2xl"><i class="fas fa-users"></i></span>
                                    <span class="badge bg-primary-50 text-primary-600 rounded-pill fw-semibold">
                                        <?= UtilityHelper::englishToPersian($activePercentage . '%'); ?> فعال
                                    </span>
                                </div>
                                <h4 class="mt-20 mb-8"><?= $formatNumber($metrics['totalUsers']); ?></h4>
                                <p class="text-gray-600 mb-16">کاربران ثبت‌شده</p>
                                <div class="d-flex gap-16 flex-wrap text-13">
                                    <span class="text-success-600 fw-semibold">فعال: <?= $formatNumber($metrics['activeUsers']); ?></span>
                                    <span class="text-gray-400">غیرفعال: <?= $formatNumber($metrics['inactiveUsers']); ?> (<?= UtilityHelper::englishToPersian($inactivePercentage . '%'); ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="flex-between gap-12 align-items-start">
                                    <span class="flex-shrink-0 w-48 h-48 flex-center rounded-circle bg-purple-600 text-white text-2xl"><i class="fas fa-clipboard-check"></i></span>
                                    <span class="badge bg-info-50 text-info-600 rounded-pill fw-semibold">
                                        <?= UtilityHelper::englishToPersian('+' . $metrics['examsToday']); ?> امروز
                                    </span>
                                </div>
                                <h4 class="mt-20 mb-8"><?= $formatNumber($metrics['totalExamsHeld']); ?></h4>
                                <p class="text-gray-600 mb-16">آزمون‌های برگزار شده</p>
                                <div class="d-flex gap-16 flex-wrap text-13">
                                    <span class="text-main-600 fw-semibold">امروز: <?= $formatNumber($metrics['examsToday']); ?></span>
                                    <span class="text-gray-400">این ماه: <?= $formatNumber($metrics['examsThisMonth']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3 col-sm-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="flex-between gap-12 align-items-start">
                                    <span class="flex-shrink-0 w-48 h-48 flex-center rounded-circle bg-warning-600 text-white text-2xl"><i class="fas fa-wallet"></i></span>
                                    <span class="badge bg-success-50 text-success-600 rounded-pill fw-semibold">
                                        <?= UtilityHelper::englishToPersian('+' . $metrics['revenueGrowth'] . '%'); ?> رشد
                                    </span>
                                </div>
                                <h4 class="mt-20 mb-8"><?= $formatCurrency($metrics['totalRevenue']); ?></h4>
                                <p class="text-gray-600 mb-16">درآمد کل سیستم</p>
                                <span class="text-13 text-gray-400">مجموع تراکنش‌های تایید شده تا امروز</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit usage vs Top orgs -->
            <div class="col-xxl-8 col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="flex-between flex-wrap gap-8 mb-20">
                            <h4 class="mb-0">اعتبار مصرف شده توسط سازمان‌ها</h4>
                            <span class="text-13 text-gray-400">نمایش توزیع ۳۰ روز گذشته</span>
                        </div>
                        <div id="creditUsageChart" data-credit-usage="<?= $creditUsageData; ?>" data-credit-colors="<?= $creditUsageColorsData; ?>"></div>
                        <div class="row g-16 mt-24 pt-16 border-top border-gray-100">
                            <?php foreach ($creditUsage as $segment): ?>
                                <div class="col-sm-6">
                                    <div class="d-flex justify-content-between align-items-center gap-8 p-12 rounded-12 bg-main-50">
                                        <span class="fw-semibold text-gray-800"><?= $segment['label']; ?></span>
                                        <span class="text-gray-600"><?= $formatCurrency($segment['value']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="flex-between flex-wrap gap-12 mb-16">
                            <h4 class="mb-0">۱۰ سازمان برتر</h4>
                            <span class="text-13 text-gray-400">براساس تعداد آزمون و مصرف اعتبار</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-gray-400 text-13">
                                        <th scope="col">سازمان</th>
                                        <th scope="col" class="text-center">آزمون‌ها</th>
                                        <th scope="col" class="text-end">اعتبار مصرفی</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topOrganizations as $organization): ?>
                                        <tr class="border-bottom border-gray-100">
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= $organization['name']; ?></span>
                                                    <span class="text-13 text-gray-400"><?= $organization['industry']; ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center fw-semibold text-gray-700">
                                                <?= $formatNumber($organization['exams']); ?>
                                            </td>
                                            <td class="text-end text-gray-600">
                                                <?= $formatCurrency($organization['credit']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions & Tickets -->
            <div class="col-xxl-7 col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="flex-between flex-wrap gap-12 mb-16">
                            <h4 class="mb-0">آخرین تراکنش‌های مالی</h4>
                            <a href="<?= UtilityHelper::baseUrl('supperadmin/finance/transactions'); ?>" class="text-13 fw-semibold text-main-600 d-flex align-items-center gap-4">
                                مشاهده همه
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead>
                                    <tr class="text-gray-400 text-13">
                                        <th scope="col">سازمان</th>
                                        <th scope="col" class="text-center">مبلغ</th>
                                        <th scope="col" class="text-center">وضعیت</th>
                                        <th scope="col" class="text-end">زمان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latestTransactions as $transaction): ?>
                                        <tr class="border-bottom border-gray-100">
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-gray-900"><?= $transaction['organization']; ?></span>
                                                    <span class="text-13 text-gray-400">کد تراکنش: <?= UtilityHelper::englishToPersian($transaction['reference']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center text-gray-700 fw-semibold">
                                                <?= $formatCurrency($transaction['amount']); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                $statusClasses = [
                                                    'settled' => 'bg-success-50 text-success-600',
                                                    'pending' => 'bg-warning-50 text-warning-600',
                                                    'failed' => 'bg-danger-50 text-danger-600',
                                                ];
                                                $statusLabels = [
                                                    'settled' => 'موفق',
                                                    'pending' => 'در انتظار',
                                                    'failed' => 'ناموفق',
                                                ];
                                                $status = $transaction['status'];
                                                ?>
                                                <span class="badge rounded-pill <?= $statusClasses[$status] ?? 'bg-gray-100 text-gray-500'; ?>">
                                                    <?= $statusLabels[$status] ?? 'نامشخص'; ?>
                                                </span>
                                            </td>
                                            <td class="text-end text-gray-400">
                                                <?= $formatDateTime($transaction['date']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-5 col-lg-5">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="flex-between flex-wrap gap-12 mb-16">
                            <h4 class="mb-0">آخرین تیکت‌های پشتیبانی</h4>
                            <a href="<?= UtilityHelper::baseUrl('supperadmin/support/tickets'); ?>" class="text-13 fw-semibold text-main-600 د-flex align-items-center gap-4">
                                مرکز پشتیبانی
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($latestTickets as $ticket): ?>
                                <?php
                                $priorityClasses = [
                                    'high' => 'bg-danger-50 text-danger-600',
                                    'medium' => 'bg-warning-50 text-warning-600',
                                    'low' => 'bg-success-50 text-success-600',
                                ];
                                $priorityLabel = $ticket['priority'] === 'high' ? 'زیاد' : ($ticket['priority'] === 'medium' ? 'متوسط' : 'کم');
                                ?>
                                <li class="py-12 border-bottom border-gray-100">
                                    <div class="d-flex flex-column gap-6">
                                        <div class="d-flex justify-content-between align-items-start gap-8">
                                            <h6 class="mb-0 text-gray-900"><?= $ticket['subject']; ?></h6>
                                            <span class="badge rounded-pill <?= $priorityClasses[$ticket['priority']] ?? 'bg-gray-100 text-gray-500'; ?>">
                                                اولویت <?= UtilityHelper::englishToPersian($priorityLabel); ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between flex-wrap gap-8 text-13 text-gray-400">
                                            <span>سازمان: <?= $ticket['organization']; ?></span>
                                            <span>آخرین بروزرسانی: <?= $formatDateTime($ticket['opened_at']); ?></span>
                                        </div>
                                        <span class="text-13 text-main-600">وضعیت: <?= $ticket['status']; ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent user activity -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="flex-between flex-wrap gap-12 mb-16">
                            <h4 class="mb-0">فعالیت اخیر کاربران</h4>
                            <span class="text-13 text-gray-400">آخرین رخدادهای ثبت شده در سیستم</span>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($userActivityLogs as $activity): ?>
                                <li class="py-12 border-bottom border-gray-100">
                                    <div class="d-flex align-items-start gap-12">
                                        <span class="flex-shrink-0 w-40 h-40 flex-center rounded-circle bg-main-50 text-main-600 text-lg"><i class="fas <?= $activity['icon']; ?>"></i></span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between flex-wrap gap-8">
                                                <div>
                                                    <h6 class="mb-4 text-gray-900"><?= $activity['user']; ?></h6>
                                                    <span class="text-13 text-gray-400 d-block"><?= $activity['role']; ?></span>
                                                </div>
                                                <span class="text-13 text-gray-400"><?= $formatDateTime($activity['time']); ?></span>
                                            </div>
                                            <p class="mb-0 mt-8 text-gray-600"><?= $activity['action']; ?></p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
