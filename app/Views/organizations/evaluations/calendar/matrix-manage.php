<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? 'مدیریت ماتریس ارزیابی';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$evaluationMeta = $evaluationMeta ?? [];
$matrixRows = $matrixRows ?? [];
$evaluatees = $evaluatees ?? [];
$formAction = $formAction ?? UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix/manage');
$successMessage = $successMessage ?? flash('success');
$errorMessage = $errorMessage ?? flash('error');

$generalModelLabel = trim((string) ($evaluationMeta['general_model_label'] ?? ''));
if ($generalModelLabel === '') {
    $generalModelLabel = trim((string) ($evaluationMeta['general_model'] ?? ''));
}
$generalModelRaw = trim((string) ($evaluationMeta['general_model_raw'] ?? ''));
$generalModelDisplayValue = $generalModelLabel !== '' ? $generalModelLabel : $generalModelRaw;

$specificModelLabel = trim((string) ($evaluationMeta['specific_model_label'] ?? ''));
if ($specificModelLabel === '') {
    $specificModelLabel = trim((string) ($evaluationMeta['specific_model'] ?? ''));
}
$specificModelRaw = trim((string) ($evaluationMeta['specific_model_raw'] ?? ''));
$specificModelDisplayValue = $specificModelLabel !== '' ? $specificModelLabel : $specificModelRaw;

$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .matrix-manage-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .matrix-manage-header h2 {
        font-size: 22px;
        font-weight: 600;
    }
    .matrix-manage-header p {
        color: #475467;
        margin-bottom: 0;
    }
    .matrix-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .matrix-summary .summary-item {
        background: #f8fafc;
        border-radius: 16px;
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .matrix-summary .summary-item .label {
        font-size: 12px;
        color: #6b7280;
    }
    .matrix-summary .summary-item .value {
        font-weight: 600;
        color: #1f2937;
    }
    .matrix-table-wrapper {
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        overflow-x: auto;
    }
    .evaluation-matrix-manage-table {
        width: 100%;
        min-width: 640px;
    }
    .evaluation-matrix-manage-table thead th {
        white-space: nowrap;
        background: #f4f5fb;
        color: #475467;
        font-weight: 600;
        text-align: center;
    }
    .evaluation-matrix-manage-table tbody td {
        vertical-align: middle;
        text-align: center;
        white-space: nowrap;
    }
    .evaluation-matrix-manage-table tbody td.tool-cell {
        text-align: start;
        white-space: normal;
    }
    .tool-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .tool-info .tool-order {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background: #eef2ff;
        color: #312e81;
        font-weight: 700;
        font-size: 14px;
    }
    .tool-info .tool-name {
        font-weight: 600;
        color: #111827;
        font-size: 15px;
    }
    .tool-info .tool-meta {
        font-size: 12px;
        color: #64748b;
    }
    .evaluator-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 14px;
        font-size: 13px;
        background: #f1f5f9;
        color: #1e293b;
    }
    .evaluator-badge.system {
        background: #eef2ff;
        color: #3730a3;
        font-weight: 600;
    }
    .matrix-empty {
        text-align: center;
        padding: 48px 16px;
        color: #64748b;
        font-size: 15px;
    }
    .cell-placeholder {
        color: #94a3b8;
        font-size: 13px;
    }
    .matrix-toggle-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 10px;
        border-radius: 12px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        font-weight: 600;
        user-select: none;
    }
    .matrix-toggle-cell.is-visible {
        background-color: #dcfce7;
        color: #15803d;
        box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.35);
    }
    .matrix-toggle-cell.is-hidden {
        background-color: #fee2e2;
        color: #b91c1c;
        box-shadow: inset 0 0 0 1px rgba(248, 113, 113, 0.45);
    }
    .matrix-toggle-cell.is-visible:hover {
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(34, 197, 94, 0.55), 0 14px 28px -18px rgba(34, 197, 94, 0.65);
    }
    .matrix-toggle-cell.is-hidden:hover {
        transform: translateY(-1px);
        box-shadow: inset 0 0 0 1px rgba(248, 113, 113, 0.65), 0 14px 28px -18px rgba(248, 113, 113, 0.65);
    }
    .matrix-toggle-cell:focus {
        outline: 2px solid rgba(14, 165, 233, 0.7);
        outline-offset: 2px;
    }
    .matrix-toggle-cell .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background-color: currentColor;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.65);
    }
    .matrix-toggle-cell .matrix-toggle-text {
        font-size: 13px;
    }
    .matrix-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }
CSS;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12">
                <div class="card matrix-manage-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24 matrix-manage-header">
                            <div>
                                <h2 class="mb-6 text-gray-900">ماتریس ارزیابی - <?= htmlspecialchars($evaluationMeta['title'] ?? 'بدون عنوان', ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p>نمای کلی از ارتباط ابزارهای ارزشیابی با ارزیاب‌ها و ارزیاب‌شونده‌ها.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-10">
                                <a href="<?= htmlspecialchars(UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-6">
                                    <ion-icon name="arrow-back-outline"></ion-icon>
                                    بازگشت به ماتریس
                                </a>
                                <a href="<?= htmlspecialchars($evaluationMeta['calendar_link'] ?? UtilityHelper::baseUrl('organizations/evaluation-calendar'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-main d-inline-flex align-items-center gap-6">
                                    <ion-icon name="calendar-outline"></ion-icon>
                                    مشاهده در تقویم
                                </a>
                            </div>
                        </div>

                        <div class="matrix-summary mb-24">
                            <div class="summary-item">
                                <span class="label">تاریخ ارزیابی</span>
                                <span class="value"><?= htmlspecialchars($evaluationMeta['date_display'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">تعداد ارزیاب‌ها</span>
                                <span class="value"><?= UtilityHelper::englishToPersian((string) ($evaluationMeta['evaluators_count'] ?? 0)); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">تعداد ارزیاب‌شونده‌ها</span>
                                <span class="value"><?= UtilityHelper::englishToPersian((string) ($evaluationMeta['evaluatees_count'] ?? 0)); ?></span>
                            </div>
                            <?php if ($generalModelDisplayValue !== ''): ?>
                                <div class="summary-item">
                                    <span class="label">مدل ارزیابی عمومی</span>
                                    <span class="value"><?= htmlspecialchars($generalModelDisplayValue, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($specificModelDisplayValue !== ''): ?>
                                <div class="summary-item">
                                    <span class="label">مدل ارزیابی اختصاصی</span>
                                    <span class="value"><?= htmlspecialchars($specificModelDisplayValue, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endif; ?>
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

                        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="evaluation_id" value="<?= htmlspecialchars((string) ($evaluationMeta['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

                            <div class="matrix-table-wrapper">
                                <table class="table align-middle evaluation-matrix-manage-table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">ابزار</th>
                                            <th scope="col">ارزیاب</th>
                                            <?php if (!empty($evaluatees)): ?>
                                                <?php foreach ($evaluatees as $evaluatee): ?>
                                                    <?php
                                                        $evaluateeId = (int) ($evaluatee['id'] ?? 0);
                                                        $evaluateeLabel = htmlspecialchars($evaluatee['label'] ?? 'ارزیاب‌شونده', ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <th scope="col" data-evaluatee-id="<?= htmlspecialchars((string) $evaluateeId, ENT_QUOTES, 'UTF-8'); ?>"><?= $evaluateeLabel; ?></th>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <th scope="col">ارزیاب‌شونده‌ای انتخاب نشده است</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($matrixRows)): ?>
                                            <?php foreach ($matrixRows as $row): ?>
                                                <?php
                                                    $orderDisplay = UtilityHelper::englishToPersian((string) max(1, (int) ($row['tool_order'] ?? 0)));
                                                    $toolNameDisplay = htmlspecialchars($row['tool_name'] ?? 'ابزار نامشخص', ENT_QUOTES, 'UTF-8');
                                                    $evaluatorLabel = htmlspecialchars($row['evaluator_label'] ?? '—', ENT_QUOTES, 'UTF-8');
                                                    $isSystemEvaluator = !empty($row['is_exam']);
                                                    $toolId = (int) ($row['tool_id'] ?? 0);
                                                    $evaluatorKey = (string) ($row['evaluator_key'] ?? '0');
                                                    $rowVisibility = $row['visibility'] ?? [];
                                                ?>
                                                <tr>
                                                    <td class="tool-cell">
                                                        <div class="tool-info">
                                                            <span class="tool-order" aria-label="ترتیب ابزار"><?= $orderDisplay; ?></span>
                                                            <span class="tool-name"><?= $toolNameDisplay; ?></span>
                                                            <?php if ($isSystemEvaluator): ?>
                                                                <span class="tool-meta">نوع ابزار: آزمون (سیستمی)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="evaluator-badge<?= $isSystemEvaluator ? ' system' : ''; ?>">
                                                            <ion-icon name="person-circle-outline"></ion-icon>
                                                            <?= $evaluatorLabel; ?>
                                                        </span>
                                                    </td>
                                                    <?php if (!empty($evaluatees)): ?>
                                                        <?php foreach ($evaluatees as $evaluatee): ?>
                                                            <?php
                                                                $evaluateeId = (int) ($evaluatee['id'] ?? 0);
                                                                $inputBaseName = 'matrix[' . $toolId . '][' . $evaluatorKey . '][' . $evaluateeId . ']';
                                                                $inputId = 'matrix-toggle-input-' . $toolId . '-' . $evaluatorKey . '-' . $evaluateeId;
                                                                $isVisible = $rowVisibility[$evaluateeId] ?? true;
                                                                $visibleLabel = 'نمایش';
                                                                $hiddenLabel = 'پنهان';
                                                            ?>
                                                            <td>
                                                                <div class="matrix-toggle-cell <?= $isVisible ? 'is-visible' : 'is-hidden'; ?>"
                                                                     role="button"
                                                                     tabindex="0"
                                                                     aria-pressed="<?= $isVisible ? 'true' : 'false'; ?>"
                                                                     data-target-input="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                     data-visible-label="<?= htmlspecialchars($visibleLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                                                     data-hidden-label="<?= htmlspecialchars($hiddenLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                                                     data-initial-state="<?= $isVisible ? 'visible' : 'hidden'; ?>"
                                                                     title="برای تغییر وضعیت کلیک کنید">
                                                                    <span class="status-dot" aria-hidden="true"></span>
                                                                    <span class="matrix-toggle-text"><?= $isVisible ? $visibleLabel : $hiddenLabel; ?></span>
                                                                    <span class="visually-hidden">
                                                                        وضعیت نمایش ابزار <?= $toolNameDisplay; ?> برای <?= htmlspecialchars($evaluatee['label'] ?? 'ارزیاب‌شونده', ENT_QUOTES, 'UTF-8'); ?>
                                                                    </span>
                                                                </div>
                                                                <input type="hidden"
                                                                       id="<?= htmlspecialchars($inputId, ENT_QUOTES, 'UTF-8'); ?>"
                                                                       data-name="<?= htmlspecialchars($inputBaseName, ENT_QUOTES, 'UTF-8'); ?>"
                                                                       <?= $isVisible ? 'name="' . htmlspecialchars($inputBaseName, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
                                                                       value="<?= $isVisible ? '1' : ''; ?>">
                                                            </td>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <td><span class="cell-placeholder">—</span></td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="<?= 2 + max(1, count($evaluatees)); ?>" class="matrix-empty">
                                                    هنوز ابزاری برای این ارزیابی تخصیص داده نشده است یا ارزیاب/ارزیاب‌شونده‌ای تعیین نشده است.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="matrix-actions">
                                <button type="submit" class="btn btn-main">
                                    ذخیره ماتریس
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var toggleCells = document.querySelectorAll('.matrix-toggle-cell');
                if (!toggleCells.length) {
                    return;
                }

                toggleCells.forEach(function (cell) {
                    var inputId = cell.getAttribute('data-target-input');
                    var input = inputId ? document.getElementById(inputId) : null;
                    if (!input) {
                        return;
                    }

                    var dataName = input.getAttribute('data-name');
                    if (!dataName) {
                        return;
                    }

                    var visibleLabel = cell.getAttribute('data-visible-label') || 'نمایش';
                    var hiddenLabel = cell.getAttribute('data-hidden-label') || 'پنهان';
                    var textNode = cell.querySelector('.matrix-toggle-text');

                    var isVisible = (cell.getAttribute('data-initial-state') || 'visible') === 'visible';

                    var applyState = function (state) {
                        isVisible = !!state;
                        cell.classList.toggle('is-visible', isVisible);
                        cell.classList.toggle('is-hidden', !isVisible);
                        cell.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
                        cell.setAttribute('data-state', isVisible ? 'visible' : 'hidden');

                        if (textNode) {
                            textNode.textContent = isVisible ? visibleLabel : hiddenLabel;
                        }

                        if (isVisible) {
                            if (!input.name) {
                                input.name = dataName;
                            }
                            input.value = '1';
                        } else {
                            if (input.name) {
                                input.removeAttribute('name');
                            }
                            input.value = '';
                        }
                    };

                    applyState(isVisible);

                    var toggleState = function () {
                        applyState(!isVisible);
                    };

                    cell.addEventListener('click', function (event) {
                        event.preventDefault();
                        toggleState();
                    });

                    cell.addEventListener('keydown', function (event) {
                        if (event.key === ' ' || event.key === 'Spacebar' || event.key === 'Enter') {
                            event.preventDefault();
                            toggleState();
                        }
                    });
                });
            });
        </script>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
