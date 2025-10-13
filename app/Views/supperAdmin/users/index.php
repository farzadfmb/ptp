<?php
$title = 'مدیریت کاربران';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

$users = $users ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();
?>

<div class="dashboard-main-wrapper" dir="rtl">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 box-shadow-custom">
                    <div class="card-body text-end">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12 mb-20">
                            <div>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/users/create'); ?>" class="btn btn-main rounded-pill px-24">
                                    <i class="fas fa-user-plus ms-6"></i>
                                    ایجاد کاربر جدید
                                </a>
                            </div>
                            <div>
                                <h3 class="mb-4">فهرست کاربران سامانه</h3>
                                <p class="text-gray-500 mb-0">لیست کاربران ثبت شده را مشاهده کرده و از این بخش کاربر جدید ایجاد کنید.</p>
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

                        <div class="table-responsive border border-gray-100 rounded-16">
                            <table class="table text-end align-middle mb-0">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th scope="col">نام و نام خانوادگی</th>
                                        <th scope="col">ایمیل</th>
                                        <th scope="col">شماره تماس</th>
                                        <th scope="col">نقش کاربری</th>
                                        <th scope="col">سازمان</th>
                                        <th scope="col">وضعیت</th>
                                        <th scope="col">تاریخ ایجاد</th>
                                        <th scope="col" class="text-center">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-24 text-gray-500">هنوز کاربری ثبت نشده است.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $userRow): ?>
                                            <?php
                                                $fullName = trim(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? ''));
                                                $fullName = $fullName !== '' ? $fullName : '—';
                                                $mobile = $userRow['mobile'] ?? '';
                                                $mobile = $mobile !== '' ? UtilityHelper::englishToPersian($mobile) : '—';
                                                $roleName = $userRow['role_name'] ?? 'نامشخص';
                                                $organizationName = $userRow['organization_name'] ?? 'سراسری';
                                                $status = $userRow['status'] ?? 'inactive';
                                                $createdAt = $userRow['created_at'] ?? null;

                                                $statusLabel = $status === 'active' ? 'فعال' : 'غیرفعال';
                                                $statusClass = $status === 'active' ? 'badge bg-success-100 text-success-600' : 'badge bg-gray-100 text-gray-600';
                                                $createdAtDisplay = $createdAt ? UtilityHelper::englishToPersian(date('Y/m/d H:i', strtotime($createdAt))) : '—';
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($userRow['email'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($organizationName ?: 'سراسری', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="<?= $statusClass; ?> px-12 py-6 rounded-pill"><?= $statusLabel; ?></span></td>
                                                <td><?= htmlspecialchars($createdAtDisplay, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php $isCoreSuperAdmin = (($userRow['role_slug'] ?? '') === 'super-admin'); ?>
                                                    <div class="d-flex justify-content-end gap-8">
                                                        <a href="<?= UtilityHelper::baseUrl('supperadmin/users/edit?id=' . (int) ($userRow['id'] ?? 0)); ?>" class="btn btn-sm btn-outline-main px-16 rounded-pill d-inline-flex align-items-center gap-6">
                                                            <i class="fas fa-edit"></i>
                                                            ویرایش
                                                        </a>
                                                        <form action="<?= UtilityHelper::baseUrl('supperadmin/users/delete'); ?>" method="post" class="d-inline-flex" onsubmit="return confirm('آیا از حذف این کاربر مطمئن هستید؟');">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="user_id" value="<?= (int) ($userRow['id'] ?? 0); ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger px-16 rounded-pill d-inline-flex align-items-center gap-6" <?= $isCoreSuperAdmin ? 'disabled aria-disabled="true"' : ''; ?>>
                                                                <i class="fas fa-trash"></i>
                                                                حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <?php if ($isCoreSuperAdmin): ?>
                                                        <small class="d-block text-danger mt-6">حذف کاربر سوپر ادمین مجاز نیست.</small>
                                                    <?php endif; ?>
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
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>
