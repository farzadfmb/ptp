    <!-- ============================ Sidebar Start ============================ -->
<?php
$sidebarSettings = $sidebarSettings ?? null;

if ($sidebarSettings === null) {
    try {
        $sidebarSettings = DatabaseHelper::fetchOne('SELECT site_name, system_logo_path, system_default_avatar_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $e) {
        $sidebarSettings = null;
    }
}

$sidebarSiteName = $sidebarSettings['site_name'] ?? 'سامانه مدیریت عملکرد';
$sidebarLogoPath = $sidebarSettings['system_logo_path'] ?? null;
$sidebarDefaultLogo = UtilityHelper::baseUrl('public/assets/images/logo/logo.png');
$sidebarLogoUrl = $sidebarLogoPath
    ? UtilityHelper::baseUrl('public/' . ltrim($sidebarLogoPath, '/'))
    : $sidebarDefaultLogo;
$sidebarDefaultAvatarPath = 'assets/images/thumbs/user-img.png';
$sidebarAvatarPath = $sidebarSettings['system_default_avatar_path'] ?? null;
$sidebarAvatarPath = $sidebarAvatarPath ?: $sidebarDefaultAvatarPath;
$sidebarAvatarUrl = UtilityHelper::baseUrl('public/' . ltrim($sidebarAvatarPath, '/'));
?>

<style>
    .sidebar__brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: right;
    }

    .sidebar__brand-logo {
        width: 42px;
        height: 42px;
        object-fit: contain;
    }

    .sidebar__brand-name {
        font-size: 17px;
        font-weight: 600;
        color: #111;
        white-space: nowrap;
    }

    @media (max-width: 1199px) {
        .sidebar__brand {
            justify-content: flex-start;
        }
    }
</style>

<aside class="sidebar" id="sidebar" role="navigation" aria-label="منوی اصلی">
    <!-- sidebar close btn -->
    <button type="button" class="sidebar-close-btn text-gray-500 hover-text-white hover-bg-main-600 text-md w-24 h-24 border border-gray-100 hover-border-main-600 d-xl-none d-flex flex-center rounded-circle position-absolute" style="right: 20px; left: auto;" aria-label="بستن منوی کناری">
        <i class="fas fa-times"></i>
    </button>
    <!-- sidebar close btn -->
    
    <div class="sidebar__logo text-center p-20 position-sticky inset-block-start-0 bg-transparent w-100 z-1 pb-10">
        <div class="sidebar__brand mb-16">
            <img src="<?= htmlspecialchars($sidebarLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="لوگوی سیستم" class="sidebar__brand-logo">
            <span class="sidebar__brand-name"><?= htmlspecialchars($sidebarSiteName, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <img src="<?= htmlspecialchars($sidebarAvatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="آواتار پیش‌فرض سیستم" class="large-logo rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
    </div>

    <div class="sidebar-menu-wrapper overflow-y-auto scroll-sm">
        <div class="p-20 pt-10">
            <ul class="sidebar-menu">
                <li class="sidebar-menu__item">
                    <a href="<?php echo UtilityHelper::baseUrl('supperadmin/dashboard'); ?>" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-tachometer-alt"></i></span>
                        <span class="text">داشبورد</span>
                    </a>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-building"></i></span>
                        <span class="text">مدیریت سازمان‌ها</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/organizations'); ?>" class="sidebar-submenu__link">لیست سازمان‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/organizations/create'); ?>" class="sidebar-submenu__link">ایجاد سازمان جدید</a>
                        </li>
              
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-user-cog"></i></span>
                        <span class="text">مدیریت کاربران</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/users'); ?>" class="sidebar-submenu__link">لیست کاربران</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/users/create'); ?>" class="sidebar-submenu__link">ایجاد کاربر جدید</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/roles'); ?>" class="sidebar-submenu__link">نقش‌ها و سطح دسترسی</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="text">مدیریت آزمون‌ها</span>
                    </a>
                    <ul class="sidebar-submenu">
                  
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/exams'); ?>" class="sidebar-submenu__link">آزمون‌ها</a>
                        </li>
                        
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-wallet"></i></span>
                        <span class="text">مدیریت مالی</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/finance/wallets'); ?>" class="sidebar-submenu__link">کیف پول سازمان‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/finance/transactions'); ?>" class="sidebar-submenu__link">تراکنش‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/finance/invoices'); ?>" class="sidebar-submenu__link">فاکتورها</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-layer-group"></i></span>
                        <span class="text">مدیریت پلن‌ها و تعرفه‌ها</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/plans'); ?>" class="sidebar-submenu__link">لیست پلن‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/plans/create'); ?>" class="sidebar-submenu__link">ایجاد پلن جدید</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/plans/pricing'); ?>" class="sidebar-submenu__link">تغییر تعرفه‌ها</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-newspaper"></i></span>
                        <span class="text">مدیریت محتوا</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/content/pages'); ?>" class="sidebar-submenu__link">صفحات CMS</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/content/announcements'); ?>" class="sidebar-submenu__link">اطلاعیه‌ها و اخبار</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/content/media'); ?>" class="sidebar-submenu__link">فایل‌ها و رسانه‌ها</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-chart-line"></i></span>
                        <span class="text">گزارش‌ها</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/reports/organizations'); ?>" class="sidebar-submenu__link">گزارش سازمان‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/reports/users'); ?>" class="sidebar-submenu__link">گزارش کاربران</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/reports/exams'); ?>" class="sidebar-submenu__link">گزارش آزمون‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/reports/finance'); ?>" class="sidebar-submenu__link">گزارش مالی</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-cogs"></i></span>
                        <span class="text">تنظیمات</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/settings/general'); ?>" class="sidebar-submenu__link">تنظیمات کلی سیستم</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/settings/notifications'); ?>" class="sidebar-submenu__link">تنظیمات ایمیل و پیامک</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/settings/security'); ?>" class="sidebar-submenu__link">امنیت و لاگ‌ها</a>
                        </li>
                    </ul>
                </li>

                <li class="sidebar-menu__item has-dropdown">
                    <a href="javascript:void(0)" class="sidebar-menu__link">
                        <span class="icon"><i class="fas fa-headset"></i></span>
                        <span class="text">پشتیبانی و تیکت‌ها</span>
                    </a>
                    <ul class="sidebar-submenu">
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/support/tickets'); ?>" class="sidebar-submenu__link">لیست تیکت‌ها</a>
                        </li>
                        <li class="sidebar-submenu__item">
                            <a href="<?php echo UtilityHelper::baseUrl('supperadmin/support/responses'); ?>" class="sidebar-submenu__link">پاسخ به تیکت‌ها</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
       
    </div>
</aside>    
<!-- ============================ Sidebar End  ============================ -->