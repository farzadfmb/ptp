<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'پروفایل کاربری';
$user = $user ?? (class_exists('AuthHelper') ? AuthHelper::getUser() : null);
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$displayName = is_array($user)
    ? trim(($user['name'] ?? '') !== '' ? $user['name'] : trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')))
    : '';
if ($displayName === '') {
    $displayName = 'کاربر سیستم';
}

$inline_styles .= <<<'CSS'
.profile-card { border-radius: 24px; border: 1px solid rgba(148,163,184,.2); box-shadow: 0 18px 32px rgba(15,23,42,.06); }
.profile-card .card-body { padding: 28px; }
CSS;

include __DIR__ . '/../../layouts/home-header.php';
include __DIR__ . '/../../layouts/home-sidebar.php';
$navbarUser = $user;
?>

<?php include __DIR__ . '/../../layouts/home-navbar.php'; ?>

<div class="page-content-wrapper">
  <div class="page-content">
    <div class="row g-4">
      <div class="col-12">
        <div class="card profile-card border-0">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <h2 class="h5 mb-1">پروفایل کاربری</h2>
                <div class="text-secondary small">اطلاعات حساب کاربری شما</div>
              </div>
              <div class="text-end">
                <a href="<?= UtilityHelper::baseUrl('home'); ?>" class="btn btn-outline-secondary rounded-pill">بازگشت به داشبورد</a>
              </div>
            </div>
            <hr>
            <?php if (!empty($successMessage)): ?>
              <div class="alert alert-success rounded-3 py-2 px-3 mb-3 small"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
              <div class="alert alert-danger rounded-3 py-2 px-3 mb-3 small"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div class="row g-4">
              <div class="col-md-6">
                <div class="mb-2 text-secondary">نام</div>
                <div class="fw-semibold"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="col-md-6">
                <div class="mb-2 text-secondary">نام کاربری</div>
                <div class="fw-semibold"><?= htmlspecialchars((string)($user['username'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="col-md-6">
                <div class="mb-2 text-secondary">ایمیل</div>
                <div class="fw-semibold"><?= htmlspecialchars((string)($user['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
              <div class="col-md-6">
                <div class="mb-2 text-secondary">موبایل</div>
                <div class="fw-semibold"><?= htmlspecialchars((string)($user['mobile'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
            </div>
            <hr>
            <?php
              $v = function($key, $fallback = '') use ($oldInput, $user, $orgUserRecord) {
                  if (isset($oldInput[$key])) { return (string)$oldInput[$key]; }
                  if (is_array($orgUserRecord) && isset($orgUserRecord[$key]) && $orgUserRecord[$key] !== null && $orgUserRecord[$key] !== '') { return (string)$orgUserRecord[$key]; }
                  if (is_array($user) && isset($user[$key]) && $user[$key] !== null && $user[$key] !== '') { return (string)$user[$key]; }
                  return $fallback;
              };
              $err = function($key) use ($validationErrors) {
                  return isset($validationErrors[$key]) ? '<div class="invalid-feedback d-block small">'.htmlspecialchars($validationErrors[$key], ENT_QUOTES, 'UTF-8').'</div>' : '';
              };
              $csrf = AuthHelper::generateCsrfToken();
            ?>
            <form class="mt-2" method="post" action="<?= UtilityHelper::baseUrl('profile'); ?>">
              <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small text-secondary">ایمیل</label>
                  <input type="email" class="form-control rounded-3 <?= isset($validationErrors['email']) ? 'is-invalid' : ''; ?>" name="email" value="<?= htmlspecialchars($v('email', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="example@email.com">
                  <?= $err('email'); ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">کد ملی</label>
                  <input type="text" class="form-control rounded-3 <?= isset($validationErrors['national_code']) ? 'is-invalid' : ''; ?>" name="national_code" value="<?= htmlspecialchars($v('national_code', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً 0012345678">
                  <?= $err('national_code'); ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">کد پرسنلی</label>
                  <input type="text" class="form-control rounded-3" name="personnel_code" value="<?= htmlspecialchars($v('personnel_code', ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">سمت سازمانی</label>
                  <input type="text" class="form-control rounded-3" name="organization_post" value="<?= htmlspecialchars($v('organization_post', ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">محل خدمت</label>
                  <input type="text" class="form-control rounded-3" name="service_location" value="<?= htmlspecialchars($v('service_location', ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">استان</label>
                  <input type="text" class="form-control rounded-3" name="province" value="<?= htmlspecialchars($v('province', ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-secondary">شهر</label>
                  <input type="text" class="form-control rounded-3" name="city" value="<?= htmlspecialchars($v('city', ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
              </div>
              <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-main rounded-pill px-4">ذخیره تغییرات</button>
                <a href="<?= UtilityHelper::baseUrl('profile'); ?>" class="btn btn-light rounded-pill">انصراف</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../layouts/home-footer.php'; ?>
