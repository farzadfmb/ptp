<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ویرایش گزارش سازمانی';
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
$reportSetting = $reportSetting ?? [];

$createdAt = $reportSetting['created_at'] ?? null;
$updatedAt = $reportSetting['updated_at'] ?? null;

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

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .report-setting-form label,\n    .report-setting-form small {\n        text-align: right;\n        display: block;\n    }\n    .report-setting-form .form-control,\n    .report-setting-form textarea {\n        text-align: right;\n        direction: rtl;\n    }\n    .report-setting-form .ltr-input {\n        direction: ltr;\n        text-align: left;\n    }\n    .report-setting-actions ion-icon {\n        font-size: 18px;\n    }\n    .report-setting-summary {\n        background: #f9fafc;\n    }\n    .report-setting-summary .summary-section + .summary-section {\n        border-top: 1px solid #e5e7eb;\n        margin-top: 16px;\n        padding-top: 16px;\n    }\n    .report-setting-summary dt {\n        color: #6b7280;\n        font-weight: 500;\n    }\n    .report-setting-summary dd {\n        margin: 0;\n        color: #111827;\n        font-weight: 600;\n    }\n    .report-setting-summary .summary-icon ion-icon {\n        font-size: 20px;\n    }\n";
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
                                <h2 class="mb-6 text-gray-900">ویرایش گزارش</h2>
                                <p class="text-gray-500 mb-0">مقادیر مورد نظر را اصلاح کنید و تغییرات را ذخیره نمایید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap report-setting-actions">
                                <a href="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    بازگشت
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span class="visually-hidden">بازگشت به لیست</span>
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
                                <form action="<?= UtilityHelper::baseUrl('organizations/report-settings/update'); ?>" method="post" class="report-setting-form text-start">
                                    <?= csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($reportSetting['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                                    <div class="row g-16">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">شناسه <span class="text-danger">*</span></label>
                                            <input type="text" name="identifier" class="form-control ltr-input" value="<?= htmlspecialchars(old('identifier', $reportSetting['identifier'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: RPT-100" required>
                                            <?php if (!empty($validationErrors['identifier'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['identifier'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">کد <span class="text-danger">*</span></label>
                                            <input type="text" name="code" class="form-control ltr-input" value="<?= htmlspecialchars(old('code', $reportSetting['code'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: CODE-01" required>
                                            <?php if (!empty($validationErrors['code'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(old('name', $reportSetting['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: گزارش عملکرد سه‌ماهه" required>
                                            <?php if (!empty($validationErrors['name'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">نام گزارش <span class="text-danger">*</span></label>
                                            <input type="text" name="report_name" class="form-control" value="<?= htmlspecialchars(old('report_name', $reportSetting['report_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: گزارش تحلیل شایستگی" required>
                                            <?php if (!empty($validationErrors['report_name'])): ?>
                                                <small class="text-danger mt-6"><?= htmlspecialchars($validationErrors['report_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">گزارش برای</label>
                                            <input type="text" name="report_for" class="form-control" value="<?= htmlspecialchars(old('report_for', $reportSetting['report_for'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیر منابع انسانی">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">سطح</label>
                                            <input type="text" name="level" class="form-control" value="<?= htmlspecialchars(old('level', $reportSetting['level'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: سازمانی">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">مقدمه گزارش</label>
                                            <textarea name="report_intro" rows="3" class="form-control" placeholder="توضیحی کوتاه درباره هدف گزارش"><?= htmlspecialchars(old('report_intro', $reportSetting['report_intro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">فرآیند ارزیابی</label>
                                            <textarea name="evaluation_process" rows="3" class="form-control" placeholder="مراحل انجام ارزیابی را توضیح دهید"><?= htmlspecialchars(old('evaluation_process', $reportSetting['evaluation_process'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">تعریف مدل</label>
                                            <textarea name="model_definition" rows="3" class="form-control" placeholder="مدل یا چارچوب گزارش را توضیح دهید"><?= htmlspecialchars(old('model_definition', $reportSetting['model_definition'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">مقدمه تعاریف شایستگی</label>
                                            <textarea name="competency_intro" rows="3" class="form-control" placeholder="تعاریف کلیدی شایستگی‌ها را ذکر کنید"><?= htmlspecialchars(old('competency_intro', $reportSetting['competency_intro'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">بخشنامه</label>
                                            <textarea name="regulation" rows="3" class="form-control" placeholder="بخشنامه یا مستند مرتبط را درج کنید"><?= htmlspecialchars(old('regulation', $reportSetting['regulation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-12 mt-28 report-setting-actions">
                                        <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                            <ion-icon name="save-outline"></ion-icon>
                                            <span>ذخیره تغییرات</span>
                                        </button>
                                        <a href="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="border border-gray-200 rounded-20 p-20 report-setting-summary shadow-sm h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-16">
                                        <h5 class="mb-0 text-gray-900 fw-semibold">خلاصه تنظیم</h5>
                                        <div class="summary-icon rounded-circle bg-light text-primary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <ion-icon name="document-text-outline"></ion-icon>
                                        </div>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">شناسه سیستم</dt>
                                                <dd><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($reportSetting['id'] ?? '—')), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">شناسه</dt>
                                                <dd><?= htmlspecialchars($reportSetting['identifier'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">کد</dt>
                                                <dd><?= htmlspecialchars($reportSetting['code'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">نام گزارش</dt>
                                                <dd><?= htmlspecialchars($reportSetting['name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">عنوان نمایشی</dt>
                                                <dd><?= htmlspecialchars($reportSetting['report_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="summary-section">
                                        <dl class="mb-0">
                                            <div class="d-flex justify-content-between align-items-center gap-12 mb-12">
                                                <dt class="mb-0">شناسه سازمان</dt>
                                                <dd><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($reportSetting['organization_id'] ?? '—')), ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-12">
                                                <dt class="mb-0">شناسه کاربر</dt>
                                                <dd><?= htmlspecialchars(UtilityHelper::englishToPersian((string) ($reportSetting['user_id'] ?? '—')), ENT_QUOTES, 'UTF-8'); ?></dd>
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
                                        <p class="text-gray-500 mb-0">پس از ذخیره تغییرات، وضعیت جدید این گزارش در فهرست تنظیمات قابل مشاهده خواهد بود.</p>
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
