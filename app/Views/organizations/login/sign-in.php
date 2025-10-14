<?php
$title = $title ?? 'ورود سازمان';
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$validationErrors = $validationErrors ?? [];
AuthHelper::startSession();
$oldInput = $_SESSION['old_input'] ?? [];
$authError = $authError ?? flash('error');
$authSuccess = $authSuccess ?? flash('success');
$assetsBase = UtilityHelper::baseUrl('public/assets');

$generalSettings = $generalSettings ?? null;
if ($generalSettings === null) {
    try {
        $generalSettings = DatabaseHelper::fetchOne('SELECT site_name, system_logo_path FROM system_settings ORDER BY id ASC LIMIT 1');
    } catch (Exception $exception) {
        $generalSettings = null;
    }
}

$systemLogoPath = $generalSettings['system_logo_path'] ?? null;
$systemLogoUrl = $systemLogoPath
    ? UtilityHelper::baseUrl('public/' . ltrim($systemLogoPath, '/'))
    : UtilityHelper::baseUrl('public/assets/images/logo/logo.png');

$rightIllustrationUrl = UtilityHelper::baseUrl('public/images/superAdmin/admin-control-panel-illustration-svg-download-png-3722637.png');
include __DIR__ . '/../../../Views/layouts/auth-header.php';
?>

<style>
    @media (max-width: 991.98px) {
        .auth {
            flex-direction: column;
        }

        .auth-left {
            display: none !important;
        }
    }

    .auth-right__inner .form-label,
    .auth-right__inner .form-check-label {
        display: block;
        text-align: right;
    }
</style>

<section class="auth d-flex flex-row-reverse">
    <div class="auth-left bg-main-50 flex-center p-24">
        <img src="<?= htmlspecialchars($rightIllustrationUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="ورود سازمان" style="max-width: 100%; height: auto;">
    </div>
    <div class="auth-right py-40 px-24 flex-center flex-column" dir="rtl">
        <div class="auth-right__inner mx-auto w-100" style="max-width: 440px;">
            <div class="text-center mb-24">
                <img src="<?= htmlspecialchars($systemLogoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="لوگوی سامانه" style="max-height: 96px; object-fit: contain;">
            </div>
            <h2 class="mb-8 text-start">به پنل سازمانی سامانه مدیریت عملکرد خوش آمدید</h2>
            <p class="text-gray-600 text-15 mb-32 text-start">برای ورود به پنل سازمان، اطلاعات حساب سازمانی خود را وارد کنید.</p>

            <?php if (!empty($authSuccess)): ?>
                <div class="alert alert-success text-end" role="alert">
                    <?= htmlspecialchars($authSuccess, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($authError)): ?>
                <div class="alert alert-danger text-end" role="alert">
                    <?= htmlspecialchars($authError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form action="<?= UtilityHelper::baseUrl('organizations/login'); ?>" method="post" class="text-end">
                <?= csrf_field(); ?>
                <div class="mb-24">
                    <label for="identifier" class="form-label mb-8 h6">ایمیل، نام کاربری یا کد ملی سازمانی</label>
                    <div class="position-relative">
                        <input type="text" name="email" class="form-control py-11 ps-40" id="identifier" placeholder="example@organization.com ، org.admin یا 0012345678" value="<?= htmlspecialchars(old('email', $oldInput['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                        <span class="position-absolute top-50 translate-middle-y ms-16 text-gray-600 d-flex"><i class="ph ph-user"></i></span>
                    </div>
                    <?php if (!empty($validationErrors['email'])): ?>
                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
                <div class="mb-24">
                    <label for="password" class="form-label mb-8 h6">رمز عبور</label>
                    <div class="position-relative">
                        <input type="password" name="password" class="form-control py-11 ps-40" id="password" placeholder="رمز عبور" required>
                        <span class="toggle-password position-absolute top-50 inset-inline-end-0 me-16 translate-middle-y ph ph-eye-slash" data-target="#password"></span>
                        <span class="position-absolute top-50 translate-middle-y ms-16 text-gray-600 d-flex"><i class="ph ph-lock"></i></span>
                    </div>
                    <?php if (!empty($validationErrors['password'])): ?>
                        <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['password'], ENT_QUOTES, 'UTF-8'); ?></small>
                    <?php endif; ?>
                </div>
                <div class="mb-32 flex-between flex-wrap gap-8">
                    <div class="form-check mb-0 flex-shrink-0">
                        <input class="form-check-input flex-shrink-0 rounded-4" type="checkbox" value="1" id="remember" name="remember_me" <?= old('remember_me', $oldInput['remember_me'] ?? '') ? 'checked' : ''; ?>>
                        <label class="form-check-label text-15 flex-grow-1" for="remember">مرا به خاطر بسپار</label>
                    </div>
                    <a href="<?= UtilityHelper::baseUrl('organizations/password/forgot'); ?>" class="text-main-600 hover-text-decoration-underline text-15 fw-medium">فراموشی رمز عبور</a>
                </div>
                <button type="submit" class="btn btn-main rounded-pill w-100">ورود به پنل سازمان</button>
            </form>
        </div>
    </div>
</section>

<?php
include __DIR__ . '/../../../Views/layouts/auth-footer.php';
unset($_SESSION['validation_errors'], $_SESSION['old_input']);
?>
