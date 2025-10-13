<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'صفحه اصلی';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$inline_styles .= <<<'CSS'
    .home-hero {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(14, 165, 233, 0.2));
        border-radius: 24px;
        padding: 36px;
        color: #0f172a;
    }
    .home-hero h1 {
        font-size: 1.9rem;
        font-weight: 700;
    }
    .home-hero p {
        font-size: 1rem;
        color: rgba(15, 23, 42, 0.75);
    }
    .home-card {
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
    }
    .feature-list li::marker {
        color: #2563eb;
    }
    .home-note {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.05));
        border-radius: 20px;
        padding: 20px 24px;
    }
CSS;

AuthHelper::startSession();
$user = AuthHelper::getUser();
$navbarUser = $user;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';

?>

<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="home-hero mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-4">
                        <div>
                            <h1 class="mb-3">به پرتال کاربران خوش آمدید</h1>
                            <p class="mb-0">از اینجا می‌توانید به ابزارهای ارزیابی، دوره‌های توسعه فردی و گزارش‌های شخصی خود دسترسی پیدا کنید.</p>
                        </div>
                      
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card border-0 home-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-2 text-gray-900">داشبورد کاربری</h2>
                                <p class="text-gray-500 mb-0"><?= htmlspecialchars(($user['name'] ?? 'کاربر مهمان'), ENT_QUOTES, 'UTF-8'); ?> عزیز، از اینجا می‌توانید فعالیت‌های اخیر و مسیر یادگیری خود را مدیریت کنید.</p>
                            </div>
                         
                        </div>

                        <?php $flash = ResponseHelper::getFlash(); ?>
                        <?php if (!empty($flash)): ?>
                            <div class="flash-messages mb-3">
                                <?php foreach ($flash as $type => $message): ?>
                                    <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?> rounded-16 border-0" role="alert">
                                        <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h3 class="fw-semibold mb-3">امکانات سیستم</h3>
                        <ul class="feature-list text-gray-600 mb-4">
                            <li>مدیریت حساب کاربری و به‌روزرسانی اطلاعات شخصی</li>
                            <li>دسترسی به نتایج آزمون‌ها و دریافت گزارش‌های تحلیلی</li>
                            <li>مشاهده برنامه‌های توسعه فردی و ثبت پیشرفت</li>
                            <li>دریافت اعلان‌ها و پیام‌های پشتیبانی</li>
                        </ul>

                        <div class="home-note mt-4">
                            <h4 class="mb-3 text-primary fw-semibold">راهنمای شروع سریع</h4>
                            <ul class="mb-0 text-gray-700">
                                <li>اگر تازه ثبت‌نام کرده‌اید، از بخش «آزمون‌ها» شروع کنید و اولین ارزیابی خود را انجام دهید.</li>
                                <li>برای پیگیری پیشرفت، گزارش‌های شخصی را از داشبورد دریافت کنید.</li>
                                <li>هر زمان نیاز به راهنمایی داشتید، با پشتیبانی در تماس باشید.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 home-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <h3 class="fw-semibold text-gray-900 mb-3">مسیر بعدی شما</h3>
                        <p class="text-gray-500">با توجه به نقش شما در سازمان، توصیه می‌کنیم مراحل زیر را دنبال کنید:</p>
                        <ol class="text-gray-700 ps-3 mb-0">
                            <li class="mb-2">تکمیل آزمون‌های باز در لیست کارهای شما</li>
                            <li class="mb-2">بررسی پیشنهادهای توسعه فردی در پروفایل</li>
                            <li>رزرو جلسه بازخورد با مربی سازمانی</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
