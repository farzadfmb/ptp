<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ایجاد پست سازمانی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$validationErrors = $validationErrors ?? [];
$errorMessage = $errorMessage ?? flash('error');

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .organization-post-form label,\n    .organization-post-form small {\n        text-align: right;\n        display: block;\n    }\n    .organization-post-form .form-control {\n        text-align: right;\n        direction: rtl;\n    }\n    .organization-post-form .ltr-input {\n        direction: ltr;\n        text-align: left;\n    }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="text-start flex-grow-1">
                                <h2 class="mb-6 text-gray-900">ایجاد پست سازمانی</h2>
                                <p class="text-gray-500 mb-0">اطلاعات پست جدید را وارد کرده و آن را به لیست اضافه کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/posts'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست پست‌ها</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/posts'); ?>" method="post" class="organization-post-form text-start">
                            <?= csrf_field(); ?>
                            <div class="row g-16">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">کد <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: POST-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(old('name', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیر منابع انسانی" required>
                                    <?php if (!empty($validationErrors['name'])): ?>
                                        <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ثبت پست</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/posts'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
