<?php
$title = 'ثبت آزمون جدید';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

AuthHelper::startSession();
$oldInput = $_SESSION['old_input'] ?? [];
$validationErrors = $validationErrors ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$examTypeDefinitions = $examTypeDefinitions ?? [];
$defaultExamType = array_key_first($examTypeDefinitions);
$selectedExamType = old('exam_type', $defaultExamType);

$fieldError = function (string $field) use ($validationErrors) {
    return $validationErrors[$field] ?? null;
};

$examTypeDefaults = [];
foreach ($examTypeDefinitions as $typeKey => $definition) {
    foreach (($definition['fields'] ?? []) as $field) {
        if (array_key_exists('default', $field)) {
            $examTypeDefaults[$typeKey][$field['name']] = $field['default'];
        }
    }
}

$inline_styles = ($inline_styles ?? '') . <<<CSS
.exam-type-option {
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.25s ease;
    cursor: pointer;
}
.exam-type-option.is-active {
    border-color: rgba(64, 117, 255, 0.35) !important;
    box-shadow: 0 10px 30px rgba(64, 117, 255, 0.08);
}
.exam-type-option:hover {
    border-color: rgba(64, 117, 255, 0.25);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
}
.exam-type-fields {
    background: #f8faff;
}
CSS;

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body pb-0 text-end">
                        <div class="mb-16">
                            <h3 class="mb-4">ثبت آزمون جدید</h3>
                            <p class="text-gray-500 mb-0">نوع آزمون مورد نظر را انتخاب و اطلاعات لازم را تکمیل کنید.</p>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end" role="alert">
                                <i class="fas fa-check-circle ms-6"></i>
                                <?= $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end" role="alert">
                                <i class="fas fa-exclamation-triangle ms-6"></i>
                                <?= $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($validationErrors) && empty($errorMessage)): ?>
                            <div class="alert alert-warning rounded-12 text-end" role="alert">
                                <i class="fas fa-info-circle ms-6"></i>
                                لطفاً خطاهای مشخص شده در فرم را بررسی کنید.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <form action="<?= UtilityHelper::baseUrl('supperadmin/exams'); ?>" method="post" class="card" novalidate>
                    <div class="card-body text-end">
                        <?= csrf_field(); ?>

                        <div class="mb-20">
                            <h5 class="mb-8">اطلاعات پایه آزمون</h5>
                            <p class="text-13 text-gray-500 mb-0">عنوان، توضیحات و بازه زمانی آزمون را مشخص کنید.</p>
                        </div>

                        <div class="row g-16">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold text-end d-block">عنوان آزمون <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="<?= old('title'); ?>" placeholder="مثال: آزمون MBTI کارکنان" required>
                                <?php if ($fieldError('title')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('title'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold text-end d-block">وضعیت آزمون <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <?php
                                    $statuses = [
                                        'draft' => 'پیش‌نویس',
                                        'scheduled' => 'زمان‌بندی شده',
                                        'published' => 'منتشر شده',
                                        'archived' => 'بایگانی شده',
                                    ];
                                    $selectedStatus = old('status', 'draft');
                                    foreach ($statuses as $statusValue => $statusLabel):
                                    ?>
                                        <option value="<?= $statusValue; ?>" <?= $selectedStatus === $statusValue ? 'selected' : ''; ?>><?= $statusLabel; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($fieldError('status')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('status'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-end d-block">توضیحات آزمون</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="شرح کوتاهی از اهداف و نحوه برگزاری آزمون ارائه دهید."><?= old('description'); ?></textarea>
                                <?php if ($fieldError('description')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('description'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-24">

                        <div class="row g-16">
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">تاریخ شروع</label>
                                <input type="datetime-local" name="start_at" class="form-control" value="<?= old('start_at'); ?>">
                                <?php if ($fieldError('start_at')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('start_at'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">تاریخ پایان</label>
                                <input type="datetime-local" name="end_at" class="form-control" value="<?= old('end_at'); ?>">
                                <?php if ($fieldError('end_at')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('end_at'); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label fw-semibold text-end d-block">نمره قبولی (۰ تا ۱۰۰)</label>
                                <input type="number" name="passing_score" class="form-control" min="0" max="100" step="0.1" value="<?= old('passing_score'); ?>" placeholder="مثال: ۷۰">
                                <?php if ($fieldError('passing_score')): ?>
                                    <small class="text-danger d-block mt-6 text-end"><?= $fieldError('passing_score'); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-32">

                        <div class="mb-20">
                            <h5 class="mb-8">انتخاب نوع آزمون</h5>
                            <p class="text-13 text-gray-500 mb-0">نوع آزمون را انتخاب کنید تا فیلدهای اختصاصی آن نمایش داده شود.</p>
                            <?php if ($fieldError('exam_type')): ?>
                                <small class="text-danger d-block mt-6 text-end"><?= $fieldError('exam_type'); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="row g-16 exam-type-options">
                            <?php foreach ($examTypeDefinitions as $typeKey => $definition):
                                $icon = $definition['icon'] ?? 'fa-file-alt';
                                $label = $definition['label'] ?? $typeKey;
                                $description = $definition['description'] ?? '';
                                $isActive = $selectedExamType === $typeKey;
                            ?>
                                <div class="col-xl-4 col-md-6">
                                    <label class="w-100">
                                        <input type="radio" name="exam_type" value="<?= $typeKey; ?>" class="d-none exam-type-radio" <?= $isActive ? 'checked' : ''; ?> required>
                                        <div class="card border exam-type-option <?= $isActive ? 'is-active' : ''; ?>">
                                            <div class="card-body text-end">
                                                <div class="d-flex justify-content-between align-items-center mb-12">
                                                    <div class="icon flex-shrink-0 ms-12 text-main-500">
                                                        <i class="fas <?= $icon; ?> text-2xl"></i>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" tabindex="-1" <?= $isActive ? 'checked' : ''; ?>>
                                                    </div>
                                                </div>
                                                <h6 class="mb-8 text-gray-900"><?= $label; ?></h6>
                                                <?php if ($description): ?>
                                                    <p class="mb-0 text-13 text-gray-500"><?= $description; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-32">
                            <?php foreach ($examTypeDefinitions as $typeKey => $definition):
                                $fields = $definition['fields'] ?? [];
                            ?>
                                <div class="exam-type-fields border rounded-16 p-20 mb-24 <?= $selectedExamType === $typeKey ? '' : 'd-none'; ?>" data-exam-type="<?= $typeKey; ?>">
                                    <div class="d-flex justify-content-between flex-wrap gap-12 align-items-center mb-16">
                                        <div class="text-end">
                                            <h6 class="mb-4 text-gray-900">فیلدهای اختصاصی <?= $definition['label'] ?? $typeKey; ?></h6>
                                            <?php if (!empty($definition['description'])): ?>
                                                <p class="mb-0 text-13 text-gray-500">اطلاعات متناسب با این آزمون را تکمیل کنید.</p>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge bg-main-50 text-main-600 rounded-pill px-16 py-8">نوع آزمون: <?= $definition['label'] ?? $typeKey; ?></span>
                                    </div>
                                    <div class="row g-16">
                                        <?php foreach ($fields as $field):
                                            $fieldName = $field['name'];
                                            $fieldLabel = $field['label'] ?? $fieldName;
                                            $fieldType = $field['type'] ?? 'text';
                                            $fieldAttributes = $field['attributes'] ?? [];
                                            $fieldHelp = $field['help'] ?? '';
                                            $options = $field['options'] ?? [];
                                            $fieldDefault = $field['default'] ?? null;
                                            $value = old($fieldName, $fieldDefault);
                                            $error = $fieldError($fieldName);

                                            $inputAttributes = '';
                                            foreach ($fieldAttributes as $attrKey => $attrValue) {
                                                $inputAttributes .= ' ' . $attrKey . '="' . htmlspecialchars((string) $attrValue, ENT_QUOTES, 'UTF-8') . '"';
                                            }
                                        ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-end d-block">
                                                    <?= $fieldLabel; ?><?= !empty($field['required']) ? ' <span class="text-danger">*</span>' : ''; ?>
                                                </label>
                                                <?php if ($fieldType === 'select'): ?>
                                                    <select name="<?= $fieldName; ?>" class="form-select" <?= !empty($field['required']) ? 'required' : ''; ?>>
                                                        <option value="">انتخاب کنید...</option>
                                                        <?php foreach ($options as $optionValue => $optionLabel): ?>
                                                            <option value="<?= htmlspecialchars((string) $optionValue, ENT_QUOTES, 'UTF-8'); ?>" <?= (string) $value === (string) $optionValue ? 'selected' : ''; ?>>
                                                                <?= htmlspecialchars((string) $optionLabel, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php elseif ($fieldType === 'number'): ?>
                                                    <input type="number" name="<?= $fieldName; ?>" class="form-control" value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= !empty($field['required']) ? 'required' : ''; ?><?= $inputAttributes; ?>>
                                                <?php elseif ($fieldType === 'checkbox'): ?>
                                                    <div class="form-check form-switch text-end">
                                                        <input type="checkbox" class="form-check-input" id="<?= $fieldName; ?>" name="<?= $fieldName; ?>" value="1" <?= !empty($value) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="<?= $fieldName; ?>"><?= $fieldLabel; ?></label>
                                                    </div>
                                                <?php else: ?>
                                                    <input type="text" name="<?= $fieldName; ?>" class="form-control" value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?>" <?= !empty($field['required']) ? 'required' : ''; ?><?= $inputAttributes; ?>>
                                                <?php endif; ?>
                                                <?php if ($fieldHelp && $fieldType !== 'checkbox'): ?>
                                                    <small class="d-block text-gray-500 mt-6 text-end"><?= $fieldHelp; ?></small>
                                                <?php endif; ?>
                                                <?php if ($error): ?>
                                                    <small class="text-danger d-block mt-6 text-end"><?= $error; ?></small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-12 mt-32">
                            <a href="<?= UtilityHelper::baseUrl('supperadmin/exams'); ?>" class="btn btn-outline-main rounded-pill px-24">
                                انصراف
                            </a>
                            <button type="submit" class="btn btn-main rounded-pill px-24">
                                <i class="fas fa-save ms-6"></i>
                                ذخیره و ثبت آزمون
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    $examDefaultsJson = json_encode($examTypeDefaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $inline_scripts = ($inline_scripts ?? '') . <<<JS
document.addEventListener('DOMContentLoaded', function () {
    const examDefaults = $examDefaultsJson;
    const radios = document.querySelectorAll('.exam-type-radio');
    const optionCards = document.querySelectorAll('.exam-type-option');
    const fieldGroups = document.querySelectorAll('.exam-type-fields');

    const applyDefaults = (defaults, force) => {
        Object.entries(defaults).forEach(([fieldName, defaultValue]) => {
            const field = document.querySelector('[name="' + fieldName + '"]');
            if (!field) {
                return;
            }

            const isCheckbox = field instanceof HTMLInputElement && field.type === 'checkbox';
            const hasValue = () => {
                if (isCheckbox) {
                    return field.checked;
                }
                if (field instanceof HTMLSelectElement || field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                    return field.value !== '';
                }
                return false;
            };

            if (!force && hasValue()) {
                return;
            }

            if (isCheckbox) {
                const shouldCheck = defaultValue === true || defaultValue === 1 || defaultValue === '1';
                field.checked = shouldCheck;
            } else {
                let prepared = '';
                if (defaultValue !== null && defaultValue !== undefined) {
                    prepared = String(defaultValue);
                }
                field.value = prepared;
            }
        });
    };

    const updateView = (forceDefaults = false) => {
        let selectedValue = null;
        radios.forEach((radio, index) => {
            if (radio.checked) {
                selectedValue = radio.value;
            }
            if (optionCards[index]) {
                optionCards[index].classList.toggle('is-active', radio.checked);
            }
        });

        fieldGroups.forEach(group => {
            if (!selectedValue) {
                group.classList.add('d-none');
                return;
            }
            const isActiveGroup = group.dataset.examType === selectedValue;
            group.classList.toggle('d-none', !isActiveGroup);
        });

        if (selectedValue && examDefaults[selectedValue]) {
            applyDefaults(examDefaults[selectedValue], forceDefaults);
        }
    };

    radios.forEach(radio => {
        radio.addEventListener('change', () => updateView(true));
    });

    updateView(false);
});
JS;
    include __DIR__ . '/../../layouts/admin-footer.php';
    ?>
</div>
