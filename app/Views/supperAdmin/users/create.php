<?php
$title = 'ایجاد کاربر جدید';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

$roles = $roles ?? [];
$organizations = $organizations ?? [];
$validationErrors = $validationErrors ?? [];

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();

$selectedRoleId = old('role_id', '');
$selectedRoleScope = null;
foreach ($roles as $roleCandidate) {
    if ((string) ($roleCandidate['id'] ?? '') === (string) $selectedRoleId) {
        $selectedRoleScope = mb_strtolower($roleCandidate['scope_type'] ?? 'global', 'UTF-8');
        break;
    }
}
$requiresOrganization = $selectedRoleScope === 'organization';
$hasOrganizations = !empty($organizations);
?>

<div class="dashboard-main-wrapper" dir="rtl">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 box-shadow-custom">
                    <div class="card-body text-end">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-12 mb-20">
                            <div>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/users'); ?>" class="btn btn-outline-main rounded-pill px-24">
                                    <i class="fas fa-list ms-6"></i>
                                    بازگشت به فهرست کاربران
                                </a>
                            </div>
                            <div>
                                <h3 class="mb-4">ایجاد کاربر جدید</h3>
                                <p class="text-gray-500 mb-0">اطلاعات کاربر را تکمیل کرده و نقش کاربری مورد نظر را انتخاب کنید.</p>
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

                        <?php if (!empty($validationErrors) && empty($errorMessage)): ?>
                            <div class="alert alert-warning rounded-12 text-end d-flex align-items-center gap-8" role="alert">
                                <i class="fas fa-info-circle text-lg"></i>
                                <span>لطفاً خطاهای مشخص شده در فرم را بررسی کنید.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <form action="<?= UtilityHelper::baseUrl('supperadmin/users'); ?>" method="post" class="card">
                    <div class="card-body text-end">
                        <?= csrf_field(); ?>

                        <div class="row g-16">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نام <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars(old('first_name'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: علی" required>
                                <?php if (!empty($validationErrors['first_name'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['first_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نام خانوادگی <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars(old('last_name'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: محمدی" required>
                                <?php if (!empty($validationErrors['last_name'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['last_name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">ایمیل <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars(old('email'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: user@example.com" required>
                                <?php if (!empty($validationErrors['email'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">شماره تماس</label>
                                <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars(old('mobile'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 09121234567">
                                <?php if (!empty($validationErrors['mobile'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['mobile'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">کد ملی</label>
                                <input type="text" name="national_code" class="form-control" value="<?= htmlspecialchars(old('national_code'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 1234567890" maxlength="10">
                                <?php if (!empty($validationErrors['national_code'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['national_code'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">وضعیت کاربر</label>
                                <?php $selectedStatus = old('status', 'active'); ?>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $selectedStatus === 'active' ? 'selected' : ''; ?>>فعال</option>
                                    <option value="inactive" <?= $selectedStatus === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                </select>
                                <?php if (!empty($validationErrors['status'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['status'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نقش کاربری <span class="text-danger">*</span></label>
                                <select name="role_id" id="roleSelect" class="form-select" required>
                                    <option value="">انتخاب نقش</option>
                                    <?php foreach ($roles as $roleItem): ?>
                                        <?php
                                            $scopeTypeRaw = $roleItem['scope_type'] ?? 'global';
                                            $scopeType = mb_strtolower($scopeTypeRaw, 'UTF-8');
                                            $scopeLabel = 'سراسری';
                                            if ($scopeType === 'organization') {
                                                $scopeLabel = 'سازمانی';
                                            } elseif ($scopeType === 'superadmin') {
                                                $scopeLabel = 'سوپر ادمین';
                                            }
                                        ?>
                                        <option value="<?= (int) $roleItem['id']; ?>" data-scope="<?= htmlspecialchars($scopeType, ENT_QUOTES, 'UTF-8'); ?>" <?= (string) $selectedRoleId === (string) ($roleItem['id'] ?? '') ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars(($roleItem['name'] ?? 'بدون عنوان') . ' (' . $scopeLabel . ')', ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!empty($validationErrors['role_id'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['role_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">سازمان مرتبط</label>
                                <select name="organization_id" id="organizationSelect" class="form-select" <?= $requiresOrganization ? 'required' : ''; ?> data-requires-org="<?= $requiresOrganization ? '1' : '0'; ?>">
                                    <option value="">انتخاب سازمان</option>
                                    <?php foreach ($organizations as $organization): ?>
                                        <option value="<?= (int) $organization['id']; ?>" <?= (string) old('organization_id', '') === (string) $organization['id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($organization['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$hasOrganizations): ?>
                                    <small class="text-danger d-block mt-6">هنوز سازمانی ثبت نشده است. ابتدا از بخش مدیریت سازمان‌ها یک سازمان ایجاد کنید.</small>
                                <?php else: ?>
                                    <small class="text-gray-500 d-block mt-6">برای نقش‌های سازمانی یک سازمان را انتخاب کنید؛ برای نقش‌های سراسری یا سوپر ادمین خالی بگذارید.</small>
                                <?php endif; ?>
                                <?php if (!empty($validationErrors['organization_id'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['organization_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رمز عبور <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="حداقل ۸ کاراکتر" required>
                                <?php if (!empty($validationErrors['password'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['password'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تکرار رمز عبور <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="تکرار رمز عبور" required>
                                <?php if (!empty($validationErrors['password_confirmation'])): ?>
                                    <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['password_confirmation'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($validationErrors['general'])): ?>
                            <div class="alert alert-danger rounded-12 text-end d-flex align-items-center gap-8 mt-24" role="alert">
                                <i class="fas fa-times-circle text-lg"></i>
                                <span><?= htmlspecialchars($validationErrors['general'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end mt-32">
                            <button type="submit" class="btn btn-main rounded-pill px-32">
                                <i class="fas fa-save ms-6"></i>
                                ثبت کاربر
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>

<script>
    (function() {
        const roleSelect = document.getElementById('roleSelect');
        const organizationSelect = document.getElementById('organizationSelect');

        if (!roleSelect || !organizationSelect) {
            return;
        }

        const toggleOrganizationRequirement = () => {
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            const scope = selectedOption ? selectedOption.getAttribute('data-scope') : '';
            const isOrganizationRole = scope === 'organization';

            if (isOrganizationRole) {
                organizationSelect.removeAttribute('disabled');
                organizationSelect.setAttribute('required', 'required');
            } else {
                organizationSelect.removeAttribute('required');
                organizationSelect.value = '';
            }

            organizationSelect.dataset.requiresOrg = isOrganizationRole ? '1' : '0';
        };

        roleSelect.addEventListener('change', toggleOrganizationRequirement);
        toggleOrganizationRequirement();
    })();
</script>

<?php unset($_SESSION['old_input']); ?>
