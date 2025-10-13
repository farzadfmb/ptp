<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'بارگذاری کاربران سازمانی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com'
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$validationErrors = $validationErrors ?? [];
$importErrors = $importErrors ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .upload-card {\n        border: 1px solid #eef2f7;\n        border-radius: 24px;\n    }\n    .upload-guidelines {\n        background: #f9fbff;\n        border: 1px dashed #c7d7eb;\n        border-radius: 20px;\n        padding: 20px;\n    }\n    .upload-guidelines li::marker {\n        color: #3b82f6;\n    }\n    .import-errors-list {\n        max-height: 220px;\n        overflow-y: auto;\n    }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 upload-card">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-6 text-gray-900">بارگذاری گروهی کاربران</h2>
                                <p class="text-gray-500 mb-0">فایل اکسل آماده‌شده را بارگذاری کنید تا کاربران سازمان به‌صورت گروهی ثبت شوند.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-10">
                                <a href="<?= UtilityHelper::baseUrl('organizations/users/import/sample'); ?>" class="btn btn-outline-main rounded-pill px-24 d-flex align-items-center gap-8">
                                    <i class="fas fa-file-download"></i>
                                    دانلود فایل نمونه
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/users'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8">
                                    <i class="fas fa-arrow-right"></i>
                                    بازگشت به لیست
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 text-end d-flex align-items-center gap-12" role="alert">
                                <i class="fas fa-check-circle"></i>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-end d-flex align-items-center gap-12" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($importErrors)): ?>
                            <div class="alert alert-warning rounded-16 text-end" role="alert">
                                <div class="d-flex align-items-start gap-12">
                                    <i class="fas fa-info-circle mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold mb-10">برخی ردیف‌ها ثبت نشدند:</h6>
                                        <ul class="import-errors-list pe-3 mb-0 text-gray-700">
                                            <?php foreach ($importErrors as $error): ?>
                                                <li class="mb-6"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row g-24 align-items-start">
                            <div class="col-lg-7">
                                <form action="<?= UtilityHelper::baseUrl('organizations/users/import'); ?>" method="post" enctype="multipart/form-data" class="text-end">
                                    <?= csrf_field(); ?>
                                    <div class="mb-20">
                                        <label for="users_file" class="form-label fw-semibold text-gray-700">انتخاب فایل اکسل کاربران <span class="text-danger">*</span></label>
                                        <input type="file" name="users_file" id="users_file" class="form-control" accept=".xlsx" required>
                                        <small class="text-gray-500 d-block mt-6">تنها فایل‌های با فرمت XLSX پشتیبانی می‌شوند.</small>
                                        <?php if (!empty($validationErrors['users_file'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['users_file'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-0">
                                        <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                            <i class="fas fa-upload"></i>
                                            بارگذاری و ثبت کاربران
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-5">
                                <div class="upload-guidelines text-end">
                                    <h6 class="fw-semibold text-gray-800 mb-12">راهنمای تکمیل فایل اکسل</h6>
                                    <ul class="pe-2 mb-0 text-gray-600">
                                        <li class="mb-8">ستون‌های <strong>نام کاربری، رمز، نام، نام خانوادگی، کد ارزیابی، کد ملی، کد پرسنلی</strong> الزامی هستند.</li>
                                        <li class="mb-8">از فایل نمونه دانلود شده استفاده کنید تا ترتیب و نام ستون‌ها صحیح باشد.</li>
                                        <li class="mb-8">از تکرار نام کاربری پرهیز کنید؛ سامانه ردیف‌های تکراری را نادیده می‌گیرد.</li>
                                        <li class="mb-8">برای کاراکترهای فارسی، فونت و صفحه‌کلید استاندارد را در اکسل فعال کنید.</li>
                                        <li class="mb-0">پس از بارگذاری، نتیجه عملیات و خطاهای احتمالی در همین صفحه نمایش داده خواهد شد.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
