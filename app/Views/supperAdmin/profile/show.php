<?php
$title = 'پروفایل کاربری';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'کاربر سیستم', 'email' => 'user@example.com'];
$additional_js = [];

$userRecord = $userRecord ?? [];
$recentActivities = $recentActivities ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$profileAvatarUrl = $profileAvatarUrl ?? UtilityHelper::baseUrl('public/assets/images/thumbs/user-img.png');

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();
?>

<div class="dashboard-main-wrapper" dir="rtl">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body profile-page">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 box-shadow-custom">
                    <div class="card-body text-end">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12 mb-20">
                            <div class="d-flex gap-12">
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/users/edit?id=' . (int) $userRecord['id']); ?>" class="btn btn-outline-main rounded-pill px-24">
                                    <i class="fas fa-edit ms-6"></i>
                                    ویرایش پروفایل
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/users'); ?>" class="btn btn-light-main rounded-pill px-24">
                                    <i class="fas fa-users ms-6"></i>
                                    مدیریت کاربران
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-16">
                                <div class="text-end">
                                    <h3 class="mb-4"><?= htmlspecialchars($userRecord['first_name'] . ' ' . $userRecord['last_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="text-gray-500 mb-0">ایمیل: <?= htmlspecialchars($userRecord['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <span class="position-relative">
                                    <img src="<?= $profileAvatarUrl; ?>" alt="آواتار" class="w-72 h-72 rounded-circle object-fit-cover">
                                    <span class="activation-badge w-12 h-12 position-absolute end-0 bottom-0"></span>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end d-flex align-items-center gap-8" role="alert">
                                <i class="fas fa-check-circle text-lg"></i>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end d-flex align-items-center gap-8" role="alert">
                                <i class="fas fa-exclamation-triangle text-lg"></i>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xxl-8 col-lg-7">
                <div class="card border-0 box-shadow-custom h-100">
                    <div class="card-body text-end">
                        <h4 class="mb-16">اطلاعات حساب</h4>
                        <div class="row g-16">
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">نام</p>
                                    <h6 class="mb-0"><?= htmlspecialchars($userRecord['first_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">نام خانوادگی</p>
                                    <h6 class="mb-0"><?= htmlspecialchars($userRecord['last_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">نقش کاربری</p>
                                    <h6 class="mb-0"><?= htmlspecialchars($userRecord['role_name'] ?? 'نامشخص', ENT_QUOTES, 'UTF-8'); ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">نوع نقش</p>
                                    <?php
                                        $scope = $userRecord['scope_type'] ?? 'global';
                                        $scopeLabel = 'سراسری';
                                        if ($scope === 'organization') {
                                            $scopeLabel = 'سازمانی';
                                        } elseif ($scope === 'superadmin') {
                                            $scopeLabel = 'سوپر ادمین';
                                        }
                                    ?>
                                    <h6 class="mb-0"><?= htmlspecialchars($scopeLabel, ENT_QUOTES, 'UTF-8'); ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">سازمان مرتبط</p>
                                    <h6 class="mb-0"><?= htmlspecialchars($userRecord['organization_name'] ?? 'سراسری', ENT_QUOTES, 'UTF-8'); ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">وضعیت حساب</p>
                                    <?php
                                        $status = $userRecord['status'] ?? 'inactive';
                                        $statusLabel = $status === 'active' ? 'فعال' : 'غیرفعال';
                                        $statusClass = $status === 'active' ? 'badge bg-success-100 text-success-600' : 'badge bg-gray-100 text-gray-600';
                                    ?>
                                    <h6 class="mb-0">
                                        <span class="<?= $statusClass; ?> px-12 py-6 rounded-pill"><?= $statusLabel; ?></span>
                                    </h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">شماره تماس</p>
                                    <?php $mobile = $userRecord['mobile'] ?? ''; ?>
                                    <h6 class="mb-0"><?= $mobile !== '' ? htmlspecialchars(UtilityHelper::englishToPersian($mobile), ENT_QUOTES, 'UTF-8') : '—'; ?></h6>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border border-gray-100 rounded-16 p-16">
                                    <p class="text-gray-400 mb-4">کد ملی</p>
                                    <?php $national = $userRecord['national_code'] ?? ''; ?>
                                    <h6 class="mb-0"><?= $national !== '' ? htmlspecialchars(UtilityHelper::englishToPersian($national), ENT_QUOTES, 'UTF-8') : '—'; ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-5">
                <div class="card border-0 box-shadow-custom h-100">
                    <div class="card-body text-end">
                        <h4 class="mb-16">آخرین فعالیت‌ها</h4>
                        <?php if (empty($recentActivities)): ?>
                            <p class="text-gray-400 mb-0">هنوز فعالیتی ثبت نشده است.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($recentActivities as $activity): ?>
                                    <li class="mb-12 pb-12 border-bottom border-gray-100">
                                        <h6 class="mb-4"><?= htmlspecialchars($activity['title'] ?? 'فعالیت', ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <p class="text-gray-500 text-13 mb-0"><?= htmlspecialchars($activity['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        <span class="text-12 text-gray-300"><?= htmlspecialchars($activity['time'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
