<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ماتریس مدل شایستگی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$competencyModels = $competencyModels ?? [];
$competencies = $competencies ?? [];
$modelMemberships = $modelMemberships ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;

$additional_css[] = 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css';
$additional_css[] = 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
$additional_js[] = 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
$additional_js[] = 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js';
$additional_js[] = 'public/assets/js/datatables-init.js';

$tableOptions = [
    'paging' => true,
    'pageLength' => 10,
    'lengthChange' => true,
    'responsive' => true,
    'responsiveDesktopMin' => 768,
    'scrollX' => true,
    'order' => [[0, 'asc']],
    'columnDefs' => [
        ['targets' => '_all', 'className' => 'all'],
        ['targets' => 0, 'orderable' => true, 'searchable' => true],
        ['targets' => 1, 'orderable' => true, 'searchable' => true],
    ],
];

$tableOptionsAttr = htmlspecialchars(json_encode($tableOptions, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .competency-model-matrix-table {
        min-width: 720px;
    }
    .competency-model-matrix-table tbody tr td {
        vertical-align: middle;
        text-align: center;
    }
    .competency-model-matrix-table tbody tr td:first-child,
    .competency-model-matrix-table tbody tr td:nth-child(2),
    .competency-model-matrix-table thead tr th:first-child,
    .competency-model-matrix-table thead tr th:nth-child(2) {
        text-align: right;
    }
    .competency-model-matrix-table .badge {
        font-size: 0.8rem;
    }
    .matrix-legend {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }
    .matrix-legend span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }
    .toggle-membership-btn {
        min-width: 120px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.8rem;
        transition: all 0.2s ease-in-out;
    }
    .competency-model-matrix-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .btn-outline-gray {
        color: #6b7280;
        border-color: #d1d5db;
        background-color: #ffffff;
    }
    .btn-outline-gray:hover,
    .btn-outline-gray:focus {
        color: #374151;
        border-color: #9ca3af;
        background-color: #f3f4f6;
    }
    .legend-toggle-btn {
        pointer-events: none;
        opacity: 0.85;
    }
CSS;

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
                                <h2 class="mb-6 text-gray-900">ماتریس مدل شایستگی</h2>
                                <p class="text-gray-500 mb-0">ارتباط میان شایستگی‌ها و مدل‌های تعریف‌شده را بررسی کنید.</p>
                            </div>
                            <div class="matrix-legend text-gray-600">
                                <span>
                                    <button type="button" class="btn btn-sm btn-main rounded-pill toggle-membership-btn legend-toggle-btn">
                                        <ion-icon name="checkmark-outline"></ion-icon>
                                        <span>حذف</span>
                                    </button>
                                    <span>شایستگی موجود در مدل</span>
                                </span>
                                <span>
                                    <button type="button" class="btn btn-sm btn-outline-gray rounded-pill toggle-membership-btn legend-toggle-btn">
                                        <ion-icon name="add-outline"></ion-icon>
                                        <span>افزودن</span>
                                    </button>
                                    <span>شایستگی موجود نیست</span>
                                </span>
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

                        <?php if (empty($competencyModels)): ?>
                            <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="information-circle-outline"></ion-icon>
                                <span>هنوز مدلی برای نمایش در ماتریس وجود ندارد. ابتدا مدل‌های شایستگی را ایجاد کنید.</span>
                            </div>
                        <?php elseif (empty($competencies)): ?>
                            <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                                <ion-icon name="alert-circle-outline"></ion-icon>
                                <span>هیچ شایستگی‌ای برای سازمان ثبت نشده است. ابتدا شایستگی‌های سازمان را تکمیل کنید.</span>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive competency-model-matrix-wrapper rounded-16 border border-gray-100" style="direction: rtl;">
                                <table class="table align-middle mb-0 competency-model-matrix-table js-data-table" data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                    <thead class="bg-gray-100 text-gray-700">
                                        <tr>
                                            <th scope="col">نوع شایستگی</th>
                                            <th scope="col">شایستگی</th>
                                            <?php foreach ($competencyModels as $model): ?>
                                                <?php
                                                    $modelTitle = trim((string) ($model['title'] ?? '-'));
                                                    $modelCode = trim((string) ($model['code'] ?? ''));
                                                    $columnLabel = $modelCode !== '' ? $modelCode . ' - ' . $modelTitle : $modelTitle;
                                                ?>
                                                <th scope="col" class="text-center">
                                                    <?= htmlspecialchars($columnLabel !== '' ? $columnLabel : 'مدل', ENT_QUOTES, 'UTF-8'); ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($competencies as $competency): ?>
                                            <?php
                                                $competencyId = (int) ($competency['id'] ?? 0);
                                                $competencyCode = trim((string) ($competency['code'] ?? ''));
                                                $competencyTitle = trim((string) ($competency['title'] ?? ''));
                                                $competencyLabel = $competencyCode !== ''
                                                    ? ($competencyTitle !== '' ? $competencyCode . ' - ' . $competencyTitle : $competencyCode)
                                                    : ($competencyTitle !== '' ? $competencyTitle : 'شایستگی #' . $competencyId);
                                                $dimensionName = trim((string) ($competency['dimension_name'] ?? ''));
                                            ?>
                                            <tr>
                                                <td class="text-start">
                                                    <?= htmlspecialchars($dimensionName !== '' ? $dimensionName : '—', ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td class="text-start">
                                                    <?= htmlspecialchars($competencyLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <?php foreach ($competencyModels as $model): ?>
                                                    <?php
                                                        $modelId = (int) ($model['id'] ?? 0);
                                                        $hasCompetency = isset($modelMemberships[$modelId][$competencyId]);
                                                        $buttonClasses = 'btn btn-sm rounded-pill toggle-membership-btn ' . ($hasCompetency ? 'btn-main text-white' : 'btn-outline-gray');
                                                        $buttonIcon = $hasCompetency ? 'checkmark-outline' : 'add-outline';
                                                        $buttonLabel = $hasCompetency ? 'حذف' : 'افزودن';
                                                        $ariaPressed = $hasCompetency ? 'true' : 'false';
                                                    ?>
                                                    <td>
                                                        <button type="button"
                                                            class="<?= htmlspecialchars($buttonClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-model-id="<?= htmlspecialchars((string) $modelId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-competency-id="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-active="<?= $hasCompetency ? '1' : '0'; ?>"
                                                            aria-pressed="<?= htmlspecialchars($ariaPressed, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <ion-icon name="<?= htmlspecialchars($buttonIcon, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                                                            <span><?= htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </button>
                                                    </td>
                                                <?php endforeach; ?>
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

        <form id="matrixToggleForm" action="<?= UtilityHelper::baseUrl('organizations/competency-models/matrix'); ?>" method="post" class="d-none">
            <?= csrf_field(); ?>
            <input type="hidden" name="model_id" value="">
            <input type="hidden" name="competency_id" value="">
        </form>

        <?php
        $inline_scripts .= <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    const toggleForm = document.getElementById('matrixToggleForm');

    if (!toggleForm) {
        return;
    }

    const buttons = document.querySelectorAll('.toggle-membership-btn');

    buttons.forEach(function (button) {
        if (button.classList.contains('legend-toggle-btn')) {
            return;
        }

        button.addEventListener('click', function () {
            const modelId = button.getAttribute('data-model-id');
            const competencyId = button.getAttribute('data-competency-id');

            if (!modelId || !competencyId) {
                return;
            }

            const modelInput = toggleForm.querySelector('input[name="model_id"]');
            const competencyInput = toggleForm.querySelector('input[name="competency_id"]');

            if (!modelInput || !competencyInput) {
                return;
            }

            button.setAttribute('disabled', 'disabled');
            modelInput.value = modelId;
            competencyInput.value = competencyId;
            toggleForm.submit();
        });
    });
});
JS;
        ?>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
