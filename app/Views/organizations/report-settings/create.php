<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ایجاد گزارش جدید';
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
$errorMessage = flash('error');

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n";

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
                            <div>
                                <h2 class="mb-6 text-gray-900">ایجاد گزارش جدید</h2>
                                <p class="text-gray-500 mb-0">فرم زیر را تکمیل کنید تا گزارش موردنظر به لیست افزوده شود.</p>
                            </div>
                            <div class="d-flex gap-10">
                                <a href="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8">
                                    <i class="fas fa-arrow-right"></i>
                                    بازگشت به لیست
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-start d-flex align-items-center gap-12" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" method="post" class="text-start">
                            <?= csrf_field(); ?>
                            <div class="row g-16">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">شناسه <span class="text-danger">*</span></label>
                                    <input type="text" name="identifier" class="form-control" value="<?= htmlspecialchars(old('identifier', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: RPT-100" required>
                                    <?php if (!empty($validationErrors['identifier'])): ?>
                                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['identifier'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">کد <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" value="<?= htmlspecialchars(old('code', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: CODE-01" required>
                                    <?php if (!empty($validationErrors['code'])): ?>
                                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(old('name', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: گزارش عملکرد سه‌ماهه" required>
                                    <?php if (!empty($validationErrors['name'])): ?>
                                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">نام گزارش <span class="text-danger">*</span></label>
                                    <input type="text" name="report_name" class="form-control" value="<?= htmlspecialchars(old('report_name', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: گزارش تحلیل شایستگی" required>
                                    <?php if (!empty($validationErrors['report_name'])): ?>
                                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['report_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">گزارش برای</label>
                                    <input type="text" name="report_for" class="form-control" value="<?= htmlspecialchars(old('report_for', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیر منابع انسانی">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">سطح</label>
                                    <input type="text" name="level" class="form-control" value="<?= htmlspecialchars(old('level', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: سازمانی">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">مقدمه گزارش</label>
                                    <textarea name="report_intro" rows="3" class="form-control" placeholder="توضیحی کوتاه درباره هدف گزارش"><?= htmlspecialchars(old('report_intro', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">فرآیند ارزیابی</label>
                                    <textarea name="evaluation_process" rows="3" class="form-control" placeholder="مراحل انجام ارزیابی را توضیح دهید"><?= htmlspecialchars(old('evaluation_process', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">تعریف مدل</label>
                                    <textarea name="model_definition" rows="3" class="form-control" placeholder="مدل یا چارچوب گزارش را توضیح دهید"><?= htmlspecialchars(old('model_definition', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">مقدمه تعاریف شایستگی</label>
                                    <textarea name="competency_intro" rows="3" class="form-control" placeholder="تعاریف کلیدی شایستگی‌ها را ذکر کنید"><?= htmlspecialchars(old('competency_intro', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">بخشنامه</label>
                                    <textarea name="regulation" rows="3" class="form-control" placeholder="بخشنامه یا مستند مرتبط را درج کنید"><?= htmlspecialchars(old('regulation', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <i class="fas fa-save"></i>
                                    ثبت گزارش
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
