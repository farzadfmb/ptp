<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

if (!isset($navbarUser)) {
    AuthHelper::startSession();
    $navbarUser = AuthHelper::getUser();
}

$navbarSettings = $navbarSettings ?? null;
if ($navbarSettings === null) {
    try {
        $navbarSettings = DatabaseHelper::fetchOne('SELECT site_name, system_default_avatar_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $exception) {
        $navbarSettings = null;
    }
}

$defaultAvatarPath = 'assets/images/thumbs/user-img.png';
$systemDefaultAvatarPath = $navbarSettings['system_default_avatar_path'] ?? null;
$systemDefaultAvatarPath = $systemDefaultAvatarPath ?: $defaultAvatarPath;

$userAvatarPath = $navbarUser['avatar_path'] ?? null;
$resolvedAvatarPath = trim($userAvatarPath ?: $systemDefaultAvatarPath);
if ($resolvedAvatarPath === '') {
    $resolvedAvatarPath = $defaultAvatarPath;
}

if (preg_match('/^https?:\/\//i', $resolvedAvatarPath)) {
    $navbarAvatarUrl = $resolvedAvatarPath;
} else {
    $relativeAvatarPath = ltrim($resolvedAvatarPath, '/');
    if (strpos($relativeAvatarPath, 'public/') === 0) {
        $navbarAvatarUrl = UtilityHelper::baseUrl($relativeAvatarPath);
    } else {
        $navbarAvatarUrl = UtilityHelper::baseUrl('public/' . $relativeAvatarPath);
    }
}

$navbarFullName = trim(($navbarUser['name'] ?? '') !== '' ? $navbarUser['name'] : trim(($navbarUser['first_name'] ?? '') . ' ' . ($navbarUser['last_name'] ?? '')));
if ($navbarFullName === '') {
    $navbarFullName = 'مدیر سیستم';
}

$navbarEmail = $navbarUser['email'] ?? 'admin@example.com';
?>
        <div class="top-navbar flex-between gap-16">
            <div class="flex-align gap-16">
                <!-- Toggle Button Start -->

                <!-- Toggle Button End -->

                <form action="#" class="w-350 d-sm-block d-none">
                    <div class="position-relative">
                        <button type="submit" class="input-icon text-xl d-flex text-gray-100 pointer-event-none">
                            <i class="fas fa-search"></i>
                        </button>
                        <input type="text" class="form-control pe-40 h-40 border-transparent focus-border-main-600 bg-main-50 rounded-pill placeholder-15" placeholder="جستجو...">
                    </div>
                </form>

                <div class="text-gray-300 d-none d-md-flex align-items-center gap-8">
                    <span class="text-xl text-primary-600 d-flex"><i class="fas fa-calendar-alt"></i></span>
                    <span class="text-15 fw-medium">
                        <?php echo UtilityHelper::getTodayDate(); ?>
                    </span>
                </div>
            </div>

            <div class="flex-align gap-16">
                <div class="flex-align gap-8">
                    <!-- Notification Start -->
                    <div class="dropdown">
                        <button class="dropdown-btn shaking-animation text-gray-500 w-40 h-40 bg-main-50 hover-bg-main-100 transition-2 rounded-circle text-xl flex-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="position-relative">
                                <i class="fas fa-bell"></i>
                                <span class="alarm-notify position-absolute end-0"></span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu--lg border-0 bg-transparent p-0">
                            <div class="card border border-gray-100 rounded-12 box-shadow-custom p-0 overflow-hidden">
                                <div class="card-body p-0">
                                    <div class="py-8 px-24 bg-main-600">
                                        <div class="flex-between">
                                            <h5 class="text-xl fw-semibold text-white mb-0">اعلان‌ها</h5>
                                            <div class="flex-align gap-12">
                                                <button type="button" class="bg-white rounded-6 text-sm px-8 py-2 hover-text-primary-600">جدید</button>
                                                <button type="button" class="close-dropdown hover-scale-1 text-xl text-white">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-24 max-h-270 overflow-y-auto scroll-sm">
                                        <div class="d-flex align-items-start gap-12">
                                            <img src="<?php echo UtilityHelper::baseUrl('public/assets/images/thumbs/notification-img1.png'); ?>" alt="" class="w-48 h-48 rounded-circle object-fit-cover">
                                            <div class="border-bottom border-gray-100 mb-24 pb-24">
                                                <div class="flex-align gap-4">
                                                    <a href="#" class="fw-medium text-15 mb-0 text-gray-300 hover-text-main-600 text-line-2">اشوین بوس درخواست دسترسی به فایل طراحی - پروژه نهایی دارد.</a>
                                                    <!-- Three Dot Dropdown Start -->
                                                    <div class="dropdown flex-shrink-0">
                                                        <button class="text-gray-200 rounded-4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu--md border-0 bg-transparent p-0">
                                                            <div class="card border border-gray-100 rounded-12 box-shadow-custom">
                                                                <div class="card-body p-12">
                                                                    <div class="max-h-200 overflow-y-auto scroll-sm pe-8">
                                                                        <ul>
                                                                            <li class="mb-0">
                                                                                <a href="#" class="py-6 text-15 px-8 hover-bg-gray-50 text-gray-300 rounded-8 fw-normal text-xs d-block">
                                                                                    <span class="text">علامت‌گذاری به عنوان خوانده شده</span>
                                                                                </a>
                                                                            </li>
                                                                            <li class="mb-0">
                                                                                <a href="#" class="py-6 text-15 px-8 hover-bg-gray-50 text-gray-300 rounded-8 fw-normal text-xs d-block">
                                                                                    <span class="text">حذف اعلان</span>
                                                                                </a>
                                                                            </li>
                                                                            <li class="mb-0">
                                                                                <a href="#" class="py-6 text-15 px-8 hover-bg-gray-50 text-gray-300 rounded-8 fw-normal text-xs d-block">
                                                                                    <span class="text">گزارش</span>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Three Dot Dropdown End -->
                                                </div>
                                                <div class="flex-align gap-6 mt-8">
                                                    <img src="<?php echo UtilityHelper::baseUrl('public/assets/images/icons/google-drive.png'); ?>" alt="">
                                                    <div class="flex-align gap-4">
                                                        <p class="text-gray-900 text-sm text-line-1">خلاصه طراحی و ایده‌ها.txt</p>
                                                        <span class="text-xs text-gray-200 flex-shrink-0">۲.۲ مگابایت</span>
                                                    </div>
                                                </div>
                                                <div class="mt-16 flex-align gap-8">
                                                    <button type="button" class="btn btn-main py-8 text-15 fw-normal px-16">پذیرش</button>
                                                    <button type="button" class="btn btn-outline-gray py-8 text-15 fw-normal px-16">رد</button>
                                                </div>
                                                <span class="text-gray-200 text-13 mt-8">۲ دقیقه پیش</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start gap-12">
                                            <img src="<?php echo UtilityHelper::baseUrl('public/assets/images/thumbs/notification-img2.png'); ?>" alt="" class="w-48 h-48 rounded-circle object-fit-cover">
                                            <div class="">
                                                <a href="#" class="fw-medium text-15 mb-0 text-gray-300 hover-text-main-600 text-line-2">پاتریک نظری بر فایل منابع طراحی - برچسب‌های هوشمند افزود:</a>
                                                <span class="text-gray-200 text-13">۲ دقیقه پیش</span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="#" class="py-13 px-24 fw-bold text-center d-block text-primary-600 border-top border-gray-100 hover-text-decoration-underline">مشاهده همه</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Notification End -->

                
                </div>

                <!-- User Profile Start -->
                <div class="dropdown">
                    <button class="users arrow-down-icon border border-gray-200 rounded-pill p-4 d-inline-block pe-40 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="position-relative">
                            <img src="<?= htmlspecialchars($navbarAvatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="آواتار کاربر" class="h-32 w-32 rounded-circle object-fit-cover">
                            <span class="activation-badge w-8 h-8 position-absolute inset-block-end-0 inset-inline-end-0"></span>
                        </span>
                        <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu--lg border-0 bg-transparent p-0">
                        <div class="card border border-gray-100 rounded-12 box-shadow-custom">
                            <div class="card-body">
                                <div class="flex-align gap-8 mb-20 pb-20 border-bottom border-gray-100">
                                    <img src="<?= htmlspecialchars($navbarAvatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="آواتار کاربر" class="w-54 h-54 rounded-circle object-fit-cover">
                                    <div class="">
                                        <h4 class="mb-0"><?= htmlspecialchars($navbarFullName, ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <p class="fw-medium text-13 text-gray-200"><?= htmlspecialchars($navbarEmail, ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                                <ul class="max-h-270 overflow-y-auto scroll-sm pe-4">
                                    <li class="mb-4">
                                        <a href="<?php echo UtilityHelper::baseUrl('supperadmin/profile'); ?>" class="py-12 text-15 px-20 hover-bg-gray-50 text-gray-300 rounded-8 flex-align gap-8 fw-medium text-15">
                                            <span class="text-2xl text-primary-600 d-flex"><i class="fas fa-user"></i></span>
                                            <span class="text">پروفایل</span>
                                        </a>
                                    </li>
                                   
                                
                                    <li class="pt-8 border-top border-gray-100">
                                        <a href="<?= UtilityHelper::baseUrl('supperadmin/logout'); ?>" class="py-12 text-15 px-20 hover-bg-danger-50 text-gray-300 hover-text-danger-600 rounded-8 flex-align gap-8 fw-medium text-15">
                                            <span class="text-2xl text-danger-600 d-flex"><i class="fas fa-sign-out-alt"></i></span>
                                            <span class="text">خروج</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- User Profile End -->
                <button type="button" class="toggle-btn d-xl-none d-flex text-26 text-gray-500" aria-label="باز کردن منوی کناری" aria-expanded="false" aria-controls="sidebar">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>