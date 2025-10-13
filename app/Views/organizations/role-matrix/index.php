<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ماتریس نقش دسترسی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$additional_css[] = 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css';
$additional_css[] = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$roles = $roles ?? [];
$permissionDefinitions = $permissionDefinitions ?? [];
$matrixAssignments = $matrixAssignments ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .role-matrix-table tbody tr td {\n        vertical-align: middle;\n    }\n    .role-matrix-table th,\n    .role-matrix-table td {\n        white-space: nowrap;\n    }\n    .role-matrix-table .matrix-role-header {\n        min-width: 110px;\n    }\n    .role-matrix-table .matrix-cell {\n        min-width: 110px;\n        max-width: 140px;\n        cursor: pointer;\n        transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;\n        text-align: center;\n        user-select: none;\n    }\n    .role-matrix-table .matrix-cell .matrix-state-label {\n        font-weight: 600;\n    }\n    .role-matrix-table .matrix-cell.is-active {\n        background-color: #dcfce7;\n        color: #065f46;\n        box-shadow: inset 0 0 0 2px rgba(34, 197, 94, 0.65);\n    }\n    .role-matrix-table .matrix-cell.is-inactive {\n        background-color: #fee2e2;\n        color: #991b1b;\n        box-shadow: inset 0 0 0 2px rgba(248, 113, 113, 0.65);\n    }\n    .role-matrix-table .matrix-cell:hover {\n        filter: brightness(0.97);\n    }\n    .role-matrix-table .matrix-cell:focus-visible {\n        outline: 3px solid rgba(37, 99, 235, 0.35);\n        outline-offset: 2px;\n    }\n";

$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        var cells = document.querySelectorAll('.matrix-toggle-cell');

        cells.forEach(function (cell) {
            var checkbox = cell.querySelector('.matrix-toggle-input');
            var label = cell.querySelector('.matrix-state-label');

            if (!checkbox || !label) {
                return;
            }

            function applyState(isActive) {
                cell.classList.toggle('is-active', isActive);
                cell.classList.toggle('is-inactive', !isActive);
                cell.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                label.textContent = isActive ? 'فعال' : 'غیرفعال';
            }

            function toggleState() {
                checkbox.checked = !checkbox.checked;
                applyState(checkbox.checked);
            }

            applyState(checkbox.checked);

            cell.addEventListener('click', function (event) {
                if (event.target === checkbox) {
                    return;
                }

                event.preventDefault();
                toggleState();
            });

            cell.addEventListener('keydown', function (event) {
                if (event.key === ' ' || event.key === 'Enter') {
                    event.preventDefault();
                    toggleState();
                }
            });

            checkbox.addEventListener('change', function () {
                applyState(checkbox.checked);
            });
        });
    });
SCRIPT;

$inline_scripts .= "\n";

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
                            <div>
                                <h2 class="mb-6 text-gray-900">ماتریس نقش دسترسی</h2>
                                <p class="text-gray-500 mb-0">سطح دسترسی هر نقش به بخش‌های مختلف سامانه را مدیریت کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/roles'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="مدیریت نقش‌ها">
                                    مدیریت نقش‌ها
                                    <ion-icon name="briefcase-outline"></ion-icon>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="checkmark-circle-outline"></ion-icon>
                                <span><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="warning-outline"></ion-icon>
                                <span><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= UtilityHelper::baseUrl('organizations/role-access-matrix'); ?>" method="post">
                            <?= csrf_field(); ?>

                            <?php
                            $groupedPermissions = [];
                            if (!empty($permissionDefinitions)) {
                                foreach ($permissionDefinitions as $definition) {
                                    $groupKey = (string) ($definition['group'] ?? 'سایر');
                                    if (!isset($groupedPermissions[$groupKey])) {
                                        $groupedPermissions[$groupKey] = [];
                                    }
                                    $groupedPermissions[$groupKey][] = $definition;
                                }
                            }
                            ?>

                            <div class="table-responsive rounded-16 border border-gray-100" style="direction: rtl;">
                                <table class="table align-middle mb-0 role-matrix-table mt-5" data-datatable-skip="true">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th scope="col" class="nowrap">گروه</th>
                                            <th scope="col" class="nowrap">دسترسی</th>
                                            <?php foreach ($roles as $role): ?>
                                                <th scope="col" class="nowrap text-center matrix-role-header"><?= htmlspecialchars($role['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($groupedPermissions)): ?>
                                            <?php foreach ($groupedPermissions as $groupTitle => $definitions): ?>
                                                <?php $rowspan = count($definitions); ?>
                                                <?php foreach ($definitions as $index => $definition): ?>
                                                    <?php
                                                        $permissionKey = (string) ($definition['key'] ?? '');
                                                        $accessTitle = (string) ($definition['label'] ?? '');
                                                    ?>
                                                    <tr>
                                                        <?php if ($index === 0): ?>
                                                            <td rowspan="<?= (int) $rowspan; ?>"><?= htmlspecialchars($groupTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <?php endif; ?>
                                                        <td><?= htmlspecialchars($accessTitle, ENT_QUOTES, 'UTF-8'); ?></td>
                                                        <?php foreach ($roles as $role): ?>
                                                            <?php
                                                                $roleId = (int) ($role['id'] ?? 0);
                                                                $checkboxId = 'perm-' . md5($permissionKey . '-' . $roleId);
                                                                $isAllowed = (int) ($matrixAssignments[$permissionKey][$roleId] ?? 0);
                                                                $isChecked = $isAllowed === 1;
                                                            ?>
                                                            <td class="matrix-cell matrix-toggle-cell <?= $isChecked ? 'is-active' : 'is-inactive'; ?>" tabindex="0" role="button" aria-pressed="<?= $isChecked ? 'true' : 'false'; ?>" data-permission="<?= htmlspecialchars($permissionKey, ENT_QUOTES, 'UTF-8'); ?>" data-role="<?= htmlspecialchars((string) $roleId, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <input class="matrix-toggle-input d-none" type="checkbox" id="<?= htmlspecialchars($checkboxId, ENT_QUOTES, 'UTF-8'); ?>" name="permissions[<?= htmlspecialchars($permissionKey, ENT_QUOTES, 'UTF-8'); ?>][<?= htmlspecialchars((string) $roleId, ENT_QUOTES, 'UTF-8'); ?>]" value="1" <?= $isChecked ? 'checked' : ''; ?>>
                                                                <span class="matrix-state-label">
                                                                    <?= $isChecked ? 'فعال' : 'غیرفعال'; ?>
                                                                </span>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?= 2 + count($roles); ?>" class="text-center py-24 text-gray-500">داده‌ای برای نمایش وجود ندارد.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end gap-12 mt-28">
                                <button type="submit" class="btn btn-main rounded-pill px-32 d-flex align-items-center gap-8">
                                    <ion-icon name="save-outline"></ion-icon>
                                    <span>ذخیره تغییرات</span>
                                </button>
                                <a href="<?= UtilityHelper::baseUrl('organizations/dashboard'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
</div>
