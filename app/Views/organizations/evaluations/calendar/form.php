<?php
if (!class_exists('UtilityHelper')) {
    require_once __DIR__ . '/../../../Helpers/autoload.php';
}

$title = $title ?? ($isEdit ?? false ? 'ویرایش ارزیابی' : 'افزودن ارزیابی');
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : [
    'name' => 'کاربر سازمان',
    'email' => 'organization@example.com',
];
$additional_css = $additional_css ?? [];
$additional_js = $additional_js ?? [];
$inline_styles = $inline_styles ?? '';
$inline_scripts = $inline_scripts ?? '';

$additional_css[] = 'public/themes/dashkote/plugins/select2/css/select2.min.css';
$additional_css[] = 'public/themes/dashkote/plugins/select2/css/select2-bootstrap4.css';
$additional_css[] = 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css';
$additional_js[] = 'public/themes/dashkote/plugins/select2/js/select2.min.js';
$additional_js[] = 'public/assets/js/select2-fa-init.js';
$additional_js[] = 'public/themes/dashkote/js/form-select2.js';
$additional_js[] = 'https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js';
$additional_js[] = 'https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js';

$evaluationTools = $evaluationTools ?? [];
$organizationUsers = $organizationUsers ?? [];
$evaluation = $evaluation ?? [
    'id' => null,
    'title' => '',
    'evaluation_date' => '',
    'general_model' => '',
    'specific_model' => '',
    'evaluators' => [],
    'evaluatees' => [],
    'tools' => [],
];
$validationErrors = $validationErrors ?? [];
$formAction = $formAction ?? UtilityHelper::baseUrl('organizations/evaluation-calendar');
$isEdit = $isEdit ?? false;
$evaluationDateLocked = $evaluationDateLocked ?? false;
$lockedEvaluationDateRaw = $lockedEvaluationDateRaw ?? null;
$lockedDateDisplay = $lockedDateDisplay ?? null;

$formTitle = $isEdit ? 'ویرایش ارزیابی' : 'افزودن ارزیابی جدید';
$submitLabel = $isEdit ? 'به‌روزرسانی ارزیابی' : 'ثبت ارزیابی';
$cancelUrl = UtilityHelper::baseUrl('organizations/evaluation-calendar/matrix');

$oldTitle = old('title', $evaluation['title'] ?? '');
$oldDate = old('evaluation_date', $evaluation['evaluation_date'] ?? '');
$oldGeneralModel = old('general_model', $evaluation['general_model'] ?? '');
$oldSpecificModelSelect = old('specific_model_select', $evaluation['specific_model'] ?? '');
$oldSpecificModelCustom = old('specific_model_custom', '');
$resolvedSpecificModel = old('specific_model', $evaluation['specific_model'] ?? '');
$oldEvaluators = old('evaluators', $evaluation['evaluators'] ?? []);
$oldEvaluatees = old('evaluatees', $evaluation['evaluatees'] ?? []);
$oldTools = old('tools', $evaluation['tools'] ?? []);
$evaluationModelSuggestions = $evaluationModelSuggestions ?? ['general' => [], 'specific' => []];
$generalModelSuggestions = $evaluationModelSuggestions['general'] ?? [];
$specificModelSuggestions = $evaluationModelSuggestions['specific'] ?? [];

$generalModelTableOptions = array_values(array_filter($generalModelSuggestions, static function ($suggestion): bool {
    if (!is_array($suggestion)) {
        return false;
    }

    return isset($suggestion['source']) && $suggestion['source'] === 'model';
}));

$resolvedGeneralModel = is_array($oldGeneralModel) ? '' : trim((string) $oldGeneralModel);
$oldSpecificModelSelect = is_array($oldSpecificModelSelect) ? '' : trim((string) $oldSpecificModelSelect);
$oldSpecificModelCustom = is_array($oldSpecificModelCustom) ? '' : trim((string) $oldSpecificModelCustom);
$resolvedSpecificModel = is_array($resolvedSpecificModel) ? '' : trim((string) $resolvedSpecificModel);

$generalModelValueSet = [];
foreach ($generalModelTableOptions as $suggestion) {
    $value = trim((string) ($suggestion['value'] ?? ''));
    if ($value === '') {
        continue;
    }
    $generalModelValueSet[$value] = true;
}

$specificModelValueSet = [];
foreach ($specificModelSuggestions as $suggestion) {
    $value = trim((string) ($suggestion['value'] ?? ''));
    if ($value === '') {
        continue;
    }
    $specificModelValueSet[$value] = true;
}

$generalModelSelectedValue = $resolvedGeneralModel;
$generalModelFallbackOption = null;
if ($generalModelSelectedValue !== '' && !isset($generalModelValueSet[$generalModelSelectedValue])) {
    $generalModelFallbackOption = $generalModelSelectedValue;
}

$specificModelSelectValue = $oldSpecificModelSelect;
$isSpecificCustom = false;
if ($specificModelSelectValue === '__custom__') {
    $isSpecificCustom = true;
} elseif ($specificModelSelectValue === '') {
    if ($resolvedSpecificModel !== '' && !isset($specificModelValueSet[$resolvedSpecificModel])) {
        $specificModelSelectValue = '__custom__';
        $isSpecificCustom = true;
    } elseif ($resolvedSpecificModel !== '') {
        $specificModelSelectValue = $resolvedSpecificModel;
    }
} elseif (!isset($specificModelValueSet[$specificModelSelectValue])) {
    if ($resolvedSpecificModel !== '' && isset($specificModelValueSet[$resolvedSpecificModel])) {
        $specificModelSelectValue = $resolvedSpecificModel;
    } else {
        $specificModelSelectValue = '__custom__';
        $isSpecificCustom = true;
    }
}

$specificModelCustomValue = $oldSpecificModelCustom !== '' ? $oldSpecificModelCustom : ($isSpecificCustom ? $resolvedSpecificModel : '');

if (!is_array($oldEvaluators)) {
    $oldEvaluators = [];
}
if (!is_array($oldEvaluatees)) {
    $oldEvaluatees = [];
}
if (!is_array($oldTools)) {
    $oldTools = [];
}

$organizationUsers = array_filter(is_array($organizationUsers) ? $organizationUsers : []);
$organizationUsersById = [];
$organizationEvaluators = [];
$organizationEvaluatees = [];

foreach ($organizationUsers as $orgUser) {
    if (!is_array($orgUser)) {
        continue;
    }

    $userId = (int) ($orgUser['id'] ?? 0);
    if ($userId <= 0) {
        continue;
    }

    $organizationUsersById[$userId] = $orgUser;

    if ((int) ($orgUser['is_evaluator'] ?? 0) === 1) {
        $organizationEvaluators[$userId] = $orgUser;
    }

    if ((int) ($orgUser['is_evaluee'] ?? 0) === 1) {
        $organizationEvaluatees[$userId] = $orgUser;
    }
}

foreach ($oldEvaluators as $evaluatorId) {
    $evaluatorId = (int) $evaluatorId;
    if ($evaluatorId <= 0) {
        continue;
    }

    if (isset($organizationUsersById[$evaluatorId]) && !isset($organizationEvaluators[$evaluatorId])) {
        $organizationEvaluators[$evaluatorId] = $organizationUsersById[$evaluatorId];
    }
}

foreach ($oldEvaluatees as $evaluateeId) {
    $evaluateeId = (int) $evaluateeId;
    if ($evaluateeId <= 0) {
        continue;
    }

    if (isset($organizationUsersById[$evaluateeId]) && !isset($organizationEvaluatees[$evaluateeId])) {
        $organizationEvaluatees[$evaluateeId] = $organizationUsersById[$evaluateeId];
    }
}

$organizationEvaluators = array_values($organizationEvaluators);
$organizationEvaluatees = array_values($organizationEvaluatees);

$oldEvaluators = array_values(array_filter(array_map('intval', $oldEvaluators), static function (int $id): bool {
    return $id > 0;
}));
$oldEvaluatees = array_values(array_filter(array_map('intval', $oldEvaluatees), static function (int $id): bool {
    return $id > 0;
}));

$toolOrders = [];
foreach ($oldTools as $toolIdKey => $toolData) {
    $toolId = (int) $toolIdKey;
    if ($toolId <= 0) {
        continue;
    }

    if (is_array($toolData)) {
        $value = trim((string) ($toolData['order'] ?? ''));
    } else {
        $value = trim((string) $toolData);
    }

    $toolOrders[$toolId] = $value;
}

$oldDateForInput = '';
$oldDateOriginal = is_string($oldDate) ? trim($oldDate) : '';
if ($oldDateOriginal !== '') {
    $oldDateEnglish = UtilityHelper::persianToEnglish($oldDateOriginal);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $oldDateEnglish)) {
        try {
            $dateTime = new DateTime($oldDateEnglish, new DateTimeZone('Asia/Tehran'));
            if (class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    'fa_IR@calendar=persian',
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                    'Asia/Tehran',
                    IntlDateFormatter::TRADITIONAL,
                    'yyyy/MM/dd'
                );
                if ($formatter !== false) {
                    $formatted = $formatter->format($dateTime);
                    if ($formatted !== false && $formatted !== null && preg_match('/^\d{4}\/\d{2}\/\d{2}$/', UtilityHelper::persianToEnglish($formatted))) {
                        $oldDateForInput = UtilityHelper::persianToEnglish($formatted);
                    }
                }
            }
            if ($oldDateForInput === '') {
                $oldDateForInput = str_replace('-', '/', $oldDateEnglish);
            }
        } catch (Exception $exception) {
            $oldDateForInput = str_replace('-', '/', $oldDateEnglish);
        }
    }

    if ($oldDateForInput === '') {
        $oldDateNormalized = str_replace('-', '/', $oldDateEnglish);

        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $oldDateNormalized)) {
            $oldDateForInput = $oldDateNormalized;
        } elseif (preg_match('/^\d{4}\/\d{1}\/\d{1}$/', $oldDateNormalized)) {
            [$y, $m, $d] = array_map('intval', explode('/', $oldDateNormalized));
            $oldDateForInput = sprintf('%04d/%02d/%02d', $y, $m, $d);
        }
    }

    if ($oldDateForInput !== '') {
        $oldDateForInput = UtilityHelper::englishToPersian($oldDateForInput);
    }
}

if ($oldDateForInput === '' && $evaluationDateLocked && is_string($lockedEvaluationDateRaw) && $lockedEvaluationDateRaw !== '') {
    $oldDateForInput = str_replace('-', '/', UtilityHelper::persianToEnglish($lockedEvaluationDateRaw));
}

$lockedDateDisplaySafe = null;
if ($evaluationDateLocked) {
    if (is_string($lockedDateDisplay) && trim($lockedDateDisplay) !== '') {
        $lockedDateDisplaySafe = trim($lockedDateDisplay);
    } elseif (is_string($lockedEvaluationDateRaw) && $lockedEvaluationDateRaw !== '') {
        $lockedDateDisplaySafe = UtilityHelper::englishToPersian(str_replace('-', '/', UtilityHelper::persianToEnglish($lockedEvaluationDateRaw)));
    }
}


$inline_styles .= <<<'CSS'
    body {
        background: #f5f7fb;
    }
    .evaluation-form-card {
        border-radius: 24px;
        border: 1px solid #e4e9f2;
        background: #ffffff;
    }
    .evaluation-form-card h2 {
        font-size: 22px;
        font-weight: 600;
    }
    .evaluation-form-card p {
        color: #475467;
    }
    .form-label .required {
        color: #ef4444;
    }
    .tool-order-input {
        max-width: 110px;
    }
    .tool-row {
        border: 1px solid #eef2ff;
        border-radius: 16px;
        padding: 16px;
        background: #f9fafc;
    }
    .tool-row + .tool-row {
        margin-top: 12px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 52px;
        border-radius: 14px;
        border: 1px solid #ced4da;
        padding: 6px 8px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        border-radius: 12px;
        font-size: 0.85rem;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        gap: 6px;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #7c3aed;
        box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.15);
    }
    .select2-results__option {
        font-size: 0.92rem;
    }
    .datepicker-plot-area {
        font-family: inherit;
        border-radius: 20px;
        overflow: hidden;
    }
    .locked-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        background-color: #fef3c7;
        color: #92400e;
        padding: 6px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 8px;
    }
    .locked-date-badge .locked-date-value {
        background: rgba(250, 204, 21, 0.25);
        color: inherit;
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 700;
    }
    .model-select-wrapper {
        position: relative;
    }
    .model-select-wrapper select.form-select {
        border-radius: 14px;
    }
    .model-custom-input {
        display: none;
        margin-top: 10px;
    }
    .model-custom-input.active {
        display: block;
    }
    .model-custom-input input {
        border-radius: 12px;
    }
CSS;

$inline_scripts .= <<<'SCRIPT'
    document.addEventListener('DOMContentLoaded', function () {
        const $ = window.jQuery;

        if (typeof $ !== 'undefined') {
            const $dateInput = $('#evaluation-date');
            if ($dateInput.length && !$dateInput.prop('readOnly') && typeof $dateInput.persianDatepicker === 'function') {
                const currentValue = $dateInput.val();
                $dateInput.persianDatepicker({
                    format: 'YYYY/MM/DD',
                    initialValue: currentValue !== '',
                    initialValueType: 'persian',
                    autoClose: true,
                    persianDigit: false,
                    calendar: {
                        persian: {
                            locale: 'fa',
                            leapYearMode: 'astronomical',
                        },
                    },
                    toolbox: {
                        calendarSwitch: {
                            enabled: false,
                        },
                        todayButton: {
                            enabled: true,
                            text: 'امروز',
                        },
                        submitButton: {
                            enabled: true,
                            text: 'تأیید',
                        },
                    },
                    navigator: {
                        enabled: true,
                        nextText: 'بعدی',
                        prevText: 'قبلی',
                    },
                    timePicker: {
                        enabled: false,
                    },
                });
            }
        }

        function normalizeDigitString(value) {
            if (typeof value !== 'string') {
                return '';
            }

            const persianDigits = {
                '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4',
                '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9',
            };
            const arabicDigits = {
                '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
                '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9',
            };

            let normalized = '';
            for (let i = 0; i < value.length; i += 1) {
                const char = value[i];
                if (typeof persianDigits[char] !== 'undefined') {
                    normalized += persianDigits[char];
                } else if (typeof arabicDigits[char] !== 'undefined') {
                    normalized += arabicDigits[char];
                } else if (/\d/.test(char)) {
                    normalized += char;
                }

                if (normalized.length >= 4) {
                    normalized = normalized.slice(0, 4);
                    break;
                }
            }

            return normalized;
        }

        function normalizeDigitInput(element) {
            if (!element) {
                return;
            }

            const currentValue = element.value;
            const normalizedValue = normalizeDigitString(currentValue);
            if (normalizedValue !== currentValue) {
                element.value = normalizedValue;
            }
        }

        const digitNormalizerInputs = document.querySelectorAll('[data-digit-normalizer]');
        digitNormalizerInputs.forEach(function (inputElement) {
            const applyNormalization = function () {
                const selectionStart = inputElement.selectionStart;
                normalizeDigitInput(inputElement);
                if (typeof selectionStart === 'number' && inputElement === document.activeElement) {
                    const valueLength = inputElement.value.length;
                    inputElement.setSelectionRange(valueLength, valueLength);
                }
            };

            applyNormalization();
            inputElement.addEventListener('input', applyNormalization);
            inputElement.addEventListener('change', applyNormalization);
            inputElement.addEventListener('blur', applyNormalization);
        });

        function toggleCustomInput(selectElement) {
            if (!selectElement) {
                return;
            }

            const customContainerSelector = selectElement.getAttribute('data-custom-container');
            if (!customContainerSelector) {
                return;
            }

            const container = document.querySelector(customContainerSelector);
            if (!container) {
                return;
            }

            const customInput = container.querySelector('input[type="text"]');
            if (!customInput) {
                return;
            }

            if (selectElement.value === '__custom__') {
                container.classList.add('active');
                customInput.disabled = false;
                customInput.focus();
            } else {
                container.classList.remove('active');
                customInput.disabled = true;
            }
        }

        const modelSelects = document.querySelectorAll('[data-model-select]');
        modelSelects.forEach(function (selectElement) {
            toggleCustomInput(selectElement);
            selectElement.addEventListener('change', function () {
                toggleCustomInput(selectElement);
            });
        });
    });
SCRIPT;

include __DIR__ . '/../../../layouts/organization-header.php';
include __DIR__ . '/../../../layouts/organization-sidebar.php';

$navbarUser = $user;
?>

<?php include __DIR__ . '/../../../layouts/organization-navbar.php'; ?>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="row g-4">
            <div class="col-12 col-xl-10 mx-auto">
                <div class="card evaluation-form-card shadow-sm h-100">
                    <div class="card-body p-24">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-16 mb-24">
                            <div>
                                <h2 class="mb-6 text-gray-900"><?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                                <p class="mb-0">اطلاعات ارزیابی را تکمیل کنید و ابزارهای ارزیابی مرتبط را به ترتیب مورد نظر خود تنظیم نمایید.</p>
                            </div>
                            <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-6">
                                <ion-icon name="arrow-back-outline"></ion-icon>
                                بازگشت به ماتریس
                            </a>
                        </div>

                        <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="needs-validation" novalidate>
                            <?= csrf_field(); ?>
                            <?php if ($isEdit && !empty($evaluation['id'])): ?>
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $evaluation['id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>

                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label for="evaluation-title" class="form-label">عنوان ارزیابی <span class="required">*</span></label>
                                    <input type="text" class="form-control<?= isset($validationErrors['title']) ? ' is-invalid' : ''; ?>" id="evaluation-title" name="title" placeholder="مثال: ارزیابی دوره‌ای مدیران" value="<?= htmlspecialchars($oldTitle, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <?php if (isset($validationErrors['title'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="evaluation-date" class="form-label">تاریخ ارزیابی</label>
                                    <input type="text" class="form-control<?= isset($validationErrors['evaluation_date']) ? ' is-invalid' : ''; ?>" id="evaluation-date" name="evaluation_date" placeholder="مثال: 1404/07/14" value="<?= htmlspecialchars($oldDateForInput, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off"<?= $evaluationDateLocked ? ' readonly' : ''; ?>>
                                    <?php if ($evaluationDateLocked && $lockedEvaluationDateRaw !== null): ?>
                                        <input type="hidden" name="locked_evaluation_date" value="<?= htmlspecialchars(str_replace('/', '-', UtilityHelper::persianToEnglish($lockedEvaluationDateRaw)), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php endif; ?>
                                    <?php if ($evaluationDateLocked): ?>
                                        <div class="locked-date-badge">
                                            <ion-icon name="lock-closed-outline"></ion-icon>
                                            <span>این تاریخ از تقویم انتخاب شده و قابل تغییر نیست.</span>
                                            <?php if ($lockedDateDisplaySafe !== null): ?>
                                                <span class="locked-date-value"><?= htmlspecialchars($lockedDateDisplaySafe, ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-text">برای انتخاب تاریخ از تقویم شمسی استفاده کنید یا در صورت نامشخص بودن خالی بگذارید.</div>
                                    <?php endif; ?>
                                    <?php if (isset($validationErrors['evaluation_date'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors['evaluation_date'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="general-model" class="form-label">مدل ارزیابی عمومی</label>
                                    <div class="model-select-wrapper">
                                        <select class="form-select" id="general-model" name="general_model" data-placeholder="انتخاب مدل عمومی">
                                            <option value=""<?= $generalModelSelectedValue === '' ? ' selected' : ''; ?>>یک مدل را انتخاب کنید</option>
                                            <?php if ($generalModelFallbackOption !== null): ?>
                                                <option value="<?= htmlspecialchars($generalModelFallbackOption, ENT_QUOTES, 'UTF-8'); ?>" selected><?= htmlspecialchars($generalModelFallbackOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endif; ?>
                                            <?php foreach ($generalModelTableOptions as $suggestion): ?>
                                                <?php
                                                    $value = trim((string) ($suggestion['value'] ?? ''));
                                                    if ($value === '') {
                                                        continue;
                                                    }
                                                    $label = trim((string) ($suggestion['label'] ?? $value));
                                                    if ($label === '') {
                                                        $label = $value;
                                                    }
                                                    $code = trim((string) ($suggestion['code'] ?? ''));
                                                    $description = trim((string) ($suggestion['description'] ?? ''));
                                                    $optionLabel = $label;
                                                    if ($code !== '') {
                                                        $codeComparable = function_exists('mb_strtolower') ? mb_strtolower($code, 'UTF-8') : strtolower($code);
                                                        $labelComparable = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
                                                        if ($codeComparable !== $labelComparable) {
                                                            $optionLabel .= ' (' . $code . ')';
                                                        }
                                                    }
                                                    if ($generalModelFallbackOption !== null && $generalModelFallbackOption === $value) {
                                                        continue;
                                                    }
                                                    $isSelected = $generalModelSelectedValue === $value;
                                                ?>
                                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?= $isSelected ? ' selected' : ''; ?><?= $description !== '' ? ' title="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>><?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-text">از فهرست گزینه‌های موجود یک مدل عمومی را انتخاب کنید.</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="specific-model" class="form-label">مدل ارزیابی اختصاصی</label>
                                    <div class="model-select-wrapper">
                                        <select class="form-select" id="specific-model" name="specific_model" data-model-select data-custom-container="#specific-model-custom" data-placeholder="انتخاب مدل اختصاصی">
                                            <option value=""<?= $specificModelSelectValue === '' ? ' selected' : ''; ?>>یک مدل را انتخاب کنید</option>
                                            <?php foreach ($specificModelSuggestions as $suggestion): ?>
                                                <?php
                                                    $value = trim((string) ($suggestion['value'] ?? ''));
                                                    if ($value === '') {
                                                        continue;
                                                    }
                                                    $label = trim((string) ($suggestion['label'] ?? $value));
                                                    if ($label === '') {
                                                        $label = $value;
                                                    }
                                                    $code = trim((string) ($suggestion['code'] ?? ''));
                                                    $description = trim((string) ($suggestion['description'] ?? ''));
                                                    $optionLabel = $label;
                                                    if ($code !== '') {
                                                        $codeComparable = function_exists('mb_strtolower') ? mb_strtolower($code, 'UTF-8') : strtolower($code);
                                                        $labelComparable = function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
                                                        if ($codeComparable !== $labelComparable) {
                                                            $optionLabel .= ' (' . $code . ')';
                                                        }
                                                    }
                                                    $isSelected = $specificModelSelectValue !== '__custom__' && $specificModelSelectValue === $value;
                                                ?>
                                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"<?= $isSelected ? ' selected' : ''; ?><?= $description !== '' ? ' title="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>><?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                            <option value="__custom__"<?= $specificModelSelectValue === '__custom__' ? ' selected' : ''; ?>>مدل دلخواه...</option>
                                        </select>
                                        <div id="specific-model-custom" class="model-custom-input mt-2<?= $isSpecificCustom ? ' active' : ''; ?>">
                                            <label for="specific-model-custom-input" class="form-label small mb-2">نام مدل اختصاصی دلخواه</label>
                                            <input type="text" class="form-control" id="specific-model-custom-input" name="specific_model_custom" value="<?= htmlspecialchars($specificModelCustomValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: مدل ارزیابی مهارت‌های تخصصی"<?= $isSpecificCustom ? '' : ' disabled'; ?>>
                                        </div>
                                    </div>
                                    <div class="form-text">مدل‌های اختصاصی موجود را انتخاب کنید یا گزینه «مدل دلخواه...» را برای ثبت عنوان جدید برگزینید.</div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label for="evaluators" class="form-label">ارزیاب‌ها <span class="required">*</span></label>
                                    <select class="form-select multiple-select js-searchable-multiselect w-100<?= isset($validationErrors['evaluators']) ? ' is-invalid' : ''; ?>" id="evaluators" name="evaluators[]" multiple data-placeholder="انتخاب ارزیاب" data-width="100%" required>
                                        <?php foreach ($organizationEvaluators as $orgUser): ?>
                                            <?php
                                                $userId = (int) ($orgUser['id'] ?? 0);
                                                if ($userId <= 0) {
                                                    continue;
                                                }
                                                $fullName = trim((string) ($orgUser['full_name'] ?? ''));
                                                if ($fullName === '') {
                                                    $fullName = trim((string) ($orgUser['username'] ?? ''));
                                                }
                                                if ($fullName === '') {
                                                    $fullName = 'کاربر #' . $userId;
                                                }
                                                $isSelected = in_array($userId, $oldEvaluators, true);
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8'); ?>"<?= $isSelected ? ' selected' : ''; ?>><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">با تایپ نام یا نام کاربری، ارزیاب‌های مورد نظر را جستجو و انتخاب کنید.</div>
                                    <?php if (isset($validationErrors['evaluators'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors['evaluators'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label for="evaluatees" class="form-label">ارزیاب‌شونده‌ها <span class="required">*</span></label>
                                    <select class="form-select multiple-select js-searchable-multiselect w-100<?= isset($validationErrors['evaluatees']) ? ' is-invalid' : ''; ?>" id="evaluatees" name="evaluatees[]" multiple data-placeholder="انتخاب ارزیاب‌شونده" data-width="100%" required>
                                        <?php foreach ($organizationEvaluatees as $orgUser): ?>
                                            <?php
                                                $userId = (int) ($orgUser['id'] ?? 0);
                                                if ($userId <= 0) {
                                                    continue;
                                                }
                                                $fullName = trim((string) ($orgUser['full_name'] ?? ''));
                                                if ($fullName === '') {
                                                    $fullName = trim((string) ($orgUser['username'] ?? ''));
                                                }
                                                if ($fullName === '') {
                                                    $fullName = 'کاربر #' . $userId;
                                                }
                                                $isSelected = in_array($userId, $oldEvaluatees, true);
                                            ?>
                                            <option value="<?= htmlspecialchars((string) $userId, ENT_QUOTES, 'UTF-8'); ?>"<?= $isSelected ? ' selected' : ''; ?>><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">با جستجو کاربران مناسب را پیدا کرده و به فهرست ارزیاب‌شونده‌ها اضافه کنید.</div>
                                    <?php if (isset($validationErrors['evaluatees'])): ?>
                                        <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors['evaluatees'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">ابزارهای ارزشیابی</label>
                                    <p class="evaluation-meta mb-3">برای هر ابزار یک ترتیب (ردیف) تعیین کنید. ابزارهای بدون ترتیب لحاظ نخواهند شد.</p>

                                    <?php if (!empty($evaluationTools)): ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($evaluationTools as $tool): ?>
                                                <?php
                                                    $toolId = (int) ($tool['id'] ?? 0);
                                                    if ($toolId <= 0) {
                                                        continue;
                                                    }
                                                    $toolName = htmlspecialchars($tool['name'] ?? ('ابزار #' . $toolId), ENT_QUOTES, 'UTF-8');
                                                    $orderValue = $toolOrders[$toolId] ?? '';
                                                    $orderValueDisplay = htmlspecialchars($orderValue, ENT_QUOTES, 'UTF-8');
                                                    $errorKey = 'tools.' . $toolId;
                                                    $hasError = isset($validationErrors[$errorKey]);
                                                ?>
                                                <div class="tool-row d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-12">
                                                    <div>
                                                        <div class="fw-semibold text-gray-900"><?= $toolName; ?></div>
                                                        <?php if (!empty($tool['question_type'])): ?>
                                                            <div class="evaluation-meta">نوع سؤال: <?= htmlspecialchars($tool['question_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <label class="form-label mb-0" for="tool-order-<?= htmlspecialchars((string) $toolId, ENT_QUOTES, 'UTF-8'); ?>">ترتیب ابزار</label>
                                                        <input type="text" inputmode="numeric" pattern="[0-9۰-۹٠-٩]*" maxlength="4" class="form-control tool-order-input<?= $hasError ? ' is-invalid' : ''; ?>" id="tool-order-<?= htmlspecialchars((string) $toolId, ENT_QUOTES, 'UTF-8'); ?>" name="tools[<?= htmlspecialchars((string) $toolId, ENT_QUOTES, 'UTF-8'); ?>][order]" value="<?= $orderValueDisplay; ?>" placeholder="مثال: 1" autocomplete="off" data-digit-normalizer="true" dir="ltr">
                                                        <?php if ($hasError): ?>
                                                            <div class="invalid-feedback d-block"><?= htmlspecialchars($validationErrors[$errorKey], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning rounded-16" role="alert">
                                            هیچ ابزار ارزیابی‌ای برای سازمان شما ثبت نشده است. ابتدا از بخش ابزارهای ارزشیابی ابزار جدیدی ایجاد کنید.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-end gap-12 mt-24">
                                <a href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-secondary">
                                    انصراف
                                </a>
                                <button type="submit" class="btn btn-main">
                                    <?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../../../layouts/organization-footer.php'; ?>
    </div>
</div>
