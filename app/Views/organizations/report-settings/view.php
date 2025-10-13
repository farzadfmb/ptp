<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'جزئیات گزارش سازمانی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

AuthHelper::startSession();

$reportSetting = $reportSetting ?? [];
$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');

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

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .report-setting-view .detail-label {\n        font-size: 13px;\n        color: #6b7280;\n        display: block;\n        margin-bottom: 4px;\n        text-align: right;\n    }\n    .report-setting-view .detail-value {\n        font-size: 16px;\n        font-weight: 600;\n        color: #111827;\n        margin: 0;\n        text-align: right;\n    }\n    .report-setting-view .detail-card {\n        background: #ffffff;\n        border: 1px solid #e5e7eb;\n        border-radius: 20px;\n        padding: 20px;\n        height: 100%;\n    }\n    .report-setting-view .detail-card + .detail-card {\n        margin-top: 16px;\n    }\n    .report-setting-view .rich-text {\n        white-space: pre-line;\n        font-size: 15px;\n        line-height: 1.8;\n        text-align: right;\n        direction: rtl;\n    }\n    .report-setting-summary {\n        background: #f9fafc;\n    }\n    .report-setting-summary .summary-section + .summary-section {\n        border-top: 1px solid #e5e7eb;\n        margin-top: 16px;\n        padding-top: 16px;\n    }\n    .report-setting-summary dt {\n        color: #6b7280;\n        font-weight: 500;\n    }\n    .report-setting-summary dd {\n        margin: 0;\n        color: #111827;\n        font-weight: 600;\n    }\n    .report-setting-summary .summary-icon ion-icon {\n        font-size: 20px;\n    }\n";

include __DIR__ . '/../../layouts/organization-header.php';
include __DIR__ . '/../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper report-setting-view">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-24 h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div class="text-end flex-grow-1">
                                <h2 class="mb-6 text-gray-900">جزئیات گزارش</h2>
                                <p class="text-gray-500 mb-0">اطلاعات کامل گزارش انتخاب‌شده را در این صفحه مشاهده کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/report-settings/edit?id=' . urlencode((string) ($reportSetting['id'] ?? ''))); ?>" class="btn btn-main rounded-pill px-24 d-flex align-items-center gap-8" title="ویرایش">
                                    <ion-icon name="create-outline"></ion-icon>
                                    <span>ویرایش</span>
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/report-settings'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت">
                                    <ion-icon name="arrow-undo-outline"></ion-icon>
                                    <span>بازگشت</span>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 text-end d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 text-end d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="row g-24 align-items-start">
                            <div class="col-12 col-lg-8">
                                <div class="d-flex flex-column gap-16">
                                    <div class="detail-card">
                                        <div class="row g-20">
                                            <div class="col-sm-6">
                                                <span class="detail-label">شناسه</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['identifier'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="detail-label">کد</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['code'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
        
                                            <div class="col-sm-6">
                                                <span class="detail-label">نام گزارش</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="detail-label">عنوان نمایشی</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['report_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>

                                            <div class="col-sm-6">
                                                <span class="detail-label">گزارش برای</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['report_for'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="detail-label">سطح</span>
                                                <p class="detail-value"><?= htmlspecialchars($reportSetting['level'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="detail-card">
                                        <h5 class="text-gray-900 fw-semibold mb-12">مقدمه گزارش</h5>
                                        <div class="rich-text text-gray-700"><?= nl2br(htmlspecialchars($reportSetting['report_intro'] ?? 'اطلاعاتی ثبت نشده است.', ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>

                                    <div class="detail-card">
                                        <h5 class="text-gray-900 fw-semibold mb-12">فرآیند ارزیابی</h5>
                                        <div class="rich-text text-gray-700"><?= nl2br(htmlspecialchars($reportSetting['evaluation_process'] ?? 'اطلاعاتی ثبت نشده است.', ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>

                                    <div class="detail-card">
                                        <h5 class="text-gray-900 fw-semibold mb-12">تعریف مدل</h5>
                                        <div class="rich-text text-gray-700"><?= nl2br(htmlspecialchars($reportSetting['model_definition'] ?? 'اطلاعاتی ثبت نشده است.', ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>

                                    <div class="detail-card">
                                        <h5 class="text-gray-900 fw-semibold mb-12">مقدمه تعاریف شایستگی</h5>
                                        <div class="rich-text text-gray-700"><?= nl2br(htmlspecialchars($reportSetting['competency_intro'] ?? 'اطلاعاتی ثبت نشده است.', ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>

                                    <div class="detail-card mb-0">
                                        <h5 class="text-gray-900 fw-semibold mb-12">بخشنامه</h5>
                                        <div class="rich-text text-gray-700"><?= nl2br(htmlspecialchars($reportSetting['regulation'] ?? 'اطلاعاتی ثبت نشده است.', ENT_QUOTES, 'UTF-8')); ?></div>
                                    </div>
                                </div>
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
                                        <p class="text-gray-500 mb-0">در صورت نیاز به ویرایش، از دکمه «ویرایش» در بالای صفحه استفاده کنید.</p>
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
