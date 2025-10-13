<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش پست سازمانی';
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
$successMessage = $successMessage ?? flash('success');
$organizationPost = $organizationPost ?? [];

$createdAt = $organizationPost['created_at'] ?? null;
$updatedAt = $organizationPost['updated_at'] ?? null;

$createdAtDisplay = '—';
if (!empty($createdAt) && $createdAt !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($createdAt);
    if ($timestamp !== false) {
        $createdAtDisplay = UtilityHelper::englishToPersian(date('Y/m/d H:i', $timestamp));
    }
}

$updatedAtDisplay = '—';
if (!empty($updatedAt) && $updatedAt !== '0000-00-00 00:00:00') {
    $timestamp = strtotime($updatedAt);
    if ($timestamp !== false) {
        $updatedAtDisplay = UtilityHelper::englishToPersian(date('Y/m/d H:i', $timestamp));
    }
}

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .organization-post-form label,\n    .organization-post-form small {\n        text-align: right;\n        display: block;\n    }\n    .organization-post-form .form-control {\n        text-align: right;\n        direction: rtl;\n    }\n    .organization-post-form .ltr-input {\n        direction: ltr;\n        text-align: left;\n    }\n    .organization-post-summary {\n        background: #f9fafc;\n    }\n    .organization-post-summary .summary-section + .summary-section {\n        border-top: 1px solid #e5e7eb;\n        margin-top: 16px;\n        padding-top: 16px;\n    }\n    .organization-post-summary dt {\n        color: #6b7280;\n        font-weight: 500;\n    }\n    .organization-post-summary dd {\n        margin: 0;\n        color: #111827;\n        font-weight: 600;\n    }\n";

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
                                <h2 class="mb-6 text-gray-900">ویرایش پست سازمانی</h2>
                                <p class="text-gray-500 mb-0">مقادیر مورد نظر را اصلاح کرده و تغییرات را ذخیره نمایید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/posts'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت به لیست
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست پست‌ها</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="row g-24 align-items-start">
                            <div class="col-12 col-lg-8">
                                <form action="<?= UtilityHelper::baseUrl('organizations/posts/update'); ?>" method="post" class="organization-post-form text-start">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($organizationPost['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                                    <div class="row g-16">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">کد <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', $organizationPost['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: POST-01" required>
                                            <?php if (!empty($validationErrors['code'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(old('name', $organizationPost['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیر منابع انسانی" required>
                                            <?php if (!empty($validationErrors['name'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-12 mt-28">
                                        <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                            <ion-icon name="save-outline"></ion-icon>
                                            <span>ذخیره تغییرات</span>
                                        </button>
                                        <a href="<?= UtilityHelper::baseUrl('organizations/posts'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="border border-gray-200 rounded-20 p-20 organization-post-summary shadow-sm h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-16">
                                        <h5 class="mb-0 text-gray-900 fw-semibold">خلاصه پست</h5>
                                        <div class="rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <ion-icon name="briefcase-outline"></ion-icon>
                                        </div>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">شناسه سیستم</dt>
                                                <dd><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($organizationPost['id'] ?? '—')), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">کد</dt>
                                                <dd><?= htmlspecialchars($organizationPost['code'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">نام</dt>
                                                <dd><?= htmlspecialchars($organizationPost['name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">شناسه سازمان</dt>
                                                <dd><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($organizationPost['organization_id'] ?? '—')), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">شناسه کاربر</dt>
                                                <dd><?= htmlspecialchars((string) ($organizationPost['user_id'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">تاریخ ایجاد</dt>
                                                <dd><?= htmlspecialchars($createdAtDisplay, ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">آخرین به‌روزرسانی</dt>
                                                <dd><?= htmlspecialchars($updatedAtDisplay, ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="summary-section">
                                        <p class="text-gray-500 mb-0">پس از ذخیره تغییرات، جزئیات جدید در فهرست پست‌های سازمانی قابل مشاهده خواهد بود.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
