<?php
$title = $title ?? 'تنظیمات کلی سیستم';
$user = $user ?? ((class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com']);
$additional_js = $additional_js ?? [];
$settings = $settings ?? [];
$validationErrors = $validationErrors ?? [];
$languageOptions = $languageOptions ?? [
    'fa' => 'فارسی (پیش‌فرض)',
    'en' => 'English',
    'ar' => 'العربية'
];
$timezoneOptions = $timezoneOptions ?? [
    'Asia/Tehran' => 'Asia/Tehran (GMT+03:30)',
    'UTC' => 'UTC',
    'Europe/Berlin' => 'Europe/Berlin',
    'America/New_York' => 'America/New_York'
];

$selectedLanguage = old('default_language', $settings['default_language'] ?? 'fa');
$selectedTimezone = old('timezone', $settings['timezone'] ?? 'Asia/Tehran');
$maintenanceChecked = old('maintenance_mode', ($settings['maintenance_mode'] ?? false) ? '1' : '0') === '1';
$registrationChecked = old('allow_registration', ($settings['allow_registration'] ?? false) ? '1' : '0') === '1';
$updatedAt = $settings['updated_at'] ?? null;
$updatedAtLabel = $updatedAt ? UtilityHelper::englishToPersian(date('H:i Y/m/d', strtotime($updatedAt))) : 'ثبت نشده';
$systemLogoPath = $settings['system_logo_path'] ?? null;
$systemLogoUrl = $systemLogoPath ? UtilityHelper::baseUrl('public/' . ltrim($systemLogoPath, '/')) : null;
$defaultAvatarFallbackPath = $fallbackDefaultAvatarPath ?? 'assets/images/thumbs/user-img.png';
$systemDefaultAvatarStoredPath = $settings['system_default_avatar_path'] ?? null;
$systemDefaultAvatarPath = $systemDefaultAvatarStoredPath ?: $defaultAvatarFallbackPath;
$systemDefaultAvatarUrl = UtilityHelper::baseUrl('public/' . ltrim($systemDefaultAvatarPath, '/'));
$resetDefaultAvatarChecked = old('reset_system_default_avatar', '0') === '1';
$hasCustomDefaultAvatar = !empty($systemDefaultAvatarStoredPath) && $systemDefaultAvatarStoredPath !== $defaultAvatarFallbackPath;

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';
?>

<style>
    .general-settings-wrapper {
        direction: rtl;
    }
    .general-settings-wrapper h1,
    .general-settings-wrapper h2,
    .general-settings-wrapper h3,
    .general-settings-wrapper h4,
    .general-settings-wrapper h5,
    .general-settings-wrapper h6,
    .general-settings-wrapper p,
    .general-settings-wrapper label,
    .general-settings-wrapper small,
    .general-settings-wrapper span,
    .general-settings-wrapper li,
    .general-settings-wrapper a,
    .general-settings-wrapper textarea,
    .general-settings-wrapper input,
    .general-settings-wrapper select,
    .general-settings-wrapper .card-body,
    .general-settings-wrapper .badge,
    .general-settings-wrapper .alert,
    .general-settings-wrapper .form-check-label,
    .general-settings-wrapper .form-control,
    .general-settings-wrapper .form-select {
        text-align: right;
    }

    .general-settings-wrapper .dropdown-menu {
        text-align: right;
    }
</style>

<div class="dashboard-main-wrapper general-settings-wrapper" dir="rtl">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 box-shadow-custom">
                    <div class="card-body p-24 text-end">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12 mb-16">
                             <div class="text-end">
                                <span class="badge bg-main-50 text-main-600 rounded-pill py-8 px-16 d-inline-flex align-items-center gap-8">
                                    <i class="fas fa-history"></i>
                                    آخرین بروزرسانی: <?= $updatedAtLabel; ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-4">تنظیمات کلی سیستم</h3>
                                <p class="mb-0 text-gray-500">در این بخش می‌توانید اطلاعات عمومی سامانه، گزینه‌های پشتیبانی و تنظیمات عملکرد را مدیریت کنید.</p>
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

                        <form action="<?= UtilityHelper::baseUrl('supperadmin/settings/general'); ?>" method="post" enctype="multipart/form-data" class="mt-24">
                            <?= csrf_field(); ?>
                            <div class="row g-24">
                                <div class="col-xxl-8 col-lg-7">
                                    <div class="border border-gray-100 rounded-16 p-24 mb-24">
                                        <div class="d-flex justify-content-between align-items-center mb-20">
                                            <span class="badge bg-main-100 text-main-700 rounded-pill py-6 px-12">نمایش بیرونی</span>
                                            <div class="text-end">
                                                <h5 class="mb-4">اطلاعات عمومی سامانه</h5>
                                                <p class="text-gray-500 mb-0">نام سامانه و توضیحات نمایشی را برای کاربران تعیین کنید.</p>
                                            </div>
                                        </div>
                                        <div class="row g-16">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-end d-block">نام سیستم <span class="text-danger">*</span></label>
                                                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(old('site_name', $settings['site_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: سامانه مدیریت عملکرد" required>
                                                <?php if (!empty($validationErrors['site_name'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['site_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-end d-block">توضیح کوتاه سیستم</label>
                                                <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(old('site_tagline', $settings['site_tagline'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیریت یکپارچه ارزیابی و آموزش">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-end d-block">لوگوی سیستم <span class="text-gray-400">(PNG, JPG, WEBP)</span></label>
                                                <?php if ($systemLogoUrl): ?>
                                                    <div class="border border-gray-100 rounded-12 p-12 mb-12 bg-white text-center">
                                                        <img src="<?= $systemLogoUrl; ?>" alt="لوگوی سیستم" class="img-fluid" style="max-height: 120px; object-fit: contain;">
                                                    </div>
                                                <?php else: ?>
                                                    <div class="border border-dashed border-gray-200 rounded-12 p-24 mb-12 text-gray-400 text-sm">
                                                        هنوز لوگویی برای سیستم بارگذاری نشده است.
                                                    </div>
                                                <?php endif; ?>
                                                <input type="file" name="system_logo" class="form-control" accept="image/png, image/jpeg, image/gif, image/webp">
                                                <small class="text-12 text-gray-500 d-block mt-6 text-end">در صورت عدم انتخاب فایل، لوگوی فعلی بدون تغییر باقی می‌ماند.</small>
                                                <?php if (!empty($validationErrors['system_logo'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['system_logo'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-end d-block">آواتار پیش‌فرض سیستم <span class="text-gray-400">(PNG, JPG, WEBP)</span></label>
                                                    <div class="border border-gray-100 rounded-12 p-20 mb-12 bg-white text-center">
                                                        <img src="<?= $systemDefaultAvatarUrl; ?>" alt="آواتار پیش‌فرض سیستم" class="rounded-circle" style="width: 108px; height: 108px; object-fit: cover;">
                                                        <div class="mt-12 text-13 text-gray-500">
                                                            <?= $hasCustomDefaultAvatar ? 'یک تصویر سفارشی به عنوان آواتار پیش‌فرض استفاده می‌شود.' : 'در حال حاضر از تصویر پیش‌فرض سیستم استفاده می‌شود.'; ?>
                                                        </div>
                                                    </div>
                                                    <input type="file" name="system_default_avatar" class="form-control" accept="image/png, image/jpeg, image/gif, image/webp">
                                                    <small class="text-12 text-gray-500 d-block mt-6 text-end">با بارگذاری تصویر جدید، برای کاربرانی که تصویر شخصی ندارند این تصویر نمایش داده می‌شود.</small>
                                                    <?php if (!empty($validationErrors['system_default_avatar'])): ?>
                                                        <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['system_default_avatar'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                    <?php endif; ?>
                                                    <div class="form-check form-switch text-end mt-12">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="resetSystemDefaultAvatar" name="reset_system_default_avatar" value="1" <?= $resetDefaultAvatarChecked ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="resetSystemDefaultAvatar">بازگشت به تصویر پیش‌فرض اولیه سیستم</label>
                                                    </div>
                                                    <small class="text-gray-400 d-block mt-4">در صورت فعال‌سازی، تصویر سفارشی حذف شده و تصویر پیش‌فرض سیستم اعمال می‌شود.</small>
                                                    <?php if ($resetDefaultAvatarChecked && !empty($systemDefaultAvatarStoredPath) && $systemDefaultAvatarStoredPath !== $defaultAvatarFallbackPath): ?>
                                                        <small class="text-warning d-block mt-2">با ذخیره تنظیمات، تصویر سفارشی فعلی حذف خواهد شد.</small>
                                                    <?php endif; ?>
                                                </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold text-end d-block">پیام خوش‌آمدگویی داشبورد</label>
                                                <textarea name="dashboard_welcome_message" rows="3" class="form-control" placeholder="پیامی که در بالای داشبورد به کاربران نمایش داده می‌شود."><?= htmlspecialchars(old('dashboard_welcome_message', $settings['dashboard_welcome_message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-100 rounded-16 p-24 mb-24">
                                        <div class="d-flex justify-content-between align-items-center mb-20">
                                            <span class="badge bg-main-100 text-main-700 rounded-pill py-6 px-12">تماس کاربران</span>
                                            <div class="text-end">
                                                <h5 class="mb-4">اطلاعات پشتیبانی</h5>
                                                <p class="text-gray-500 mb-0">راه‌های ارتباطی کاربران با تیم پشتیبانی را مشخص کنید.</p>
                                            </div>
                                        </div>
                                        <div class="row g-16">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold text-end d-block">ایمیل پشتیبانی</label>
                                                <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars(old('support_email', $settings['support_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="support@example.com">
                                                <?php if (!empty($validationErrors['support_email'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['support_email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold text-end d-block">شماره تماس پشتیبانی</label>
                                                <input type="text" name="support_phone" class="form-control" value="<?= htmlspecialchars(old('support_phone', $settings['support_phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: +98 21 1234 5678">
                                                <?php if (!empty($validationErrors['support_phone'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['support_phone'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-100 rounded-16 p-24 mb-24">
                                        <div class="d-flex justify-content-between align-items-center mb-20">
                                            <span class="badge bg-main-100 text-main-700 rounded-pill py-6 px-12">پایداری و دسترسی</span>
                                            <div class="text-end">
                                                <h5 class="mb-4">تنظیمات سیستم</h5>
                                                <p class="text-gray-500 mb-0">زبان پیش‌فرض، منطقه زمانی و وضعیت سامانه را مشخص کنید.</p>
                                            </div>
                                        </div>
                                        <div class="row g-16">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold text-end d-block">زبان پیش‌فرض سامانه</label>
                                                <select name="default_language" class="form-select">
                                                    <?php foreach ($languageOptions as $code => $label): ?>
                                                        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" <?= $code === $selectedLanguage ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if (!empty($validationErrors['default_language'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['default_language'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold text-end d-block">منطقه زمانی</label>
                                                <select name="timezone" class="form-select">
                                                    <?php foreach ($timezoneOptions as $timezone => $label): ?>
                                                        <option value="<?= htmlspecialchars($timezone, ENT_QUOTES, 'UTF-8'); ?>" <?= $timezone === $selectedTimezone ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <?php if (!empty($validationErrors['timezone'])): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= htmlspecialchars($validationErrors['timezone'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="row g-16 mt-8">
                                            <div class="col-md-6">
                                                <div class="form-check form-switch text-end">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="maintenanceMode" name="maintenance_mode" value="1" <?= $maintenanceChecked ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="maintenanceMode">فعال‌سازی حالت نگهداری سامانه</label>
                                                </div>
                                                <small class="text-gray-400 d-block mt-4">در حالت نگهداری، کاربران عادی به سامانه دسترسی نخواهند داشت.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check form-switch text-end">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="allowRegistration" name="allow_registration" value="1" <?= $registrationChecked ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="allowRegistration">اجازه ثبت‌نام کاربران جدید</label>
                                                </div>
                                                <small class="text-gray-400 d-block mt-4">در صورت غیرفعالسازی، ثبت‌نام کاربران جدید متوقف می‌شود.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border border-gray-100 rounded-16 p-24">
                                        <div class="d-flex justify-content-between align-items-center mb-20">
                                            <span class="badge bg-main-100 text-main-700 rounded-pill py-6 px-12">پیشرفته</span>
                                            <div class="text-end">
                                                <h5 class="mb-4">کدهای تحلیلی و یکپارچه‌سازی</h5>
                                                <p class="text-gray-500 mb-0">اسکریپت‌های ردیابی و آنالیز (مانند Google Analytics) را در این بخش قرار دهید.</p>
                                            </div>
                                        </div>
                                        <textarea name="analytics_script" rows="6" class="form-control" placeholder="کد اسکریپت یا تگ تحلیل خود را در اینجا قرار دهید."><?= htmlspecialchars(old('analytics_script', $settings['analytics_script'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        <small class="text-gray-400 d-block mt-6 text-end">کدهای HTML/JS وارد شده به‌صورت مستقیم در قالب سایت قرار می‌گیرند. از معتبر بودن آن‌ها اطمینان حاصل کنید.</small>
                                    </div>

                                    <div class="d-flex justify-content-end mt-32">
                                        <button type="submit" class="btn btn-main rounded-pill px-32">
                                            <i class="fas fa-save ms-6"></i>
                                            ذخیره تنظیمات
                                        </button>
                                    </div>
                                </div>

                                <div class="col-xxl-4 col-lg-5">
                                    <div class="border border-gray-100 rounded-16 p-24 mb-24 bg-main-25">
                                        <h5 class="mb-16">مرور کلی وضعیت سامانه</h5>
                                        <ul class="list-unstyled mb-0 text-gray-600">
                                            <li class="d-flex justify-content-between align-items-center border-bottom border-gray-100 pb-12 mb-12">
                                                <span>وضعیت نگهداری</span>
                                                <span class="fw-semibold <?= $maintenanceChecked ? 'text-danger' : 'text-success'; ?>"><?= $maintenanceChecked ? 'فعال' : 'غیرفعال'; ?></span>
                                            </li>
                                            <li class="d-flex justify-content-between align-items-center border-bottom border-gray-100 pb-12 mb-12">
                                                <span>ثبت‌نام کاربران</span>
                                                <span class="fw-semibold <?= $registrationChecked ? 'text-success' : 'text-warning'; ?>"><?= $registrationChecked ? 'مجاز' : 'محدود'; ?></span>
                                            </li>
                                            <li class="d-flex justify-content-between align-items-center border-bottom border-gray-100 pb-12 mb-12">
                                                <span>زبان پیش‌فرض</span>
                                                <span class="fw-semibold"><?= htmlspecialchars($languageOptions[$selectedLanguage] ?? $selectedLanguage, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </li>
                                            <li class="d-flex justify-content-between align-items-center">
                                                <span>منطقه زمانی</span>
                                                <span class="fw-semibold"><?= htmlspecialchars($timezoneOptions[$selectedTimezone] ?? $selectedTimezone, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="border border-gray-100 rounded-16 p-24">
                                        <h5 class="mb-16">راهنمای سریع</h5>
                                        <ul class="list-unstyled text-gray-500 mb-0">
                                            <li class="d-flex align-items-start gap-8 mb-12">
                                                <span class="text-main-600 text-lg"><i class="fas fa-lightbulb"></i></span>
                                                <span>برای اعمال تغییرات گسترده پیش از ذخیره، یک نسخه پشتیبان تهیه کنید.</span>
                                            </li>
                                            <li class="d-flex align-items-start gap-8 mb-12">
                                                <span class="text-main-600 text-lg"><i class="fas fa-shield-alt"></i></span>
                                                <span>اطمینان حاصل کنید که ایمیل و شماره تماس پشتیبانی همیشه به‌روز باشد.</span>
                                            </li>
                                            <li class="d-flex align-items-start gap-8 mb-0">
                                                <span class="text-main-600 text-lg"><i class="fas fa-user-shield"></i></span>
                                                <span>فعال‌سازی حالت نگهداری برای بروزرسانی‌های حساس توصیه می‌شود.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
    </div>
</div>

<?php unset($_SESSION['old_input']); ?>
