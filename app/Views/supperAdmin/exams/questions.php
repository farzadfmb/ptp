<?php
$title = 'سوالات آزمون';
$user = (class_exists('AuthHelper') && AuthHelper::getUser()) ? AuthHelper::getUser() : ['name' => 'مدیر سیستم', 'email' => 'admin@example.com'];
$additional_js = [];

AuthHelper::startSession();
$oldInput = $_SESSION['old_input'] ?? [];
$validationErrors = $validationErrors ?? [];
$successMessage = $successMessage ?? null;
$errorMessage = $errorMessage ?? null;
$warningMessage = $warningMessage ?? null;
$infoMessage = $infoMessage ?? null;
$exam = $exam ?? [];
$questions = $questions ?? [];
$questionTypeOptions = $questionTypeOptions ?? [];

$questionErrors = $validationErrors['questions'] ?? [];

if (!function_exists('exam_is_assoc_array')) {
    function exam_is_assoc_array(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}

if (!function_exists('exam_field_label')) {
    function exam_field_label(string $key): string
    {
        $map = [
            'value' => 'کد گزینه',
            'label' => 'متن گزینه',
            'dimension' => 'بُعد',
            'score' => 'امتیاز',
            'points' => 'امتیاز',
            'best' => 'گزینه بهترین',
            'least' => 'گزینه ضعیف‌ترین',
            'answer' => 'پاسخ',
            'answer_key' => 'پاسخ',
            'text' => 'متن',
            'note' => 'یادداشت',
            'option_scores' => 'امتیاز گزینه‌ها',
            'option_dimensions' => 'ابعاد گزینه‌ها',
            'response_mode' => 'حالت پاسخ‌دهی',
            'skill' => 'مهارت',
            'difficulty' => 'سطح دشواری',
            'explanation' => 'توضیح',
            'dimension_pair' => 'جفت بُعد',
            'key' => 'کلید',
            'metadata' => 'متادیتا',
            'points_positive' => 'امتیاز مثبت',
            'points_negative' => 'امتیاز منفی',
            'dimension_positive' => 'بُعد مثبت',
            'dimension_negative' => 'بُعد منفی',
        ];

        return $map[$key] ?? $key;
    }
}

if (!function_exists('exam_string_length')) {
    function exam_string_length($value): int
    {
        $value = (string) $value;

        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }
}

if (!function_exists('exam_render_scalar_input')) {
    function exam_render_scalar_input(string $name, string $key, $value, int $level = 0): void
    {
        $longKeys = ['label', 'text', 'note', 'explanation', 'instruction'];
        $colClass = in_array($key, $longKeys, true) ? 'col-12' : 'col-md-6';
        $label = exam_field_label($key);
        $rawValue = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        $inputName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $displayValue = htmlspecialchars($rawValue ?? '', ENT_QUOTES, 'UTF-8');
        $directionKeys = ['value', 'dimension', 'points', 'answer', 'best', 'least', 'key', 'code'];
        $dir = in_array($key, $directionKeys, true) ? 'ltr' : 'auto';
        $isTextarea = in_array($key, $longKeys, true) || exam_string_length($rawValue) > 200;
        $rows = $isTextarea ? (exam_string_length($rawValue) > 400 ? 6 : 4) : 1;

        echo '<div class="' . $colClass . '">';
        echo '<label class="form-label fw-semibold text-end d-block">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
        if ($isTextarea) {
            echo '<textarea name="' . $inputName . '" class="form-control" rows="' . $rows . '" dir="' . $dir . '">' . $displayValue . '</textarea>';
        } else {
            echo '<input type="text" name="' . $inputName . '" class="form-control" value="' . $displayValue . '" dir="' . $dir . '">';
        }
        echo '</div>';
    }
}

if (!function_exists('exam_render_nested_inputs')) {
    function exam_render_nested_inputs(string $baseName, $data, int $level = 0): void
    {
        if (!is_array($data)) {
            return;
        }

        $isAssoc = exam_is_assoc_array($data);

        if (!$isAssoc) {
            foreach ($data as $index => $value) {
                $childBase = $baseName . '[' . $index . ']';
                echo '<div class="border rounded-12 p-16 mb-16">';
                echo '<div class="d-flex justify-content-between align-items-center mb-12">';
                $title = $level === 0
                    ? 'آیتم ' . UtilityHelper::englishToPersian((int) $index + 1)
                    : exam_field_label((string) $index);
                echo '<span class="fw-semibold text-gray-700">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>';
                echo '</div>';
                exam_render_nested_inputs($childBase, $value, $level + 1);
                echo '</div>';
            }

            return;
        }

        echo '<div class="row g-12">';
        foreach ($data as $key => $value) {
            $childBase = $baseName . '[' . $key . ']';
            if (is_array($value)) {
                echo '<div class="col-12">';
                echo '<div class="border rounded-12 p-16 mb-12">';
                echo '<span class="fw-semibold d-block mb-12 text-gray-700">' . htmlspecialchars(exam_field_label((string) $key), ENT_QUOTES, 'UTF-8') . '</span>';
                exam_render_nested_inputs($childBase, $value, $level + 1);
                echo '</div>';
                echo '</div>';
            } else {
                exam_render_scalar_input($childBase, (string) $key, $value, $level);
            }
        }
        echo '</div>';
    }
}

if (!function_exists('exam_prepare_option_pairs')) {
    function exam_prepare_option_pairs(array $options): array
    {
        $pairs = [];

        foreach ($options as $key => $value) {
            if (is_array($value) && isset($value['value']) && isset($value['label'])) {
                $pairs[] = $value;
                continue;
            }

            if (is_array($value)) {
                $pairs[] = [
                    'value' => $key,
                    'label' => json_encode($value, JSON_UNESCAPED_UNICODE),
                ];
                continue;
            }

            $pairs[] = [
                'value' => $key,
                'label' => (string) $value,
            ];
        }

        return $pairs;
    }
}

include __DIR__ . '/../../layouts/admin-header.php';
include __DIR__ . '/../../layouts/admin-sidebar.php';
?>

<div class="dashboard-main-wrapper">
    <?php include __DIR__ . '/../../layouts/admin-navbar.php'; ?>

    <div class="dashboard-body">
        <div class="row gy-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-end">
                        <div class="mb-16">
                            <h3 class="mb-4">مدیریت سوالات آزمون</h3>
                            <p class="text-gray-500 mb-0">
                                در این بخش می‌توانید سوالات و گزینه‌های آزمون «<?= htmlspecialchars($exam['title'] ?? '---', ENT_QUOTES, 'UTF-8'); ?>» را مشاهده و ویرایش کنید.
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-end d-block">وزن سوال</label>
                                                        <input type="number" name="questions[<?= $questionId; ?>][weight]" class="form-control" step="0.01" value="<?= htmlspecialchars((string) $weight, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 1">
                                                        <?php if (!empty($questionError['weight'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['weight']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                            </p>
                        </div>
                        <div class="d-flex justify-content-between flex-wrap gap-12 mb-16">
                            <div>
                                <a href="<?= UtilityHelper::baseUrl('supperadmin/exams'); ?>" class="btn btn-outline-main rounded-pill px-20">
                                    <i class="fas fa-arrow-right ms-6"></i>
                                    بازگشت به فهرست آزمون‌ها
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-8 text-gray-500">
                                <span class="badge bg-main-50 text-main-600 rounded-pill py-8 px-16">
                                    مجموع سوالات: <?= UtilityHelper::englishToPersian(count($questions)); ?>
                                </span>
                                <span class="badge bg-gray-100 text-gray-600 rounded-pill py-8 px-16">
                                    نوع آزمون: <?= htmlspecialchars($exam['type'] ?? '---', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($successMessage)): ?>
                            <div class="alert alert-success rounded-12 text-end" role="alert">
                                <i class="fas fa-check-circle ms-6"></i>
                                <?= $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($warningMessage)): ?>
                            <div class="alert alert-warning rounded-12 text-end" role="alert">
                                <i class="fas fa-info-circle ms-6"></i>
                                <?= $warningMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($infoMessage)): ?>
                            <div class="alert alert-info rounded-12 text-end" role="alert">
                                <i class="fas fa-lightbulb ms-6"></i>
                                <?= $infoMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger rounded-12 text-end" role="alert">
                                <i class="fas fa-exclamation-triangle ms-6"></i>
                                <?= $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <form action="<?= UtilityHelper::baseUrl('supperadmin/exams/questions'); ?>" method="post" class="card" novalidate>
                    <div class="card-body">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="exam_id" value="<?= (int) ($exam['id'] ?? 0); ?>">

                        <?php if (empty($questions)): ?>
                            <div class="py-48 text-center text-gray-500">
                                <i class="fas fa-question-circle mb-16 text-3xl d-block"></i>
                                هنوز سوالی برای این آزمون ثبت نشده است.
                            </div>
                        <?php else: ?>
                            <div class="accordion" id="examQuestionsAccordion">
                                <?php foreach ($questions as $index => $question):
                                    $questionId = (int) $question['id'];
                                    $panelId = 'questionPanel' . $questionId;
                                    $headingId = 'questionHeading' . $questionId;
                                    $questionError = $questionErrors[$questionId] ?? [];
                                    $hasErrors = !empty($questionError);

                                    $oldQuestionData = $oldInput['questions'][$questionId] ?? [];

                                    $questionText = $oldQuestionData['question_text'] ?? $question['question_text'];
                                    $questionCode = $oldQuestionData['question_code'] ?? $question['question_code'];
                                    $questionType = $oldQuestionData['question_type'] ?? $question['question_type'];
                                    $answerKey = $oldQuestionData['answer_key'] ?? $question['answer_key'];
                                    $weight = $oldQuestionData['weight'] ?? $question['weight'];
                                    $optionsOriginal = $question['options_json'] ?? null;
                                    $metadataOriginal = $question['metadata_json'] ?? null;

                                    $optionsDecoded = is_string($optionsOriginal) ? json_decode($optionsOriginal, true) : ($optionsOriginal ?? []);
                                    if (!is_array($optionsDecoded)) {
                                        $optionsDecoded = [];
                                    }

                                    $metadataDecoded = is_string($metadataOriginal) ? json_decode($metadataOriginal, true) : ($metadataOriginal ?? []);
                                    if (!is_array($metadataDecoded)) {
                                        $metadataDecoded = [];
                                    }

                                    $defaultOptionsFormat = exam_is_assoc_array($optionsDecoded) ? 'map' : 'list';
                                    $optionsFormat = $oldQuestionData['options_format'] ?? $defaultOptionsFormat;

                                    if (isset($oldQuestionData['options'])) {
                                        $optionsForForm = $oldQuestionData['options'];
                                    } elseif (isset($oldQuestionData['options_json'])) {
                                        $legacyOptions = json_decode($oldQuestionData['options_json'], true);
                                        $optionsForForm = is_array($legacyOptions)
                                            ? ($optionsFormat === 'map' ? exam_prepare_option_pairs($legacyOptions) : $legacyOptions)
                                            : [];
                                    } else {
                                        $optionsForForm = $optionsFormat === 'map' ? exam_prepare_option_pairs($optionsDecoded) : $optionsDecoded;
                                    }

                                    $metadataForForm = $oldQuestionData['metadata'] ?? null;
                                    if ($metadataForForm === null && isset($oldQuestionData['metadata_json'])) {
                                        $legacyMetadata = json_decode($oldQuestionData['metadata_json'], true);
                                        $metadataForForm = is_array($legacyMetadata) ? $legacyMetadata : [];
                                    }
                                    if ($metadataForForm === null) {
                                        $metadataForForm = $metadataDecoded;
                                    }
                                    if (!is_array($metadataForForm)) {
                                        $metadataForForm = [];
                                    }

                                    if (!is_array($optionsForForm)) {
                                        $optionsForForm = [];
                                    }

                                    $optionsCount = is_array($optionsDecoded) ? count($optionsDecoded) : 0;
                                ?>
                                    <div class="accordion-item border rounded-16 mb-16 overflow-hidden <?= $hasErrors ? 'border-danger' : 'border-gray-100'; ?>">
                                        <h2 class="accordion-header" id="<?= $headingId; ?>">
                                            <button class="accordion-button d-flex justify-content-between align-items-center text-end <?= $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $panelId; ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?= $panelId; ?>">
                                                <div class="text-end">
                                                    <strong class="d-block text-gray-900">
                                                        سوال <?= UtilityHelper::englishToPersian($index + 1); ?><?= $questionCode ? ' - ' . htmlspecialchars($questionCode, ENT_QUOTES, 'UTF-8') : ''; ?>
                                                    </strong>
                                                    <small class="text-gray-500">
                                                        نوع سوال: <?= htmlspecialchars($questionType !== '' ? $questionType : 'نامشخص', ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                </div>
                                                <span class="badge rounded-pill <?= $hasErrors ? 'bg-danger-50 text-danger-700' : 'bg-main-25 text-main-600'; ?>">
                                                    <?= $hasErrors ? 'نیاز به بررسی' : 'بدون خطا'; ?>
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="<?= $panelId; ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : ''; ?>" aria-labelledby="<?= $headingId; ?>" data-bs-parent="#examQuestionsAccordion">
                                            <div class="accordion-body">
                                                <?php if (!empty($questionError['general'])): ?>
                                                    <div class="alert alert-danger text-end" role="alert">
                                                        <i class="fas fa-exclamation-triangle ms-6"></i>
                                                        <?= $questionError['general']; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="row g-16">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-end d-block">کد سوال</label>
                                                        <input type="text" name="questions[<?= $questionId; ?>][question_code]" class="form-control" value="<?= htmlspecialchars((string) $questionCode, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: MBTI-Q01">
                                                        <?php if (!empty($questionError['question_code'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['question_code']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-end d-block">نوع سوال <span class="text-danger">*</span></label>
                                                        <select name="questions[<?= $questionId; ?>][question_type]" class="form-select" required>
                                                            <option value="">انتخاب کنید...</option>
                                                            <?php foreach ($questionTypeOptions as $typeOption): ?>
                                                                <option value="<?= htmlspecialchars($typeOption, ENT_QUOTES, 'UTF-8'); ?>" <?= (string) $questionType === (string) $typeOption ? 'selected' : ''; ?>>
                                                                    <?= htmlspecialchars($typeOption, ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <?php if (!empty($questionError['question_type'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['question_type']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold text-end d-block">متن سوال <span class="text-danger">*</span></label>
                                                        <textarea name="questions[<?= $questionId; ?>][question_text]" class="form-control" rows="4" placeholder="متن دقیق سوال را وارد کنید." required><?= htmlspecialchars((string) $questionText, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                        <?php if (!empty($questionError['question_text'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['question_text']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-end d-block">پاسخ صحیح</label>
                                                        <input type="text" name="questions[<?= $questionId; ?>][answer_key]" class="form-control" value="<?= htmlspecialchars((string) $answerKey, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: A">
                                                        <?php if (!empty($questionError['answer_key'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['answer_key']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold text-end d-block">وزن سوال</label>
                                                        <input type="number" name="questions[<?= $questionId; ?>][weight]" class="form-control" step="0.01" value="<?= htmlspecialchars((string) $weight, ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثال: 1">
                                                        <?php if (!empty($questionError['weight'])): ?>
                                                            <small class="text-danger d-block mt-6 text-end"><?= $questionError['weight']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="border rounded-16 p-16">
                                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-12 mb-12">
                                                                <div class="d-flex align-items-center gap-8 text-gray-700">
                                                                    <i class="fas fa-list-ul ms-6"></i>
                                                                    <span class="fw-semibold">گزینه‌های سوال</span>
                                                                </div>
                                                                <span class="badge bg-main-25 text-main-600 rounded-pill">
                                                                    تعداد: <?= UtilityHelper::englishToPersian($optionsCount); ?>
                                                                </span>
                                                            </div>
                                                            <input type="hidden" name="questions[<?= $questionId; ?>][options_format]" value="<?= htmlspecialchars($optionsFormat, ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?php if (!empty($questionError['options'])): ?>
                                                                <div class="alert alert-danger text-end" role="alert">
                                                                    <i class="fas fa-exclamation-triangle ms-6"></i>
                                                                    <?= $questionError['options']; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (empty($optionsForForm)): ?>
                                                                <p class="text-gray-500 text-end mb-0">گزینه‌ای برای این سوال ثبت نشده است.</p>
                                                            <?php else: ?>
                                                                <?php exam_render_nested_inputs('questions[' . $questionId . '][options]', $optionsForForm); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="border rounded-16 p-16">
                                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-12 mb-12">
                                                                <div class="d-flex align-items-center gap-8 text-gray-700">
                                                                    <i class="fas fa-sitemap ms-6"></i>
                                                                    <span class="fw-semibold">متادیتا</span>
                                                                </div>
                                                            </div>
                                                            <?php if (!empty($questionError['metadata'])): ?>
                                                                <div class="alert alert-danger text-end" role="alert">
                                                                    <i class="fas fa-exclamation-triangle ms-6"></i>
                                                                    <?= $questionError['metadata']; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (empty($metadataForForm)): ?>
                                                                <p class="text-gray-500 text-end mb-0">متادیتایی برای این سوال ثبت نشده است.</p>
                                                            <?php else: ?>
                                                                <?php exam_render_nested_inputs('questions[' . $questionId . '][metadata]', $metadataForForm); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="border-top pt-20 mt-32 text-end d-flex justify-content-end gap-12">
                            <a href="<?= UtilityHelper::baseUrl('supperadmin/exams'); ?>" class="btn btn-outline-main rounded-pill px-24">
                                انصراف
                            </a>
                            <button type="submit" class="btn btn-main rounded-pill px-24" <?= empty($questions) ? 'disabled' : ''; ?>>
                                <i class="fas fa-save ms-6"></i>
                                ذخیره تغییرات سوالات
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../layouts/admin-footer.php'; ?>
</div>

<?php unset($_SESSION['old_input']); ?>
