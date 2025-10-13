<?php
$title = 'نقش‌ها و سطح دسترسی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

$roles = $roles ?? [];
$organizations = $organizations ?? [];
$permissionsCatalog = $permissionsCatalog ?? [];
$validationErrors = $validationErrors ?? [];

$selectedScope = old('scope_type', 'global');
$selectedOrganization = old('organization_id', '');
$selectedPermissions = old('permissions', []);

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';

AuthHelper::startSession();
?>

<style>
    .roles-management-wrapper {
        direction: rtl;
    }
    .roles-management-wrapper label,
    .roles-management-wrapper h1,
    .roles-management-wrapper h2,
    .roles-management-wrapper h3,
    .roles-management-wrapper h4,
    .roles-management-wrapper h5,
    .roles-management-wrapper h6,
    .roles-management-wrapper p,
    .roles-management-wrapper span,
    .roles-management-wrapper small,
    .roles-management-wrapper a,
    .roles-management-wrapper li,
    .roles-management-wrapper .card-body,
    .roles-management-wrapper .badge,
    .roles-management-wrapper .alert,
    .roles-management-wrapper .form-check-label,
    .roles-management-wrapper .form-control,
    .roles-management-wrapper .form-select,
    .roles-management-wrapper .form-check,
    .roles-management-wrapper .table,
    .roles-management-wrapper .table th,
    .roles-management-wrapper .table td {
        text-align: right;
    }
    .roles-management-wrapper .permissions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
    }
    .roles-management-wrapper .permissions-category {
        border: 1px solid #eef1f5;
        border-radius: 12px;
        padding: 16px;
        background: #fff;
    }
    .roles-management-wrapper .permissions-category h6 {
        font-size: 14px;
        margin-bottom: 12px;
        color: #1c1f23;
    }
    .roles-management-wrapper .form-check {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
    }
    .roles-management-wrapper .form-check-input {
        margin-left: 0;
        margin-right: 0;
    }
    .organization-selector {
        transition: all 0.2s ease;
    }
    .organization-selector.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
</style>

<div class="dashboard-main-wrapper roles-management-wrapper" dir="rtl">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card border-0 box-shadow-custom">
                    <div class="card-body text-end">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-12 mb-20">
                            <div class="text-end">
                                <span class="badge bg-main-50 text-main-600 rounded-pill py-8 px-16 d-inline-flex align-items-center gap-8">
                                    <i class="fas fa-user-shield"></i>
                                    مجموع نقش‌ها: <?= UtilityHelper::englishToPersian(count($roles)); ?>
                                </span>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-4">مدیریت نقش‌ها و سطح دسترسی</h3>
                                <p class="text-gray-500 mb-0">نقش‌های جدید ایجاد کرده و دسترسی‌های مرتبط با هر نقش را تعیین کنید.</p>
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

                        <div class="border border-gray-100 rounded-16 p-24 bg-white mb-32">
                            <h5 class="mb-20">تعریف نقش جدید</h5>
                            <form action="<?= UtilityHelper::baseUrl('supperadmin/roles'); ?>" method="post">
                                <?= csrf_field(); ?>
                                <div class="row g-16">
                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">نام نقش <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars(old('name', ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدیر سامانه" required>
                                        <?php if (!empty($validationErrors['name'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['name'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">نوع سطح دسترسی <span class="text-danger">*</span></label>
                                        <div class="d-flex justify-content-end align-items-center gap-16">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="scope_type" id="scopeGlobal" value="global" <?= $selectedScope === 'global' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="scopeGlobal">سراسری</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="scope_type" id="scopeOrganization" value="organization" <?= $selectedScope === 'organization' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="scopeOrganization">مختص سازمان</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="scope_type" id="scopeSuperAdmin" value="superadmin" <?= $selectedScope === 'superadmin' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="scopeSuperAdmin">سوپر ادمین</label>
                                            </div>
                                        </div>
                                        <?php if (!empty($validationErrors['scope_type'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['scope_type'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">توضیحات نقش</label>
                                        <textarea name="description" rows="3" class="form-control" placeholder="شرح کوتاهی از مسئولیت‌ها و محدوده اختیارات نقش"><?= htmlspecialchars(old('description', ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">انتخاب سازمان</label>
                                        <select name="organization_id" id="organizationSelect" class="form-select organization-selector <?= $selectedScope === 'organization' ? '' : 'disabled'; ?>" <?= $selectedScope === 'organization' ? '' : 'disabled'; ?>>
                                            <option value="">انتخاب نشده</option>
                                            <?php foreach ($organizations as $organization): ?>
                                                <option value="<?= (int) $organization['id']; ?>" <?= (string) $selectedOrganization === (string) $organization['id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($organization['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-gray-500 d-block mt-6">برای نقش‌های سراسری یا سوپر ادمین این گزینه را خالی بگذارید.</small>
                                        <?php if (!empty($validationErrors['organization_id'])): ?>
                                            <small class="text-danger d-block mt-6"><?= htmlspecialchars($validationErrors['organization_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-24">
                                    <h6 class="mb-12">انتخاب دسترسی‌ها <span class="text-danger">*</span></h6>
                                    <?php if (!empty($validationErrors['permissions'])): ?>
                                        <small class="text-danger d-block mb-12"><?= htmlspecialchars($validationErrors['permissions'], ENT_QUOTES, 'UTF-8'); ?></small>
                                    <?php endif; ?>
                                    <div class="permissions-grid">
                                        <?php foreach ($permissionsCatalog as $category => $permissions): ?>
                                            <div class="permissions-category">
                                                <h6 class="fw-semibold mb-12 d-flex justify-content-between align-items-center gap-8">
                                                    <span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <span class="badge bg-main-25 text-main-600"><?= UtilityHelper::englishToPersian(count($permissions)); ?></span>
                                                </h6>
                                                <div class="d-flex flex-column gap-8">
                                                    <?php foreach ($permissions as $permission): ?>
                                                        <?php $isChecked = in_array($permission, (array) $selectedPermissions, true); ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'); ?>" id="perm_<?= md5($permission); ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="perm_<?= md5($permission); ?>">
                                                                <?= htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-32">
                                    <button type="submit" class="btn btn-main rounded-pill px-32">
                                        <i class="fas fa-save ms-6"></i>
                                        ثبت نقش جدید
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="border border-gray-100 rounded-16 p-24 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-16">
                                <span class="text-gray-500">مجموع: <?= UtilityHelper::englishToPersian(count($roles)); ?></span>
                                <h5 class="mb-0">لیست نقش‌های ثبت شده</h5>
                            </div>
                            <?php if (empty($roles)): ?>
                                <div class="py-40 text-center text-gray-500">
                                    <i class="fas fa-user-tag mb-16 text-3xl d-block"></i>
                                    هنوز نقشی ثبت نشده است.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-end mb-0">
                                        <thead class="bg-gray-50 text-gray-500">
                                            <tr>
                                                <th class="text-end">نام نقش</th>
                                                <th class="text-end">نوع سطح دسترسی</th>
                                                <th class="text-end">سازمان مرتبط</th>
                                                <th class="text-end">تعداد دسترسی‌ها</th>
                                                <th class="text-end">تاریخ ایجاد</th>
                                                <th class="text-center">اقدامات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($roles as $role): ?>
                                                <?php
                                                    $permissions = json_decode($role['permissions'] ?? '[]', true) ?: [];
                                                    $createdAt = $role['created_at'] ?? null;
                                                    $createdLabel = '-';
                                                    if ($createdAt) {
                                                        $timestamp = strtotime($createdAt);
                                                        if ($timestamp) {
                                                            $createdLabel = UtilityHelper::englishToPersian(date('H:i Y/m/d', $timestamp));
                                                        } else {
                                                            $createdLabel = UtilityHelper::englishToPersian($createdAt);
                                                        }
                                                    }

                                                    $scopeType = $role['scope_type'] ?? 'global';
                                                    $scopeLabels = [
                                                        'global' => 'سراسری',
                                                        'organization' => 'مختص سازمان',
                                                        'superadmin' => 'سوپر ادمین',
                                                    ];
                                                    $scopeBadgeClasses = [
                                                        'global' => 'bg-main-25 text-main-600',
                                                        'organization' => 'bg-primary-25 text-primary-600',
                                                        'superadmin' => 'bg-danger-25 text-danger-600',
                                                    ];
                                                    $scopeLabel = $scopeLabels[$scopeType] ?? 'نامشخص';
                                                    $scopeBadgeClass = $scopeBadgeClasses[$scopeType] ?? 'bg-gray-100 text-gray-600';

                                                    $orgLabel = $role['organization_name'] ? htmlspecialchars($role['organization_name'], ENT_QUOTES, 'UTF-8') : '---';
                                                    $editUrl = UtilityHelper::baseUrl('supperadmin/roles/edit') . '?id=' . (int) $role['id'];
                                                    $deleteUrl = UtilityHelper::baseUrl('supperadmin/roles/delete');
                                                    $isSuperAdminRole = isset($role['slug']) && $role['slug'] === 'super-admin';
                                                    $canDeleteRole = !$isSuperAdminRole;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($role['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if (!empty($role['description'])): ?>
                                                            <div class="text-gray-500 text-sm mt-4">
                                                                <?= nl2br(htmlspecialchars($role['description'], ENT_QUOTES, 'UTF-8')); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="badge <?= $scopeBadgeClass; ?>"><?= $scopeLabel; ?></span></td>
                                                    <td><?= $orgLabel; ?></td>
                                                    <td><?= UtilityHelper::englishToPersian(count($permissions)); ?></td>
                                                    <td><?= $createdLabel; ?></td>
                                                    <td class="text-center">
                                                        <div class="d-inline-flex flex-wrap justify-content-center gap-8">
                                                            <a href="<?= $editUrl; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-edit ms-4"></i>
                                                                ویرایش
                                                            </a>
                                                            <form action="<?= $deleteUrl; ?>" method="post" class="d-inline" onsubmit="return confirm('آیا از حذف این نقش اطمینان دارید؟');">
                                                                <?= csrf_field(); ?>
                                                                <input type="hidden" name="role_id" value="<?= (int) $role['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" <?= $canDeleteRole ? '' : 'disabled'; ?>>
                                                                    <i class="fas fa-trash ms-4"></i>
                                                                    حذف
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scopeRadios = document.querySelectorAll('input[name="scope_type"]');
        const organizationSelect = document.getElementById('organizationSelect');

        function toggleOrganizationSelect() {
            const selected = document.querySelector('input[name="scope_type"]:checked');
            if (!selected) return;
            if (selected.value === 'organization') {
                organizationSelect.classList.remove('disabled');
                organizationSelect.removeAttribute('disabled');
            } else {
                organizationSelect.classList.add('disabled');
                organizationSelect.setAttribute('disabled', 'disabled');
                organizationSelect.value = '';
            }
        }

        scopeRadios.forEach(function (radio) {
            radio.addEventListener('change', toggleOrganizationSelect);
        });

        toggleOrganizationSelect();
    });
</script>

<?php unset($_SESSION['old_input']); ?>
