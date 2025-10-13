<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ماتریس نقش کاربران';
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

$tableOptions = [
    'paging' => true,
    'pageLength' => 25,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 768,
    'scrollX' => true,
    'order' => [[0, 'asc']],
    'columnDefs' => [
        ['targets' => '_all', 'className' => 'all'],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$roles = $roles ?? [];
$organizationUsers = $organizationUsers ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$inline_styles .= "\n    body {\n        background: #f5f7fb;\n    }\n    .user-role-matrix-table tbody tr td {\n        vertical-align: middle;\n    }\n    .user-role-matrix-table th,\n    .user-role-matrix-table td {\n        white-space: nowrap;\n    }\n    .user-role-matrix-table .user-role-cell {\n        width: 100%;\n        min-width: 120px;\n        border-radius: 14px;\n        border: 1px solid #e5e7eb;\n        background-color: #f9fafb;\n        color: #4b5563;\n        padding: 10px 16px;\n        display: flex;\n        align-items: center;\n        justify-content: center;\n        gap: 8px;\n        cursor: pointer;\n        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;\n    }\n    .user-role-matrix-table .user-role-cell.is-active {\n        background-color: #dcfce7;\n        border-color: rgba(34, 197, 94, 0.4);\n        color: #065f46;\n        box-shadow: inset 0 0 0 2px rgba(34, 197, 94, 0.35);\n    }\n    .user-role-matrix-table .user-role-cell.is-inactive {\n        background-color: #f9fafb;\n        border-color: #e5e7eb;\n        color: #4b5563;\n    }\n    .user-role-matrix-table .user-role-cell:focus-visible {\n        outline: 3px solid rgba(37, 99, 235, 0.35);\n        outline-offset: 2px;\n    }\n    .user-role-matrix-table .user-role-indicator {\n        font-size: 18px;\n        line-height: 1;\n    }\n    .user-role-matrix-table .user-role-cell:hover {\n        filter: brightness(0.97);\n    }\n    .user-role-matrix-table .user-name {\n        font-weight: 600;\n        color: #111827;\n    }\n    .user-role-matrix-table .user-meta {\n        font-size: 0.85rem;\n        color: #6b7280;\n    }\n";

$inline_styles .= "\n    .user-role-matrix-wrapper {\n        overflow-x: auto;\n        -webkit-overflow-scrolling: touch;\n    }\n";

$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        var rows = document.querySelectorAll('.user-role-row');

        rows.forEach(function (row) {
            var hiddenInput = row.querySelector('.user-role-hidden-input');
            var toggles = row.querySelectorAll('.user-role-cell');

            if (!hiddenInput) {
                return;
            }

            function refreshStates() {
                var currentValue = hiddenInput.value;

                toggles.forEach(function (toggle) {
                    var roleId = toggle.getAttribute('data-role-id');
                    var indicator = toggle.querySelector('.user-role-indicator');
                    var label = toggle.querySelector('.user-role-label');
                    var isActive = currentValue !== '' && currentValue === roleId;

                    toggle.classList.toggle('is-active', isActive);
                    toggle.classList.toggle('is-inactive', !isActive);
                    toggle.setAttribute('aria-pressed', isActive ? 'true' : 'false');

                    if (indicator) {
                        indicator.textContent = isActive ? '✓' : '–';
                    }

                    if (label) {
                        label.textContent = isActive ? 'فعال' : 'انتخاب';
                    }
                });
            }

            toggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var roleId = toggle.getAttribute('data-role-id');
                    var currentValue = hiddenInput.value;

                    if (currentValue === roleId) {
                        hiddenInput.value = '';
                    } else {
                        hiddenInput.value = roleId;
                    }

                    refreshStates();
                });

                toggle.addEventListener('keydown', function (event) {
                    if (event.key === ' ' || event.key === 'Enter') {
                        event.preventDefault();
                        toggle.click();
                    }
                });
            });

            refreshStates();
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
                                <h2 class="mb-6 text-gray-900">ماتریس نقش کاربران</h2>
                                <p class="text-gray-500 mb-0">نقش هر کاربر را به‌صورت سریع و یکپارچه مدیریت کنید. برای حذف نقش، دوباره روی نقش فعال کلیک کنید.</p>
                            </div>
                            <div class="d-flex gap-10 flex-wrap">
                                <a href="<?= UtilityHelper::baseUrl('organizations/roles'); ?>" class="btn btn-outline-gray rounded-pill px-24 d-flex align-items-center gap-8" title="مدیریت نقش‌ها">
                                    مدیریت نقش‌ها
                                    <ion-icon name="briefcase-outline"></ion-icon>
                                </a>
                                <a href="<?= UtilityHelper::baseUrl('organizations/users'); ?>" class="btn btn-outline-main rounded-pill px-24 d-flex align-items-center gap-8" title="بازگشت به کاربران">
                                    لیست کاربران
                                    <ion-icon name="people-outline"></ion-icon>
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

                        <form action="<?= UtilityHelper::baseUrl('organizations/users/role-matrix'); ?>" method="post">
                            <?= csrf_field(); ?>

                            <div class="table-responsive user-role-matrix-wrapper rounded-16 border border-gray-100" style="direction: rtl;">
                                <table class="table align-middle mb-0 user-role-matrix-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th scope="col">ردیف</th>
                                            <th scope="col">نام کاربری</th>
                                            <th scope="col">نام و نام خانوادگی</th>
                                            <?php foreach ($roles as $role): ?>
                                                <th scope="col" class="text-center"><?= htmlspecialchars($role['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($organizationUsers)): ?>
                                            <?php $rowIndex = 0; ?>
                                            <?php foreach ($organizationUsers as $organizationUser): ?>
                                                <?php
                                                    $rowIndex++;
                                                    $userId = (int) ($organizationUser['id'] ?? 0);
                                                    $username = trim((string) ($organizationUser['username'] ?? ''));
                                                    $firstName = trim((string) ($organizationUser['first_name'] ?? ''));
                                                    $lastName = trim((string) ($organizationUser['last_name'] ?? ''));
                                                    $fullName = trim($firstName . ' ' . $lastName);
                                                    $assignedRoleId = (int) ($organizationUser['organization_role_id'] ?? 0);
                                                    $assignedRoleValue = $assignedRoleId > 0 ? (string) $assignedRoleId : '';
                                                    $isActiveUser = (int) ($organizationUser['is_active'] ?? 0) === 1;
                                                ?>
                                                <tr class="user-role-row">
                                                    <td><?= UtilityHelper::englishToPersian((string) $rowIndex); ?></td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="user-name"><?= htmlspecialchars($username !== '' ? $username : '—', ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <span class="user-meta">کد کاربر: <?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="user-name"><?= htmlspecialchars($fullName !== '' ? $fullName : 'نام ثبت نشده', ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <span class="user-meta <?= $isActiveUser ? 'text-success' : 'text-danger'; ?>">
                                                                <?= $isActiveUser ? 'فعال' : 'غیرفعال'; ?>
                                                            </span>
                                                        </div>
                                                        <input type="hidden" name="user_roles[<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8'); ?>]" value="<?= htmlspecialchars($assignedRoleValue, ENT_QUOTES, 'UTF-8'); ?>" class="user-role-hidden-input">
                                                    </td>
                                                    <?php foreach ($roles as $role): ?>
                                                        <?php
                                                            $roleId = (int) ($role['id'] ?? 0);
                                                            $roleIdValue = (string) $roleId;
                                                            $isAssigned = $assignedRoleId === $roleId;
                                                        ?>
                                                        <td class="text-center">
                                                            <button type="button" class="user-role-cell <?= $isAssigned ? 'is-active' : 'is-inactive'; ?>" data-role-id="<?= htmlspecialchars($roleIdValue, ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="<?= $isAssigned ? 'true' : 'false'; ?>">
                                                                <span class="user-role-indicator"><?= $isAssigned ? '✓' : '–'; ?></span>
                                                                <span class="user-role-label"><?= $isAssigned ? 'فعال' : 'انتخاب'; ?></span>
                                                            </button>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?= 3 + count($roles); ?>" class="text-center py-24 text-gray-500">کاربری یافت نشد. ابتدا کاربران سازمان را ایجاد کنید.</td>
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
                                <a href="<?= UtilityHelper::baseUrl('organizations/users'); ?>" class="btn btn-outline-gray rounded-pill px-28">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
