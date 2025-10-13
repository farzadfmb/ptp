<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../Helpers/autoload.php';
}

$title = $title ?? 'ماتریس شایستگی ابزار';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$competencyModels = $competencyModels ?? [];
$evaluationTools = $evaluationTools ?? [];
$competencies = $competencies ?? [];
$toolMemberships = $toolMemberships ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$selectedModelId = isset($selectedModelId) ? (int) $selectedModelId : 0;
$selectedModel = $selectedModel ?? null;

$selectedModelTitle = 'مدل انتخاب نشده';
if (is_array($selectedModel)) {
    $modelTitle = trim((string) ($selectedModel['title'] ?? ''));
    $modelCode = trim((string) ($selectedModel['code'] ?? ''));
    $selectedModelTitle = $modelCode !== ''
        ? ($modelTitle !== '' ? $modelCode . ' - ' . $modelTitle : $modelCode)
        : ($modelTitle !== '' ? $modelTitle : 'مدل انتخاب شده');
}

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
    .competency-tool-matrix-table {
        min-width: 720px;
    }
    .competency-tool-matrix-table tbody tr td {
        vertical-align: middle;
        text-align: center;
    }
    .competency-tool-matrix-table tbody tr td:first-child,
    .competency-tool-matrix-table tbody tr td:nth-child(2),
    .competency-tool-matrix-table thead tr th:first-child,
    .competency-tool-matrix-table thead tr th:nth-child(2) {
        text-align: right;
    }
    .toggle-tool-membership-btn {
        min-width: 120px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.8rem;
        transition: all 0.2s ease-in-out;
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
    .legend-toggle-btn {
        pointer-events: none;
        opacity: 0.85;
    }
    .competency-tool-matrix-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
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
                                <h2 class="mb-6 text-gray-900">ماتریس شایستگی ابزار</h2>
                                <p class="text-gray-500 mb-0">ارتباط میان شایستگی‌های مدل «<?= htmlspecialchars($selectedModelTitle, ENT_QUOTES, 'UTF-8'); ?>» و ابزارهای ارزیابی را مدیریت کنید.</p>
                            </div>
                            <div class="matrix-legend text-gray-600">
                                <span>
                                    <button type="button" class="btn btn-sm btn-main rounded-pill toggle-tool-membership-btn legend-toggle-btn">
                                        <ion-icon name="checkmark-outline"></ion-icon>
                                        <span>حذف</span>
                                    </button>
                                    <span>شایستگی به ابزار متصل است</span>
                                </span>
                                <span>
                                    <button type="button" class="btn btn-sm btn-outline-gray rounded-pill toggle-tool-membership-btn legend-toggle-btn">
                                        <ion-icon name="add-outline"></ion-icon>
                                        <span>افزودن</span>
                                    </button>
                                    <span>شایستگی متصل نیست</span>
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
                        <?php else: ?>
                            <div class="row g-3 align-items-end mb-24">
                                <div class="col-12 col-md-6 col-lg-4">
                                    <form id="matrixFilterForm" action="<?= UtilityHelper::baseUrl('organizations/competency-tools/matrix'); ?>" method="get" class="d-flex flex-column gap-8">
                                        <label for="matrixModelSelect" class="form-label text-gray-600 mb-0">انتخاب مدل شایستگی</label>
                                        <select id="matrixModelSelect" name="model_id" class="form-select rounded-16 py-2 px-3">
                                            <?php foreach ($competencyModels as $model): ?>
                                                <?php
                                                    $modelId = (int) ($model['id'] ?? 0);
                                                    $modelTitle = trim((string) ($model['title'] ?? ''));
                                                    $modelCode = trim((string) ($model['code'] ?? ''));
                                                    $optionLabel = $modelCode !== ''
                                                        ? ($modelTitle !== '' ? $modelCode . ' - ' . $modelTitle : $modelCode)
                                                        : ($modelTitle !== '' ? $modelTitle : 'مدل #' . $modelId);
                                                ?>
                                                <option value="<?= htmlspecialchars((string) $modelId, ENT_QUOTES, 'UTF-8'); ?>" <?= $modelId === $selectedModelId ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <?php if (empty($evaluationTools)): ?>
                                <div class="alert alert-warning rounded-16 d-flex align-items-center gap-12" role="alert">
                                    <ion-icon name="construct-outline"></ion-icon>
                                    <span>هیچ ابزار ارزیابی (غیر آزمون) برای نمایش وجود ندارد. ابتدا ابزارهای ارزیابی مناسب را ایجاد کنید.</span>
                                </div>
                            <?php elseif ($selectedModelId > 0 && empty($competencies)): ?>
                                <div class="alert alert-info rounded-16 d-flex align-items-center gap-12" role="alert">
                                    <ion-icon name="list-outline"></ion-icon>
                                    <span>برای مدل انتخاب‌شده، هیچ شایستگی‌ای ثبت نشده است.</span>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive competency-tool-matrix-wrapper rounded-16 border border-gray-100" style="direction: rtl;">
                                    <table class="table align-middle mb-0 competency-tool-matrix-table js-data-table" data-tool-matrix-table data-datatable-options="<?= $tableOptionsAttr; ?>" data-responsive-desktop-min="768">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th scope="col">نوع شایستگی</th>
                                                <th scope="col">شایستگی</th>
                                                <?php foreach ($evaluationTools as $tool): ?>
                                                    <?php
                                                        $toolId = (int) ($tool['id'] ?? 0);
                                                        $toolName = trim((string) ($tool['name'] ?? ''));
                                                        $toolCode = trim((string) ($tool['code'] ?? ''));
                                                        $columnLabel = $toolCode !== ''
                                                            ? ($toolName !== '' ? $toolCode . ' - ' . $toolName : $toolCode)
                                                            : ($toolName !== '' ? $toolName : 'ابزار #' . $toolId);
                                                    ?>
                                                    <th scope="col" class="text-center">
                                                        <?= htmlspecialchars($columnLabel, ENT_QUOTES, 'UTF-8'); ?>
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
                                                    $dimensionName = trim((string) ($competency['dimension_name'] ?? ''));
                                                    $competencyLabel = $competencyCode !== ''
                                                        ? ($competencyTitle !== '' ? $competencyCode . ' - ' . $competencyTitle : $competencyCode)
                                                        : ($competencyTitle !== '' ? $competencyTitle : 'شایستگی #' . $competencyId);
                                                ?>
                                                <tr>
                                                    <td class="text-start">
                                                        <?= htmlspecialchars($dimensionName !== '' ? $dimensionName : '—', ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td class="text-start">
                                                        <?= htmlspecialchars($competencyLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <?php foreach ($evaluationTools as $tool): ?>
                                                        <?php
                                                            $toolId = (int) ($tool['id'] ?? 0);
                                                            $hasLink = isset($toolMemberships[$toolId][$competencyId]);
                                                            $buttonClasses = 'btn btn-sm rounded-pill toggle-tool-membership-btn ' . ($hasLink ? 'btn-main text-white' : 'btn-outline-gray');
                                                            $buttonIcon = $hasLink ? 'checkmark-outline' : 'add-outline';
                                                            $buttonLabel = $hasLink ? 'حذف' : 'افزودن';
                                                            $ariaPressed = $hasLink ? 'true' : 'false';
                                                        ?>
                                                        <td>
                                                            <button type="button"
                                                                class="<?= htmlspecialchars($buttonClasses, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-tool-id="<?= htmlspecialchars((string) $toolId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-competency-id="<?= htmlspecialchars((string) $competencyId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-active="<?= $hasLink ? '1' : '0'; ?>"
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <form id="toolMatrixToggleForm" action="<?= UtilityHelper::baseUrl('organizations/competency-tools/matrix'); ?>" method="post" class="d-none">
            <?= csrf_field(); ?>
            <input type="hidden" name="model_id" value="<?= htmlspecialchars((string) $selectedModelId, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="tool_id" value="">
            <input type="hidden" name="competency_id" value="">
        </form>

        <?php
        $inline_scripts .= <<<'JS'
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('matrixFilterForm');
    const modelSelect = document.getElementById('matrixModelSelect');
    const toggleForm = document.getElementById('toolMatrixToggleForm');

    if (modelSelect && filterForm) {
        modelSelect.addEventListener('change', function () {
            filterForm.submit();
        });
    }

    if (!toggleForm) {
        return;
    }

    const toggleButtons = document.querySelectorAll('.toggle-tool-membership-btn');

    toggleButtons.forEach(function (button) {
        if (button.classList.contains('legend-toggle-btn')) {
            return;
        }

        button.addEventListener('click', function () {
            const toolId = button.getAttribute('data-tool-id');
            const competencyId = button.getAttribute('data-competency-id');

            if (!toolId || !competencyId) {
                return;
            }

            const toolInput = toggleForm.querySelector('input[name="tool_id"]');
            const competencyInput = toggleForm.querySelector('input[name="competency_id"]');
            const modelInput = toggleForm.querySelector('input[name="model_id"]');

            if (!toolInput || !competencyInput || !modelInput) {
                return;
            }

            button.setAttribute('disabled', 'disabled');
            toolInput.value = toolId;
            competencyInput.value = competencyId;
            modelInput.value = modelSelect ? modelSelect.value : '';
            toggleForm.submit();
        });
    });
});
JS;
        ?>

        <?php include __DIR__ . '/../../layouts/organization-footer.php'; ?>
    </div>
</div>
