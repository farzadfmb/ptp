<?php
$title = 'لیست سازمان‌ها';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();

$formatDateTime = function ($dateTime) {
    if (!$dateTime) {
        return '-';
    }

    $timestamp = strtotime($dateTime);
    if (!$timestamp) {
        return UtilityHelper::englishToPersian($dateTime);
    }

    return UtilityHelper::englishToPersian(date('H:i Y/m/d', $timestamp));
};

$organizations = $organizations ?? [];

$maxCreditAmount = 0.0;
foreach ($organizations as $org) {
    $creditValue = $org['credit_amount'] ?? null;
    if ($creditValue !== null && $creditValue !== '' && is_numeric($creditValue)) {
        $numericValue = (float) $creditValue;
        if ($numericValue > $maxCreditAmount) {
            $maxCreditAmount = $numericValue;
        }
    }
}

$formatCreditAmount = function ($amount) {
    if ($amount === null) {
        return 'نامشخص';
    }

    $formatted = number_format((float) $amount, 0, '.', ',');
    return UtilityHelper::englishToPersian($formatted) . ' تومان';
};

$getCreditPercent = function ($amount) use ($maxCreditAmount) {
    if ($maxCreditAmount <= 0) {
        return 0;
    }

    $percent = ((float) $amount / $maxCreditAmount) * 100;
    return max(0, min(100, $percent));
};
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body pb-0 text-end">
                        <div class="mb-16">
                            <h3 class="mb-4">لیست سازمان‌ها</h3>
                            <p class="text-gray-500 mb-0">در این بخش می‌توانید سازمان‌های ثبت شده را مشاهده و مدیریت کنید.</p>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap gap-12 mb-16">
                            <div class="d-flex flex-wrap gap-8">
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/organizations/create'); ?>" class="btn btn-main rounded-pill px-20">
                                    <i class="fas fa-plus ms-6"></i>
                                    ایجاد سازمان جدید
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-8 text-gray-500">
                                <span class="badge bg-main-50 text-main-600 rounded-pill py-8 px-16">
                                    مجموع سازمان‌ها: <?= UtilityHelper::englishToPersian(count($organizations)); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end" role="alert">
                                <i class="fas fa-check-circle ms-6"></i>
                                <?= $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end" role="alert">
                                <i class="fas fa-exclamation-triangle ms-6"></i>
                                <?= $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <?php if (empty($organizations)): ?>
                            <div class="p-40 text-center text-gray-500">
                                <i class="fas fa-folder-open mb-16 text-3xl d-block"></i>
                                هنوز سازمانی ثبت نشده است.
                                <div class="mt-12">
                                    <a href="<?= UtilityHelper::baseUrl('supperadmin/organizations/create'); ?>" class="btn btn-main rounded-pill">
                                        <i class="fas fa-plus ms-6"></i>
                                        افزودن اولین سازمان
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle text-end mb-0">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th scope="col" class="text-end">سازمان</th>
                                            <th scope="col" class="text-end">کد</th>
                                            <th scope="col" class="text-end">ساب‌دومین</th>
                                            <th scope="col" class="text-end">واحد ارزیابی‌شونده</th>
                                            <th scope="col" class="text-end">اعتبار</th>
                                            <th scope="col" class="text-end">وضعیت</th>
                                            <th scope="col" class="text-end">تاریخ ایجاد</th>
                                            <th scope="col" class="text-center">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($organizations as $organization): ?>
                                            <?php
                                                $logoPath = $organization['logo_path'] ?? null;
                                                $logoUrl = $logoPath ? UtilityHelper::baseUrl('public/' . ltrim($logoPath, '/')) : null;
                                                $isActive = (int) ($organization['is_active'] ?? 1) === 1;
                                                $rawCreditAmount = $organization['credit_amount'] ?? null;
                                                $creditAmount = ($rawCreditAmount !== null && $rawCreditAmount !== '' && is_numeric($rawCreditAmount)) ? (float) $rawCreditAmount : null;
                                                $creditPercent = $creditAmount !== null ? $getCreditPercent($creditAmount) : 0;
                                                $creditPercentRounded = $creditAmount !== null ? round($creditPercent) : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex justify-content-end align-items-center gap-12">
                                                        <div class="w-44 h-44 rounded-circle overflow-hidden bg-gray-100 flex-center">
                                                            <?php if ($logoUrl): ?>
                                                                <img src="<?= $logoUrl; ?>" alt="<?= htmlspecialchars($organization['name'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;">
                                                            <?php else: ?>
                                                                <span class="text-gray-400 text-lg">
                                                                    <?= UtilityHelper::englishToPersian(mb_substr($organization['name'], 0, 1)); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-end">
                                                            <strong class="d-block text-gray-900"><?= $organization['name']; ?></strong>
                                                            <small class="text-gray-400"><?= $organization['latin_name']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= UtilityHelper::englishToPersian($organization['code']); ?></td>
                                                <td><?= UtilityHelper::englishToPersian($organization['subdomain']); ?></td>
                                                <td><?= $organization['evaluation_unit'] ? htmlspecialchars($organization['evaluation_unit'], ENT_QUOTES, 'UTF-8') : '-' ; ?></td>
                                                <td>
                                                    <?php if ($creditAmount !== null): ?>
                                                        <div class="credit-meter">
                                                            <div class="progress bg-gray-200 rounded-pill overflow-hidden mb-6" style="height: 8px;">
                                                                <div class="progress-bar bg-main-500" role="progressbar" style="width: <?= round($creditPercent, 2); ?>%;" aria-valuenow="<?= round($creditPercent, 2); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center text-gray-500 small">
                                                                <span><?= UtilityHelper::englishToPersian(number_format($creditPercentRounded, 0)); ?>٪</span>
                                                                <span>اعتبار باقی‌مانده: <?= $formatCreditAmount($creditAmount); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-gray-400">نامشخص</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill <?= $isActive ? 'bg-success-50 text-success-600' : 'bg-danger-50 text-danger-600'; ?>">
                                                        <?= $isActive ? 'فعال' : 'غیرفعال'; ?>
                                                    </span>
                                                </td>
                                                <td><?= $formatDateTime($organization['created_at'] ?? null); ?></td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex align-items-center gap-8 justify-content-center">
                                                        <a href="<?= UtilityHelper::baseUrl('supperadmin/organizations/edit?id=' . $organization['id']); ?>" class="btn btn-sm btn-outline-main rounded-pill d-flex align-items-center gap-6 px-16" title="ویرایش">
                                                            <i class="fas fa-edit"></i>
                                                            <span>ویرایش</span>
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('supperadmin/organizations/delete'); ?>" method="post" class="d-inline" onsubmit="return confirm('آیا از حذف این سازمان مطمئن هستید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="id" value="<?= $organization['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill d-flex align-items-center gap-6 px-16" title="حذف">
                                                                <i class="fas fa-trash-alt"></i>
                                                                <span>حذف</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
